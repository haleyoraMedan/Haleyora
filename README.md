# Haleyora - Aplikasi Manajemen Pemakaian Mobil

Ringkasan singkat
- Haleyora adalah aplikasi berbasis Laravel untuk manajemen data mobil, penempatan, pengguna, dan pemakaian kendaraan dinas.
- Menyediakan area admin untuk mengelola master data, melihat daftar pemakaian (dengan detail dan foto), serta fitur import/export (XLSX) dan operasi massal (export & bulk delete).

Teknologi
- Backend: PHP 8.3, Laravel (versi sesuai vendor folder)
- Frontend: Blade, Bootstrap 5, Font Awesome, vanilla JS
- Database: MySQL
- Files: public/uploads untuk foto pemakaian
- Optional: PhpSpreadsheet (`phpoffice/phpspreadsheet`) untuk XLSX import/export
- Push notifications: Service Worker + VAPID (environment variables)

Fitur utama
- Autentikasi: register (dengan `penempatan`), login, logout
- Role-based: `admin`, `pegawai`, `penempatan` (hak akses dibatasi lewat middleware)
- Admin dashboard: ringkasan data (pengguna, mobil, pemakaian pending)
- Master data: CRUD `Mobil`, `MerekMobil`, `JenisMobil`, `Penempatan`, `User`
- Pemakaian:
  - Pegawai: pilih mobil, input detail, upload foto kondisi
  - Admin: daftar pemakaian dengan pencarian, filter status, modal detail (foto enlarge), ubah status, bulk export & delete
- Import / Export:
  - Templates XLSX (user, jenis, merek, mobil, penempatan)
  - Export selected (multi-select) dari list data
- Notifications: polling & service-worker push for admin pemakaian updates

Instalasi & setup (development)
1. Clone repo ke server lokal (mis: Laragon)
2. Install dependency PHP:

```bash
composer install
```

3. Copy `.env.example` ke `.env` dan sesuaikan konfigurasi DB, APP_URL, mail, dsb.

4. Buat database dan jalankan migration & seeder:

```bash
php artisan migrate --seed
```

5. Buat symbolic link storage (jika diperlukan):

```bash
php artisan storage:link
```

6. (Optional) Install PhpSpreadsheet jika ingin menggunakan fitur XLSX import/export:

```bash
composer require phpoffice/phpspreadsheet
```

7. Jalankan dev server:

```bash
php artisan queue:work
php artisan serve
# atau jalankan Laragon / built-in webserver Anda
```

Variabel environment penting
- `DB_*` untuk koneksi MySQL
- `APP_URL` (digunakan di beberapa link)
- `VAPID_PUBLIC_KEY` dan `VAPID_PRIVATE_KEY` untuk push notification (opsional)

Rute penting
- Auth:
  - `GET /login` - form login
  - `POST /login` - proses login
  - `GET /register` - form registrasi
  - `POST /register` - registrasi user
  - `POST /logout` - logout
- Admin:
  - `/admin/dashboard` - dashboard admin
  - `/admin/pemakaian` - daftar pemakaian (AJAX list, modal detail)
  - `/admin/pemakaian/export` - export selected (CSV by default)
  - `/admin/tools/import-export` - import/export tools (XLSX templates)
- Pegawai:
  - `/pemakaian/pilih-mobil` - pilih mobil untuk pemakaian
  - `/pemakaian/input-detail` - input data pasca-pemakaian

Catatan implementasi & tips
- Password hashing: model `User` memiliki mutator `setPasswordAttribute` sehingga menyimpan plaintext pada create akan otomatis ter-hash.
- Penempatan: model `Penempatan` menggunakan tabel `penempatan` (singular) — validation menggunakan nama tabel yang sama.
- Export admin pemakaian saat ini menghasilkan CSV; untuk konsistensi dan styling XLSX, install PhpSpreadsheet.
- Foto pemakaian disimpan di `public/uploads/pemakaian_sebelum/` (periksa controller upload path bila konfigurasi berbeda).
- Jika UI JS memodifikasi DOM (AJAX), periksa console browser untuk error dan network responses.

Debugging umum
- SQL error `Base table or view not found`: jalankan `php artisan migrate` dan periksa nama tabel di model (`$table`).
- Masalah export kosong: pastikan `ids[]` dikirim lewat form POST, atau export tanpa ids akan mengekspor filter saat ini.
- Foto tidak muncul: periksa path (public path) dan apakah file benar-benar ada di `public/uploads`.

Pengembangan lebih lanjut (opsional)
- Ubah admin pemakaian export menjadi XLSX (lebih rapi) — butuh `phpoffice/phpspreadsheet`.
- Tambah unit/feature tests (PHPUnit) untuk alur pemakaian dan import/export.
- Tambah pagination-wide "select all pages" control bila pengguna ingin memilih semua data di semua halaman.

Kontak & kontribusi
- Jika Anda ingin fitur tambahan atau perbaikan, buka issue atau PR di repo lokal ini.

Lisensi
- Internal / project-specific (tambahkan lisensi jika diperlukan)

---

README dibuat otomatis oleh asisten pengembangan. Jika Anda ingin versi bahasa Inggris, ringkasan fitur lain, atau dokumentasi API yang lebih rinci (list endpoints + sample payload), beri tahu saya dan saya akan tambahkan.
<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework. You can also check out [Laravel Learn](https://laravel.com/learn), where you will be guided through building a modern Laravel application.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

