## [14/5/2026] Analisis Requirement & Standarisasi Project

Saya meminta penjelasan mengenai bukti pengerjaan yang perlu disiapkan, termasuk repository GitHub organisasi, Swagger UI, GraphQL Playground, dan file dokumentasi prompting AI.


## [14/5/2026] Audit Struktur Project & Pengecekan Contract

Saya meminta AI melakukan audit terhadap project Laravel yang sudah saya kerjakan untuk memastikan seluruh endpoint telah mengikuti Standard Integration Contract IAE-T2. Saya juga meminta pengecekan kesesuaian antara migration, model, controller, dan response API agar tidak terjadi inkonsistensi field saat proses integrasi service.


## [14/5/2026] Debug Middleware & Validasi API Key

Saya mengalami bug pada middleware autentikasi karena request selalu gagal walaupun API Key yang dikirim sudah benar. Saya meminta AI menjelaskan penyebab perbandingan API Key gagal, khususnya terkait perbedaan tipe data integer dan string pada header request `X-IAE-KEY`, lalu membantu memperbaiki implementasi middleware agar validasi berjalan normal.


## [14/5/2026] Perbaikan Session Driver Laravel

Saat menjalankan project di environment container, saya mengalami error terkait session table yang belum tersedia. Saya meminta AI menjelaskan kenapa konfigurasi `SESSION_DRIVER=database` menyebabkan aplikasi gagal berjalan, lalu membantu mengganti konfigurasi session menjadi `file` agar container dapat dijalankan tanpa dependency tambahan migration session.


## [15/5/2026] Debug Dockerfile & Build Container

Saya mengalami kegagalan build Docker karena file `DockerfilE` tidak dikenali oleh Docker Engine. Saya meminta AI membantu menelusuri penyebab error tersebut dan menjelaskan standar penamaan file Docker yang benar agar proses build dapat dijalankan.


## [15/5/2026] Perbaikan Base Image PHP Docker

Setelah Dockerfile berhasil terbaca, saya kembali mengalami error karena image `php:8.2-fpm-cli` tidak tersedia di registry Docker Hub. Saya meminta AI menjelaskan perbedaan image PHP FPM & CLI, lalu membantu memilih base image yang kompatibel dengan kebutuhan Laravel service saya.


## [15/5/2026] Debug Koneksi Database Antar Container

Saya mengalami error koneksi database karena container Laravel berjalan lebih cepat dibanding MySQL. Saya meminta AI membantu menjelaskan bagaimana mekanisme startup container pada Docker Compose bekerja, serta membantu menambahkan konfigurasi `healthcheck`, `restart: on-failure`, dan `start_period` agar aplikasi menunggu database benar-benar siap sebelum melakukan koneksi.


## [15/5/2026] Perbaikan Kredensial MySQL

Saya kembali mengalami error `Access denied for user 'root'@'%'` saat aplikasi mencoba terhubung ke database. Saya meminta AI menjelaskan kenapa MySQL 8 membatasi koneksi root dari host eksternal, lalu membantu membuat user database baru (`laravel`) beserta konfigurasi `.env` dan `docker-compose.yml` agar koneksi database berhasil dilakukan.


## [15/5/2026] Regenerasi Volume & Migrasi Database

Setelah konfigurasi database diperbaiki, saya meminta AI membantu membersihkan volume lama Docker menggunakan `docker compose down -v` agar environment benar-benar fresh. Saya juga meminta penjelasan bagaimana migration Laravel dapat dijalankan otomatis saat container berhasil start.


## [15/5/2026] Debug Swagger Generator Laravel

Saya kembali mengalami error lanjutan berupa `"Required @OA\PathItem() not found"` saat menjalankan `php artisan l5-swagger:generate`. Saya meminta AI menjelaskan kenapa routing pada `VerificationController.php` tidak terbaca oleh Swagger parser, serta membantu mengonversi seluruh anotasi DocBlock menjadi PHP 8 Attributes agar dokumentasi API dapat digenerate dengan sempurna.


## [15/5/2026] Perbaikan Dokumentasi OpenAPI

Saya meminta AI membantu menyesuaikan seluruh struktur response Swagger agar mengikuti Standard Integration Contract IAE-T2, termasuk wrapper `success`, `message`, dan `data`. Saya juga meminta bantuan memperbaiki schema request body dan response code agar dokumentasi API terlihat konsisten dan profesional.


## [15/5/2026] Implementasi GraphQL dengan Lighthouse

Saya meminta AI membantu mengintegrasikan GraphQL menggunakan package Lighthouse pada Laravel 11. Saya juga meminta penjelasan mengenai struktur schema GraphQL, cara membuat query `loans`, serta bagaimana GraphQL memberikan fleksibilitas field selection dibanding REST API biasa.


## [15/5/2026] Pengujian Endpoint REST & GraphQL

Saya meminta AI membantu melakukan validasi terhadap seluruh endpoint REST API melalui Swagger UI dan memastikan response `201 Created` sudah sesuai kontrak integrasi. Selain itu, saya juga menguji query GraphQL melalui Playground untuk memastikan data berhasil dikembalikan secara dinamis berdasarkan field yang dipilih client.
