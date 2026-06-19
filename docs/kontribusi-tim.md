# Kontribusi Tim

Dokumen ini merangkum kontribusi tiap anggota pada repository gabungan Kelompok 5. Commit service dibuat oleh akun masing-masing anggota agar bukti kontribusi individu terlihat di Git history.

## Faris Fadil Arifin

Folder: `Member-Service-Faris`

Commit:

- `ccec7e0` - `Add Member Service`
- `6a6aac7` - `Configure Member Service testing and GraphiQL headers`

Kontribusi:

- Menambahkan Member Service ke repository gabungan.
- Menyediakan REST endpoint member di `/api/v1/members`.
- Menyediakan GraphQL member query.
- Menambahkan konfigurasi GraphiQL agar header `X-IAE-KEY` tersedia untuk pengujian.
- Menambahkan konfigurasi PHPUnit agar test Member Service bisa berjalan di environment lokal.

## Muhammad Amin Hazami

Folder: `Peminjaman-Service-Amin`

Commit:

- `26d949f` - `Add Peminjaman Service`
- `b56af37` - `Integrate loan flow with member and catalog services`
- `fe27494` - `Configure Peminjaman Service testing`

Kontribusi:

- Menambahkan Peminjaman Service ke repository gabungan.
- Menyediakan endpoint loan di `/api/v1/loans`.
- Menyediakan proses return loan di `/api/v1/loans/{id}/return`.
- Menambahkan validasi member ke Member Service saat create loan.
- Menambahkan validasi buku dan stok ke Katalog Buku Service saat create loan.
- Menambahkan proses pengurangan dan pengembalian stok katalog dari Peminjaman Service.
- Menyertakan integrasi return-side untuk token SSO, SOAP Audit, dan publish event AMQP.

## Dewinda Salsabila Putri Haidir

Folder: `Katalog-Buku-Service-Dewinda`

Commit:

- `f7520e5` - `Add Katalog Buku Service`
- `9c691eb` - `Add catalog stock endpoints for loan flow`

Kontribusi:

- Menambahkan Katalog Buku Service ke repository gabungan.
- Menyediakan endpoint buku di `/api/v1/books`.
- Menambahkan endpoint pengurangan stok buku saat peminjaman.
- Menambahkan endpoint pengembalian stok buku saat return.
- Menyediakan integrasi SSO, SOAP Audit, dan RabbitMQ publisher untuk proses katalog.

## Integrasi Root

Folder/file: `api-gateway`, `docker-compose.yml`, `README.md`, `docs`, dan `postman`.

Kontribusi:

- Menambahkan gateway NGINX.
- Menambahkan Docker Compose gabungan.
- Menyusun dokumentasi requirement, kontribusi, test, dan Postman.
- Memastikan hanya gateway yang terbuka ke host.
