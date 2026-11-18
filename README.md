# PustakaKu - Toko Buku & Peranti Literatur Online

PustakaKu adalah platform e-commerce modern untuk pembelian buku dan alat tulis secara online. Sistem ini dirancang dengan antarmuka yang bersih, responsif, dan terinspirasi dari e-commerce terkemuka di Indonesia, dilengkapi integrasi sistem pembayaran, kalkulasi ongkos kirim multi-kurir, notifikasi email otomatis, serta manajemen multi-alamat pengiriman.

---

## Fitur Utama

- **Katalog & Kurasi Produk:** Penjelajahan koleksi buku dan alat tulis dengan pencarian real-time, filter kategori dinamis, pagination, dan halaman detail produk.
- **Autentikasi Pengguna:** Pendaftaran akun, login konvensional, reset kata sandi via email, dan login cepat menggunakan **Google OAuth 2.0**.
- **Keranjang Belanja:** Pemilihan item belanja berbasis checkbox, pengaturan kuantitas produk, subtotal otomatis, dan proteksi stok inventaris.
- **Checkout & Pengiriman:** Pilihan multi-kurir (JNE, POS, TIKI) dengan estimasi tarif akurat, pencarian wilayah (Provinsi, Kota/Kabupaten, Kecamatan, dan Kode Pos), serta pemilihan alamat pengiriman yang fleksibel.
- **Gateway Pembayaran Terintegrasi:** Integrasi **Midtrans Snap** (Virtual Account, E-Wallet, QRIS, Kartu Kredit) dengan pembaruan status otomatis tanpa jeda (*auto-sync status*).
- **Notifikasi Email:** Pengiriman email HTML konfirmasi pesanan dan pengaturan ulang kata sandi dengan desain rapi serta branding resmi.
- **Manajemen Akun & Alamat:** Riwayat pesanan berbasis nomor unik, pelacakan nomor resi, buku alamat pengiriman (alamat utama dan alamat alternatif), serta pengaturan profil pelanggan.
- **Panel Pengelolaan Admin:** Dashboard analitik pendapatan, manajemen data buku dan stok inventaris, pengelolaan kategori produk, validasi transaksi pesanan, dan input nomor resi pengiriman.

---

## Teknologi yang Digunakan

- **Backend:** PHP 8.1+ (Native dengan arsitektur modular)
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla ES6), Bootstrap 5.3, Bootstrap Icons
- **Database:** MySQL / MariaDB (Mendukung MySQL lokal dan Cloud MySQL seperti Aiven)
- **Layanan Pihak Ketiga (Cloud Services):**
  - **Payment Gateway:** Midtrans Snap API
  - **Logistik / Ongkir:** RajaOngkir API
  - **Media Storage:** Cloudinary CDN
  - **Email Service:** PHPMailer dengan SMTP Google Mail
  - **Social Authentication:** Google Identity Services / OAuth 2.0 Client

---

## Konfigurasi Lingkungan (`.env`)

Buat file bernama `.env` pada direktori utama aplikasi dan isi konfigurasi berikut sesuai dengan akun layanan Anda:

```env
# URL Dasar Aplikasi
APP_URL=http://localhost/projek_web

# Konfigurasi Database (Gunakan localhost untuk lokal atau Host Cloud untuk hosting)
DB_HOST=localhost
DB_PORT=3306
DB_USER=root
DB_PASS=
DB_NAME=pustakaku

# Midtrans Payment Gateway
MIDTRANS_SERVER_KEY=your_midtrans_server_key
MIDTRANS_CLIENT_KEY=your_midtrans_client_key
MIDTRANS_IS_PRODUCTION=false

# Layanan Pengiriman (RajaOngkir)
RAJAONGKIR_API_KEY=your_rajaongkir_api_key
RAJAONGKIR_ORIGIN_CITY_ID=501

# Penyimpanan Gambar (Cloudinary)
CLOUDINARY_CLOUD_NAME=your_cloud_name
CLOUDINARY_API_KEY=your_api_key
CLOUDINARY_API_SECRET=your_api_secret

# Pengiriman Email (SMTP Gmail)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your_email@gmail.com
SMTP_PASS=your_app_password

# Google OAuth 2.0
GOOGLE_CLIENT_ID=your_google_client_id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost/projek_web/auth/google_callback.php
```

---

## Panduan Instalasi Lokal (XAMPP / Apache)

1. **Clone atau Letakkan Repositori:**
   Pindahkan repositori proyek ke dalam direktori web server Anda, misalnya:
   `/opt/lampp/htdocs/projek_web` (Linux) atau `C:\xampp\htdocs\projek_web` (Windows).

2. **Instal Dependensi PHP:**
   Buka terminal pada direktori proyek dan jalankan:
   ```bash
   composer install
   ```

3. **Impor Skema Database:**
   - Buka **phpMyAdmin** (`http://localhost/phpmyadmin`) atau terminal MySQL.
   - Buat database baru dengan nama `pustakaku`.
   - Impor file SQL yang tersedia pada `schema/pustakaku.sql`.

4. **Konfigurasi File `.env`:**
   Salin atau buat file `.env` dan pastikan kredensial database lokal telah sesuai.

5. **Jalankan Aplikasi:**
   Akses aplikasi melalui peramban di `http://localhost/projek_web`.

---

## Panduan Deployment ke Vercel

Proyek ini telah dikonfigurasi agar dapat langsung di-deploy ke Vercel menggunakan runtime serverless PHP (`vercel-php`).

### Langkah-Langkah Deployment:

1. **Siapkan Database Cloud:**
   Karena Vercel berjalan pada lingkungan serverless tanpa penyimpanan MySQL lokal, siapkan database MySQL berbasis cloud seperti **Aiven**, **TiDB Cloud**, atau **Railway**.
   - Buat database baru di cloud provider pilihan Anda.
   - Impor skema tabel dari file `schema/pustakaku.sql` ke database cloud tersebut.

2. **Push Proyek ke GitHub / GitLab:**
   Pastikan seluruh file proyek (termasuk `vercel.json` dan `.vercelignore`) telah di-commit dan di-push ke repositori Git Anda.

3. **Import Project di Dashboard Vercel:**
   - Buka [vercel.com](https://vercel.com) dan masuk ke akun Anda.
   - Klik **"Add New..."** lalu pilih **"Project"**.
   - Pilih repositori Git proyek PustakaKu Anda, lalu klik **"Import"**.

4. **Konfigurasi Environment Variables di Vercel:**
   Pada tahap konfigurasi proyek sebelum deploy, buka bagian **Environment Variables** dan tambahkan variabel berikut:
   - `DB_HOST`: Alamat host MySQL cloud Anda (misal: `mysql-xxx.aivencloud.com`).
   - `DB_PORT`: Port MySQL cloud (misal: `13360` atau `3306`).
   - `DB_USER`: Username database cloud.
   - `DB_PASS`: Password database cloud.
   - `DB_NAME`: Nama database (`pustakaku`).
   - `APP_URL`: URL deployment Vercel Anda (misal: `https://pustakaku.vercel.app`).
   - `MIDTRANS_SERVER_KEY`, `MIDTRANS_CLIENT_KEY`, `MIDTRANS_IS_PRODUCTION`.
   - `RAJAONGKIR_API_KEY`, `RAJAONGKIR_ORIGIN_CITY_ID`.
   - `CLOUDINARY_CLOUD_NAME`, `CLOUDINARY_API_KEY`, `CLOUDINARY_API_SECRET`.
   - `SMTP_HOST`, `SMTP_PORT`, `SMTP_USER`, `SMTP_PASS`.
   - `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET`.
   - `GOOGLE_REDIRECT_URI`: Setel ke URL callback di Vercel, misalnya `https://pustakaku.vercel.app/auth/google_callback.php`.

5. **Selesaikan Deployment:**
   - Klik **"Deploy"**. Vercel akan otomatis membaca file `vercel.json` dan membangun aplikasi Anda.
   - Setelah deployment berhasil, perbarui **Authorized Redirect URIs** pada Google Cloud Console dan URL Callback pada Midtrans Dashboard agar sesuai dengan domain Vercel Anda (`https://your-project.vercel.app`).

---

## Lisensi & Kontribusi

Proyek ini dikembangkan untuk kebutuhan platform transaksi literatur dan alat tulis yang aman, modern, dan handal. Hak cipta dilindungi undang-undang.

