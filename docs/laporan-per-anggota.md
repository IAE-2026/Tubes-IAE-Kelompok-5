# Laporan Per Anggota

Dokumen ini menjelaskan file yang diubah atau ditambahkan pada patch integrasi Tugas Besar, dikelompokkan berdasarkan anggota dan bagian integrasi kelompok.

Commit acuan: `ace3c0e` - `Patch Tubes integration requirements`

## Faris Fadil Arifin - Member Service

Folder service: `Member-Service-Faris`

| Status | File | Penjelasan |
| --- | --- | --- |
| Diubah | `Member-Service-Faris/.gitignore` | Menambahkan pengecualian agar override view GraphiQL di `resources/views/vendor/graphiql` bisa masuk repository. |
| Diubah | `Member-Service-Faris/config/graphiql.php` | Menambahkan konfigurasi header `X-IAE-KEY` untuk kebutuhan akses GraphQL yang diproteksi API key. |
| Diubah | `Member-Service-Faris/phpunit.xml` | Menambahkan environment testing seperti `APP_KEY`, `IAE_API_KEY`, `GRAPHIQL_ENABLED`, dan `LIGHTHOUSE_SCHEMA_CACHE_ENABLE` agar test berjalan konsisten. |
| Ditambah | `Member-Service-Faris/resources/views/vendor/graphiql/index.blade.php` | Override tampilan GraphiQL supaya header `X-IAE-KEY` otomatis terlihat dan dipakai saat menjalankan query GraphQL. |

### Ringkasan

Perubahan pada service Faris berfokus pada kesiapan Member Service sebagai sumber validasi keanggotaan. Service ini tetap menyediakan REST API dan GraphQL untuk data member, dengan proteksi API key yang lebih jelas saat memakai GraphiQL.

## Muhammad Amin Hazami - Peminjaman Service

Folder service: `Peminjaman-Service-Amin`

| Status | File | Penjelasan |
| --- | --- | --- |
| Diubah | `Peminjaman-Service-Amin/app/Http/Controllers/LoanController.php` | Menambahkan validasi lintas service saat membuat loan. Peminjaman Service sekarang memanggil Member Service untuk cek status member dan Katalog Service untuk cek stok buku. Saat loan dibuat, stok dikurangi. Saat return, stok dikembalikan, lalu proses SSO, SOAP Audit, dan RabbitMQ dijalankan. |
| Diubah | `Peminjaman-Service-Amin/app/Http/Middleware/JwtMiddleware.php` | Mengubah sumber JWKS agar memakai `SSO_URL` dari environment, mencoba endpoint `/api/v1/auth/jwks`, lalu fallback ke `/.well-known/jwks.json`. |
| Diubah | `Peminjaman-Service-Amin/app/Services/SoapAuditService.php` | Merapikan komentar agar lebih konsisten dan mudah dibaca. |
| Diubah | `Peminjaman-Service-Amin/phpunit.xml` | Menambahkan environment testing untuk API key, team id, SSO URL, serta URL internal Member Service dan Katalog Service. |

### Ringkasan

Perubahan pada service Amin menjadikan Peminjaman Service sebagai pusat alur bisnis end-to-end. Service ini tidak lagi hanya membuat data loan lokal, tetapi juga memvalidasi member, memvalidasi stok buku, mengubah stok katalog, dan menjalankan integrasi eksternal saat pengembalian.

## Dewinda Salsabila Putri Haidir - Katalog Buku Service

Folder service: `Katalog-Buku-Service-Dewinda`

| Status | File | Penjelasan |
| --- | --- | --- |
| Diubah | `Katalog-Buku-Service-Dewinda/app/Http/Controllers/Api/BookController.php` | Menambahkan endpoint internal untuk mengurangi stok saat buku dipinjam dan mengembalikan stok saat buku dikembalikan. |
| Diubah | `Katalog-Buku-Service-Dewinda/routes/api.php` | Menambahkan route `POST /api/v1/books/{id}/stock/borrow` dan `POST /api/v1/books/{id}/stock/return`. |
| Diubah | `Katalog-Buku-Service-Dewinda/app/Models/Book.php` | Menambahkan cast `available_stock` sebagai integer agar nilai stok konsisten saat dibaca dan dikembalikan dalam response. |
| Diubah | `Katalog-Buku-Service-Dewinda/app/Services/RabbitMQPublisherService.php` | Menyesuaikan payload publish event dengan format `exchange`, `routing_key`, dan `message`, serta memakai `SSO_URL` dari environment. |
| Diubah | `Katalog-Buku-Service-Dewinda/app/Services/SOAPAuditService.php` | Mengubah base URL SOAP agar configurable lewat `SSO_URL`. |
| Diubah | `Katalog-Buku-Service-Dewinda/app/Services/SSOService.php` | Mengubah `SSO_URL`, `SSO_EMAIL`, dan `SSO_PASSWORD` agar configurable lewat environment. |
| Diubah | `Katalog-Buku-Service-Dewinda/phpunit.xml` | Menambahkan environment testing untuk `APP_KEY`, `IAE_API_KEY`, `IAE_TEAM_ID`, dan `SSO_URL`. |

### Ringkasan

Perubahan pada service Dewinda membuat Katalog Service mendukung proses bisnis peminjaman secara langsung. Service ini tidak hanya menyediakan data buku, tetapi juga menjadi sumber kebenaran untuk ketersediaan stok buku.

## Integrasi Kelompok 5

Bagian ini adalah perubahan di root repository yang mendukung integrasi semua service.

| Status | File | Penjelasan |
| --- | --- | --- |
| Diubah | `.gitignore` | Menambahkan pengecualian untuk file override GraphiQL yang memang perlu dilacak Git. |
| Diubah | `docker-compose.yml` | Mengubah arsitektur menjadi gateway-only dari host. Hanya `api-gateway` yang expose port `8080`; service dan database internal hanya memakai Docker network. Menambahkan environment URL antar-service. |
| Diubah | `README.md` | Menambahkan alur bisnis terintegrasi, endpoint gateway, struktur service, dan link dokumen pendukung. |
| Ditambah | `docs/tubes-compliance-report.md` | Laporan pemenuhan requirement Tugas Besar dan mapping kontrak proses bisnis ke implementasi. |
| Ditambah | `docs/test-report.md` | Laporan hasil pengujian, command yang dijalankan, hasil yang pass, dan blocker lokal. |
| Ditambah | `docs/kontribusi-tim.md` | Ringkasan kontribusi tiap anggota dan kepemilikan service. |
| Ditambah | `docs/log-prompting-ai.md` | Rekap penggunaan AI/prompting sesuai kebutuhan luaran Tugas Besar. |
| Ditambah | `docs/laporan-per-anggota.md` | Dokumen ini, berisi laporan perubahan file per anggota. |

### Ringkasan

Perubahan integrasi kelompok memastikan repository memenuhi luaran Tugas Besar: repository gabungan, Docker compose, API Gateway, routing hub, alur bisnis end-to-end, dokumentasi kontribusi, dan log prompting AI.

## Status Pengujian Singkat

| Komponen | Status |
| --- | --- |
| `docker compose config` | Lulus |
| Syntax PHP file yang diubah | Lulus |
| PHPUnit Member Service | Lulus dengan warning dependency |
| PHPUnit Peminjaman Service | Lulus dengan warning dependency |
| PHPUnit Katalog Service | Belum bisa dijalankan lokal karena lockfile membutuhkan PHP `>=8.4`, sedangkan mesin lokal memakai PHP `8.3.16` |
| Docker build/run | Belum bisa dijalankan karena Docker Desktop Linux engine belum aktif |
