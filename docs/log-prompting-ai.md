# Log Prompting AI Kelompok

## Prompt 1

**Tujuan:** Menggabungkan repository service individu ke repository kelompok.

**Ringkasan prompt:** Meminta Codex menggunakan repo kelompok `IAE-2026/Tubes-IAE-Kelompok-5`, menjadikan repo kelompok lain sebagai referensi struktur folder, lalu menarik service Faris, Amin, dan Dewinda.

**Output:** Tiga service dipindahkan ke folder masing-masing, ditambahkan `docker-compose.yml`, `api-gateway/nginx.conf`, `.gitignore`, dan `README.md`.

## Prompt 2

**Tujuan:** Memastikan repository GitHub tidak kosong.

**Ringkasan prompt:** Menanyakan kenapa repo GitHub masih empty.

**Output:** Perubahan lokal di-commit dan di-push ke branch `main`.

## Prompt 3

**Tujuan:** Menguji project dan menyiapkan report.

**Ringkasan prompt:** Meminta test dan report.

**Output:** Dilakukan pemeriksaan Docker, PHP/Composer, Node/npm, validasi compose, validasi composer, dan sebagian PHPUnit. Docker runtime belum bisa diuji karena Docker Desktop Linux engine belum berjalan.

## Prompt 4

**Tujuan:** Patch project berdasarkan requirement Tugas Besar dan kontrak proses bisnis.

**Ringkasan prompt:** Meminta patch berdasarkan luaran/rubrik Tugas Besar dan file `Kontrak TUBES IAE.md`.

**Output:** Root compose dibuat gateway-only, loan service ditambahi validasi internal ke member dan katalog, catalog service ditambahi endpoint update stok internal, SSO/SOAP/RabbitMQ dibuat berbasis environment, dan laporan compliance dibuat di folder `docs/`.
