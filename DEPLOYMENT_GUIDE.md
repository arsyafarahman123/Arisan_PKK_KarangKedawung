# Panduan Hosting & Deploy Aplikasi Arisan PKK KarangKedawung

Aplikasi ini dibangun menggunakan **Laravel 12 (PHP 8.2+)**. Berikut adalah panduan lengkap cara melakukan hosting aplikasi ke berbagai platform hosting.

---

## Opsi 1: Hosting di Railway (Sangat Direkomendasikan - Paling Cepat & Mudah)

Railway adalah platform cloud modern yang sangat cocok untuk aplikasi Laravel dengan integrasi langsung ke GitHub.

### Langkah-langkah:
1. **Daftar & Login di Railway:**
   - Kunjungi [railway.app](https://railway.app/) dan masuk dengan akun GitHub Anda (`arsyafarahman123`).

2. **Buat Project Baru:**
   - Klik tombol **"New Project"** -> Pilih **"Deploy from GitHub repo"**.
   - Pilih repositori `arsyafarahman123/Arisan_PKK_KarangKedawung`.

3. **Tambah Database (MySQL):**
   - Di dashboard project Railway, klik **"New"** -> **"Database"** -> **"Add MySQL"**.

4. **Konfigurasi Environment Variables (`Variables`):**
   Tambahkan variabel berikut pada service aplikasi Anda di Railway:
   - `APP_NAME` = `Arisan PKK KarangKedawung`
   - `APP_ENV` = `production`
   - `APP_DEBUG` = `false`
   - `APP_KEY` = *(Jalankan `php artisan key:generate --show` di lokal dan tempel kodenya)*
   - `APP_URL` = `${{RAILWAY_PUBLIC_DOMAIN}}`
   - `DB_CONNECTION` = `mysql`
   - `DB_HOST` = `${{MySQL.MYSQLHOST}}`
   - `DB_PORT` = `${{MySQL.MYSQLPORT}}`
   - `DB_DATABASE` = `${{MySQL.MYSQLDATABASE}}`
   - `DB_USERNAME` = `${{MySQL.MYSQLUSER}}`
   - `DB_PASSWORD` = `${{MySQL.MYSQLPASSWORD}}`

5. **Generate Domain Publik:**
   - Di tab **"Settings"** -> bagian **"Networking"**, klik **"Generate Domain"**.
   - Aplikasi Anda langsung aktif dan dapat diakses dari HP & Laptop!

---

## Opsi 2: Hosting di cPanel / Shared Hosting (Hostinger, Niagahoster, DomaiNesia, dll.)

Jika Anda memiliki hosting cPanel berbayar atau domain desa (`.desa.id` / `.com`):

### Langkah-langkah:
1. **Ekspor Database Lokal:**
   - Buka phpMyAdmin / jalankan migrasi di hosting.
   - Buat database MySQL baru dan user database di menu **MySQL Databases** cPanel.

2. **Upload File Proyek:**
   - Compress folder proyek menjadi format `.zip` (kecuali folder `node_modules` dan `.git`).
   - Buka **File Manager** cPanel:
     - Buat folder di luar `public_html`, misal `/home/username/arisan_pkk/` dan ekstrak semua file ke sana.
     - Pindahkan semua isi folder `public/*` ke dalam folder `public_html/`.

3. **Sesuaikan `public_html/index.php`:**
   Ubah baris path require di `public_html/index.php`:
   ```php
   require __DIR__.'/../arisan_pkk/vendor/autoload.php';
   $app = require_once __DIR__.'/../arisan_pkk/bootstrap/app.php';
   ```

4. **Konfigurasi `.env`:**
   - Salin file `.env` ke folder `/home/username/arisan_pkk/.env`.
   - Sesuaikan `APP_URL`, `DB_DATABASE`, `DB_USERNAME`, dan `DB_PASSWORD`.

5. **Buat Symlink Storage (untuk upload bukti transfer):**
   - Jalankan Cron Job sekali saja atau buat script `symlink.php`:
     ```php
     <?php
     symlink('/home/username/arisan_pkk/storage/app/public', '/home/username/public_html/storage');
     echo "Symlink created successfully!";
     ```

---

## Opsi 3: Hosting di VPS (Ubuntu 22.04 / 24.04 + Nginx)

1. **Clone repositori:**
   ```bash
   git clone https://github.com/arsyafarahman123/Arisan_PKK_KarangKedawung.git /var/www/arisan_pkk
   cd /var/www/arisan_pkk
   ```
2. **Install dependencies:**
   ```bash
   composer install --no-dev --optimize-autoloader
   cp .env.example .env
   php artisan key:generate
   ```
3. **Set permissions & migrate:**
   ```bash
   chmod -R 775 storage bootstrap/cache
   chown -R www-data:www-data /var/www/arisan_pkk
   php artisan storage:link
   php artisan migrate --force --seed
   ```
4. **Konfigurasi Nginx Web Server:** Arahkan `root` ke `/var/www/arisan_pkk/public`.
