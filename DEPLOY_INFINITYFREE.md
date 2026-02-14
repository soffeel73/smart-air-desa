# Panduan Upload ke InfinityFree (Revised)

Berikut adalah langkah-langkah untuk memperbaiki error "Cetak Struk tidak berjalan" dan mengupdate website Anda.

## 1. Persiapan File
Saya telah memperbaiki kode yang menyebabkan error (path yang salah). Anda perlu mengupload ulang file hasil build terbaru.

1.  Pastikan proses build di komputer Anda sudah selesai (folder `dist` diperbarui).
2.  Siapkan folder `dist` dan folder `api`.

## 2. Setting Database (PENTING)
Agar website bisa connect ke database InfinityFree, Anda **WAJIB** membuat file koneksi baru di server.

1.  Buka File Manager di InfinityFree (atau gunakan FileZilla).
2.  Masuk ke folder `htdocs/api/util/`.
3.  Upload file `api/util/db_production.php` yang baru saya buat.
4.  **RENAME (Ubah Nama)** file `db_production.php` menjadi `db.php` di server (ini akan menimpa `db.php` lama yang isinya localhost).
5.  **EDIT** file `db.php` tersebut di InfinityFree, lalu isi dengan detail database Anda:
    - **Host**: (Contoh: `sql300.infinityfree.com`)
    - **Database Name**: (Contoh: `if0_38246473_smart_air`)
    - **Username**: (Contoh: `if0_38246473`)
    - **Password**: Password Vpanel/Database Anda.

## 3. Upload File Website
Karena kita mengubah struktur kode, sebaiknya hapus file lama untuk menghindari konflik.

1.  Di File Manager InfinityFree, masuk ke `htdocs`.
2.  **HAPUS** semua isinya (hati-hati jangan hapus `api` jika Anda sudah upload banyak gambar di `uploads`, cukup hapus file `index.html`, folder `assets`, dll).
3.  **UPLOAD** isi dari folder `dist` (komputer) ke `htdocs` (server).
    - Isinya harusnya: `index.html`, folder `assets`, `favicon.ico`.
4.  **UPLOAD** folder `api` (komputer) ke `htdocs` (server).
    - Pastikan strukturnya: `htdocs/api/cetak_struk.php`, dll.

## 4. Struktur Akhir di Server
Pastikan struktur file di `htdocs` Anda terlihat seperti ini:

```
htdocs/
├── api/
│   ├── util/
│   │   └── db.php (Isinya kredensial InfinityFree)
│   ├── cetak_struk.php
│   ├── pelanggan.php
│   └── ... (file php lainnya)
├── assets/
│   ├── index-xxxx.css
│   └── index-xxxx.js
├── uploads/
│   └── (folder gambar pengurus, dll)
├── index.html
└── ...
```

## 5. Cek Website
Buka `https://hippams.rf.gd/#/admin/cetak-struk`. Seharusnya sekarang sudah berjalan normal.
