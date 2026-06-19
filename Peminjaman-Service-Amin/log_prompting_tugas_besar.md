Log Prompting AI - Tugas Besar Peminjaman Service
Nama: Muhammad Amin Hazami (Zami)
NIM: 102022400110
Layanan: Peminjaman-Service-Amin

[10/06/2026 - 13:15]

Bantu aku merancang API untuk Peminjaman Service di Laravel. Alur bisnisnya: siswa meminjam buku, terus sistem harus ngecek dulu apakah dia terdaftar sebagai member aktif di Member Service, dan apakah stok bukunya ada di Katalog Service. Kalau semua aman, sistem akan memotong stok buku di Katalog Service. Apa saja endpoint yang perlu aku buat di controller peminjaman?

[11/06/2026 - 10:20]

Gimana caranya melakukan request HTTP API ke service lain di Laravel? Di Docker, alamat Member Service itu http://member-service:8000 dan Katalog Service itu http://katalog-buku-service:8000. Aku mau validasi member ID dan potong stok buku pas peminjaman baru dibuat. Buatkan fungsi privat di controller Laravel untuk handle request ini.

[12/06/2026 - 15:35]

Endpoint peminjaman dan pengembalian buku di Peminjaman Service harus diamankan menggunakan token JWT dari SSO Warga. Bagaimana cara membuat middleware di Laravel 11 untuk memverifikasi token JWT tersebut memakai public key (JWKS) dari endpoint SSO https://iae-sso.virtualfri.id/api/v1/auth/jwks? Aku tidak mau pakai library eksternal JWT yang berat, tolong buatkan parser JWT manual menggunakan openssl bawaan PHP.

[13/06/2026 - 09:40]

Saat pengembalian buku berhasil, sistem harus mengirimkan log transaksi ke server audit pusat dosen via SOAP XML untuk mendapatkan nomor resi resmi (Receipt Number). Format XML SOAP-nya harus berisi <iae:AuditRequest> dengan tag <iae:TeamID>, <iae:ActivityName>, dan <iae:LogContent> (berisi data JSON pengembalian). Tolong buatkan class service di Laravel untuk menyusun envelope XML ini dan mengirimkannya ke endpoint SOAP central.

[13/06/2026 - 14:15]

Selain SOAP, setelah return buku sukses aku juga harus mempublikasikan event pengembalian ke antrean RabbitMQ pusat lewat REST API proxy milik SSO di https://iae-sso.virtualfri.id/api/v1/messages/publish. Payload JSON-nya berisi target exchange iae.central.exchange dan routing key library.loan.returned. Tolong buatkan service class di Laravel untuk mempublikasikan data ini secara terstruktur.

[19/06/2026 - 21:05]

Ada aturan baru dari dosen. Sekarang kalau mau meminta token SSO M2M, kita wajib melampirkan parameter nim di body request, bukan cuma api_key. Contoh curl dari dosen:
curl -s -X POST http://localhost:8080/api/v1/auth/token -H "Content-Type: application/json" -d '{"api_key":"KEY-MHS-45","nim":"102022400033"}'
Tolong benerin controller Peminjaman aku agar memanggil token SSO M2M dengan mengirimkan api_key dan nim (NIM-ku: 102022400110, API Key: KEY-MHS-122) dalam format JSON. Serta perbaiki caching tokennya supaya tidak nge-cache nilai null kalau request-nya sempat gagal.

[19/06/2026 - 21:30]

Kenapa ya pas aku coba ngetes endpoint Create Loan lewat API Gateway di port 8080 muncul error:
SQLSTATE[HY000] [2002] php_network_getaddresses: getaddrinfo for db failed: No address associated with hostname (Connection: mysql, SQL: select * from cache where key in (sso_jwks))
Padahal di docker-compose.yml peminjaman service, aku sudah set DB_HOST: mysql-peminjaman. Tolong bantu cari tahu kenapa dia malah nyari host bernama db.

[19/06/2026 - 22:35]

Aku mau mengunggah perbaikan kode ini ke GitHub kelompok secara aman. Teman-teman meminta agar tidak langsung di-push ke main, tapi dibuatkan branch dulu biar bisa ditinjau lewat Pull Request. Tolong sebutkan perintah Git untuk membuat branch baru bernama zami, melakukan commit perubahan, dan mengunggahnya ke GitHub.

[19/06/2026 - 22:38]

Berarti kalau kodenya sudah digabung ke main semua, terus branch zami itu masih ada gunanya nggak ya?

[19/06/2026 - 22:42]

Jadi selama kodenya belum benar-benar selesai atau belum yakin, jangan langsung digabung ke main dulu ya?

[19/06/2026 - 22:45]

Oiya, waktu meminjam buku kok aku nggak dapat nomor resi (receipt number) ya?

[19/06/2026 - 22:55]

Terus setelah ini kita harus ngapain lagi?

[19/06/2026 - 22:58]

Eh, ini ada tulisan branch main belum dilindungi di GitHub. Maksudnya apa ya?

[19/06/2026 - 22:59]

Kalau status behind sama ahead di branch git itu artinya apa sih?

[19/06/2026 - 23:00]

Oke, coba kita pull kodenya sekarang biar update.

[19/06/2026 - 23:02]

Kok status behind punya aku masih ada aja ya di terminal?

[19/06/2026 - 23:04]

Jadi tugas punyaku sekarang gmn? Apakah sudah beres semua?

[19/06/2026 - 23:05]

Oiya, Swagger itu buat apa sih sebenarnya?
