const assert = require('node:assert/strict');
const test = require('node:test');
const {
  buildSoapEnvelope,
  parseReceiptNumber,
  parseSoapStatus,
} = require('../src/centralClient');
const { buildPermitPaymentEvent } = require('../src/paymentService');

test('buildSoapEnvelope wraps JSON content in required SOAP tags', () => {
  const xml = buildSoapEnvelope({
    teamId: 'TEAM-38',
    activityName: 'PermitPaymentConfirmed',
    logContent: '{"permit_number":"IMB-DC-2026-0038"}',
  });

  assert.match(xml, /<iae:TeamID>TEAM-38<\/iae:TeamID>/);
  assert.match(xml, /<iae:ActivityName>PermitPaymentConfirmed<\/iae:ActivityName>/);
  assert.match(xml, /<!\[CDATA\[/);
  assert.match(xml, /IMB-DC-2026-0038/);
});

test('parseReceiptNumber extracts receipt from SOAP response', () => {
  const xml = `
    <soap:Envelope>
      <soap:Body>
        <iae:AuditResponse>
          <iae:Status>SUCCESS</iae:Status>
          <iae:ReceiptNumber>IAE-LOG-2026-8891A7BC</iae:ReceiptNumber>
        </iae:AuditResponse>
      </soap:Body>
    </soap:Envelope>
  `;

  assert.equal(parseSoapStatus(xml), 'SUCCESS');
  assert.equal(parseReceiptNumber(xml), 'IAE-LOG-2026-8891A7BC');
});

test('buildPermitPaymentEvent includes receipt, actor, and routing event', () => {
  const event = buildPermitPaymentEvent({
    teamId: 'TEAM-38',
    receiptNumber: 'IAE-LOG-2026-8891A7BC',
    occurredAt: '2026-06-12T00:00:00.000Z',
    permit: {
      id: 1,
      permitNumber: 'IMB-DC-2026-0038',
      permitType: 'Izin Mendirikan Bangunan Digital City',
      address: 'Jl. Integrasi Aplikasi No. 38',
      citizenEmail: 'warga38@ktp.iae.id',
      payment: {
        id: 1,
        amount: 350000,
        method: 'VIRTUAL_ACCOUNT',
      },
    },
    actor: {
      email: 'warga38@ktp.iae.id',
      role: {
        role: 'WARGA',
        permissions: ['permit:pay'],
      },
    },
  });

  assert.equal(event.event, 'permit.payment.confirmed');
  assert.equal(event.team_id, 'TEAM-38');
  assert.equal(event.payment.receipt_number, 'IAE-LOG-2026-8891A7BC');
  assert.deepEqual(event.approved_by.roles, ['WARGA']);
});
