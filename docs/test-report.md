# Test Report

Tanggal: 2026-06-17

Lingkungan lokal:

- OS: Windows
- PHP lokal: 8.3.16
- Docker CLI: tersedia
- Docker Desktop Linux engine: aktif setelah Docker Desktop dijalankan

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
| Docker build | PASS | `docker compose build` berhasil membangun image Member, Peminjaman, dan Katalog. |
| Docker runtime | PASS | `docker compose up -d` berhasil menjalankan gateway, tiga service, dan tiga database. |
| Gateway smoke test | PASS | `GET http://localhost:8080/health` mengembalikan `{"status":"ok"}`. |
| Member gateway smoke test | PASS | `GET /api/v1/members` lewat gateway mengembalikan 200 setelah gateway direstart untuk refresh upstream DNS. |
| Postman runtime | READY | Collection siap dijalankan melalui gateway `http://localhost:8080`. |

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
docker compose up -d
docker compose up -d --build
docker compose ps
docker compose restart api-gateway
Invoke-WebRequest http://localhost:8080/health
Invoke-WebRequest http://localhost:8080/api/v1/members -Headers @{ 'X-IAE-KEY'='102022400255' }
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
- Docker Desktop Linux engine awalnya mati, lalu berhasil aktif setelah Docker Desktop dijalankan.
- Runtime awal sempat terkena konflik network lama `tubes-iae-network`; network lama dibersihkan dan Compose berhasil membuat network baru dengan label yang benar.
- Member Service membutuhkan migrasi database; `docker-compose.yml` sudah diperbarui agar Member Service menjalankan `php artisan migrate --force` saat container start.
- Setelah service container direcreate, `api-gateway` perlu direstart agar NGINX menyegarkan resolusi DNS upstream Docker.

## Rekomendasi Lanjutan

1. Jalankan Docker Desktop sebelum melakukan test.
2. Jalankan `docker compose up -d --build`.
3. Jika service container direcreate tetapi gateway sudah lebih dulu hidup, jalankan `docker compose restart api-gateway`.
4. Import Postman collection dan environment dari folder `postman/`.
5. Uji gateway dari `http://localhost:8080`.
