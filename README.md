# E-Services-FT

`E-Services-FT` adalah aplikasi layanan fakultas berbasis Laravel 12 yang menggunakan React, Inertia, Vite, dan Filament Admin Panel. Aplikasi ini menyediakan layanan publik seperti pengajuan surat, booking ruangan, dan verifikasi dokumen.

## Fitur Utama

- Form pengajuan berbagai jenis surat secara online
- Booking ruangan dari halaman publik
- Verifikasi dokumen melalui token publik
- Panel admin berbasis Filament di path `/admin`
- Queue, session, dan cache berbasis database

## Teknologi yang Digunakan

- PHP `^8.2`
- Laravel `^12.0`
- React `^18`
- Inertia.js `^2.0`
- Vite `^7`
- Filament `^4.0`
- SQLite atau MySQL/MariaDB

## Prasyarat

Pastikan perangkat lokal sudah terpasang:

- PHP 8.2 atau lebih baru
- Composer 2
- Node.js 20 atau lebih baru
- npm
- Database:
  - SQLite untuk setup cepat lokal, atau
  - MySQL/MariaDB jika ingin menggunakan database server

## Instalasi Project

1. Clone repository lalu masuk ke folder project:

```bash
git clone <repo-url> E-Services-FT
cd E-Services-FT
```

2. Install dependency backend:

```bash
composer install
```

3. Install dependency frontend:

```bash
npm install
```

## Konfigurasi Environment

1. Copy file environment:

```bash
cp .env.example .env
```

2. Generate application key:

```bash
php artisan key:generate
```

3. Secara default project menggunakan SQLite. Jika tetap menggunakan SQLite, buat file databasenya:

```bash
touch database/database.sqlite
```

4. Pastikan isi `.env` untuk mode lokal minimal seperti berikut:

```env
APP_NAME="E-Services-FT"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=sqlite

QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database

WHATSAPP_BASE_URL=https://wa.me
WHATSAPP_APP_URL="${APP_URL}"
```

Jika ingin memakai MySQL/MariaDB, ubah bagian database pada `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eservices_ft
DB_USERNAME=root
DB_PASSWORD=
```

## Menyiapkan Database

Jalankan migrasi agar seluruh tabel aplikasi, queue, session, dan cache dibuat:

```bash
php artisan migrate
```

Jika ingin sekaligus mengisi data awal seperti akun admin dan data ruangan, jalankan:

```bash
php artisan db:seed
```

Seeder bawaan juga akan mengisi konfigurasi awal nomor WhatsApp admin pada record `id = 1`.

Untuk mengisi data dummy pengajuan surat, jalankan seeder surat secara terpisah karena saat ini belum didaftarkan di `DatabaseSeeder`:

```bash
php artisan db:seed --class=LetterSubmissionSeeder
```

Jika ingin reset database lalu isi ulang migrasi dan data surat dummy sekaligus, gunakan:

```bash
php artisan migrate:fresh --seed
php artisan db:seed --class=LetterSubmissionSeeder
```

Seeder bawaan akan membuat akun berikut:

- Super Admin
  - Email: `superadmin@eservices.test`
  - Password: `password`
- Admin Fakultas
  - Email: `adminfakultas@eservices.test`
  - Password: `password`

## Setup Praktis Nomor WA Admin

Konfigurasi nomor WA admin disimpan pada tabel `admin_whatsapp_contacts` dan hanya memakai satu record tetap dengan `id = 1`.

### Opsi 1: Pakai Seeder

Untuk mengisi nomor awal dengan cepat:

```bash
php artisan db:seed --class=AdminWhatsappContactSeeder
```

Seeder ini akan mengisi nomor default:

- `081234567890`

Jika ingin mengganti nomor default, ubah file [database/seeders/AdminWhatsappContactSeeder.php](/mnt/e/Kerjaan/Side%20Hustle/E-Services-FT/database/seeders/AdminWhatsappContactSeeder.php:1) lalu jalankan seeder lagi.

### Opsi 2: Inject Langsung ke Database

Untuk SQLite:

```bash
sqlite3 database/database.sqlite "UPDATE admin_whatsapp_contacts SET whatsapp_number = '081234567890', updated_at = CURRENT_TIMESTAMP WHERE id = 1;"
```

Untuk MySQL/MariaDB:

```sql
UPDATE admin_whatsapp_contacts
SET whatsapp_number = '081234567890',
    updated_at = NOW()
WHERE id = 1;
```

Jika record `id = 1` belum ada, gunakan:

```sql
INSERT INTO admin_whatsapp_contacts (id, whatsapp_number, created_at, updated_at)
VALUES (1, '081234567890', NOW(), NOW())
ON DUPLICATE KEY UPDATE
    whatsapp_number = VALUES(whatsapp_number),
    updated_at = VALUES(updated_at);
```

## Cara Menjalankan Project

Ada dua cara yang bisa dipakai.

### Opsi 1: Menjalankan semua service sekaligus

Perintah ini akan menjalankan:

- Laravel development server
- Queue listener
- Log watcher
- Vite dev server

Gunakan:

```bash
composer run dev
```

Lalu buka aplikasi di:

- Frontend publik: `http://127.0.0.1:8000`
- Admin panel: `http://127.0.0.1:8000/admin`

### Opsi 2: Menjalankan manual per service

Jika ingin dijalankan terpisah, gunakan terminal berbeda:

Terminal 1:

```bash
php artisan serve
```

Terminal 2:

```bash
npm run dev
```

Terminal 3:

```bash
php artisan queue:listen --tries=1 --timeout=0
```

Opsional untuk melihat log secara realtime:

```bash
php artisan pail --timeout=0
```

## Build untuk Production

Untuk build asset frontend production:

```bash
npm run build
```

Jika ingin build SSR juga:

```bash
npm run build:ssr
```

## Shortcut Setup Cepat

Project ini memiliki script Composer `setup` untuk membantu instalasi awal:

```bash
composer run setup
```

Script tersebut akan menjalankan proses berikut:

- `composer install`
- membuat file `.env` jika belum ada
- `php artisan key:generate`
- `php artisan migrate --force`
- `npm install`
- `npm run build`

Catatan:
- Script ini tidak menjalankan `db:seed`
- Untuk SQLite, pastikan file `database/database.sqlite` sudah tersedia sebelum migrasi bila diperlukan

## Testing

Untuk menjalankan test:

```bash
composer test
```

Atau langsung dengan Artisan:

```bash
php artisan test
```

## Struktur Akses Aplikasi

- Halaman utama: `/`
- Daftar layanan: `/services`
- Booking ruangan: `/booking`
- Panduan: `/guidelines`
- Form layanan: `/form/{letterType}`
- Verifikasi dokumen: `/verify/{letterType}/{token}`
- Admin panel: `/admin`

## Dokumentasi Tambahan

Untuk panduan deploy ke VPS, lihat file [DEPLOY-VPS.md](/run/media/yanuar/New%20Volume/Kerjaan/Side%20Hustle/E-Services-FT/DEPLOY-VPS.md).
