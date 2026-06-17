# Tubes IAE Kelompok 5

Monorepo untuk menggabungkan service Kelompok 5 dengan pola folder seperti repo kelompok lain.

## Struktur

- `Member-Service-Faris` dari `IAE-2026/102022400255_Faris-Fadil-Arifin-Member-Service`
- `Peminjaman-Service-Amin` dari `IAE-2026/102022400110_Muhammad-Amin-Hazami-Peminjaman-Service`
- `Katalog-Buku-Service-Dewinda` dari `IAE-2026/102022430028_Dewinda-Salsabila-Putri-Haidir-Katalog-Buku`
- `api-gateway` untuk konfigurasi NGINX gateway
- `docker-compose.yml` untuk menjalankan semua service bersama-sama

## Menjalankan

```bash
docker compose up --build
```

Gateway tersedia di:

- `http://localhost:8080/api/v1/members`
- `http://localhost:8080/api/v1/books`
- `http://localhost:8080/api/v1/loans`

Port direct service:

- Member service: `http://localhost:8001`
- Katalog buku service: `http://localhost:8002`
- Peminjaman service: `http://localhost:8003`

Database host port:

- Member MySQL: `localhost:3307`
- Peminjaman MySQL: `localhost:3308`
- Katalog MySQL: `localhost:3309`
