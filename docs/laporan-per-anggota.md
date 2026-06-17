# Laporan Per Anggota

Dokumen ini menjelaskan file yang ditambahkan atau diubah per anggota pada repository fresh-history Kelompok 5.

## Faris Fadil Arifin - Member Service

Folder: `Member-Service-Faris`

| Status | File | Penjelasan |
| --- | --- | --- |
| Ditambah | `Member-Service-Faris/` | Menambahkan kode Member Service dari repository individu Faris. |
| Diubah | `Member-Service-Faris/.gitignore` | Menambahkan pengecualian agar override GraphiQL bisa masuk repository. |
| Diubah | `Member-Service-Faris/config/graphiql.php` | Menambahkan header default `X-IAE-KEY` untuk GraphiQL/GraphQL testing. |
| Diubah | `Member-Service-Faris/phpunit.xml` | Menambahkan environment testing untuk `APP_KEY`, `IAE_API_KEY`, GraphiQL, SQLite, cache, queue, dan session. |
| Ditambah | `Member-Service-Faris/resources/views/vendor/graphiql/index.blade.php` | Override GraphiQL agar header `X-IAE-KEY` otomatis tersedia. |

## Muhammad Amin Hazami - Peminjaman Service

Folder: `Peminjaman-Service-Amin`

| Status | File | Penjelasan |
| --- | --- | --- |
| Ditambah | `Peminjaman-Service-Amin/` | Menambahkan kode Peminjaman Service dari repository individu Amin. |
| Diubah | `Peminjaman-Service-Amin/app/Http/Controllers/LoanController.php` | Menambahkan validasi member, validasi buku, pengurangan stok katalog saat create loan, dan pengembalian stok katalog saat return. |
| Diubah | `Peminjaman-Service-Amin/app/Http/Middleware/JwtMiddleware.php` | Menyesuaikan JWKS agar memakai konfigurasi SSO dan fallback endpoint. |
| Diubah | `Peminjaman-Service-Amin/app/Services/SoapAuditService.php` | Menyesuaikan SOAP audit agar memakai URL SSO dan team id dari environment. |
| Diubah | `Peminjaman-Service-Amin/phpunit.xml` | Menambahkan environment testing untuk API key, SSO, Member Service, dan Katalog Service. |
| Ada | `Peminjaman-Service-Amin/app/Services/AmqpPublisherService.php` | Menyediakan publish event AMQP untuk proses return. |

## Dewinda Salsabila Putri Haidir - Katalog Buku Service

Folder: `Katalog-Buku-Service-Dewinda`

| Status | File | Penjelasan |
| --- | --- | --- |
| Ditambah | `Katalog-Buku-Service-Dewinda/` | Menambahkan kode Katalog Buku Service dari repository individu Dewinda. |
| Diubah | `Katalog-Buku-Service-Dewinda/app/Http/Controllers/Api/BookController.php` | Menambahkan endpoint `borrowStock` dan `returnStock` untuk kebutuhan flow peminjaman. |
| Diubah | `Katalog-Buku-Service-Dewinda/routes/api.php` | Menambahkan route `POST /api/v1/books/{id}/stock/borrow` dan `POST /api/v1/books/{id}/stock/return`. |
| Diubah | `Katalog-Buku-Service-Dewinda/app/Models/Book.php` | Menambahkan pengelolaan atribut stok buku. |

## Integrasi Kelompok

| Status | File | Penjelasan |
| --- | --- | --- |
| Ditambah | `.gitignore` | Mengabaikan dependency, env lokal, cache, database lokal, dan file editor. |
| Ditambah | `api-gateway/nginx.conf` | Menambahkan routing gateway NGINX ke Member, Katalog, dan Peminjaman Service. |
| Ditambah | `docker-compose.yml` | Menambahkan orkestrasi gateway, tiga service, tiga database, network, dan volume. |
| Ditambah | `README.md` | Menjelaskan struktur, flow bisnis, cara menjalankan, endpoint gateway, docs, dan Postman. |
| Ditambah | `docs/` | Menambahkan laporan compliance, kontribusi, per anggota, test, dan log prompting AI. |
| Ditambah | `postman/` | Menambahkan collection dan environment Postman untuk pengujian gateway. |
