# AppFinder

Direktori Software Legal & Gratis Berkualitas.
Stack: PHP (Native PDO) + MySQL + Tailwind CSS.

## Setup Database

1. Buka MySQL di Laragon / phpMyAdmin.
2. Buat database baru bernama `appfinder`.
3. Import file `database.sql`:
   ```bash
   mysql -u root -p appfinder < database.sql
   ```
4. Sesuaikan kredensial di `config/db.php` jika diperlukan (default: `127.0.0.1`, user `root`, password kosong).

## Menjalankan Aplikasi

Akses via Laragon:
```
http://localhost/another/appfinder/
```
Atau via PHP Built-in Server:
```bash
php -S localhost:8000
```
Buka <http://localhost:8000/>.

## Struktur File

```
appfinder/
├── index.php           # Router utama & action handler
├── database.sql        # Skema & seed data MySQL
├── config/
│   └── db.php          # Koneksi PDO MySQL
├── includes/
│   ├── header.php      # Layout header, navigasi, Tailwind config
│   └── footer.php      # Layout footer
└── pages/
    ├── home.php        # Halaman utama
    ├── explore.php     # Katalog, pencarian, & filter
    ├── detail.php      # Detail software & spesifikasi
    ├── login.php       # Form login
    ├── register.php    # Form registrasi
    ├── dashboard.php   # Profil user & daftar bookmark
    └── logout.php      # Destroy session & redirect
```

## Akun Demo Bawaan

- **Admin**: `admin@appfinder.local` / pass: default hash
- **User**: `test@example.com` / pass: default hash
