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

Postman runtime belum dijalankan karena Docker Desktop Linux engine belum aktif pada mesin lokal. Collection sudah bisa diimport, tetapi request akan berhasil hanya setelah gateway berjalan dengan:

```bash
docker compose up --build
```

## Catatan Auth

- Member endpoint memakai header `X-IAE-KEY: {{member_api_key}}`.
- Katalog endpoint memakai header `X-IAE-KEY: {{catalog_api_key}}` dan `Authorization: Bearer {{bearer_token}}`.
- Peminjaman endpoint memakai `Authorization: Bearer {{bearer_token}}`.
