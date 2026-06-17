## [12/6/2026] Analisis Aliran Integrasi & Kebutuhan Tugas 3

**Prompt:**
Tolong bantu aku merancang integrasi untuk Tugas 3 di Service Peminjaman. Aku mau menganalisis kenapa fitur pengembalian buku dinilai paling kritis di sistem perpustakaan kelompok saya, dan bagaimana alur hubungannya dengan server login, sistem pencatatan bukti transaksi resmi, serta notifikasi stok ke katalog buku dan status batas pinjaman ke member.

---

## [12/6/2026] Analisis Pemetaan Profil Pengguna dari Token SSO

**Prompt:**
Bagaimana cara membaca data identitas warga (nama, email, NIM) secara otomatis ketika mereka login? Aku ingin memetakan profil tersebut secara aman dari data login pusat dan mencegah terjadinya kesalahan format penulisan email di database lokal saya.

---

## [12/6/2026] Analisis Pencatatan Resi Transaksi via SOAP Audit

**Prompt:**
Aku mau mengirimkan laporan transaksi pengembalian buku ke server audit pusat. Bagaimana cara mengirimkan data transaksi tersebut dengan aman agar laporan saya tercatat secara sah di pusat dan berhasil menerima nomor resi resmi sebagai tanda buktinya?

---

## [12/6/2026] Analisis Standardisasi Payload RabbitMQ

**Prompt:**
Di dashboard penerima pesan RabbitMQ, judul atau nama status transaksi yang aku kirimkan masih kosong atau berupa strip. Bagaimana cara menyusun ulang struktur data yang dikirim agar status transaksi pengembalian buku ini terbaca dengan jelas oleh sistem pusat?

---

## [12/6/2026] Analisis Sinkronisasi Docker & Pengujian Integrasi Akhir

**Prompt:**
Kenapa setiap kali aku mengubah kode program di komputer, perubahannya tidak langsung terbaca di dalam server virtual Docker? Tolong bantu aku memperbaiki pengaturan penyimpanan servernya agar otomatis tersinkronisasi, dan bantu buatkan data dummy peminjaman untuk uji coba integrasi akhir.

---

## [13/6/2026] Pengecekan Endpoint & Konfigurasi Server

**Prompt:**
Tolong periksa semua pintu akses data (endpoint) di layanan peminjaman ini, apakah sudah sesuai dengan kontrak kesepakatan integrasi kelompok. Dan tolong pastikan konfigurasi server virtual saya sudah benar agar tidak ada masalah koneksi dengan service milik anggota kelompok lain.

---

## [13/6/2026] Analisis Autentikasi & Validasi Akses Pengguna

**Prompt:**
Aku ada kendala di mana pengguna ditolak sistem saat mau melihat data atau mengembalikan buku, padahal status mereka sudah login dengan benar. Bagaimana cara memvalidasi identitas akses pengguna agar sistem lebih fleksibel, dan memastikan hak akses warga dikonfigurasi dengan tepat untuk transaksi peminjaman?

---

## [13/6/2026] Analisis Sinkronisasi Resi & Pengiriman Pesan Transaksi

**Prompt:**
Mengapa proses pengembalian buku yang sudah selesai tidak berhasil mengirimkan laporan resi dan notifikasi pesan ke layanan lain? Tolong bantu aku mendeteksi masalahnya, terutama jika itu terkait dengan alamat server pusat yang tidak terbaca di server virtual atau kegagalan sistem dalam memperoleh kunci akses.
