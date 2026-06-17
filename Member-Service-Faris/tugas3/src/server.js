const fs = require('node:fs');
const http = require('node:http');
const path = require('node:path');
const { CentralClient } = require('./centralClient');
const { getConfig } = require('./config');
const { createDatabase } = require('./database');
const { AppError, AuthError } = require('./errors');
const { PaymentService } = require('./paymentService');

const PUBLIC_DIR = path.join(__dirname, '..', 'public');

function sendJson(res, statusCode, data) {
  const body = JSON.stringify(data, null, 2);
  res.writeHead(statusCode, {
    'content-type': 'application/json; charset=utf-8',
    'content-length': Buffer.byteLength(body),
  });
  res.end(body);
}

function sendText(res, statusCode, contentType, body) {
  res.writeHead(statusCode, {
    'content-type': contentType,
    'content-length': Buffer.byteLength(body),
  });
  res.end(body);
}

function contentTypeFor(filePath) {
  const ext = path.extname(filePath).toLowerCase();
  return {
    '.html': 'text/html; charset=utf-8',
    '.css': 'text/css; charset=utf-8',
    '.js': 'application/javascript; charset=utf-8',
    '.json': 'application/json; charset=utf-8',
  }[ext] || 'application/octet-stream';
}

async function readJsonBody(req) {
  const chunks = [];
  for await (const chunk of req) {
    chunks.push(chunk);
  }
  const raw = Buffer.concat(chunks).toString('utf8');
  if (!raw.trim()) {
    return {};
  }
  try {
    return JSON.parse(raw);
  } catch (error) {
    throw new AppError('Request body must be valid JSON', 400, error.message);
  }
}

function bearerToken(req) {
  const value = req.headers.authorization || '';
  const match = value.match(/^Bearer\s+(.+)$/i);
  return match ? match[1] : null;
}

async function authenticate(req, context) {
  const token = bearerToken(req);
  if (!token) {
    throw new AuthError('Missing Bearer token');
  }

  const verified = await context.centralClient.verifyToken(token);
  const email = verified.payload.sub || verified.payload.profile?.email;
  if (!email) {
    throw new AuthError('JWT does not contain citizen email');
  }

  const role = context.database.getRoleByEmail(email);
  if (!role) {
    throw new AuthError('No local role mapping found for SSO user', 403);
  }

  return {
    email,
    claims: verified.payload,
    role,
  };
}

async function handleApi(req, res, url, context) {
  if (req.method === 'GET' && url.pathname === '/health') {
    const local = context.database.getRoleByEmail(context.config.citizenEmail);
    let central = null;
    try {
      central = await context.centralClient.health();
    } catch (error) {
      central = { status: 'error', message: error.message };
    }
    return sendJson(res, 200, {
      status: 'ok',
      local_db: local ? 'ready' : 'missing-seed',
      central,
    });
  }

  if (req.method === 'POST' && url.pathname === '/api/auth/login') {
    const body = await readJsonBody(req);
    const email = body.email || context.config.citizenEmail;
    const password = body.password || context.config.citizenPassword;
    if (!email || !password) {
      throw new AppError('email and password are required', 400);
    }

    const login = await context.centralClient.loginUser(email, password);
    const verified = await context.centralClient.verifyToken(login.token);
    const mappedEmail = verified.payload.sub || verified.payload.profile?.email;
    const role = context.database.getRoleByEmail(mappedEmail);
    if (!role) {
      throw new AuthError('SSO login succeeded but local role mapping was not found', 403);
    }

    return sendJson(res, 200, {
      status: 'success',
      token: login.token,
      token_type: login.token_type,
      expires_in: login.expires_in,
      profile: login.profile || verified.payload.profile,
      claims: verified.payload,
      local_role: role,
    });
  }

  if (req.method === 'GET' && url.pathname === '/api/auth/me') {
    const actor = await authenticate(req, context);
    return sendJson(res, 200, actor);
  }

  if (req.method === 'GET' && url.pathname === '/api/permits') {
    const actor = await authenticate(req, context);
    return sendJson(res, 200, {
      data: context.paymentService.listPermits(actor),
    });
  }

  const paymentMatch = url.pathname.match(/^\/api\/permits\/(\d+)\/pay$/);
  if (req.method === 'POST' && paymentMatch) {
    const actor = await authenticate(req, context);
    const body = await readJsonBody(req);
    const result = await context.paymentService.payPermit({
      permitId: Number(paymentMatch[1]),
      actor,
      method: body.method || 'VIRTUAL_ACCOUNT',
    });
    return sendJson(res, 200, result);
  }

  throw new AppError('Route not found', 404);
}

function serveStatic(req, res, url) {
  const relativePath = url.pathname === '/' ? 'index.html' : url.pathname.slice(1);
  const target = path.normalize(path.join(PUBLIC_DIR, relativePath));
  if (!target.startsWith(PUBLIC_DIR)) {
    throw new AppError('Invalid static path', 400);
  }

  if (!fs.existsSync(target) || fs.statSync(target).isDirectory()) {
    return false;
  }

  const body = fs.readFileSync(target);
  res.writeHead(200, {
    'content-type': contentTypeFor(target),
    'content-length': body.length,
  });
  res.end(body);
  return true;
}

function createApp(context) {
  return http.createServer(async (req, res) => {
    try {
      const url = new URL(req.url, `http://${req.headers.host || 'localhost'}`);

      if (req.method === 'GET' && serveStatic(req, res, url)) {
        return;
      }

      await handleApi(req, res, url, context);
    } catch (error) {
      const statusCode = error.statusCode || 500;
      if (req.url?.startsWith('/api') || req.url === '/health') {
        sendJson(res, statusCode, {
          error: error.message || 'Internal Server Error',
          details: error.details,
        });
      } else {
        sendText(res, statusCode, 'text/plain; charset=utf-8', error.message || 'Internal Server Error');
      }
    }
  });
}

function createContext(config = getConfig()) {
  const database = createDatabase(config.dbPath);
  const centralClient = new CentralClient({
    baseUrl: config.baseUrl,
    apiKey: config.apiKey,
    teamId: config.teamId,
  });
  const paymentService = new PaymentService({
    database,
    centralClient,
    teamId: config.teamId,
  });

  return {
    config,
    database,
    centralClient,
    paymentService,
  };
}

if (require.main === module) {
  const context = createContext();
  const server = createApp(context);
  server.listen(context.config.port, () => {
    console.log(`Tugas 3 service listening on http://localhost:${context.config.port}`);
  });
}

module.exports = {
  createApp,
  createContext,
};
