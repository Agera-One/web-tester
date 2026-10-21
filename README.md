# SeventhSoft Web Tester

Aplikasi *Automatic Testing End-to-End* (E2E) untuk modul ERP SeventhSoft (web-new). Dibangun menggunakan antarmuka **Laravel 8** untuk manajemen eksekusi, serta **Playwright** dan peramban **Chromium** di latar belakang untuk melakukan pengujian otomatis layaknya interaksi pengguna asli.

---

## 🚀 Panduan Instalasi (Local/Server)

Pastikan sistem Anda telah terpasang **PHP 7.4 / 8.0**, **MySQL 5.5+**, dan **Node.js 18+**.

1. **Kloning Repositori & Instalasi Dependensi**
   ```bash
   git clone <url-repo-web-tester>
   cd web-tester
   composer install
   npm install
   ```

2. **Konfigurasi Environment**  
   Salin file konfigurasi dan buat application key:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```
   Atur kredensial pangkalan data (`DB_DATABASE=web_tester`) dan kredensial Telegram (`TELEGRAM_BOT_TOKEN`, `TELEGRAM_CHAT_ID`) di dalam `.env`.

3. **Migrasi Pangkalan Data**
   ```bash
   php artisan migrate
   ```

4. **Instalasi Peramban Playwright**  
   Karena aplikasi ini menggunakan mode headless, pasang Chromium beserta dependensinya:
   ```bash
   npx playwright install --with-deps chromium
   ```

---

## ⚙️ Cara Menjalankan Aplikasi

Aplikasi Web Tester memerlukan beberapa proses yang berjalan secara bersamaan agar antrian pengujian dan penjadwalan (scheduler) berfungsi penuh.

1. **Menjalankan Server Web (Lokal)**
   ```bash
   php artisan serve
   ```

2. **Menjalankan Pekerja Antrean (Queue Worker)**  
   Wajib dijalankan agar aplikasi dapat mengeksekusi Playwright saat tombol "Run" ditekan. Gunakan batas waktu 3000 detik (50 menit) karena pengujian E2E memakan waktu:
   ```bash
   php artisan queue:listen --timeout=3000
   ```
   *(Catatan Server: Pada server produksi, gunakan Supervisor agar proses ini berjalan abadi di latar belakang).*

3. **Menjalankan Penjadwalan Otomatis (Cron Job)**  
   Agar fitur eksekusi otomatis jam 00:00 berjalan, tambahkan baris berikut ke konfigurasi Cron di server:
   ```bash
   * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
   ```

---

## 📝 Konvensi & Tata Cara Penulisan Skenario Baru

Semua skenario pengujian disimpan di dalam folder `tests-e2e/specs/`. Saat membuat file `.spec.ts` baru (baik via IDE maupun fitur "Tambah Skenario" di web), patuhi aturan berikut:

- **Pemilahan Direktori**: Satu file `.spec.ts` digunakan khusus untuk satu halaman/fitur web-new, dan diletakkan di dalam folder modulnya masing-masing (misal: `specs/sales/`).
- **Penamaan Judul & Tag**: Gunakan Bahasa Indonesia untuk deskripsi tes dan tambahkan tag di akhir judul untuk mempermudah filter (contoh: `test('Dapat menyimpan faktur penjualan @sales @critical', ...)`).
- **Optimasi Login**: Gunakan modul helper bawaan untuk otentikasi. Jangan melakukan penulisan skrip login berulang-ulang di setiap baris tes.
- **Penggunaan Page Object**: Gunakan metode Page Object agar selector elemen (seperti tombol dan input) tidak tersebar acak di dalam skrip skenario.
- **Prioritas Selector Playwright**: Gunakan atribut identifikasi dengan urutan prioritas: `id` / `name` > teks tombol > CSS. Hindari penggunaan XPath yang terlalu panjang dan rentan patah jika UI berubah.
- **Penanda Data Uji**: Jika tes melakukan penambahan data (misal: membuat Purchase Order baru), sisipkan penanda pada kolom keterangan, misalnya `E2E-<timestamp>`, agar data sampah mudah dibersihkan nantinya.
- **Independensi Tes**: Setiap blok tes (`test()`) harus mampu berjalan sendiri secara mandiri tanpa bergantung pada urutan keberhasilan tes lainnya.
