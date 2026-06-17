const assert = require('node:assert/strict');
const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const test = require('node:test');
const { createDatabase } = require('../src/database');
const { AppError } = require('../src/errors');
const { PaymentService } = require('../src/paymentService');

function tmpDbPath(name) {
  const dir = fs.mkdtempSync(path.join(os.tmpdir(), `tugas3-${name}-`));
  return path.join(dir, 'test.sqlite');
}

function actor(role = 'WARGA', permissions = ['permit:read', 'permit:pay']) {
  return {
    email: 'warga38@ktp.iae.id',
    role: {
      role,
      permissions,
    },
  };
}

function serviceWithClient(client, name = 'ok') {
  const database = createDatabase(tmpDbPath(name));
  const service = new PaymentService({
    database,
    centralClient: client,
    teamId: 'TEAM-38',
    now: () => new Date('2026-06-12T00:00:00.000Z'),
  });
  return { database, service };
}

test('payPermit completes SOAP audit, payment update, and publish', async () => {
  const calls = [];
  const { database, service } = serviceWithClient({
    async submitSoapAudit(input) {
      calls.push(['soap', input.activityName]);
      return {
        requestXml: '<soap/>',
        responseXml: '<iae:ReceiptNumber>IAE-LOG-2026-ABC12345</iae:ReceiptNumber>',
        status: 'SUCCESS',
        receiptNumber: 'IAE-LOG-2026-ABC12345',
      };
    },
    async publishEvent(input) {
      calls.push(['publish', input.routingKey]);
      return {
        request: input,
        response: { status: 'published' },
      };
    },
  });

  const result = await service.payPermit({
    permitId: 1,
    actor: actor(),
    method: 'VIRTUAL_ACCOUNT',
  });

  assert.equal(result.status, 'PAID');
  assert.equal(result.receiptNumber, 'IAE-LOG-2026-ABC12345');
  assert.deepEqual(calls, [
    ['soap', 'PermitPaymentConfirmed'],
    ['publish', 'permit.payment.confirmed'],
  ]);

  const permit = database.getPermitPayment(1, 'warga38@ktp.iae.id');
  assert.equal(permit.payment.status, 'PAID');
  assert.equal(permit.payment.auditStatus, 'SUCCESS');
  assert.equal(permit.payment.publishStatus, 'SUCCESS');
  database.close();
});

test('payPermit rejects users without WARGA permit permission', async () => {
  const { database, service } = serviceWithClient({}, 'invalid-role');
  await assert.rejects(
    () => service.payPermit({ permitId: 1, actor: actor('ADMIN', ['permit:read']) }),
    /Only WARGA/,
  );
  database.close();
});

test('payPermit leaves invoice pending when SOAP fails', async () => {
  const { database, service } = serviceWithClient({
    async submitSoapAudit() {
      throw new AppError('SOAP audit failed', 502);
    },
  }, 'soap-fail');

  await assert.rejects(
    () => service.payPermit({ permitId: 1, actor: actor() }),
    /SOAP audit failed/,
  );

  const permit = database.getPermitPayment(1, 'warga38@ktp.iae.id');
  assert.equal(permit.payment.status, 'PENDING');
  database.close();
});

test('payPermit records failed publish after successful SOAP', async () => {
  const { database, service } = serviceWithClient({
    async submitSoapAudit() {
      return {
        requestXml: '<soap/>',
        responseXml: '<receipt/>',
        status: 'SUCCESS',
        receiptNumber: 'IAE-LOG-2026-ABC12345',
      };
    },
    async publishEvent() {
      throw new AppError('RabbitMQ publish failed', 502);
    },
  }, 'publish-fail');

  await assert.rejects(
    () => service.payPermit({ permitId: 1, actor: actor() }),
    /RabbitMQ publish failed/,
  );

  const permit = database.getPermitPayment(1, 'warga38@ktp.iae.id');
  assert.equal(permit.payment.status, 'PAID');
  assert.equal(permit.payment.publishStatus, 'FAILED');
  database.close();
});

test('payPermit rejects duplicate payment', async () => {
  const { database, service } = serviceWithClient({
    async submitSoapAudit() {
      return {
        requestXml: '<soap/>',
        responseXml: '<receipt/>',
        status: 'SUCCESS',
        receiptNumber: 'IAE-LOG-2026-ABC12345',
      };
    },
    async publishEvent(input) {
      return { request: input, response: { status: 'published' } };
    },
  }, 'duplicate');

  await service.payPermit({ permitId: 1, actor: actor() });
  await assert.rejects(
    () => service.payPermit({ permitId: 1, actor: actor() }),
    /already paid/,
  );
  database.close();
});
