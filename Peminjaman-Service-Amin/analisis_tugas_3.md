# Dokumen Analisis Tugas 3 - Service Peminjaman (loans-service)

**Nama:** Muhammad Amin Hazami  
**NIM:** 102022400110  
**Layanan Kelompok:** Peminjaman-Service  

Fitur Pengembalian Buku dinilai sebagai transaksi paling kritis di peminjaman service karena alasan berikut: 
1. Update Stok Buku (Koneksi ke Service Katalog): 
Transaksi ini menjadi penentu utama perubahan data stok buku. Kalau proses pengembalian ini gagal, buku yang sudah dibalikin tidak akan ter-update di Service Katalog, sehingga warga lain tidak bisa melihat atau meminjam buku tersebut. 
2. Kuota Peminjaman Warga (Koneksi ke Service Member): 
Transaksi ini menandakan bahwa warga sudah memulihkan haknya. Begitu sukses, aplikasi harus mengabari Service Member agar slot atau kuota batas peminjaman aktif milik warga tersebut otomatis dikembalikan seperti semula.
3. Bukti Valid Hubungan Pusat: 
Data pengembalian rawan diotak-atik secara ilegal. Makanya, transaksi ini dilaporkan ke SOAP Audit pusat secara real-time biar kita dapat nomor resi resmi (Receipt Number) untuk TEAM-122 sebagai bukti otentik kalau buku sudah aman dikembalikan.
4. Trigger Event Lintas Service: 
Supaya semua proses di atas berjalan otomatis tanpa perlu input manual, peminjaman service wajib menyiarkan kabar suksesnya pengembalian ini via RabbitMQ agar Service Katalog dan Service Member bisa langsung menangkap datanya secara bersamaan. 
