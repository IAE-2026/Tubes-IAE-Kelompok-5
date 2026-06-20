# Ringkasan Kontribusi Faris Fadil Arifin

Saya berkontribusi utama pada `Member-Service-Faris`, yaitu service keanggotaan untuk proyek peminjaman buku. Service ini menyediakan endpoint REST untuk membuat member, melihat daftar member, melihat detail member, serta menyediakan status member yang dipakai oleh Peminjaman Service saat validasi peminjaman.

Kontribusi saya meliputi:

- Membuat dan merapikan Member Service berbasis Laravel.
- Menambahkan model, migration, controller, request validation, resource response, dan route untuk data member.
- Menambahkan proteksi API key melalui header `X-IAE-KEY`.
- Menambahkan dokumentasi API, Swagger/OpenAPI, dan GraphQL untuk data member.
- Menyesuaikan GraphiQL agar mudah dipakai dengan header API key.
- Menambahkan test dasar untuk endpoint member, dokumentasi, dan GraphQL.
- Mengerjakan bagian Tugas 3 individual di folder `Member-Service-Faris/tugas3`, termasuk SSO/JWT, SOAP audit, RabbitMQ publish, dokumentasi analisis, dan test.
- Membantu integrasi repository kelompok melalui Docker Compose gabungan, API Gateway NGINX, dokumentasi integrasi, Postman collection, dan penyesuaian payload SSO M2M agar menyertakan `nim`.

Saya tidak mengklaim sebagai pembuat utama `Peminjaman-Service-Amin` atau `Katalog-Buku-Service-Dewinda`. Untuk kedua service tersebut, kontribusi saya hanya sebatas dukungan integrasi, konfigurasi, dokumentasi, atau penyesuaian kecil agar service bisa berjalan dalam repository gabungan.

Commit yang menjadi bukti kontribusi saya antara lain:

- `ccec7e0` - Add Member Service
- `6a6aac7` - Configure Member Service testing and GraphiQL headers
- `f6d5253` - Add API gateway and Docker compose integration
- `fba2471` - Fix Docker runtime startup
- `225d4e9` - Add SSO M2M nim payload

Kesimpulannya, kontribusi saya paling besar ada pada Member Service dan dukungan integrasi kelompok. Saya juga membantu dokumentasi, testing dasar, gateway, Docker Compose, Postman, dan penyesuaian SSO, tetapi tidak mengklaim fitur utama dari service anggota lain.
