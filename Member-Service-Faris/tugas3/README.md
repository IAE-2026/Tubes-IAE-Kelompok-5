# Tugas 3 - Enterprise Digital City

Individual Tugas 3 implementation for BBK2HAB3 Integrasi Aplikasi Enterprise. This repository intentionally implements only the individual Tugas 3 scope: SSO, SOAP audit, RabbitMQ event publishing, analysis documentation, and prompt log.

It does not implement the TUBES/group API gateway or merged service architecture.

## What This Service Does

The service models a critical Digital City transaction: a citizen permit payment.

1. Citizen logs in through IAE SSO.
2. The app verifies the RS256 JWT using the central JWKS.
3. The user is mapped to a local `WARGA` role.
4. The citizen pays a pending permit invoice.
5. The payment is audited to the legacy SOAP endpoint.
6. The returned `ReceiptNumber` is stored locally.
7. A `permit.payment.confirmed` JSON event is published to the central RabbitMQ board.

## Requirements

- Node.js 24+ with `node:sqlite` support.
- No npm packages are required.

If `node` is not on your PATH in Codex, use the bundled runtime:

```powershell
& "C:\Users\Faris Fadil Arifin\.cache\codex-runtimes\codex-primary-runtime\dependencies\node\bin\node.exe" src/server.js
```

## Setup

Create `.env` from `.env.example` and fill in the real credentials.

```powershell
Copy-Item .env.example .env
```

Required values:

```env
IAE_BASE_URL=https://iae-sso.virtualfri.id
IAE_TEAM_ID=TEAM-38
IAE_API_KEY=replace-with-api-key
IAE_CITIZEN_EMAIL=warga38@ktp.iae.id
IAE_CITIZEN_PASSWORD=replace-with-password
PORT=3000
DB_PATH=data/tugas3.sqlite
```

## Run

```powershell
node src/server.js
```

Open:

```text
http://localhost:3000
```

## Test

```powershell
node --test
```

The tests use mocked central services and do not publish live RabbitMQ messages.

## Live Smoke Test

This uses a temporary local database, but it does create a real SOAP audit log and RabbitMQ board message.

```powershell
node scripts/live-smoke.js
```

Expected result:

```json
{
  "login": "success",
  "role": "WARGA",
  "receiptNumber": "IAE-LOG-2026-...",
  "routingKey": "permit.payment.confirmed",
  "boardMatches": 1
}
```

## Demo Flow

1. Start the server.
2. Open `http://localhost:3000`.
3. Login with the citizen account.
4. Load permits.
5. Pay the pending permit.
6. Confirm the response shows a SOAP receipt like `IAE-LOG-2026-*`.
7. Check the RabbitMQ board at `https://iae-sso.virtualfri.id/board` or the board search API for `permit.payment.confirmed`.

## Repository Deliverables

- `src/`: dependency-free Node.js service.
- `public/`: small dashboard for the demo flow.
- `test/`: unit and mocked integration tests.
- `analisis_tugas_3.md`: transaction justification and sequence diagram.
- `prompt_engineering_log.md`: AI exploration and implementation log.
- `.env.example`: environment template without secrets.
