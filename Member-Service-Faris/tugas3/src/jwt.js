const { webcrypto } = require('node:crypto');
const { AuthError } = require('./errors');

function base64urlToBuffer(input) {
  const normalized = input.replace(/-/g, '+').replace(/_/g, '/');
  const padded = normalized + '='.repeat((4 - (normalized.length % 4)) % 4);
  return Buffer.from(padded, 'base64');
}

function bufferToBase64url(buffer) {
  return Buffer.from(buffer)
    .toString('base64')
    .replace(/=/g, '')
    .replace(/\+/g, '-')
    .replace(/\//g, '_');
}

function parseJsonPart(part, label) {
  try {
    return JSON.parse(base64urlToBuffer(part).toString('utf8'));
  } catch (error) {
    throw new AuthError(`Invalid JWT ${label}`, 401, error.message);
  }
}

function decodeJwt(token) {
  if (!token || typeof token !== 'string') {
    throw new AuthError('Missing JWT token');
  }

  const parts = token.split('.');
  if (parts.length !== 3) {
    throw new AuthError('Invalid JWT structure');
  }

  return {
    header: parseJsonPart(parts[0], 'header'),
    payload: parseJsonPart(parts[1], 'payload'),
    signingInput: `${parts[0]}.${parts[1]}`,
    signature: base64urlToBuffer(parts[2]),
  };
}

function assertTimeClaims(payload, nowSeconds = Math.floor(Date.now() / 1000)) {
  if (payload.exp && nowSeconds >= Number(payload.exp)) {
    throw new AuthError('JWT token has expired');
  }
  if (payload.nbf && nowSeconds < Number(payload.nbf)) {
    throw new AuthError('JWT token is not active yet');
  }
}

function selectJwk(jwks, kid) {
  const keys = Array.isArray(jwks?.keys) ? jwks.keys : [];
  const key = keys.find((candidate) => candidate.kid === kid);
  if (!key) {
    throw new AuthError(`No JWKS key found for kid ${kid || '(missing)'}`);
  }
  return key;
}

async function verifyJwt(token, jwks, options = {}) {
  const decoded = decodeJwt(token);
  const { header, payload, signingInput, signature } = decoded;

  if (header.alg !== 'RS256') {
    throw new AuthError(`Unsupported JWT algorithm: ${header.alg || '(missing)'}`);
  }

  if (options.issuer && payload.iss !== options.issuer) {
    throw new AuthError('JWT issuer mismatch');
  }

  assertTimeClaims(payload, options.nowSeconds);

  const jwk = selectJwk(jwks, header.kid);
  const publicKey = await webcrypto.subtle.importKey(
    'jwk',
    jwk,
    { name: 'RSASSA-PKCS1-v1_5', hash: 'SHA-256' },
    false,
    ['verify'],
  );

  const verified = await webcrypto.subtle.verify(
    { name: 'RSASSA-PKCS1-v1_5' },
    publicKey,
    signature,
    Buffer.from(signingInput),
  );

  if (!verified) {
    throw new AuthError('JWT signature verification failed');
  }

  return { header, payload };
}

module.exports = {
  base64urlToBuffer,
  bufferToBase64url,
  decodeJwt,
  verifyJwt,
};
