# Resume Kontribusi Tim

Dokumen ini merangkum kepemilikan service dan kontribusi utama dalam repository gabungan.

| Anggota | Service | Folder | Kontribusi |
| --- | --- | --- | --- |
| Faris Fadil Arifin | Keanggotaan / Member Service | `Member-Service-Faris` | REST member API, GraphQL member query, dokumentasi Swagger, API key protection, data member dan status aktif. |
| Muhammad Amin Hazami | Peminjaman Service | `Peminjaman-Service-Amin` | REST loan API, JWT SSO middleware, validasi lintas service ke member dan katalog, return loan, SOAP audit, RabbitMQ publish. |
| Dewinda Salsabila Putri Haidir | Katalog Buku Service | `Katalog-Buku-Service-Dewinda` | REST catalog API, JWT dan API key middleware, tambah buku, cek stok, update stok internal, SOAP audit, RabbitMQ publish. |
| Kelompok 5 | Integrasi | Root repository | Docker compose gabungan, NGINX API Gateway, network internal, dokumentasi compliance, dan mapping kontrak proses bisnis. |

## Catatan Commit

Repository ini memakai struktur monorepo agar log commit kelompok memperlihatkan kontribusi integrasi dan tetap mempertahankan folder service individu.
