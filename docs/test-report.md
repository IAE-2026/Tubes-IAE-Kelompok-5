# Test Report

Tanggal: 2026-06-17

Lingkungan lokal:

- OS: Windows
- PHP lokal: 8.3.16
- Docker CLI: tersedia
- Docker Desktop Linux engine: belum aktif saat test integrasi root

## Ringkasan

| Area | Status | Catatan |
| --- | --- | --- |
| Pull fresh repo | PASS | `git pull --ff-only` berhasil dan tiga folder service tersedia. |
| Commit ownership | PASS | Git history menampilkan commit Faris, Amin, dan Dewinda secara terpisah. |
| Docker Compose config | PASS | `docker compose config` valid. |
| Gateway-only exposure | PASS | Hanya `api-gateway` yang memiliki `ports`; service dan database internal memakai `expose`. |
| Postman JSON | PASS | Collection dan environment berhasil diparse sebagai JSON. |
| Member Service PHPUnit | PASS with warnings | Sebelum integrasi root, `php artisan test` lulus dengan 1 passed, 12 warnings, 74 assertions. |
| Peminjaman cross-service validation | PASS by code inspection | `LoanController` memanggil Member Service dan Katalog Service saat create loan dan return. |
| Docker build/run | BLOCKED | Docker Desktop Linux engine belum aktif, sehingga build/run container belum bisa dieksekusi lokal. |
| Postman runtime | BLOCKED | Runtime API menunggu Docker gateway berjalan. |

## Perintah Yang Dijalankan

```bash
git pull --ff-only
git log --oneline --pretty=format:"%h %an <%ae> %s" --max-count=12
docker compose config
docker compose config --format json
php -l Peminjaman-Service-Amin/app/Http/Controllers/LoanController.php
php -l Peminjaman-Service-Amin/app/Http/Middleware/JwtMiddleware.php
php -l Peminjaman-Service-Amin/app/Services/SoapAuditService.php
docker version
docker compose build
```

Validasi JSON Postman:

```powershell
Get-Content postman/Tubes-IAE-Kelompok-5.postman_collection.json -Raw | ConvertFrom-Json
Get-Content postman/Tubes-IAE-Kelompok-5.postman_environment.json -Raw | ConvertFrom-Json
```

## Hasil Penting

- Struktur repository sudah berisi `Member-Service-Faris`, `Peminjaman-Service-Amin`, dan `Katalog-Buku-Service-Dewinda`.
- Konfigurasi gateway mengarah ke service internal di Docker network.
- Konfigurasi Compose menjaga service internal dan database tidak membuka port host.
- Katalog sudah memiliki endpoint stok untuk peminjaman.
- Peminjaman sudah memiliki helper `fetchMember`, `fetchBook`, dan `postCatalogStockAction` untuk validasi lintas service.

## Rekomendasi Lanjutan

1. Jalankan Docker Desktop Linux engine.
2. Jalankan `docker compose up --build`.
3. Import Postman collection dan environment dari folder `postman/`.
4. Uji gateway dari `http://localhost:8080`.
