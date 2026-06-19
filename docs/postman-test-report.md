# Laporan Test Postman

Tanggal: 2026-06-17

## Artefak Postman

| File | Keterangan |
| --- | --- |
| `postman/Tubes-IAE-Kelompok-5.postman_collection.json` | Collection Postman untuk menguji gateway dan alur end-to-end peminjaman buku. |
| `postman/Tubes-IAE-Kelompok-5.postman_environment.json` | Environment lokal dengan `base_url`, API key, SSO URL, dan variable runtime seperti `member_id`, `book_id`, `loan_id`. |

## Status Eksekusi Lokal

| Komponen | Status | Catatan |
| --- | --- | --- |
| Docker runtime | BLOCKED | Docker Desktop Linux engine belum berjalan. Error: `failed to connect to the docker API ... dockerDesktopLinuxEngine`. |
| Postman CLI/Newman lokal | BLOCKED | Command `postman`, `newman`, `npm`, `npx`, dan `node` tidak tersedia di PATH. |
| Collection JSON validation | PASS | Collection dan environment dibuat sebagai JSON valid dan siap diimport ke Postman. |
| API runtime test via Postman | BELUM BISA DIJALANKAN | Butuh service aktif di `http://localhost:8080` lewat `docker compose up --build`. |
| Gateway health dari PowerShell | BLOCKED | `Invoke-WebRequest http://localhost:8080/health` gagal: `Unable to connect to the remote server`. |

## Skenario Dalam Collection

| Urutan | Request | Tujuan |
| --- | --- | --- |
| 00 | `GET /health` | Memastikan API Gateway hidup. |
| 01 | `POST {{base_url}}/api/v1/auth/token` | Mengambil token M2M SSO dengan body `api_key` dan `nim`, lalu menyimpan ke `sso_m2m_token`. |
| 01 Login | `POST {{sso_url}}/api/v1/auth/token` | Mengambil JWT SSO user dan menyimpan ke `sso_jwt`. |
| 02 | `POST /api/v1/members` | Membuat member baru dan menyimpan `member_id`. |
| 03 | `GET /api/v1/members/{{member_id}}` | Memastikan member aktif. |
| 04 | `POST /api/v1/books` | Membuat buku baru dan menyimpan `book_id`. |
| 05 | `GET /api/v1/books/{{book_id}}` | Memastikan buku punya stok tersedia. |
| 06 | `POST /api/v1/loans` | Membuat loan; memvalidasi member dan buku lintas service. |
| 07 | `GET /api/v1/loans/{{loan_id}}` | Memastikan loan aktif. |
| 08 | `POST /api/v1/loans/{{loan_id}}/return` | Mengembalikan buku dan memastikan stok katalog dipulihkan. |
| 09 | `GET /` | Memastikan gateway root menampilkan daftar route. |

## Cara Menjalankan Di Postman

1. Jalankan Docker Desktop.
2. Dari root repository, jalankan:

```bash
docker compose up --build
```

3. Import file berikut ke Postman:

```text
postman/Tubes-IAE-Kelompok-5.postman_collection.json
postman/Tubes-IAE-Kelompok-5.postman_environment.json
```

4. Pilih environment `Tubes IAE Kelompok 5 - Local`.
5. Jika request SSO user login gagal karena kredensial berubah, isi manual variable `sso_jwt` dengan JWT SSO yang valid.
6. Klik `Run Collection`.

## Ekspektasi Hasil

- Gateway health mengembalikan `200 OK`.
- Member berhasil dibuat dan `is_active = true`.
- Buku berhasil dibuat dengan `available_stock >= 1`.
- Loan berhasil dibuat dengan status `active`.
- Return loan mengubah status menjadi `returned`.
- Response return loan menyertakan `catalog_stock.available_stock`.

## Catatan

Collection ini menguji akses dari host hanya melalui gateway `http://localhost:8080`, sesuai requirement bahwa service internal tidak boleh diakses langsung dari luar bypass gateway.
