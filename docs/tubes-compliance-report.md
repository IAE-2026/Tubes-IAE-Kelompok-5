# Laporan Compliance Tugas Besar IAE

Dokumen ini memetakan repository kelompok terhadap kontrak proses bisnis `Peminjaman Buku` dan rubrik Tugas Besar.

## Ringkasan Status

| Komponen | Status | Bukti |
| --- | --- | --- |
| Repository gabungan | Terpenuhi | Tiga service individu digabung sebagai folder terpisah di root repository. |
| Docker compose gabungan | Terpenuhi | `docker-compose.yml` menjalankan gateway, tiga service Laravel, dan tiga database. |
| API Gateway & routing hub | Terpenuhi | `api-gateway/nginx.conf` mem-proxy `/api/v1/auth/token`, `/api/v1/members`, `/api/v1/books`, dan `/api/v1/loans`. |
| Tidak bypass gateway dari host | Terpenuhi | Hanya `api-gateway` yang memiliki `ports`; service dan database internal hanya memakai `expose`. |
| End-to-end core business flow | Terpenuhi secara kode | Peminjaman service memanggil member service dan catalog service secara internal sebelum loan dibuat. |
| Central infrastructure compliance | Terpenuhi secara kode | Return loan menjalankan login SSO, SOAP audit, lalu RabbitMQ publish. Catalog create book juga menjalankan urutan SSO, SOAP, RabbitMQ. |

## Mapping Kontrak Proses Bisnis

| Kontrak | Endpoint Gateway | Service |
| --- | --- | --- |
| Siswa mendaftar sebagai member | `POST /api/v1/members` | Member-Service-Faris |
| Admin melihat daftar member | `GET /api/v1/members` | Member-Service-Faris |
| Sistem memverifikasi status keanggotaan | Internal `GET /api/v1/members/{id}` | Peminjaman-Service-Amin -> Member-Service-Faris |
| Siswa menelusuri katalog buku | `GET /api/v1/books` | Katalog-Buku-Service-Dewinda |
| Admin tambah buku ke katalog | `POST /api/v1/books` | Katalog-Buku-Service-Dewinda |
| Siswa memilih buku dan cek ketersediaan | `GET /api/v1/books/{id}` | Katalog-Buku-Service-Dewinda |
| Siswa mengajukan peminjaman | `POST /api/v1/loans` | Peminjaman-Service-Amin |
| Verifikasi peminjaman | Internal REST ke member dan catalog | Peminjaman-Service-Amin |
| Siswa mengembalikan buku | `POST /api/v1/loans/{id}/return` | Peminjaman-Service-Amin |
| Stok buku dikembalikan ke katalog | Internal `POST /api/v1/books/{id}/stock/return` | Peminjaman-Service-Amin -> Katalog-Buku-Service-Dewinda |

## Catatan Implementasi

- `Peminjaman-Service-Amin/app/Http/Controllers/LoanController.php` sekarang melakukan validasi lintas service sebelum membuat loan.
- `Katalog-Buku-Service-Dewinda/app/Http/Controllers/Api/BookController.php` menambahkan endpoint internal untuk mengurangi dan mengembalikan stok.
- URL internal service dikonfigurasi lewat environment root compose: `MEMBER_SERVICE_URL`, `CATALOG_SERVICE_URL`, dan API key masing-masing service.
- Endpoint internal tetap diproteksi middleware service masing-masing; peminjaman service meneruskan Bearer JWT user ketika memanggil catalog service.

## Cara Uji End-to-End

Semua request dari host harus lewat gateway `http://localhost:8080`.

1. Jalankan:

```bash
docker compose up --build
```

2. Ambil token M2M SSO:

```bash
curl -s -X POST http://localhost:8080/api/v1/auth/token \
  -H "Content-Type: application/json" \
  -d '{"api_key":"102022400255","nim":"102022400255"}'
```

3. Buat member:

```bash
curl -X POST http://localhost:8080/api/v1/members \
  -H "Content-Type: application/json" \
  -H "X-IAE-KEY: 102022400255" \
  -d "{\"name\":\"Budi Santoso\",\"student_number\":\"SISWA-00001\",\"email\":\"budi@example.com\"}"
```

4. Buat buku dengan Bearer JWT SSO dan API key katalog:

```bash
curl -X POST http://localhost:8080/api/v1/books \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <JWT_SSO>" \
  -H "X-IAE-KEY: KEY-MHS-44" \
  -d "{\"title\":\"Clean Code\",\"author\":\"Robert C. Martin\",\"isbn\":\"9780132350884\",\"publisher\":\"Prentice Hall\",\"year\":2008,\"stock\":3}"
```

5. Buat loan:

```bash
curl -X POST http://localhost:8080/api/v1/loans \
  -H "Content-Type: application/json" \
  -H "Authorization: Bearer <JWT_SSO>" \
  -d "{\"member_id\":1,\"book_id\":1}"
```

6. Return loan:

```bash
curl -X POST http://localhost:8080/api/v1/loans/1/return \
  -H "Authorization: Bearer <JWT_SSO>"
```

## Keterbatasan Uji Lokal

Docker Desktop harus aktif untuk membuktikan container runtime dan flow HTTP secara langsung. Tanpa Docker engine, validasi yang bisa dilakukan hanya `docker compose config`, lint PHP, dan PHPUnit service yang kompatibel dengan PHP lokal.
