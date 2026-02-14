# Perbaikan Thumbnail Gambar InfinityFree

## Masalah
Thumbnail gambar di section Gallery tidak muncul setelah deploy ke InfinityFree.

## Penyebab
Path gambar di database disimpan dengan prefix `/aplikasi-air/` (untuk localhost), tetapi di InfinityFree tidak ada folder `aplikasi-air` karena website di-deploy langsung ke `htdocs/`.

**Contoh path di database:**
- Localhost: `/aplikasi-air/uploads/gallery/gambar.jpg` ✅ Berfungsi
- InfinityFree: `/aplikasi-air/uploads/gallery/gambar.jpg` ❌ 404 Not Found

## Solusi yang Diterapkan
File `api/konten.php` telah diperbaiki dengan:
1. Menambahkan fungsi `normalizeImagePath()` untuk mengkonversi path otomatis
2. Path baru yang diupload sekarang menggunakan format `/uploads/...` (tanpa prefix `/aplikasi-air/`)
3. Path lama dengan prefix `/aplikasi-air/` akan otomatis dikonversi saat data dibaca

## Langkah Upload ke InfinityFree

### 1. Upload File API yang Diperbaiki
Upload file berikut ke server InfinityFree:

```
Dari komputer (lokal):          ke InfinityFree:
api/konten.php          -->     htdocs/api/konten.php
```

### 2. Upload Folder dist (Opsional - jika ada perubahan frontend)
Jika Anda telah melakukan build ulang, upload isi folder `dist/`:

```
dist/index.html         -->     htdocs/index.html
dist/assets/*           -->     htdocs/assets/
```

### 3. Pastikan Folder uploads Ada
Struktur folder yang benar di InfinityFree:

```
htdocs/
├── api/
│   ├── konten.php (BARU - upload ini)
│   ├── util/
│   │   └── db.php
│   └── ...
├── uploads/
│   ├── gallery/    <- Pastikan folder ini ada dan berisi gambar
│   ├── berita/
│   └── pengurus/
├── assets/
├── index.html
└── ...
```

### 4. Cek Website
Setelah upload, buka halaman Gallery di website Anda untuk memastikan gambar sudah muncul.

## Opsi Tambahan: Update Path di Database (Jika Diperlukan)
Jika gambar masih tidak muncul, Anda mungkin perlu mengupdate path di database.
Jalankan query SQL ini di phpMyAdmin InfinityFree:

```sql
-- Update path galeri
UPDATE galeris SET image_path = REPLACE(image_path, '/aplikasi-air/', '/');

-- Update path berita (jika ada)
UPDATE beritas SET foto_url = REPLACE(foto_url, '/aplikasi-air/', '/');

-- Update path pengurus (jika ada)
UPDATE pengurus SET foto_url = REPLACE(foto_url, '/aplikasi-air/', '/');
```

## Verifikasi

Setelah selesai, cek:
1. ✅ Halaman Gallery Archive - gambar thumbnail muncul
2. ✅ Halaman Landing Page - section galeri muncul dengan gambar
3. ✅ Halaman Berita - foto berita muncul (jika ada)
4. ✅ Halaman Profil - foto pengurus muncul (jika ada)
