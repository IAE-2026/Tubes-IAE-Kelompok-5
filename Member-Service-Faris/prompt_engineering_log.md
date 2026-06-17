# Prompt Engineering Log - Tugas 3

## 1. Identifikasi Scope

Prompt awal meminta implementasi hanya untuk Tugas 3 dan mengabaikan bagian TUBES. PDF tugas diperiksa untuk memisahkan luaran individual dari luaran kelompok. Hasilnya: fokus pada SSO, SOAP audit, RabbitMQ publisher, dokumen analisis, dan log prompting.

## 2. Pemilihan Teknologi

Repo awal hanya berisi README dan tidak memiliki framework. Karena lingkungan menyediakan Node.js bundled tetapi tidak menyediakan npm/Express, keputusan teknis diarahkan ke Node.js dependency-free dengan modul bawaan `node:http`, `fetch`, WebCrypto, dan `node:sqlite`.

## 3. Pemilihan Transaksi Kritis

Transaksi yang dipilih adalah pembayaran izin warga (`PermitPaymentConfirmed`). Alasannya: transaksi ini state-changing, bernilai finansial, membutuhkan audit legacy SOAP, dan perlu disebarkan ke departemen lain melalui RabbitMQ.

## 4. Integrasi SSO

Eksplorasi endpoint SSO menemukan:

- Base URL: `https://iae-sso.virtualfri.id`
- Token endpoint: `POST /api/v1/auth/token`
- JWKS endpoint: `GET /api/v1/auth/jwks`
- Algoritma JWT: `RS256`
- Claim utama user: `sub = warga38@ktp.iae.id`

Implementasi memverifikasi token menggunakan JWKS dan memetakan `sub` ke role lokal `WARGA`.

## 5. Integrasi SOAP

SOAP dibuat sesuai contoh PDF dengan tag wajib:

- `TeamID`
- `ActivityName`
- `LogContent`

`LogContent` diisi JSON transaksi dalam CDATA. Receipt dari response disimpan ke tabel lokal dan dikembalikan ke client.

## 6. Integrasi RabbitMQ

Endpoint publish pusat menggunakan Bearer M2M token. Payload event menggunakan routing key `permit.payment.confirmed` dan body JSON yang berisi data permit, payment, receipt SOAP, team, dan aktor.

## 7. Verifikasi

Test plan dibuat dengan `node:test` agar tidak membutuhkan package eksternal. Test mencakup verifikasi JWT, pembuatan SOAP envelope, parsing receipt, payload event, sukses pembayaran, kegagalan SOAP, kegagalan publish, role invalid, token hilang, dan pembayaran duplikat.
