# Test Report

Tanggal: 2026-06-17

## Ringkasan

| Area | Status | Catatan |
| --- | --- | --- |
| Root Docker compose | PASS | `docker compose config` valid. |
| Gateway-only exposure | PASS | Hanya `api-gateway` yang memiliki `ports`; service/database internal memakai `expose`. |
| Member service PHP syntax | PASS | File PHP yang diubah valid. |
| Peminjaman service PHP syntax | PASS | File PHP yang diubah valid. |
| Katalog service PHP syntax | PASS | File PHP yang diubah valid. |
| Member PHPUnit | PASS with warnings | `php artisan test` exit code 0; 1 passed, 12 warnings dari dependency deprecation. |
| Peminjaman PHPUnit | PASS with warnings | `php artisan test` exit code 0; 1 passed, 4 warnings dari dependency deprecation. |
| Katalog PHPUnit | BLOCKED | `composer install --dry-run` gagal di PHP lokal 8.3 karena lockfile berisi Symfony v8 packages yang butuh PHP >=8.4. |
| Docker runtime build/run | BLOCKED | Docker Desktop Linux engine tidak berjalan, sehingga `docker compose build`/`up` belum bisa diuji. |

## Perintah Yang Dijalankan

```bash
docker compose config
php -l app/Http/Controllers/LoanController.php
php -l app/Http/Middleware/JwtMiddleware.php
php -l app/Services/SoapAuditService.php
php -l app/Http/Controllers/Api/BookController.php
php -l app/Models/Book.php
php -l app/Services/RabbitMQPublisherService.php
php -l app/Services/SOAPAuditService.php
php -l app/Services/SSOService.php
php -l routes/api.php
php artisan test
composer install --dry-run --no-interaction
docker version
```

## Hasil Penting

- `docker compose config` berhasil, sehingga YAML dan service graph valid.
- `Member-Service-Faris` test suite berhasil setelah `APP_KEY` testing dan GraphiQL header override ditambahkan.
- `Peminjaman-Service-Amin` test suite berhasil setelah dependency Composer berhasil diinstall.
- `Katalog-Buku-Service-Dewinda` tidak bisa dites dengan PHP lokal karena dependency lockfile membutuhkan PHP 8.4, sedangkan mesin lokal memakai PHP 8.3.16.
- Docker client terpasang, tetapi daemon `dockerDesktopLinuxEngine` belum aktif.

## Rekomendasi Lanjutan

1. Jalankan Docker Desktop, lalu lakukan `docker compose up --build`.
2. Uji flow gateway dari host di `http://localhost:8080`.
3. Jalankan katalog test di environment PHP 8.4 atau sesuaikan lockfile agar kompatibel dengan PHP 8.3.
