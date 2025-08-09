# Fitur Baru: Manajemen Stok Opname yang Ditingkatkan

Dokumen ini menjelaskan perubahan dan fungsionalitas baru untuk modul Stok Opname, termasuk cara kerjanya dan proses deployment.

## 1. Latar Belakang Masalah

Sebelumnya, modul Stok Opname memiliki keterbatasan berikut:
*   Tidak ada entitas terpisah untuk sesi opname; penyesuaian stok langsung dicatat di `stock_movements`.
*   Tidak ada fungsionalitas untuk mengedit atau menghapus catatan opname yang sudah ada.
*   Logika pergerakan stok terkait opname tersebar dan tidak konsisten, terutama saat penghapusan.

## 2. Solusi dan Pendekatan Baru

Untuk mengatasi masalah ini, sistem Stok Opname telah dirombak total dengan pendekatan berbasis Observer dan struktur data yang lebih terpusat.

### Komponen Utama yang Diperkenalkan/Dimodifikasi:

*   **Tabel Database Baru:**
    *   `stock_opnames`: Tabel utama untuk mencatat setiap sesi opname (tanggal, catatan, petugas).
    *   `stock_opname_details`: Tabel detail yang mencatat penyesuaian stok per batch dalam setiap sesi opname (stok sistem, stok fisik, selisih).
*   **Model Eloquent Baru:**
    *   `App\Models\StockOpname`
    *   `App\Models\StockOpnameDetail`
*   **Observer Baru (Otak Logika Stok):**
    *   `App\Observers\StockOpnameObserver`: Mengamati model `StockOpname`. Saat `StockOpname` dihapus, observer ini akan memastikan semua `StockOpnameDetail` terkait dihapus satu per satu, memicu observer detail.
    *   `App\Observers\StockOpnameDetailObserver`: Mengamati model `StockOpnameDetail`. Ini adalah inti logika stok:
        *   **`created`**: Mengurangi/menambah stok di `product_batches` dan mencatat pergerakan 'OP' di `stock_movements`.
        *   **`updated`**: Menggunakan pola "Batalkan lalu Terapkan Kembali" (revert-then-reapply) untuk menyesuaikan stok dan mencatat pergerakan 'OP'/'R-OP'.
        *   **`deleted`**: Membalikkan penyesuaian stok di `product_batches` dan mencatat pergerakan 'R-OP' (Retur Opname) di `stock_movements` untuk audit.
*   **Komponen Livewire yang Direfaktor:**
    *   `App\Livewire\InventoryCount` (sebelumnya `StockOpname`): Komponen ini telah dirombak total untuk berinteraksi dengan model `StockOpname` dan `StockOpnameDetail` yang baru. UI-nya kini menampilkan daftar riwayat opname, formulir pembuatan opname baru, dan detail opname.
*   **File View yang Direvisi:**
    *   `resources/views/livewire/inventory-count.blade.php`: Tampilan telah diperbarui untuk mendukung fungsionalitas baru dan perbaikan UI (perataan tabel, tombol selalu terlihat).
*   **Migrasi Data Historis:**
    *   Migrasi `2025_08_05_160500_backfill_old_stock_opnames.php`: Skrip satu kali ini bertanggung jawab untuk memindahkan semua catatan opname lama dari `stock_movements` ke dalam struktur tabel `stock_opnames` dan `stock_opname_details` yang baru.

## 3. Fungsionalitas Baru

Dengan perubahan ini, Anda sekarang dapat:
*   **Membuat Stok Opname Baru:** Catatan opname akan disimpan sebagai entitas terpisah.
*   **Melihat Riwayat Stok Opname:** Semua sesi opname, baik lama maupun baru, akan ditampilkan dalam satu daftar yang terorganisir.
*   **Menghapus Stok Opname:** Menghapus sebuah sesi opname akan secara otomatis membalikkan penyesuaian stok yang telah dilakukan dan mencatat pembalikan tersebut di kartu stok (`stock_movements`).
*   **Mengedit Stok Opname:** Mengedit sebuah sesi opname akan menyesuaikan stok dengan benar berdasarkan perubahan yang dilakukan.

## 4. Proses Deployment ke Produksi

Untuk menerapkan perubahan ini ke lingkungan produksi tanpa kehilangan data, ikuti langkah-langkah berikut:

1.  **Pull Kode Terbaru:**
    ```bash
    git pull origin main # atau nama branch Anda
    ```
2.  **Update Dependensi (jika perlu):**
    ```bash
    composer install --no-dev --optimize-autoloader
    npm install --production # jika membangun aset di server
    npm run build # jika membangun aset di server
    ```
3.  **Jalankan Migrasi Database:**
    Ini akan membuat tabel baru dan menjalankan skrip migrasi data satu kali.
    ```bash
    php artisan migrate
    ```
    *Penting: Jangan gunakan `php artisan migrate:fresh` di produksi karena akan menghapus semua data.*
4.  **Bersihkan Cache Aplikasi:**
    ```bash
    php artisan optimize:clear
    ```
5.  **Restart Queue Workers (jika menggunakan):**
    ```bash
    php artisan queue:restart
    ```

Dengan proses ini, semua data opname historis Anda akan terintegrasi ke dalam sistem baru, dan Anda dapat memanfaatkan fungsionalitas manajemen stok opname yang ditingkatkan.
