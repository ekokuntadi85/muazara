# Prosedur Standar Operasional (SOP) Deployment Proyek Laravel

Ini adalah panduan langkah demi langkah untuk mendeploy proyek Laravel dari repositori Git ke server produksi. SOP ini mengasumsikan Anda menggunakan server di mana Anda memiliki akses SSH (seperti VPS, AWS EC2, DigitalOcean Droplet, dll.) dan **bukan** shared hosting.

---

### **Prasyarat (Hal yang Harus Siap Sebelum Mulai)**

1.  **Server Siap:** Server Linux (misalnya, Ubuntu 22.04) dengan akses SSH.
2.  **Stack LEMP/LAMP Terinstal:**
    *   **Nginx** (atau Apache) sebagai web server.
    *   **MySQL** (atau PostgreSQL) sebagai database.
    *   **PHP** versi yang sesuai dengan proyek Anda (misalnya, PHP 8.2), beserta ekstensi PHP yang umum untuk Laravel (seperti `php-mbstring`, `php-xml`, `php-curl`, `php-zip`, `php-bcmath`).
3.  **Composer Terinstal:** Composer harus terinstal secara global di server.
4.  **Git Terinstal:** Git harus terinstal di server.
5.  **Database Kosong:** Anda sudah membuat database kosong dan memiliki kredensialnya (nama database, username, password).

---

### **Fase 1: Penyiapan Awal di Server (Hanya Dilakukan Sekali)**

Langkah-langkah ini untuk menyiapkan direktori proyek Anda untuk pertama kalinya.

1.  **Masuk ke Server via SSH:**
    ```bash
    ssh username@your_server_ip
    ```

2.  **Navigasi ke Direktori Web Root:**
    Biasanya direktori ini adalah `/var/www`.
    ```bash
    cd /var/www
    ```

3.  **Clone Repositori Git Anda:**
    Gantilah `main` dengan nama branch produksi Anda jika berbeda (misalnya, `production` atau `master`).
    ```bash
    git clone -b main https://github.com/your-username/your-repository.git apotek-app
    ```
    Ini akan membuat direktori baru bernama `apotek-app` (atau nama lain yang Anda inginkan).

---

### **Fase 2: Proses Deployment Pertama Kali**

Setelah kode ada di server, konfigurasikan Laravel untuk lingkungan produksi.

1.  **Masuk ke Direktori Proyek:**
    ```bash
    cd /var/www/apotek-app
    ```

2.  **Instal Dependensi Composer:**
    Perintah ini menginstal hanya dependensi yang dibutuhkan untuk produksi dan mengoptimalkan autoloader.
    ```bash
    composer install --optimize-autoloader --no-dev
    ```

3.  **Buat dan Konfigurasi File Lingkungan (`.env`):**
    a. Salin file contoh:
    ```bash
    cp .env.example .env
    ```
    b. Buat kunci aplikasi yang unik dan aman:
    ```bash
    php artisan key:generate
    ```
    c. Edit file `.env` dengan editor teks (seperti `nano`):
    ```bash
    nano .env
    ```
    d. **Perbarui variabel-variabel berikut (INI SANGAT PENTING):**
    ```ini
    APP_NAME="Apotek App"
    APP_ENV=production
    APP_DEBUG=false  # <-- Sangat penting untuk keamanan!
    APP_URL=https://yourdomain.com

    DB_CONNECTION=mysql
    DB_HOST=127.0.0.1
    DB_PORT=3306
    DB_DATABASE=your_production_db_name
    DB_USERNAME=your_production_db_user
    DB_PASSWORD=your_production_db_password

    # Konfigurasi untuk fitur impor yang sudah kita buat
    QUEUE_CONNECTION=database
    ```
    Simpan dan tutup file (di `nano`, tekan `Ctrl+X`, lalu `Y`, lalu `Enter`).

4.  **Buat Symbolic Link untuk Storage:**
    Ini membuat folder `public/storage` dapat diakses dari `storage/app/public`.
    ```bash
    php artisan storage:link
    ```

5.  **Jalankan Migrasi Database:**
    Perintah ini akan membuat semua tabel di database Anda. Flag `--force` diperlukan agar tidak ada prompt konfirmasi di lingkungan produksi.
    ```bash
    php artisan migrate --force
    ```

6.  **(Opsional) Jalankan Database Seeder:**
    Jika Anda memiliki seeder khusus untuk data awal produksi (misalnya, role admin).
    ```bash
    php artisan db:seed --force
    ```

7.  **Optimalkan Aplikasi:**
    Ini akan membuat cache untuk konfigurasi dan rute, yang secara drastis mempercepat aplikasi.
    ```bash
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    ```

---

### **Fase 3: Konfigurasi Server & Menjalankan Antrian**

1.  **Atur Kepemilikan & Izin File:**
    Web server (biasanya user `www-data`) perlu izin untuk menulis ke direktori `storage` dan `bootstrap/cache`.
    ```bash
    # Atur kepemilikan direktori ke user web server
    sudo chown -R www-data:www-data /var/www/apotek-app/storage
    sudo chown -R www-data:www-data /var/www/apotek-app/bootstrap/cache

    # Atur izin yang benar
    sudo chmod -R 775 /var/www/apotek-app/storage
    sudo chmod -R 775 /var/www/apotek-app/bootstrap/cache
    ```

2.  **Konfigurasi Nginx (atau Apache):**
    Buat file konfigurasi Nginx baru untuk situs Anda.
    ```bash
    sudo nano /etc/nginx/sites-available/apotek-app
    ```
    Isi dengan konfigurasi berikut (sesuaikan `yourdomain.com` dan path PHP):
    ```nginx
    server {
        listen 80;
        server_name yourdomain.com;
        root /var/www/apotek-app/public;

        add_header X-Frame-Options "SAMEORIGIN";
        add_header X-Content-Type-Options "nosniff";

        index index.php;

        charset utf-8;

        location / {
            try_files $uri $uri/ /index.php?$query_string;
        }

        location = /favicon.ico { access_log off; log_not_found off; }
        location = /robots.txt  { access_log off; log_not_found off; }

        error_page 404 /index.php;

        location ~ \.php$ {
            fastcgi_pass unix:/var/run/php/php8.2-fpm.sock; # <-- Sesuaikan versi PHP
            fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
            include fastcgi_params;
        }

        location ~ /\.(?!well-known).* {
            deny all;
        }
    }
    ```
    Aktifkan situs dan restart Nginx:
    ```bash
    sudo ln -s /etc/nginx/sites-available/apotek-app /etc/nginx/sites-enabled/
    sudo nginx -t # Uji konfigurasi
    sudo systemctl restart nginx
    ```

3.  **Siapkan Supervisor untuk Menjalankan Antrian:**
    a. Buat file konfigurasi untuk worker:
    ```bash
    sudo nano /etc/supervisor/conf.d/apotek-worker.conf
    ```
    b. Isi dengan konfigurasi berikut (sesuaikan path):
    ```ini
    [program:apotek-worker]
    process_name=%(program_name)s_%(process_num)02d
    command=php /var/www/apotek-app/artisan queue:work database --sleep=3 --tries=3 --max-time=3600
    autostart=true
    autorestart=true
    stopasgroup=true
    killasgroup=true
    user=www-data # Jalankan sebagai user web server
    numprocs=1
    redirect_stderr=true
    stdout_logfile=/var/www/apotek-app/storage/logs/worker.log
    ```
    c. Muat dan jalankan worker baru:
    ```bash
    sudo supervisorctl reread
    sudo supervisorctl update
    sudo supervisorctl start apotek-worker:*
    ```

---

### **Fase 4: Proses Deployment untuk Pembaruan (Update)**

Untuk setiap pembaruan kode di masa mendatang, prosesnya jauh lebih sederhana.

1.  **Masuk ke Server dan Direktori Proyek:**
    ```bash
    ssh username@your_server_ip
    cd /var/www/apotek-app
    ```

2.  **Tarik Perubahan Terbaru dari Git:**
    ```bash
    git pull origin main
    ```

3.  **Instal Dependensi (jika ada perubahan di `composer.json`):**
    ```bash
    composer install --optimize-autoloader --no-dev
    ```

4.  **Jalankan Migrasi (jika ada migrasi baru):**
    ```bash
    php artisan migrate --force
    ```

5.  **Bersihkan dan Buat Ulang Cache:**
    Ini sangat penting agar perubahan pada kode dan konfigurasi diterapkan.
    ```bash
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    ```

6.  **Restart Queue Worker:**
    Ini akan memastikan worker menggunakan kode yang paling baru.
    ```bash
    sudo supervisorctl restart apotek-worker:*
    ```
