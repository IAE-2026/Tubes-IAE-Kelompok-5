# Analisis Tugas 3 - Digital City Permit Payment

## Transaksi Kritis

Transaksi yang dipilih adalah `PermitPaymentConfirmed`, yaitu pembayaran izin warga untuk layanan Digital City. Transaksi ini termasuk kritis karena mengubah state invoice dari `PENDING` menjadi `PAID`, menghasilkan bukti pembayaran, dan dapat memicu proses lintas departemen seperti validasi izin, pencetakan dokumen, dan notifikasi pelayanan warga.

Pembayaran juga termasuk transaksi keuangan, sehingga harus dicatat ke sistem audit legacy menggunakan SOAP/XML. Setelah audit berhasil, aktivitas bisnis disebarkan secara asinkron melalui RabbitMQ supaya departemen lain bisa bereaksi tanpa menunggu proses utama.

## Role Lokal

User SSO `warga38@ktp.iae.id` dipetakan ke role lokal `WARGA`.

Permission lokal:

- `permit:read`: melihat daftar invoice izin milik warga.
- `permit:pay`: melakukan pembayaran invoice izin yang masih `PENDING`.

## Sequence Diagram Internal

```mermaid
sequenceDiagram
    autonumber
    actor Warga as Warga 38
    participant App as Digital City Permit Service
    participant DB as Local SQLite Roles/Payments
    participant SSO as IAE SSO REST/JWKS
    participant SOAP as Legacy SOAP Audit
    participant MQ as RabbitMQ Publish API

    Warga->>App: POST /api/auth/login
    App->>SSO: POST /api/v1/auth/token
    SSO-->>App: JWT RS256
    App->>SSO: GET /api/v1/auth/jwks
    App->>App: Verify JWT signature and claims
    App->>DB: Map sub=email to local WARGA role
    App-->>Warga: Token, profile, local role

    Warga->>App: POST /api/permits/1/pay with Bearer JWT
    App->>SSO: GET /api/v1/auth/jwks
    App->>App: Verify Bearer JWT
    App->>DB: Validate WARGA and pending invoice
    App->>SSO: POST /api/v1/auth/token using API key
    SSO-->>App: M2M token
    App->>SOAP: POST /soap/v1/audit with SOAP Envelope
    SOAP-->>App: Status SUCCESS and ReceiptNumber
    App->>DB: Store PAID payment and receipt
    App->>SSO: POST /api/v1/auth/token using API key
    SSO-->>App: M2M token
    App->>MQ: POST /api/v1/messages/publish routing_key permit.payment.confirmed
    MQ-->>App: Publish accepted
    App->>DB: Store published event status
    App-->>Warga: Payment result, receipt, routing key
```

## Data yang Diaudit SOAP

SOAP `LogContent` berisi JSON di dalam CDATA:

- `team_id`: `TEAM-38`
- `activity_name`: `PermitPaymentConfirmed`
- `transaction_type`: `PERMIT_PAYMENT`
- `permit_number`: nomor izin
- `citizen_email`: subjek SSO warga
- `amount`: nilai pembayaran
- `method`: metode pembayaran
- `occurred_at`: waktu transaksi
- `actor`: email SSO dan role lokal

## Event RabbitMQ

Routing key: `permit.payment.confirmed`

Payload JSON berisi ringkasan izin, pembayaran, `receipt_number` dari SOAP, `team_id`, service name, timestamp, dan identitas aktor. Event ini memungkinkan departemen lain menerima notifikasi pembayaran tanpa coupling langsung ke service pembayaran.
