const { AppError } = require('./errors');
const { verifyJwt } = require('./jwt');

const TOKEN_PATH = '/api/v1/auth/token';
const JWKS_PATH = '/api/v1/auth/jwks';
const HEALTH_PATH = '/health';
const SOAP_AUDIT_PATH = '/soap/v1/audit';
const PUBLISH_PATH = '/api/v1/messages/publish';

function escapeXml(value) {
  return String(value)
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;')
    .replace(/'/g, '&apos;');
}

function cdata(value) {
  return `<![CDATA[${String(value).replaceAll(']]>', ']]]]><![CDATA[>')}]]>`;
}

function buildSoapEnvelope({ teamId, activityName, logContent }) {
  return `<?xml version="1.0" encoding="UTF-8"?>\n` +
    `<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" xmlns:iae="http://iae.central/audit">\n` +
    `  <soap:Body>\n` +
    `    <iae:AuditRequest>\n` +
    `      <iae:TeamID>${escapeXml(teamId)}</iae:TeamID>\n` +
    `      <iae:ActivityName>${escapeXml(activityName)}</iae:ActivityName>\n` +
    `      <iae:LogContent>${cdata(logContent)}</iae:LogContent>\n` +
    `    </iae:AuditRequest>\n` +
    `  </soap:Body>\n` +
    `</soap:Envelope>`;
}

function parseReceiptNumber(xml) {
  const match = String(xml || '').match(/<[^:>]*:?ReceiptNumber[^>]*>([^<]+)<\/[^:>]*:?ReceiptNumber>/i);
  return match ? match[1].trim() : null;
}

function parseSoapStatus(xml) {
  const match = String(xml || '').match(/<[^:>]*:?Status[^>]*>([^<]+)<\/[^:>]*:?Status>/i);
  return match ? match[1].trim() : null;
}

async function parseResponse(response) {
  const contentType = response.headers.get('content-type') || '';
  const text = await response.text();
  if (contentType.includes('application/json')) {
    try {
      return { raw: text, data: JSON.parse(text) };
    } catch {
      return { raw: text, data: null };
    }
  }
  return { raw: text, data: null };
}

class CentralClient {
  constructor(options) {
    this.baseUrl = options.baseUrl.replace(/\/+$/, '');
    this.apiKey = options.apiKey;
    this.teamId = options.teamId;
    this.fetchImpl = options.fetchImpl || fetch;
    this.jwksCacheMs = options.jwksCacheMs || 5 * 60 * 1000;
    this.jwksCache = null;
  }

  url(path) {
    return `${this.baseUrl}${path}`;
  }

  async health() {
    const response = await this.fetchImpl(this.url(HEALTH_PATH));
    const parsed = await parseResponse(response);
    if (!response.ok) {
      throw new AppError('Central health check failed', 502, parsed.raw);
    }
    return parsed.data || parsed.raw;
  }

  async tokenWithApiKey() {
    if (!this.apiKey) {
      throw new AppError('IAE_API_KEY is not configured', 500);
    }

    const response = await this.fetchImpl(this.url(TOKEN_PATH), {
      method: 'POST',
      headers: { 'content-type': 'application/json' },
      body: JSON.stringify({ api_key: this.apiKey }),
    });
    const parsed = await parseResponse(response);
    if (!response.ok || !parsed.data?.token) {
      throw new AppError('Failed to obtain M2M token', 502, parsed.data || parsed.raw);
    }
    return parsed.data;
  }

  async loginUser(email, password) {
    const response = await this.fetchImpl(this.url(TOKEN_PATH), {
      method: 'POST',
      headers: { 'content-type': 'application/json' },
      body: JSON.stringify({ email, password }),
    });
    const parsed = await parseResponse(response);
    if (!response.ok || !parsed.data?.token) {
      throw new AppError('SSO login failed', response.status || 502, parsed.data || parsed.raw);
    }
    return parsed.data;
  }

  async jwks() {
    const now = Date.now();
    if (this.jwksCache && this.jwksCache.expiresAt > now) {
      return this.jwksCache.value;
    }

    const response = await this.fetchImpl(this.url(JWKS_PATH));
    const parsed = await parseResponse(response);
    if (!response.ok || !parsed.data?.keys) {
      throw new AppError('Failed to fetch JWKS', 502, parsed.data || parsed.raw);
    }

    this.jwksCache = {
      value: parsed.data,
      expiresAt: now + this.jwksCacheMs,
    };
    return parsed.data;
  }

  async verifyToken(token, options = {}) {
    const jwks = await this.jwks();
    return verifyJwt(token, jwks, { issuer: 'iae-central-mock', ...options });
  }

  async submitSoapAudit({ activityName, logContent }) {
    const token = await this.tokenWithApiKey();
    const requestXml = buildSoapEnvelope({
      teamId: this.teamId,
      activityName,
      logContent,
    });

    const response = await this.fetchImpl(this.url(SOAP_AUDIT_PATH), {
      method: 'POST',
      headers: {
        authorization: `Bearer ${token.token}`,
        'content-type': 'text/xml; charset=utf-8',
      },
      body: requestXml,
    });
    const responseXml = await response.text();
    const status = parseSoapStatus(responseXml);
    const receiptNumber = parseReceiptNumber(responseXml);

    if (!response.ok || status !== 'SUCCESS' || !receiptNumber) {
      throw new AppError('SOAP audit failed', 502, {
        statusCode: response.status,
        soapStatus: status,
        responseXml,
        requestXml,
      });
    }

    return {
      requestXml,
      responseXml,
      status,
      receiptNumber,
    };
  }

  async publishEvent({ routingKey, event }) {
    const token = await this.tokenWithApiKey();
    const candidates = [
      { routing_key: routingKey, sender: this.teamId, message: event },
      { routing_key: routingKey, source: this.teamId, payload: event },
      { routingKey, sender: this.teamId, body: event },
    ];

    let lastError = null;
    for (const body of candidates) {
      const response = await this.fetchImpl(this.url(PUBLISH_PATH), {
        method: 'POST',
        headers: {
          authorization: `Bearer ${token.token}`,
          'content-type': 'application/json',
        },
        body: JSON.stringify(body),
      });
      const parsed = await parseResponse(response);

      if (response.ok) {
        return {
          request: body,
          response: parsed.data || parsed.raw || { status: response.status },
        };
      }

      lastError = {
        statusCode: response.status,
        response: parsed.data || parsed.raw,
        request: body,
      };

      if (![400, 422].includes(response.status)) {
        break;
      }
    }

    throw new AppError('RabbitMQ publish failed', 502, lastError);
  }
}

module.exports = {
  CentralClient,
  buildSoapEnvelope,
  parseReceiptNumber,
  parseSoapStatus,
};
