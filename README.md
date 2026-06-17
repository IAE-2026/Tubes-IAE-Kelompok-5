# Tubes IAE Kelompok 5

Monorepo untuk menggabungkan service Kelompok 5 dengan pola folder seperti repo kelompok lain.

## Struktur

- `Member-Service-Faris` dari `IAE-2026/102022400255_Faris-Fadil-Arifin-Member-Service`
- `Peminjaman-Service-Amin` dari `IAE-2026/102022400110_Muhammad-Amin-Hazami-Peminjaman-Service`
- `Katalog-Buku-Service-Dewinda` dari `IAE-2026/102022430028_Dewinda-Salsabila-Putri-Haidir-Katalog-Buku`
- `api-gateway` untuk konfigurasi NGINX gateway
- `docker-compose.yml` untuk menjalankan semua service bersama-sama
- `docs/` untuk laporan compliance, resume kontribusi, dan log prompting AI kelompok

## Alur Bisnis Terintegrasi

1. Siswa mendaftar sebagai member lewat `POST /api/v1/members`.
2. Siswa melihat katalog lewat `GET /api/v1/books`.
3. Siswa mengajukan peminjaman lewat `POST /api/v1/loans`.
4. Peminjaman service memvalidasi member ke `member-service` dan buku ke `katalog-buku-service` secara internal.
5. Jika member aktif dan stok tersedia, peminjaman service meminta catalog service mengurangi `available_stock`.
6. Saat buku dikembalikan lewat `POST /api/v1/loans/{id}/return`, peminjaman service meminta catalog service mengembalikan stok.
7. Setelah return berhasil, peminjaman service menjalankan urutan compliance eksternal: login SSO, kirim SOAP audit, lalu publish event RabbitMQ.

## Menjalankan

```bash
docker compose up --build
```

Gateway tersedia di:

- `http://localhost:8080/api/v1/members`
- `http://localhost:8080/api/v1/books`
- `http://localhost:8080/api/v1/loans`

Service internal tidak dipublish ke host agar akses dari luar harus melewati API Gateway. Di dalam jaringan Docker:

- Member service: `http://member-service:8000`
- Katalog buku service: `http://katalog-buku-service:8000`
- Peminjaman service: `http://peminjaman-service:8000`

Database hanya tersedia di jaringan Docker:

- Member MySQL: `mysql-member:3306`
- Peminjaman MySQL: `mysql-peminjaman:3306`
- Katalog MySQL: `mysql-katalog:3306`

Dokumen pendukung:

- `docs/tubes-compliance-report.md`
- `docs/kontribusi-tim.md`
- `docs/log-prompting-ai.md`
- `docs/laporan-per-anggota.md`
- `docs/test-report.md`
