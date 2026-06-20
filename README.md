# Tubes IAE Kelompok 5

Repository gabungan untuk Tugas Besar Integrasi Aplikasi Enterprise. Repo ini menyatukan tiga mini-service individu ke dalam satu arsitektur Docker dengan API Gateway sebagai satu-satunya pintu masuk dari host.

## Struktur

- `Member-Service-Faris` dari `IAE-2026/102022400255_Faris-Fadil-Arifin-Member-Service`
- `Peminjaman-Service-Amin` dari `IAE-2026/102022400110_Muhammad-Amin-Hazami-Peminjaman-Service`
- `Katalog-Buku-Service-Dewinda` dari `IAE-2026/102022430028_Dewinda-Salsabila-Putri-Haidir-Katalog-Buku`
- `api-gateway` untuk konfigurasi NGINX gateway
- `docker-compose.yml` untuk menjalankan semua service dan database
- `docs/` untuk laporan requirement, kontribusi, test, dan log prompting AI
- `postman/` untuk collection dan environment pengujian API

## Alur Bisnis

1. Siswa dibuat sebagai member lewat Member Service.
2. Siswa melihat daftar buku lewat Katalog Buku Service.
3. Siswa membuat peminjaman lewat Peminjaman Service.
4. Peminjaman Service memvalidasi member ke Member Service dan buku ke Katalog Buku Service melalui URL internal Docker.
5. Jika member aktif dan stok tersedia, Peminjaman Service meminta Katalog Buku Service mengurangi `available_stock`.
6. Saat buku dikembalikan, Peminjaman Service meminta Katalog Buku Service mengembalikan stok.
7. Setelah return berhasil, Peminjaman Service menjalankan integrasi eksternal: token SSO, SOAP Audit, lalu publish event RabbitMQ/AMQP.

## Menjalankan

```bash
docker compose up --build
```

Gateway tersedia di:

- `http://localhost:8080/health`
- `http://localhost:8080/api/v1/auth/token`
- `http://localhost:8080/api/v1/members`
- `http://localhost:8080/api/v1/books`
- `http://localhost:8080/api/v1/loans`
- `http://localhost:8080/graphql`
- `http://localhost:8080/member/`
- `http://localhost:8080/katalog/`
- `http://localhost:8080/peminjaman/`

Token M2M SSO lewat gateway memakai JSON body `api_key` dan `nim`:

```bash
curl -s -X POST http://localhost:8080/api/v1/auth/token \
  -H "Content-Type: application/json" \
  -d '{"api_key":"102022400255","nim":"102022400255"}'
```

Service internal tidak dipublish ke host. Akses dari luar harus melewati `api-gateway`:

- Member service: `http://member-service:8000`
- Katalog Buku service: `http://katalog-buku-service:8000`
- Peminjaman service: `http://peminjaman-service:8000`

Database hanya tersedia di jaringan Docker:

- Member MySQL: `mysql-member:3306`
- Peminjaman MySQL: `mysql-peminjaman:3306`
- Katalog MySQL: `mysql-katalog:3306`

## Dokumen

- `docs/tubes-compliance-report.md`
- `docs/kontribusi-tim.md`
- `docs/laporan-per-anggota.md`
- `docs/log-prompting-ai.md`
- `docs/test-report.md`
- `docs/postman-test-report.md`

## Postman

- `postman/Tubes-IAE-Kelompok-5.postman_collection.json`
- `postman/Tubes-IAE-Kelompok-5.postman_environment.json`
