const fs = require('node:fs');
const os = require('node:os');
const path = require('node:path');
const { CentralClient } = require('../src/centralClient');
const { getConfig, requireConfigured } = require('../src/config');
const { createDatabase } = require('../src/database');
const { PaymentService } = require('../src/paymentService');

async function main() {
  const tmpDir = fs.mkdtempSync(path.join(os.tmpdir(), 'tugas3-live-smoke-'));
  const config = getConfig({ dbPath: path.join(tmpDir, 'smoke.sqlite') });
  requireConfigured(config);

  const database = createDatabase(config.dbPath);
  const centralClient = new CentralClient({
    baseUrl: config.baseUrl,
    apiKey: config.apiKey,
    teamId: config.teamId,
  });

  try {
    const login = await centralClient.loginUser(config.citizenEmail, config.citizenPassword);
    const verified = await centralClient.verifyToken(login.token);
    const email = verified.payload.sub || verified.payload.profile?.email;
    const role = database.getRoleByEmail(email);

    if (!role) {
      throw new Error(`No local role mapping found for ${email}`);
    }

    const paymentService = new PaymentService({
      database,
      centralClient,
      teamId: config.teamId,
    });

    const result = await paymentService.payPermit({
      permitId: 1,
      actor: { email, claims: verified.payload, role },
    });

    const searchUrl = `${config.baseUrl}/api/v1/messages/board/search?q=${encodeURIComponent(result.receiptNumber)}`;
    const boardResponse = await fetch(searchUrl);
    const board = boardResponse.ok ? await boardResponse.json() : { match_count: 0 };

    console.log(JSON.stringify(
      {
        login: login.status,
        email,
        role: role.role,
        receiptNumber: result.receiptNumber,
        routingKey: result.routingKey,
        boardMatches: board.match_count || 0,
      },
      null,
      2,
    ));
  } finally {
    database.close();
  }
}

main().catch((error) => {
  console.error(JSON.stringify(
    {
      error: error.message,
      details: error.details,
    },
    null,
    2,
  ));
  process.exitCode = 1;
});
