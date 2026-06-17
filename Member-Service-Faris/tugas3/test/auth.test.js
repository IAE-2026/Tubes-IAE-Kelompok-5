const assert = require('node:assert/strict');
const test = require('node:test');
const { generateKeyPairSync, sign } = require('node:crypto');
const { bufferToBase64url, decodeJwt, verifyJwt } = require('../src/jwt');

function makeToken({ kid = 'test-key', exp = Math.floor(Date.now() / 1000) + 3600 } = {}) {
  const { privateKey, publicKey } = generateKeyPairSync('rsa', {
    modulusLength: 2048,
  });
  const publicJwk = publicKey.export({ format: 'jwk' });
  publicJwk.kid = kid;
  publicJwk.alg = 'RS256';
  publicJwk.use = 'sig';

  const header = bufferToBase64url(JSON.stringify({ typ: 'JWT', alg: 'RS256', kid }));
  const payload = bufferToBase64url(
    JSON.stringify({
      iss: 'iae-central-mock',
      sub: 'warga38@ktp.iae.id',
      exp,
      token_type: 'user',
    }),
  );
  const signingInput = `${header}.${payload}`;
  const signature = sign('RSA-SHA256', Buffer.from(signingInput), privateKey);
  return {
    token: `${signingInput}.${bufferToBase64url(signature)}`,
    jwks: { keys: [publicJwk] },
  };
}

test('decodeJwt reads header and payload', () => {
  const { token } = makeToken();
  const decoded = decodeJwt(token);
  assert.equal(decoded.header.alg, 'RS256');
  assert.equal(decoded.payload.sub, 'warga38@ktp.iae.id');
});

test('verifyJwt accepts a valid RS256 token', async () => {
  const { token, jwks } = makeToken();
  const verified = await verifyJwt(token, jwks, { issuer: 'iae-central-mock' });
  assert.equal(verified.payload.sub, 'warga38@ktp.iae.id');
});

test('verifyJwt rejects expired tokens', async () => {
  const { token, jwks } = makeToken({ exp: Math.floor(Date.now() / 1000) - 10 });
  await assert.rejects(
    () => verifyJwt(token, jwks, { issuer: 'iae-central-mock' }),
    /expired/,
  );
});
