# Deploy E-Services-FT ke VPS

Panduan ini diasumsikan untuk VPS Ubuntu dengan stack:

- Nginx
- PHP-FPM 8.2
- MySQL atau MariaDB
- Node.js 20
- Composer 2
- Supervisor
- Git

Project ini menggunakan:

- Laravel 12
- PHP `^8.2`
- React + Inertia
- Vite
- Queue driver default: `database`
- Session driver default: `database`
- Cache store default: `database`
- SSR Inertia tersedia dan opsional di production

## 1. Siapkan package server

```bash
sudo apt update
sudo apt install -y nginx mysql-server unzip git supervisor
sudo apt install -y php8.2 php8.2-fpm php8.2-cli php8.2-mysql php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip php8.2-bcmath php8.2-intl php8.2-gd
```

Install Composer:

```bash
cd /tmp
curl -sS https://getcomposer.org/installer -o composer-setup.php
php composer-setup.php
sudo mv composer.phar /usr/local/bin/composer
composer --version
```

Install Node.js 20:

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs
node -v
npm -v
```

## 2. Buat database

Masuk ke MySQL lalu buat database dan user:

```sql
CREATE DATABASE eservices_ft;
CREATE USER 'eservices_user'@'localhost' IDENTIFIED BY 'password-kuat';
GRANT ALL PRIVILEGES ON eservices_ft.* TO 'eservices_user'@'localhost';
FLUSH PRIVILEGES;
```

## 3. Clone project ke VPS

Contoh lokasi deploy:

```bash
cd /var/www
sudo git clone <repo-url> E-Services-FT
sudo chown -R $USER:$USER /var/www/E-Services-FT
cd /var/www/E-Services-FT
```

## 4. Install dependency backend dan frontend

```bash
composer install --no-dev --optimize-autoloader
npm install
npm run build
```

Jika ingin mengaktifkan SSR Inertia, build SSR juga:

```bash
npm run build:ssr
```

## 5. Atur file environment

Copy file environment lalu sesuaikan:

```bash
cp .env.example .env
php artisan key:generate
```

Minimal isi `.env` production:

```env
APP_NAME="E-Services-FT"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=eservices_ft
DB_USERNAME=eservices_user
DB_PASSWORD=password-kuat

QUEUE_CONNECTION=database
SESSION_DRIVER=database
CACHE_STORE=database
FILESYSTEM_DISK=public
```

Jika aplikasi memakai WhatsApp deep link, sesuaikan juga:

```env
WHATSAPP_BASE_URL=https://wa.me
WHATSAPP_APP_URL="${APP_URL}"
```

## 6. Jalankan migrasi dan optimasi Laravel

Karena queue, session, dan cache default-nya memakai database, pastikan semua tabel Laravel ikut dibuat saat migrasi.

```bash
php artisan migrate --force
php artisan storage:link
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 7. Atur permission folder

```bash
sudo chown -R www-data:www-data /var/www/E-Services-FT
sudo find /var/www/E-Services-FT -type f -exec chmod 644 {} \;
sudo find /var/www/E-Services-FT -type d -exec chmod 755 {} \;
sudo chmod -R 775 /var/www/E-Services-FT/storage /var/www/E-Services-FT/bootstrap/cache
```

## 8. Konfigurasi Nginx

Buat file config:

```bash
sudo nano /etc/nginx/sites-available/eservices-ft
```

Isi contoh:

```nginx
server {
    listen 80;
    server_name domain-anda.com www.domain-anda.com;
    root /var/www/E-Services-FT/public;

    index index.php index.html;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        include snippets/fastcgi-php.conf;
        fastcgi_pass unix:/run/php/php8.2-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

Aktifkan site:

```bash
sudo ln -s /etc/nginx/sites-available/eservices-ft /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## 9. Pasang SSL

Jika domain sudah mengarah ke VPS:

```bash
sudo apt install -y certbot python3-certbot-nginx
sudo certbot --nginx -d domain-anda.com -d www.domain-anda.com
```

## 10. Jalankan queue worker dengan Supervisor

Karena `QUEUE_CONNECTION=database`, queue worker sebaiknya aktif terus.

Buat config:

```bash
sudo nano /etc/supervisor/conf.d/eservices-ft-worker.conf
```

Isi contoh:

```ini
[program:eservices-ft-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/E-Services-FT/artisan queue:work database --sleep=3 --tries=3 --timeout=90
directory=/var/www/E-Services-FT
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=1
redirect_stderr=true
stdout_logfile=/var/www/E-Services-FT/storage/logs/worker.log
stopwaitsecs=3600
```

Aktifkan:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start eservices-ft-worker:*
```

## 11. Opsional: jalankan Inertia SSR

Project ini punya file `resources/js/ssr.tsx`. Jika ingin SSR aktif di production:

```bash
npm run build:ssr
php artisan inertia:start-ssr
```

Agar SSR tetap hidup, jalankan juga lewat Supervisor:

```bash
sudo nano /etc/supervisor/conf.d/eservices-ft-ssr.conf
```

Isi contoh:

```ini
[program:eservices-ft-ssr]
command=php /var/www/E-Services-FT/artisan inertia:start-ssr
directory=/var/www/E-Services-FT
autostart=true
autorestart=true
user=www-data
redirect_stderr=true
stdout_logfile=/var/www/E-Services-FT/storage/logs/ssr.log
```

Lalu reload supervisor:

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start eservices-ft-ssr
```

Jika tidak butuh SSR, cukup gunakan `npm run build` saja.

## 12. Checklist update saat deploy berikutnya

```bash
cd /var/www/E-Services-FT
git pull origin main
composer install --no-dev --optimize-autoloader
npm install
npm run build
php artisan migrate --force
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache
sudo supervisorctl restart eservices-ft-worker:*
```

Jika SSR dipakai:

```bash
npm run build:ssr
sudo supervisorctl restart eservices-ft-ssr
```

## 13. Verifikasi setelah deploy

```bash
php artisan about
php artisan queue:monitor default
tail -f storage/logs/laravel.log
sudo systemctl status nginx
sudo systemctl status php8.2-fpm
sudo supervisorctl status
```

## Catatan penting

- Pastikan `root` Nginx mengarah ke folder `public`, bukan root project.
- Jangan jalankan production dengan `php artisan serve`.
- Jika upload file digunakan, cek permission folder `storage`.
- Jika deploy dari branch selain `main`, ganti perintah `git pull origin main`.
