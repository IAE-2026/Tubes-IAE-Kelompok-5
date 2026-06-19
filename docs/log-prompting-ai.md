# Log Prompting AI

Dokumen ini merangkum penggunaan AI selama penyusunan repository gabungan Kelompok 5.

| Tanggal | Prompt / Aktivitas | Hasil |
| --- | --- | --- |
| 2026-06-17 | Membuat strategi fresh-history agar kontribusi tiap anggota terlihat di GitHub. | Diputuskan menggunakan repo kosong dan commit service oleh akun masing-masing anggota. |
| 2026-06-17 | Membuat prompt per anggota untuk Faris, Amin, Dewinda, dan integrator akhir. | Prompt pack dibuat untuk membantu tiap anggota melakukan commit sesuai folder service masing-masing. |
| 2026-06-17 | Menambahkan Member Service dari repository individu Faris. | Folder `Member-Service-Faris` masuk dengan commit Faris. |
| 2026-06-17 | Mengonfigurasi GraphiQL dan PHPUnit Member Service. | GraphiQL membawa header `X-IAE-KEY`; PHPUnit Member Service dapat dijalankan lokal. |
| 2026-06-17 | Menambahkan Katalog Buku Service dan endpoint stok. | Folder katalog masuk dengan commit Dewinda dan endpoint borrow/return stock tersedia. |
| 2026-06-17 | Menambahkan Peminjaman Service. | Folder peminjaman masuk dengan commit Amin. |
| 2026-06-17 | Menjalankan prompt final integrator untuk gateway, compose, docs, dan Postman. | Root integration dibuat: NGINX gateway, `docker-compose.yml`, docs, dan Postman collection. |
| 2026-06-19 | Menyesuaikan pemanggilan SSO M2M Token dengan penambahan wajib parameter NIM dan perbaikan konfigurasi DB host. | Perubahan SSO M2M token request ke format JSON dengan NIM, perbaikan DB host di Peminjaman Service, pengujian PHPUnit lulus, dan sinkronisasi branch `zami` ke branch `main`. |

Catatan: AI digunakan sebagai asisten implementasi dan dokumentasi. Commit tetap dibuat sesuai area tanggung jawab anggota agar Git history dapat menjadi bukti kontribusi.
