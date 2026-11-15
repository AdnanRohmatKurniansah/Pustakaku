# 📚 PustakaKu - Sistem Penjualan Buku dan Alat Tulis

**PustakaKu** adalah aplikasi web e-commerce penjualan buku dan alat tulis kantor (ATK) berbasis **PHP Native** dan **MySQL**. Proyek ini dikembangkan sebagai solusi terpadu belanja online buku dan perlengkapan literasi yang dilengkapi dengan manajemen inventaris, sistem checkout dinamis, kalkulasi ongkos kirim otomatis berdasarkan berat, diskon gratis ongkir, pelacakan pesanan, serta modul dashboard analitik dan laporan penjualan bagi administrator.

---

## 👥 Profil Kelompok

| No | Nama Anggota | NIM |
|:--:|:-------------|:------------------------------|
| 1  | **Rizky Prasetyo Landung Nugroho** | **123250078** |
| 2  | **Adnan Rohmat Kurniansah** | **123250085** |

---

## 🛠️ Tech Stack & Library

* **Back-End:** PHP 8.x (Native Procedural & OOP Database Layer)
* **Database:** MySQL / MariaDB (Engine InnoDB, Relational Foreign Keys, Cascade/Restrict Integrity)
* **Front-End:** HTML5, CSS3 Modern, Vanilla JavaScript 
* **CSS & Icon Framework:** Bootstrap 5.3, Bootstrap Icons 1.11, Font Awesome 6
* **Web Server & Environment:** Apache / XAMPP on Linux & Windows

---

## ✨ Fitur-Fitur Sistem

### 1. 🔐 Modul Autentikasi & Manajemen Pengguna
* **Autentikasi Multi-Role:** Sistem login terpisah untuk peran **Admin** dan **Pelanggan**.
* **Keamanan Password:** Enkripsi password menggunakan `password_hash()` (Bcrypt) untuk pelanggan baru dan kompatibilitas hash admin.
* **Role-Based Access Control:** Proteksi halaman panel admin dari akses tidak sah oleh tamu (*guest*) atau pelanggan.
* **Profil Pengguna (`akun.php`):** Pelanggan dapat memperbarui nama lengkap, nomor telepon, dan alamat pengiriman default.
* **Profil Administrator (`admin/profile/`):** Admin dapat mengelola foto profil, kontak, nama, dan detail identitas pengelola toko.

### 2. 🏪 Modul Beranda & Etalase Produk
* **Hero Promo Carousel:** Slider banner interaktif untuk promosi buku pilihan dan promo gratis ongkir.
* **Section Buku Rekomendasi:** Sorotan koleksi buku unggulan pilihan editor.
* **Section Buku Baru Berfilter Dinamis:** Filter rilisan buku terbaru per kategori (*Semua, Fiksi, Non-Fiksi, Bisnis, Komik, dll.*) langsung di halaman beranda.
* **Kategori Populer Pilihan:** Pintasan kategori buku & ATK yang sinkron dengan katalog dan navigasi.
* **Fallback Image Placeholder:** Seluruh elemen gambar terintegrasi dengan fallback otomatis ke `assets/images/placeholder.png` jika file fisik sampul tidak ditemukan di server.

### 3. 🔍 Modul Katalog & Pencarian Pintar
* **Pencarian Multi-Atribut:** Menemukan buku berdasarkan judul, nama penulis, penerbit, maupun nomor ISBN.
* **Filter Komprehensif:** Filter kategori produk, status ketersediaan stok (*Ready Stock*), dan filter rentang harga minimum-maksimum.
* **Pengurutan (Sorting):** Urutkan katalog berdasarkan Rekomendasi, Harga Terendah, Harga Tertinggi, Rilisan Terbaru, atau Nama (A-Z).
* **Pagination Dinamis:** Pembagian halaman katalog yang rapi untuk kenyamanan navigasi belanja.

### 4. 📖 Modul Detail Produk & Ulasan
* **Informasi Lengkap Produk:** Informasi detail judul, penulis, penerbit, nomor ISBN, kategori, berat gram, jumlah stok tersedia, dan sinopsis/deskripsi.
* **Kalkulator Diskon Otomatis:** Perhitungan harga coret, persentase diskon, dan nominal penghematan pelanggan.
* **Pilihan Kuantitas Real-Time:** Input kuantitas yang dibatasi secara cerdas sesuai sisa stok fisik buku.
* **Ulasan & Rating Bintang:** Tab ulasan pembeli dengan peringkat bintang 1-5 dan testimoni ulasan.
* **Rekomendasi Produk Serupa:** Daftar buku terkait dari kategori yang sama di bagian bawah halaman.

### 5. 🛒 Modul Keranjang Belanja (Shopping Cart)
* **Tambah Keranjang Tanpa Reload:** Menambahkan produk ke keranjang secara asinkron dengan respon notifikasi toast di pojok kanan atas.
* **Manajemen Kuantitas Dinamis:** Tambah atau kurangi jumlah barang langsung dari tabel keranjang dengan kalkulasi otomatis.
* **Pengecekan Stok Cerdas:** Mencegah penambahan kuantitas melebihi sisa stok yang ada di gudang.
* **Kalkulasi Akumulasi:** Ringkasan subtotal harga produk, potongan diskon, dan total estimasi berat pesanan (gram).
* **Progress Banner Gratis Ongkir:** Indikator visual syarat minimal belanja Rp 100.000 untuk mendapatkan subsidi gratis ongkir.

### 6. 💳 Modul Checkout & Transaksi Pengiriman
* **Form Alamat Penerima Lengkap:** Input nama penerima, nomor telepon aktif, alamat lengkap tujuan, dan catatan pengiriman.
* **Pilihan Jasa Ekspedisi:** Dukungan kurir JNE, SiCepat, J&T Express, GoSend Instant, dan GrabExpress.
* **Kalkulasi Ongkir Berbasis Berat:** Perhitungan tarif ongkos kirim otomatis yang dihitung secara proporsional dari total berat barang pesanan (per kg).
* **Diskon Otomatis Gratis Ongkir:** Pembelian dengan subtotal minimal Rp 100.000 otomatis memperoleh diskon ongkir hingga Rp 20.000 (jika ongkir lebih dari Rp 20.000, sisa tarif ditagihkan secara adil; jika ongkir di bawahnya maka menjadi Rp 0).
* **Pilihan Metode Pembayaran Beragam:**
  * Transfer Bank Otomatis / Virtual Account: Bank BCA, Bank Mandiri, Bank BRI
  * E-Wallet: GoPay, OVO, ShopeePay
  * Bayar di Tempat (COD / Cash on Delivery)
* **Otomasi Status Pembayaran:** Pesanan dengan metode transfer/e-wallet langsung terkonfirmasi lunas dan berstatus `diproses`, siap dikemas oleh penjual.
* **Pengurangan Stok Otomatis:** Stok produk di database otomatis berkurang sesuai kuantitas yang di-checkout, dan keranjang belanja dikosongkan.

### 7. 📄 Modul Pesanan Sukses & Riwayat Transaksi (Pelanggan)
* **Halaman Konfirmasi Pesanan (`pesanan_sukses.php`):** Menampilkan rincian invoice, nomor resi/pesanan, kurir, alamat tujuan, rincian biaya, dan metode pembayaran.
* **Cetak Invoice / Nota Resmi:** Tombol cetak nota belanja yang ramah format cetak kertas (print-friendly).
* **Tab Riwayat Pesanan (`akun.php`):** Pemantauan status transaksi pelanggan yang dikelompokkan ke dalam tab: *Semua, Menunggu Pembayaran, Diproses, Dikirim, Selesai, dan Dibatalkan*.

### 8. 📊 Modul Dashboard & Panel Kendali Toko (Admin)
* **Dashboard Metrik Utama (`admin/index.php`):**
  * Statistik total omzet pendapatan toko
  * Jumlah transaksi pesanan masuk
  * Total inventaris buku aktif
  * Jumlah pelanggan terdaftar
  * Peringatan stok kritis (*Low Stock Alert* untuk stok <= 5)
  * Tabel transaksi pesanan terkini
* **Manajemen Kategori Buku (`admin/kategori/`):** CRUD kategori buku & ATK, deskripsi kategori, dan upload gambar ikon kategori.
* **Manajemen Data Buku (`admin/buku/`):** CRUD katalog buku, upload gambar sampul, pengaturan harga, persentase diskon, nomor ISBN, penerbit, berat produk, dan status tampil.
* **Manajemen Supplier & Penerbit (`admin/supplier/`):** CRUD data penerbit dan distributor mitra buku lengkap dengan alamat, telepon, dan email.
* **Manajemen Restok Barang (`admin/restok/`):** Form pencatatan stok masuk dari supplier, otomatis mengupdate penambahan stok fisik buku, serta riwayat log restok masuk.
* **Manajemen Pesanan Masuk (`admin/pesanan/`):** Pemantauan seluruh pesanan pembeli, filter status pesanan, inspeksi detail pesanan & alamat pengiriman, serta pembaruan status pesanan (*Menunggu Pembayaran -> Diproses -> Dikirim -> Selesai / Dibatalkan*).
* **Laporan Penjualan (`admin/laporan/`):** Rekapitulasi laporan penjualan lengkap dengan filter periode tanggal, filter status transaksi, ringkasan total omzet, total transaksi, total produk terjual, serta tombol cetak laporan resmi untuk arsip fisik.

---

## 📁 Struktur Direktori Proyek

```text
projek_web/
├── admin/                        # Modul Panel Administrator
│   ├── buku/                     # CRUD data buku & upload sampul
│   │   ├── edit.php
│   │   ├── hapus.php
│   │   ├── index.php
│   │   ├── proses_edit.php
│   │   ├── proses_tambah.php
│   │   └── tambah.php
│   ├── kategori/                 # CRUD kategori buku & ATK
│   │   ├── edit.php
│   │   ├── hapus.php
│   │   ├── index.php
│   │   ├── proses_edit.php
│   │   ├── proses_tambah.php
│   │   └── tambah.php
│   ├── laporan/                  # Laporan rekapitulasi penjualan & cetak
│   │   └── index.php
│   ├── pesanan/                  # Manajemen & pemrosesan status pesanan
│   │   ├── detail.php
│   │   ├── index.php
│   │   └── proses_status.php
│   ├── profile/                  # Profil & foto administrator
│   │   ├── index.php
│   │   └── proses_edit.php
│   ├── restok/                   # Pencatatan stok masuk dari supplier
│   │   ├── detail.php
│   │   ├── index.php
│   │   ├── proses_tambah.php
│   │   └── tambah.php
│   ├── supplier/                 # CRUD penerbit & distributor buku
│   │   ├── edit.php
│   │   ├── hapus.php
│   │   ├── index.php
│   │   ├── proses_edit.php
│   │   ├── proses_tambah.php
│   │   └── tambah.php
│   └── index.php                 # Dashboard statistik & metrik utama
├── assets/                       # Aset statis aplikasi
│   ├── css/
│   │   ├── admin.css             # Styling khusus dashboard admin
│   │   └── style.css             # Styling kustom antarmuka toko
│   ├── images/
│   │   ├── banner/               # Banner promosi carousel
│   │   └── placeholder.png       # Fallback gambar saat berkas tidak ditemukan
│   ├── js/
│   │   ├── cart.js               # Handler keranjang belanja AJAX
│   │   └── toast.js              # Sistem notifikasi toast reusable (kanan atas)
│   └── uploads/                  # Berkas unggahan pengguna
│       ├── buku/                 # Gambar sampul buku
│       ├── kategori/             # Ikon kategori
│       └── user/                 # Foto profil user/admin
├── auth/                         # Modul autentikasi pengguna
│   ├── login.php                 # Halaman formulir login
│   ├── logout.php                # Pembersihan session keluar akun
│   ├── process_login.php         # Handler validasi & verifikasi peran login
│   ├── process_register.php      # Handler pendaftaran pelanggan baru
│   └── register.php              # Halaman formulir registrasi
├── cart/                         # Endpoint backend keranjang belanja
│   ├── aksi_keranjang.php
│   ├── proses_keranjang.php
│   └── tambah.php
├── config/                       # Konfigurasi aplikasi
│   ├── database.php              # Koneksi database MySQLi
│   └── helper.php                # Fungsi utilitas (format rupiah, badge status)
├── layouts/                      # Komponen antarmuka modular
│   ├── admin/                    # Layout khusus panel admin
│   │   ├── footer.php
│   │   ├── navbar.php
│   │   └── sidebar.php
│   ├── footer.php                # Footer publik
│   └── navbar.php                # Navigasi utama dengan keranjang dinamis
├── schema/                       # Skema database & data dummy
│   └── pustakaku.sql             # SQL DDL 9 tabel & data dummy lengkap
├── akun.php                      # Profil pelanggan & riwayat pesanan
├── checkout.php                  # Halaman checkout pengiriman & pembayaran
├── detail_buku.php               # Halaman spesifikasi & ulasan buku
├── index.php                     # Halaman beranda utama toko
├── katalog.php                   # Halaman katalog pencarian & filter buku
├── keranjang.php                 # Halaman keranjang belanja
├── pesanan_sukses.php            # Halaman konfirmasi pesanan & cetak nota
├── proses_checkout.php           # Handler transaksi checkout pesanan
└── README.MD                     # Dokumentasi resmi proyek
```
---

## 🚀 Panduan Instalasi & Menjalankan Proyek

### 1. Kebutuhan Sistem
* Web Server: **Apache** (XAMPP / LAMPP / WampServer)
* Versi PHP: **PHP 8.0** atau versi yang lebih baru
* Database: **MySQL** atau **MariaDB**
* Web Browser: Google Chrome, Mozilla Firefox, atau browser modern lainnya

### 2. Langkah-Langkah Instalasi

1. **Unduh / Tempatkan Proyek:**
   Pindahkan folder proyek `projek_web` ke dalam direktori root web server Anda:
   * Di Windows (XAMPP): `C:/xampp/htdocs/projek_web`
   * Di Linux (XAMPP/LAMPP): `/opt/lampp/htdocs/projek_web`

2. **Jalankan Layanan Apache & MySQL:**
   Buka aplikasi XAMPP Control Panel dan nyalakan modul **Apache** dan **MySQL**.

3. **Impor Skema & Data Database:**
   * Buka browser dan akses **phpMyAdmin** melalui alamat `http://localhost/phpmyadmin/`.
   * Buat database baru bernama `pustakaku` dengan collation `utf8mb4_general_ci`.
   * Buka tab **Import**, pilih berkas `schema/pustakaku.sql` yang berada di dalam folder proyek, lalu klik **Import / Go**.
   * *(Opsi Baris Perintah / Terminal):*
     ```bash
     mysql -u root -p pustakaku < schema/pustakaku.sql
     ```

4. **Konfigurasi Koneksi Database (Jika Diperlukan):**
   Buka berkas `config/database.php` dan sesuaikan kredensial server lokal Anda jika berbeda dari konfigurasi standar:
   ```php
   $hostname = "localhost";
   $username = "root";
   $password = "";
   $database = "pustakaku";
   ```

5. **Akses Aplikasi Melalui Browser:**
   Buka peramban web dan navigasikan ke alamat:
   ```text
   http://localhost/projek_web/
   ```

---

## 🔑 Kredensial Akun Uji Coba

Untuk memudahkan pengujian fungsionalitas sistem, telah disediakan akun bawaan di dalam database:

| Peran (Role) | Email / Username | Password | Keterangan Akses |
|:---|:---|:---|:---|
| **Administrator** | `admin@pustakaku.com` | `admin123` | Akses penuh dashboard `/admin`, inventaris buku, kategori, restok, pesanan masuk, dan laporan penjualan. |
| **Pelanggan 1** | `budi@gmail.com` | `password123` | Akun pelanggan aktif dengan data alamat lengkap di Yogyakarta dan riwayat pesanan. |
| **Pelanggan 2** | `atnandimas@gmail.com` | `password123` | Akun pelanggan aktif dengan data alamat di Jakarta Pusat. |

