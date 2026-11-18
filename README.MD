# 📚 PustakaKu - Sistem Penjualan Buku & Alat Tulis Online

**PustakaKu** adalah platform e-commerce terpadu untuk penjualan buku dan alat tulis kantor (ATK) berbasis **PHP** dan **MySQL**. Sistem ini dirancang dengan antarmuka modern yang bersih dan responsif, terintegrasi dengan gateway pembayaran Midtrans Snap, kalkulasi ongkos kirim multi-kurir (JNE, POS, TIKI), penyimpanan media cloud (Cloudinary), notifikasi email otomatis (SMTP PHPMailer), login Google OAuth 2.0, manajemen multi-alamat pengiriman pelanggan, serta panel kendali lengkap bagi administrator.

---

## 👥 Profil Pengembang

| No | Nama Anggota | NIM |
|:--:|:-------------|:----|
| 1  | **Rizky Prasetyo Landung Nugroho** | **123250078** |
| 2  | **Adnan Rohmat Kurniansah** | **123250085** |

---

## 🛠️ Teknologi & Layanan Cloud

- **Bahasa & Backend:** PHP 8.1+
- **Database:** MySQL / MariaDB (Kompatibel dengan MySQL Lokal & Cloud MySQL seperti Aiven)
- **Frontend & Styling:** HTML5, CSS3 Modern, JavaScript (Vanilla ES6), Bootstrap 5.3, Bootstrap Icons
- **Payment Gateway:** Midtrans Snap API (Virtual Account, E-Wallet, QRIS, Kartu Kredit)
- **Layanan Pengiriman:** API RajaOngkir (JNE, POS Indonesia, TIKI)
- **Penyimpanan Media (CDN):** Cloudinary Media Storage
- **Layanan Email:** PHPMailer dengan SMTP Google Mail
- **Autentikasi Sosial:** Google Identity Services / OAuth 2.0

---

## ✨ Fitur-Fitur Utama Sistem

### 1. 🔐 Autentikasi & Akun Pengguna
- **Multi-Role User:** Pemisahan hak akses antara akun **Administrator** dan **Pelanggan**.
- **Login Google OAuth 2.0:** Masuk cepat dengan sekali klik menggunakan akun Google resmi.
- **Pendaftaran & Login Standar:** Enkripsi password menggunakan `password_hash()` (Bcrypt).
- **Lupa & Reset Kata Sandi:** Pengiriman tautan reset password aman ke email pelanggan dengan batas waktu kedaluwarsa 1 jam.
- **Manajemen Akun Pelanggan (`akun.php`):**
  - Tab navigasi modern: *Riwayat Pesanan*, *Daftar Alamat*, *Ubah Profil*, dan *Ganti Kata Sandi*.
  - Pembaruan nama, nomor telepon, dan unggah foto profil langsung ke Cloudinary CDN.
  - Alamat pengiriman dikelola secara terpusat pada tab Daftar Alamat.

### 2. 🏪 Beranda & Etalase Produk
- **Hero Banner Carousel:** Banner promo buku dan informasi layanan toko.
- **Section Rekomendasi:** Kurasi buku-buku pilihan editor.
- **Filter Kategori Dinamis:** Filter cepat koleksi buku per kategori (*Fiksi, Non-Fiksi, Bisnis, Komik, ATK, dll.*) langsung di beranda tanpa memuat ulang halaman.
- **Fallback Gambar Cerdas:** Tampilan gambar sampul otomatis mengarah ke Cloudinary CDN dengan fallback ke placeholder lokal jika berkas gambar tidak ditemukan.

### 3. 🔍 Katalog & Pencarian Produk
- **Pencarian Multi-Atribut:** Menemukan buku berdasarkan judul, nama penulis, atau penerbit.
- **Filter Lengkap:** Filter kategori buku, ketersediaan stok (*Ready Stock*), dan rentang harga.
- **Pengurutan (Sorting):** Urutan berdasarkan Rekomendasi, Termurah, Termahal, dan Terbaru.
- **Navigasi Halaman (Pagination):** Pembagian halaman produk yang teratur dan responsif.

### 4. 📖 Detail Produk & Ulasan
- **Spesifikasi Lengkap:** Judul, penulis, penerbit, nomor ISBN, kategori, berat gram, sisa stok gudang, dan sinopsis buku.
- **Kalkulasi Diskon:** Tampilan harga normal, persentase diskon, dan harga hemat.
- **Kontrol Kuantitas:** Pembatasan kuantitas pemesanan secara otomatis mengikuti sisa stok fisik.
- **Ulasan & Rating:** Tampilan ulasan pembeli dengan peringkat bintang 1–5.
- **Rekomendasi Produk Serupa:** Daftar buku terkait dari kategori yang sama.

### 5. 🛒 Keranjang Belanja
- **Tambah ke Keranjang Instan:** Penambahan produk dengan feedback notifikasi toast di pojok kanan atas.
- **Pilihan Item Berbasis Checkbox:** Pelanggan bebas mencentang sebagian atau semua barang yang ingin di-checkout.
- **Pembaruan Kuantitas Real-Time:** Penyesuaian jumlah barang langsung memperbarui subtotal belanja.
- **Hapus Item:** Hapus satuan maupun hapus massal untuk item terpilih.

### 6. 💳 Checkout & Kalkulasi Pengiriman
- **Tampilan Modern (Tokopedia/Shopee Style):** Desain card bersih ber-border lembut, section alamat ber-badge, dan *sticky summary* tagihan di sebelah kanan.
- **Buku Alamat Pengiriman:** Dukungan banyak alamat (Alamat Utama & Alamat Alternatif) dengan modal pemilihan alamat bebas bug overlay.
- **Pencarian Wilayah Lengkap:** Pemilihan Provinsi dan Kota/Kabupaten otomatis dari master data logistik, dilengkapi input **Kecamatan** dan **Kode Pos** yang tersimpan rapi pada invoice.
- **Pilihan Kurir & Layanan:** Pilihan kurir JNE, POS Indonesia, dan TIKI dengan penghitungan ongkir otomatis berdasarkan akumulasi berat paket pesanan (gram).
- **Catatan Pesanan:** Kolom khusus untuk instruksi tambahan ke penjual (misal: packing bubble wrap tebal).

### 7. 💰 Pembayaran Midtrans Snap & Status Otomatis
- **Integrasi Midtrans Snap Popup:** Pembayaran langsung melalui popup Snap di halaman checkout tanpa dialihkan ke halaman pihak ketiga yang rumit.
- **Pembaruan Status Otomatis (*Auto-Sync*):** Begitu pembayaran selesai di popup Snap, sistem langsung mengarahkan ke halaman pesanan sukses dengan status **`diproses`** secara otomatis (tanpa tombol manual dan tanpa tertahan di layar *"Back to merchant page"*).
- **Cek Status Server-Side:** Dilengkapi verifikasi status otomatis melalui Server Key Midtrans jika terdapat transaksi yang tertunda.

### 8. ✉️ Notifikasi Email HTML Profesional
- **Email Konfirmasi Pesanan:** Dikirimkan otomatis saat pesanan dibuat, berisi nomor pesanan, rincian buku, ongkos kirim, alamat tujuan (lengkap dengan kecamatan), total bayar, dan tombol pelacakan pesanan.
- **Email Reset Password:** Tautan pengaturan ulang kata sandi dengan token terenkripsi.
- **Tampilan Brand Bersih:** Header berlatar putih bersih (tanpa blok hijau besar) dengan ikon buku resmi beresolusi tinggi (format *CID Embedded Image*) yang dijamin tampil di Gmail, Outlook, Apple Mail, dan peramban ponsel tanpa diblokir.

### 9. 📊 Panel Administrator (`/admin`)
- **Dashboard Statistik:** Ringkasan total pendapatan toko, total pesanan masuk, jumlah koleksi buku, jumlah pelanggan terdaftar, dan notifikasi peringatan stok menipis (<= 5).
- **Manajemen Buku:** Tambah, ubah, dan hapus katalog buku, upload gambar sampul langsung ke cloud, penetapan harga, diskon, dan stok.
- **Manajemen Kategori:** Kelola kategori buku dan ATK.
- **Manajemen Supplier & Restok:** Pencatatan mitra penerbit/distributor serta pencatatan stok masuk (*restock log*) yang otomatis menambahkan jumlah stok buku.
- **Manajemen Pesanan:** Verifikasi pesanan pembeli, pembaruan status pesanan (*Menunggu Pembayaran -> Diproses -> Dikirim -> Selesai / Dibatalkan*), dan penginputan nomor resi pengiriman.
- **Laporan Penjualan:** Rekapitulasi penjualan berdasarkan rentang tanggal dan status transaksi, dilengkapi fitur cetak laporan resmi untuk arsip fisik.

---

## ⚙️ Konfigurasi Environment Variables (`.env`)

Buat file bernama `.env` di direktori utama proyek dan sesuaikan nilainya:

```env
# URL Dasar Aplikasi
APP_URL=http://localhost/projek_web

# Database MySQL (Gunakan localhost untuk lokal atau Host Cloud untuk hosting)
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
CLOUDINARY_CLOUD_NAME=your_cloudinary_cloud_name
CLOUDINARY_API_KEY=your_cloudinary_api_key
CLOUDINARY_API_SECRET=your_cloudinary_api_secret

# Pengiriman Email (SMTP Gmail)
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USER=your_email@gmail.com
SMTP_PASS=your_gmail_app_password

# Google OAuth 2.0
GOOGLE_CLIENT_ID=your_google_client_id.apps.googleusercontent.com
GOOGLE_CLIENT_SECRET=your_google_client_secret
GOOGLE_REDIRECT_URI=http://localhost/projek_web/auth/google_callback.php
```

---

## 🚀 Panduan Instalasi Lokal (XAMPP / Apache)

1. **Pindahkan Folder Proyek:**
   Tempatkan folder `projek_web` di direktori htdocs web server Anda:
   - Linux: `/opt/lampp/htdocs/projek_web`
   - Windows: `C:/xampp/htdocs/projek_web`

2. **Instal Dependensi Composer:**
   Jalankan perintah berikut di terminal direktori proyek:
   ```bash
   composer install
   ```

3. **Impor Database:**
   - Akses **phpMyAdmin** di browser (`http://localhost/phpmyadmin`).
   - Buat database baru dengan nama `pustakaku`.
   - Buka tab **Import**, pilih file `schema/pustakaku.sql`, lalu klik **Import / Go**.

4. **Konfigurasi File `.env`:**
   Sesuaikan kredensial database lokal Anda pada file `.env`.

5. **Jalankan Aplikasi:**
   Buka browser dan akses:
   ```text
   http://localhost/projek_web/
   ```

---

## 🔑 Kredensial Akun Uji Coba

| Peran (Role) | Email | Password | Hak Akses |
|:---|:---|:---|:---|
| **Administrator** | `admin@pustakaku.com` | `admin123` | Akses penuh dashboard `/admin`, inventaris buku, restok, pesanan masuk, dan laporan penjualan. |
| **Pelanggan 1** | `budi@gmail.com` | `password123` | Akun pelanggan aktif dengan data alamat lengkap dan riwayat pesanan. |
| **Pelanggan 2** | `atnandimas@gmail.com` | `password123` | Akun pelanggan aktif untuk pengujian checkout & transaksi. |
