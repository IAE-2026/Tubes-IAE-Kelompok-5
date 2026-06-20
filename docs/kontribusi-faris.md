# Kontribusi Faris Fadil Arifin

Dokumen ini dibuat untuk menjelaskan kontribusi saya pada proyek kelompok secara jujur dan tidak melebih-lebihkan. Bagian yang ditulis di sini berdasarkan struktur repository, dokumen yang sudah ada, dan commit history.

Nama: Faris Fadil Arifin

NIM/API key yang digunakan di service: `102022400255`

Area utama: `Member-Service-Faris`

## Ringkasan Singkat

Kontribusi utama saya ada pada pembuatan dan perapihan Member Service, yaitu service keanggotaan yang dipakai oleh alur peminjaman buku untuk menyimpan data member dan memvalidasi status member. Selain itu, saya juga membantu integrasi repository kelompok, API Gateway, Docker Compose gabungan, dokumentasi, Postman collection, dan penyesuaian payload SSO M2M agar menyertakan `nim`.

Saya tidak mengklaim sebagai pembuat utama Peminjaman Service atau Katalog Buku Service. Kedua service tersebut tetap menjadi kontribusi utama anggota lain.

## Kontribusi Utama

### 1. Member Service

Saya mengerjakan bagian Member Service yang berada di folder `Member-Service-Faris`. Service ini berfungsi sebagai sumber data member pada proses bisnis peminjaman buku.

Bagian yang saya kontribusikan:

- Membuat struktur service Laravel untuk keanggotaan.
- Membuat model dan tabel `members`.
- Menyediakan endpoint REST untuk:
  - membuat member,
  - melihat daftar member,
  - melihat detail member berdasarkan ID.
- Menambahkan response format yang konsisten untuk sukses dan error.
- Menambahkan validasi input saat membuat member.
- Menambahkan proteksi API key melalui header `X-IAE-KEY`.
- Menyediakan data status member seperti `status` dan `is_active` agar bisa dipakai oleh Peminjaman Service.

File terkait:

- `Member-Service-Faris/app/Http/Controllers/MemberController.php`
- `Member-Service-Faris/app/Http/Requests/StoreMemberRequest.php`
- `Member-Service-Faris/app/Http/Resources/MemberResource.php`
- `Member-Service-Faris/app/Models/Member.php`
- `Member-Service-Faris/database/migrations/2026_05_14_000000_create_members_table.php`
- `Member-Service-Faris/routes/api.php`

### 2. Dokumentasi Member Service

Saya juga menambahkan dokumentasi untuk cara menjalankan dan memakai Member Service.

Bagian dokumentasi yang relevan:

- cara menjalankan service dengan Docker,
- konfigurasi database,
- kontrak endpoint REST,
- contoh request `curl`,
- format response,
- cara akses Swagger/OpenAPI,
- cara akses GraphQL.

File terkait:

- `Member-Service-Faris/README.md`
- `Member-Service-Faris/docs/service-requirements.md`
- `Member-Service-Faris/docs/generic-assignment-prompt.md`
- `Member-Service-Faris/docs/member-history-ai-chat.md`
- `Member-Service-Faris/prompt_engineering_log.md`

### 3. Swagger dan GraphQL

Saya menambahkan dukungan dokumentasi API dan query GraphQL pada Member Service.

Kontribusi pada bagian ini:

- Menambahkan konfigurasi Swagger/OpenAPI.
- Menambahkan dokumentasi endpoint member.
- Menambahkan schema GraphQL untuk membaca data member.
- Menyesuaikan GraphiQL agar header `X-IAE-KEY` lebih mudah dipakai saat pengujian.

File terkait:

- `Member-Service-Faris/app/OpenApi/MemberServiceOpenApi.php`
- `Member-Service-Faris/graphql/schema.graphql`
- `Member-Service-Faris/config/l5-swagger.php`
- `Member-Service-Faris/config/lighthouse.php`
- `Member-Service-Faris/config/graphiql.php`
- `Member-Service-Faris/resources/views/vendor/graphiql/index.blade.php`

### 4. Testing Member Service

Saya menambahkan dan menyesuaikan test untuk memastikan endpoint dasar Member Service berjalan.

Bagian yang diuji:

- endpoint member,
- dokumentasi dan GraphQL,
- konfigurasi testing Laravel,
- header API key untuk kebutuhan pengujian.

File terkait:

- `Member-Service-Faris/tests/Feature/MemberApiTest.php`
- `Member-Service-Faris/tests/Feature/DocumentationAndGraphqlTest.php`
- `Member-Service-Faris/phpunit.xml`

Catatan jujur: test yang ada masih fokus pada fungsi dasar service dan konfigurasi. Test ini belum membuktikan semua skenario produksi secara penuh.

### 5. Tugas 3 Individual di Dalam Folder Member Service

Di dalam `Member-Service-Faris/tugas3`, saya juga mengerjakan bagian Tugas 3 individual yang terpisah dari service Laravel utama.

Bagian yang dikerjakan:

- implementasi login/token SSO,
- verifikasi JWT,
- mapping role lokal `WARGA`,
- simulasi transaksi kritis `PermitPaymentConfirmed`,
- pembuatan SOAP audit envelope,
- publish event RabbitMQ,
- test menggunakan `node:test`,
- dokumentasi analisis dan log prompting.

File terkait:

- `Member-Service-Faris/tugas3/src/server.js`
- `Member-Service-Faris/tugas3/src/centralClient.js`
- `Member-Service-Faris/tugas3/src/paymentService.js`
- `Member-Service-Faris/tugas3/src/jwt.js`
- `Member-Service-Faris/tugas3/test/auth.test.js`
- `Member-Service-Faris/tugas3/test/centralClient.test.js`
- `Member-Service-Faris/tugas3/test/paymentService.test.js`
- `Member-Service-Faris/analisis_tugas_3.md`
- `Member-Service-Faris/prompt_engineering_log.md`

## Kontribusi Integrasi Kelompok

Selain Member Service, saya juga membantu menyusun repository gabungan dan beberapa bagian integrasi.

Bagian yang saya bantu:

- Menyiapkan struktur monorepo agar tiga service berada dalam satu repository kelompok.
- Menambahkan `docker-compose.yml` gabungan.
- Menambahkan konfigurasi `api-gateway/nginx.conf`.
- Membuat atau memperbarui dokumentasi integrasi kelompok.
- Menambahkan Postman collection dan environment untuk pengujian lewat gateway.
- Menyesuaikan konfigurasi Docker runtime agar service bisa dijalankan bersama.
- Menyesuaikan request token SSO M2M agar payload menyertakan `nim`.

File terkait:

- `docker-compose.yml`
- `api-gateway/nginx.conf`
- `README.md`
- `docs/tubes-compliance-report.md`
- `docs/test-report.md`
- `docs/postman-test-report.md`
- `docs/kontribusi-tim.md`
- `docs/laporan-per-anggota.md`
- `postman/Tubes-IAE-Kelompok-5.postman_collection.json`
- `postman/Tubes-IAE-Kelompok-5.postman_environment.json`

Catatan jujur: pada bagian integrasi, beberapa perubahan menyentuh service anggota lain untuk kebutuhan penyamaan konfigurasi, testing, atau kontrak integrasi. Itu bukan berarti saya membuat seluruh fitur utama service mereka.

## Bukti dari Commit History

Beberapa commit yang relevan dengan kontribusi saya:

- `ccec7e0` - Add Member Service
- `6a6aac7` - Configure Member Service testing and GraphiQL headers
- `f6d5253` - Add API gateway and Docker compose integration
- `fba2471` - Fix Docker runtime startup
- `225d4e9` - Add SSO M2M nim payload
- `9ad8b62` - Add Postman collection and report
- `101ebe5` - Add per-member change report
- `ace3c0e` - Patch Tubes integration requirements

## Hal yang Tidak Saya Klaim

Agar tidak berlebihan, berikut bagian yang tidak saya klaim sebagai kontribusi utama saya:

- Saya bukan pembuat utama `Peminjaman-Service-Amin`.
- Saya bukan pembuat utama `Katalog-Buku-Service-Dewinda`.
- Saya tidak mengklaim semua logic peminjaman, return loan, dan pengurangan stok sebagai pekerjaan utama saya.
- Saya tidak mengklaim semua integrasi SOAP/RabbitMQ di service lain sebagai pekerjaan pribadi.
- Saya tidak mengklaim Docker flow end-to-end sudah terbukti penuh di laptop lokal, karena pada laporan pengujian sebelumnya Docker Desktop Linux engine belum aktif.

## Kesimpulan

Kontribusi saya paling kuat ada di Member Service dan dukungan integrasi repository kelompok. Saya juga membantu dokumentasi, konfigurasi gateway, Docker Compose, Postman, dan penyesuaian SSO M2M. Secara jujur, pekerjaan saya lebih banyak pada service keanggotaan, integrasi, konfigurasi, testing dasar, dan dokumentasi, bukan pada fitur utama service peminjaman atau katalog yang dikerjakan anggota lain.
