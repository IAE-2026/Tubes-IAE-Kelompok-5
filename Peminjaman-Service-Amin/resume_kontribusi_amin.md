# Resume Kontribusi Individu — Tugas Besar IAE

Nama: Muhammad Amin Hazami NIM: 102022400110 Kelompok: 5 Service: Peminjaman Service

## Detail Kontribusi

* **Pembangunan dan Integrasi Peminjaman Service** (Commit: `26d949f` , `b56af37` ) Saya membuat fondasi awal Peminjaman Service menggunakan Laravel 11. Saya mengimplementasikan API untuk pembuatan transaksi peminjaman (`Create Loan`) dan pengembalian (`Return Loan`), yang berkomunikasi dengan Member Service dan Katalog Buku Service untuk memvalidasi data anggota serta memperbarui stok buku secara dinamis.

* **Pengujian dan Otomatisasi Unit Test** (Commit: `fe27494` ) Saya mengonfigurasi dan menulis automated testing menggunakan PHPUnit untuk memastikan semua endpoint utama (peminjaman, pengembalian, middleware JWT) dapat berjalan dan diuji secara otomatis dan handal tanpa merusak alur program lainnya.

* **Penyesuaian SSO M2M Token dan Parameter NIM** (Commit: `ac38281` , `b3a09e3` ) Saya menyesuaikan request autentikasi ke SSO M2M Dosen dengan menambahkan parameter wajib `nim` (NIM: 102022400110) serta memperbaiki alur caching token agar tidak menyimpan status null saat koneksi ke SSO gagal.

* **Review, Dokumentasi dan Sinkronisasi Branch** (Commit: `81c56c7` , `53e579c` ) Saya mengelola alur Git dengan membuat branch terpisah `zami` untuk perbaikan SSO, mengajukan Pull Request ke branch utama, meninjau perubahannya, dan menggabungkannya (*merge*) ke branch `main`. Saya juga menyusun dokumen analisis dan log prompting AI secara terstruktur.
