# Prompt Pack Fresh Commit History

Tujuan dokumen ini adalah membantu Kelompok 5 membuat ulang repository gabungan dari repository kosong dengan riwayat commit yang adil dan dapat dipertanggungjawabkan. Setiap anggota harus menjalankan prompt miliknya sendiri di komputer atau akun GitHub masing-masing, lalu melakukan commit dengan identitas GitHub sendiri.

Jangan memakai prompt ini untuk membuat commit palsu atas nama orang lain. Requirement 30% kontribusi individu dinilai dari bukti kontribusi nyata, jadi commit harus berasal dari anggota yang benar-benar mengerjakan bagian tersebut.

## Urutan Kerja

1. Buat ulang repository kosong: `IAE-2026/Tubes-IAE-Kelompok-5`.
2. Jangan centang README, `.gitignore`, atau license saat membuat repository.
3. Tambahkan Faris, Amin, dan Dewinda sebagai collaborator.
4. Faris menjalankan prompt `faris-member-service.md`, lalu push.
5. Amin menjalankan prompt `amin-peminjaman-service.md`, lalu push.
6. Dewinda menjalankan prompt `dewinda-katalog-service.md`, lalu push.
7. Setelah tiga folder service sudah masuk, satu orang integrator menjalankan prompt `final-integrator.md`.

## Target Struktur Akhir

```text
Tubes-IAE-Kelompok-5/
  Member-Service-Faris/
  Peminjaman-Service-Amin/
  Katalog-Buku-Service-Dewinda/
  api-gateway/
  docs/
  postman/
  .gitignore
  docker-compose.yml
  README.md
```

## Commit Yang Disarankan

Faris:

```text
Add Member Service
Configure Member Service testing and GraphiQL headers
```

Amin:

```text
Add Peminjaman Service
Integrate loan flow with member and catalog services
Configure Peminjaman Service testing
```

Dewinda:

```text
Add Katalog Buku Service
Add catalog stock endpoints for loan flow
Fix Katalog dependency lockfile for PHP 8.3 testing
```

Integrator:

```text
Add API gateway and Docker compose integration
Add Tubes documentation and Postman collection
```

## Catatan Penting

- Setiap anggota wajib menjalankan `git config user.name` dan `git config user.email` sebelum commit.
- Setiap anggota wajib `git pull` sebelum menambahkan commit baru.
- Jangan force push selama proses ini.
- Jangan copy `.git` dari repository service individu ke dalam folder service di repository gabungan.
- Jangan commit `vendor/`, `node_modules/`, `.env`, file cache, atau file database lokal.
- Jika Docker Desktop belum aktif, tetap jalankan minimal `docker compose config` setelah integrasi final.

