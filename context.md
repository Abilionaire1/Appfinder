# Context Transfer — AppFinder Project

Dokumen ini dibuat untuk melanjutkan pengembangan di sesi/akun baru tanpa kehilangan konteks teknis, riwayat diskusi, maupun roadmap pengerjaan.

---

## 1. Lokasi & Environment Project
- **Path Proyek:** `D:\laragon\www\another\appfinder` *(Catatan: jangan tertukar dengan `D:\laragon\www\Newfolder\appfinder`)*
- **URL Lokal Laragon:** `http://localhost/another/appfinder/`
- **Database:** MySQL / MariaDB (Database name: `appfinder`, User: `root`, Password: ``, Host: `127.0.0.1`)
- **Version Control:** Git lokal sudah diinisialisasi (`git status`, titik backup aman tersedia).

---

## 2. Tech Stack & Arsitektur
- **Backend:** Native PHP 8.x dengan PDO MySQL & Session Auth.
- **Routing:** Parameter URL query `?page={home|explore|detail|dashboard|login|register|logout}` via `index.php`.
- **Frontend / Styling:** Tailwind CSS via CDN (`https://cdn.tailwindcss.com`), Google Fonts (`Plus Jakarta Sans`, `Inter`, `JetBrains Mono`).
- **Gaya Desain yang Disepakati:**
  - **Clean, Modern, Engineering/Curated Aesthetic** (Terinspirasi Linear / Raycast).
  - *Penting:* Jangan gunakan tema neon berlebihan, gradient mencolok, atau glassmorphism norak karena sudah pernah di-reject client/user. Pertahankan gaya clean, berkelas, rapi, dan fungsional.

---

## 3. Struktur Database Singkat (`database.sql`)
1. **`categories`**: Daftar kategori software (Education, Office, Editing, Coding, Streaming, Entertainment, Art, Music, Books, Quran).
2. **`apps`**: Tabel katalog software:
   - Field penting: `id`, `name`, `slug`, `category_id`, `description`, `developer`, `icon`, `rating`, `reviews_count`, `play_store_url`, `website_url`, `is_free`, `is_open_source`, `license`, `alternative_to`, `version`, `platforms`.
3. **`users`**: Tabel akun pengguna (`id`, `username`, `email`, `password`, `role`, `created_at`).
4. **`bookmarks`**: Relasi penandaan wishlist user (`id`, `user_id`, `app_id`, `created_at`).

---

## 4. Riwayat Perkembangan & Keputusan Kunci Terakhir

### A. Evaluasi Nilai Login (Value Proposition)
- **Diskusi:** Fitur login yang hanya menawarkan "Bookmark/Wishlist" dinilai kurang memberi dampak nyata bagi pengguna.
- **Ide Pengembangan Manfaat Login ke Depan:**
  1. *Stack Generator / Software Bundle:* User bisa menyusun kumpulan software kebutuhan instalasi baru dan menyalin command instalasi batch (misal: Winget CLI command).
  2. *Community Review & Pros/Cons:* Ulasan jujur pengguna mengenai kestabilan dan apakah software tersebut tetap bebas paywall.
  3. *History Unduhan & Versi:* Pelacakan software apa saja yang sudah dipasang user di perangkat mereka.

### B. Fitur Smart Auto-Detect & Multi-Platform Download (SELESAI DIKERJAKAN)
- **File Diubah:** [`pages/detail.php`](file:///D:/laragon/www/another/appfinder/pages/detail.php)
- **Implementasi:**
  - Menggantikan sistem tanya-jawab onboarding perangkat yang merepotkan dengan sistem **Smart Auto-Detect via User-Agent Browser**.
  - **Panel Unduh Cerdas:**
    - Jika user buka dari Windows: Tab `Windows` otomatis aktif, tombol langsung mengarah ke unduhan web resmi Windows.
    - Jika user buka dari Android: Tab `Android` otomatis aktif, langsung direct ke Google Play Store.
    - Begitu pula untuk `macOS`, `Linux`, `iOS`, dan `Web App`.
    - User tetap bisa melakukan **manual override** (pindah tab sesuka hati).
    - Tombol unduh di header sinkron otomatis mengikuti tab platform yang dipilih.
    - Menampilkan badge deteksi `📍 [Platform] terdeteksi`.

### C. Ekspansi Database Software 50+ Apps (SIAP EKSEKUSI)
- **Status Terakhir:** User meminta jumlah aplikasi diperbanyak minimal 50+ aplikasi (koleksi awal hanya 20 aplikasi).
- **File Dibuat:** [`seed_apps.sql`](file:///D:/laragon/www/another/appfinder/seed_apps.sql)
  - Berisi tambahan **52 aplikasi legal & open-source terkurasi** (total ID 21 sampai 72) lengkap dengan ikon SVG resmi, deskripsi mendalam, lisensi asli (GPL, MIT, MPL, Freeware), padanan komersial (`alternative_to`), dan kompatibilitas platform yang presisi.

---

## 5. Tugas Berikutnya untuk Sesi Baru (Next Action Items)

1. **Jalankan Seeder SQL:**
   Eksekusi file [`seed_apps.sql`](file:///D:/laragon/www/another/appfinder/seed_apps.sql) ke database `appfinder` MySQL:
   ```bash
   # Di terminal Laragon / CMD:
   cd D:\laragon\www\another\appfinder
   mysql -u root appfinder < seed_apps.sql
   ```
   *(Atau import via phpMyAdmin di `http://localhost/phpmyadmin`)*.

2. **Commit Perubahan:**
   Simpan perubahan `pages/detail.php` dan `seed_apps.sql` ke Git:
   ```bash
   git add pages/detail.php seed_apps.sql
   git commit -m "feat: implement smart auto-detect platform download and add 50+ apps seeder"
   ```

3. **Verifikasi Tampilan Katalog & Detail:**
   - Cek halaman Home `http://localhost/another/appfinder/` (pastikan total aplikasi dan kategori ter-update).
   - Cek halaman Explore & Search dengan variasi 72 software baru.
   - Uji tombol download auto-detect di berbagai aplikasi (desktop-only seperti Blender vs mobile seperti Muslim Pro vs cross-platform seperti VLC).

4. **Langkah Opsional Lanjutan:**
   - Membangun fitur benefit login seperti Stack Bundle Generator atau rating komunitas.
