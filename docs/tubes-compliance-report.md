# Laporan Compliance Tugas Besar

Tanggal: 2026-06-17

## Ringkasan

Repository ini menggabungkan tiga service individu Kelompok 5 ke dalam satu repository dan satu arsitektur Docker. Integrasi root menyediakan API Gateway NGINX, Docker Compose lintas service, dokumentasi, dan Postman collection.

## Mapping Requirement

| Requirement | Implementasi | Status |
| --- | --- | --- |
| Repository gabungan | Tiga service berada di folder `Member-Service-Faris`, `Peminjaman-Service-Amin`, dan `Katalog-Buku-Service-Dewinda`. | Terpenuhi |
| API Gateway & Routing Hub | `api-gateway/nginx.conf` merutekan traffic host ke service internal. | Terpenuhi |
| Gateway-only exposure | Hanya `api-gateway` yang memiliki `ports: 8080:80`; service dan database internal memakai `expose`. | Terpenuhi |
| Docker orchestration | `docker-compose.yml` mendefinisikan gateway, tiga service, tiga database, network, dan volume. | Terpenuhi di konfigurasi |
| End-to-end core business flow | Peminjaman memvalidasi Member/Katalog via REST internal dan Katalog menyediakan endpoint stok borrow/return. | Terpenuhi di kode |
| Central infrastructure compliance | Peminjaman dan Katalog memiliki integrasi SSO, SOAP Audit, dan RabbitMQ/AMQP sesuai service masing-masing. | Terpenuhi sebagian, perlu runtime test saat Docker aktif |
| Git accountability | Commit service dilakukan oleh akun anggota masing-masing pada folder service masing-masing. | Terpenuhi |

## API Gateway & Routing

Gateway berjalan di `http://localhost:8080` dan merutekan:

| Path Gateway | Target Internal |
| --- | --- |
| `/api/v1/members` | `member-service:8000` |
| `/api/v1/books` | `katalog-buku-service:8000` |
| `/api/v1/loans` | `peminjaman-service:8000` |
| `/graphql` | `member-service:8000` |
| `/member/` | `member-service:8000` |
| `/katalog/` | `katalog-buku-service:8000` |
| `/peminjaman/` | `peminjaman-service:8000` |

## Alur Bisnis

1. Client membuat atau membaca data member melalui gateway ke Member Service.
2. Client membaca atau membuat data buku melalui gateway ke Katalog Buku Service.
3. Client membuat transaksi peminjaman melalui gateway ke Peminjaman Service.
4. Peminjaman Service memanggil Member Service untuk validasi member.
5. Peminjaman Service memanggil Katalog Buku Service untuk validasi buku dan stok.
6. Katalog Buku Service menyediakan `POST /api/v1/books/{id}/stock/borrow` untuk mengurangi stok.
7. Katalog Buku Service menyediakan `POST /api/v1/books/{id}/stock/return` untuk mengembalikan stok.
8. Peminjaman Service menjalankan proses return dan integrasi eksternal SSO, SOAP Audit, dan AMQP.

## Gateway-Only Exposure

Validasi konfigurasi dilakukan dengan `docker compose config`. Dari konfigurasi root:

- `api-gateway` memiliki `ports`.
- `member-service`, `peminjaman-service`, dan `katalog-buku-service` hanya memiliki `expose`.
- `mysql-member`, `mysql-peminjaman`, dan `mysql-katalog` hanya memiliki `expose`.
