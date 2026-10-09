# AppFinder — Context Handoff (9 October 2026)

Dokumen ini adalah handoff untuk agent berikutnya. Gunakan sebagai sumber status terkini; jangan mengandalkan `session-ses_eeff.md` atau context versi lama karena beberapa statusnya sudah usang.

## 1. Project dan cara kerja

- Project aktif: `D:\laragon\www\another\appfinder`.
- Stack: Native PHP 8.x, PDO MySQL, session auth, Tailwind CSS CDN, dan Google Fonts.
- Router: [index.php](../index.php) memilih halaman dari parameter `?page=`. Halaman yang diizinkan: `home`, `explore`, `detail`, `login`, `register`, `dashboard`, dan `logout`; admin memakai `?admin=`.
- Layout global: [header.php](../includes/header.php) dan [footer.php](../includes/footer.php).
- Konfigurasi database: [db.php](../config/db.php). Database bernama `appfinder` pada MySQL lokal Laragon.
- Gaya UI: clean modern/curated store dengan off-white, stone/slate, dan aksen oranye. Hindari neon, gradient berlebihan, atau tampilan neobrutalist.

## 2. Kondisi workspace penting

- Repository Git berada di dalam folder `appfinder`, bukan root workspace `another`.
- Worktree **sudah dirty** dan berisi perubahan yang belum dikomit. Jangan menjalankan reset/checkout atau menghapus perubahan yang ada.
- Ada perpindahan dokumentasi/database dari root project ke folder `docs/` dan `database/`: Git saat ini menandai file root lama sebagai deleted dan file-folder baru sebagai untracked. Ini adalah kondisi yang harus dipertahankan atau dirapikan dalam commit khusus, bukan dibatalkan secara tidak sengaja.
- Saat pengecekan terakhir, MySQL lokal sedang berhenti/tidak menerima koneksi pada `localhost:3306` (`ERROR 2003`). Nyalakan MySQL Laragon sebelum menjalankan aplikasi atau menguji query.
- Browser yang sempat dibagikan menunjuk ke `http://appfinder.test/`, tetapi shell terakhir tidak dapat me-resolve host itu. `http://localhost/appfinder/` juga merespons 404. Verifikasi virtual host/document root Laragon setelah MySQL dan web server dinyalakan.

## 3. Fitur baseline yang telah ada sebelum sesi ini

- Login, register, logout, dashboard bookmark, katalog explore/search/filter, detail aplikasi, kategori, dan admin katalog.
- Bookmark sudah memakai POST, autentikasi, CSRF token, validasi aplikasi, dan respons JSON di [index.php](../index.php).
- Admin mengelola aplikasi/kategori dengan CSRF, validasi slug/URL, serta pembatasan peran di [pages/admin/](../pages/admin/).
- Detail aplikasi punya panel unduh multi-platform dengan auto-detect User-Agent dan manual override di [detail.php](../pages/detail.php).
- Seed katalog besar sudah ada di [seed_apps.sql](../database/seed_apps.sql) dan seed aplikasi umum di [seed_common_apps.sql](../database/seed_common_apps.sql).

## 4. Perubahan yang selesai dalam sesi 8–9 Oktober

### A. AlternativeTo Engine dan homepage

File utama: [home.php](../pages/home.php).

- Homepage sudah menjadi landing page modern dengan hero search, kategori dinamis, marquee, promo, featured carousel, section alternatif, transparansi, dan CTA.
- Query `$stmtAlternatives` mengambil maksimal 3 aplikasi legal/gratis/open-source dari database yang memiliki `alternative_to`, lalu section “Alternatif legal” merender data tersebut. Tiga kartu hard-coded Photoshop/Microsoft 365/Maya sudah dihapus.
- Search di [explore.php](../pages/explore.php) sudah mencari `name`, `description`, `alternative_to`, dan `developer`, sehingga query nama produk berbayar dapat menemukan alternatifnya.
- CTA homepage yang dulu mengarah ke halaman register tetapi berlabel “Kirimkan Rekomendasi” telah diperjelas sebagai ajakan membuat akun/bookmark.
- Kategori `Utility` diberi icon SVG di homepage.

### B. Data desktop, spesifikasi, dan Winget ID

File terkait:

- [database.sql](../database/database.sql)
- [2026-10-08_add_desktop_specs.sql](../database/migrations/2026-10-08_add_desktop_specs.sql)
- [seed_alternative_desktop_apps.sql](../database/seed_alternative_desktop_apps.sql)
- [apps.php](../pages/admin/apps.php)
- [detail.php](../pages/detail.php)

Perubahan:

- Tabel `apps` sekarang memiliki `min_ram DECIMAL(4,1)`, `recommended_ram DECIMAL(4,1)`, `min_os`, dan `winget_id` pada schema canonical.
- Migration desktop-spec dibuat untuk database yang sudah ada. Migration **bukan idempotent** karena MySQL Laragon menolak `ADD COLUMN IF NOT EXISTS`; jalankan hanya sekali pada database yang belum mempunyai empat kolom tersebut.
- Migration ini dan seed desktop sudah pernah berhasil dijalankan ke database lokal saat MySQL masih menyala.
- Admin aplikasi kini bisa melihat, menambah, dan mengubah semua field tersebut; RAM divalidasi sebagai angka non-negatif dan nilai rekomendasi tidak boleh lebih kecil dari minimum.
- Detail aplikasi menampilkan RAM minimum/rekomendasi, OS minimum, dan Winget ID bila datanya tersedia.
- Seed desktop idempotent menggunakan slug dan menambahkan kategori `Utility` serta 12 aplikasi: VSCodium, DBeaver Community, Git, OBS Studio, Kdenlive, HandBrake, ONLYOFFICE Desktop Editors, PDF24 Creator, Everything, PeaZip, ShareX, dan BleachBit.
- Data seed menyertakan `alternative_to`, kebutuhan RAM/OS, dan Winget package ID. Tetap verifikasi Winget ID sebelum dipakai untuk instalasi otomatis produksi.

### C. Navbar responsif dan state aktif

File terkait: [header.php](../includes/header.php), [footer.php](../includes/footer.php).

- Navbar desktop berbentuk pill tetap dipakai.
- Mobile sebelumnya menyembunyikan navigasi utama tanpa pengganti. Sekarang terdapat menu hamburger (`<details>`) dengan Home, Explore, Kategori, Alternatif, serta Akun/Registrasi.
- Bug state aktif desktop sudah ditangani: link Kategori (`#categories`) dan Alternatif (`#alternatives`) diberi `data-*` attributes; JavaScript di footer mengubah class `nav-pill-active` berdasarkan hash URL dan `IntersectionObserver` ketika section terlihat.
- Perubahan terakhir ini sudah lolos `php -l`, tetapi **belum diverifikasi visual di browser hidup** setelah service Laragon berhenti. Ini harus menjadi smoke test pertama.

## 5. Validasi yang sudah dilakukan

Sebelum service database berhenti:

- `php -l` lulus untuk [home.php](../pages/home.php), [apps.php](../pages/admin/apps.php), [detail.php](../pages/detail.php), [header.php](../includes/header.php), dan [footer.php](../includes/footer.php).
- Migration desktop-spec berhasil menambah empat kolom pada database live.
- Seed alternatif desktop berhasil memasukkan/menyinkronkan 12 slug baru.
- Query contoh mengonfirmasi data VSCodium, OBS Studio, dan Everything memiliki alternatif, RAM, OS, dan Winget ID.

Catatan: angka total katalog jangan diasumsikan dari context lama. Query terakhir yang berhasil hanya mengonfirmasi ada 80 record dengan `alternative_to` terisi; hitung ulang total `apps` setelah MySQL aktif.

## 6. Pekerjaan yang belum selesai / prioritas berikutnya

1. **Smoke test aplikasi setelah Laragon aktif**
   - Buka homepage, click Home/Explore/Kategori/Alternatif, dan pastikan background putih pill navbar pindah ke item yang benar.
   - Uji menu hamburger pada mobile viewport.
   - Uji homepage tanpa data alternatif kosong dan dengan data seed.
   - Uji form admin create/edit spesifikasi.

2. **Benahi Stack Bundle Generator di dashboard**
   - [dashboard.php](../pages/dashboard.php) memang sudah membuat command Winget dari bookmark, tetapi saat ini memakai **nama aplikasi**, bukan `winget_id`.
   - Ubah agar hanya memasukkan bookmark yang mempunyai `winget_id`, dengan format per aplikasi yang valid, misalnya `winget install --id <ID> -e --accept-source-agreements --accept-package-agreements`.
   - Hindari menganggap aplikasi macOS/Linux otomatis tersedia di Winget.

3. **Hardware Compatibility Matcher belum diimplementasikan penuh**
   - Belum ada migration canonical untuk `users.device_ram`/`users.device_os` dan belum ada UI penyimpanan spek perangkat.
   - Tambahkan migration idempotency yang jelas/terdokumentasi, form dashboard, validasi server, lalu badge kompatibilitas pada detail dan explore.
   - Tentukan aturan: RAM >= recommended = lancar; RAM >= min = pas-pasan; RAM < min = berat. Jangan tampilkan hasil bila data app atau data perangkat kosong.

4. **Riset/validasi data eksternal sebelum fitur instalasi**
   - Pastikan semua `winget_id`, link unduh, versi, lisensi, dan requirement yang ditampilkan masih akurat dengan sumber resmi.
   - Jangan membuat rating atau jumlah unduhan palsu untuk aplikasi baru.

5. **Dokumentasi dan commit**
   - Perbarui [docs/README.md](README.md) agar instruksi import mengarah ke `database/database.sql`, migration, dan seed yang benar.
   - Pisahkan commit refactor pemindahan docs/database dari commit fitur UI/database bila memungkinkan.

## 7. Perintah verifikasi yang disarankan

```powershell
# Dari D:\laragon\www\another\appfinder
& 'D:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' -l pages\home.php
& 'D:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' -l includes\header.php
& 'D:\laragon\bin\php\php-8.3.30-Win32-vs16-x64\php.exe' -l includes\footer.php
& 'D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe' -u root -e "SELECT COUNT(*) AS total_apps FROM appfinder.apps;"
```

Jika database baru belum memiliki kolom desktop specs, jalankan migration sekali lalu seed tambahan:

```powershell
Get-Content -Raw database\migrations\2026-10-08_add_desktop_specs.sql | & 'D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe' -u root appfinder
Get-Content -Raw database\seed_alternative_desktop_apps.sql | & 'D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysql.exe' -u root appfinder
```
