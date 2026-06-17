# Postman Test Report

Tanggal: 2026-06-17

## Ringkasan

Postman collection dan environment sudah disiapkan untuk menguji API lewat API Gateway `http://localhost:8080`.

File:

- `postman/Tubes-IAE-Kelompok-5.postman_collection.json`
- `postman/Tubes-IAE-Kelompok-5.postman_environment.json`

## Endpoint Yang Disiapkan

| Request | Method | URL |
| --- | --- | --- |
| Gateway Health | GET | `{{base_url}}/health` |
| Get Members | GET | `{{base_url}}/api/v1/members` |
| Get Member Detail | GET | `{{base_url}}/api/v1/members/{{member_id}}` |
| GraphQL Members | POST | `{{base_url}}/graphql` |
| Get Books | GET | `{{base_url}}/api/v1/books` |
| Get Book Detail | GET | `{{base_url}}/api/v1/books/{{book_id}}` |
| Borrow Stock | POST | `{{base_url}}/api/v1/books/{{book_id}}/stock/borrow` |
| Return Stock | POST | `{{base_url}}/api/v1/books/{{book_id}}/stock/return` |
| Get Loans | GET | `{{base_url}}/api/v1/loans` |
| Create Loan | POST | `{{base_url}}/api/v1/loans` |
| Return Loan | POST | `{{base_url}}/api/v1/loans/{{loan_id}}/return` |

## Status Runtime

Postman collection sudah siap dijalankan setelah gateway aktif. Pada test lokal, gateway berhasil berjalan dan endpoint health mengembalikan status OK.

```bash
docker compose up -d --build
```

Smoke test:

```bash
GET http://localhost:8080/health
GET http://localhost:8080/api/v1/members
```

Hasil:

```json
{"status":"ok"}
```

Member endpoint juga berhasil lewat gateway dengan header `X-IAE-KEY: 102022400255` dan status HTTP 200.

## Catatan Auth

- Member endpoint memakai header `X-IAE-KEY: {{member_api_key}}`.
- Katalog endpoint memakai header `X-IAE-KEY: {{catalog_api_key}}` dan `Authorization: Bearer {{bearer_token}}`.
- Peminjaman endpoint memakai `Authorization: Bearer {{bearer_token}}`.
