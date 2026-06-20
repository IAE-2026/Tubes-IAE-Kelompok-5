# Test Report

Tanggal: 2026-06-17

Lingkungan lokal: PHP 8.3.16, Docker CLI 29.4.1, Docker Desktop Linux engine belum aktif.

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
| Katalog Composer compatibility | PASS | `composer install --dry-run` sekarang lolos di PHP 8.3 setelah `composer.lock` disesuaikan ulang. |
| Katalog PHPUnit | PASS | `php artisan test` exit code 0; 2 tests passed, 2 assertions. |
| Docker runtime build/run | BLOCKED | `docker compose build` masih gagal karena daemon `dockerDesktopLinuxEngine` tidak berjalan. |

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
composer validate --strict
composer update --no-interaction --prefer-dist --no-progress
composer install --dry-run --no-interaction
composer check-platform-reqs
docker version
docker compose build
```

## Hasil Penting

- `docker compose config` berhasil, sehingga YAML dan service graph valid.
- `Member-Service-Faris` test suite berhasil setelah `APP_KEY` testing dan GraphiQL header override ditambahkan.
- `Peminjaman-Service-Amin` test suite berhasil setelah dependency Composer berhasil diinstall.
- `Katalog-Buku-Service-Dewinda` awalnya gagal di PHP lokal 8.3 karena lockfile berisi Symfony v8 yang membutuhkan PHP >=8.4.
- `composer.lock` Katalog sudah disesuaikan ulang di PHP 8.3; dependency install, platform requirements, dan PHPUnit Katalog berhasil.
- Docker client terpasang, tetapi daemon `dockerDesktopLinuxEngine` belum aktif. `docker version` gagal terhubung ke pipe Docker Desktop, sehingga `docker compose build` belum bisa berjalan.

## Rekomendasi Lanjutan

1. Jalankan Docker Desktop, lalu lakukan `docker compose up --build`.
2. Uji flow gateway dari host di `http://localhost:8080`.
3. Jalankan Postman collection setelah gateway hidup.
