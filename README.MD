# 📚 PustakaKu - Sistem Penjualan Buku dan Alat Tulis

**PustakaKu**, aplikasi web penjualan buku dan alat tulis kantor (ATK) berbasis PHP Native dan MySQL. Proyek ini dikembangkan untuk memenuhi tugas akhir praktikum mata kuliah Pemrograman Web.

---

## 👥 Profil Kelompok

| No | Nama Anggota | NIM | 
|:--:|:-------------|:------------------------------|
| 1  | **Rizky Prasetyo Landung Nugroho** | **123250078** |
| 2  | **Adnan Rohmat Kurniansah** | **123250085** |

---

## 📖 Ringkasan Proyek

**PustakaKu** adalah platform toko online terpadu yang memadukan katalog digital buku (fiksi, non-fiksi, pelajaran, dll.) dan alat tulis kantor/sekolah dengan sistem manajemen inventaris dan transaksi penjualan. 

Sistem ini dirancang dengan dua hak akses utama:
1. **Pelanggan (Customer):** Menjelajah katalog, melakukan pencarian dan filter cerdas, mengelola wishlist, belanja lewat keranjang, checkout belanja, upload bukti pembayaran, hingga cetak invoice belanja.
2. **Administrator (Admin):** Mengelola produk (buku & ATK), kategori, stok & supplier, kode diskon/voucher, validasi pesanan, pemantauan statistik pendapatan, serta rekapitulasi laporan penjualan.

---

## ✨ Fitur-Fitur Sistem

### 1. Modul Autentikasi & Manajemen Session
* **Registrasi & Login Multi-Role:** Autentikasi untuk Admin dan Pelanggan, validasi data, hashing password (`password_hash`), dan logout aman.
* **Role-Based Access Control (Middleware Sederhana):** Proteksi halaman admin agar tidak dapat diakses sembarangan oleh pelanggan/tamu.
* **Manajemen Profil & Session:** Pengaturan profil pengguna, ubah password, penanganan timeout session, dan fitur *remember me*.

### 2. Modul Manajemen Katalog & Inventaris (Admin)
* **CRUD Data Kategori & Sub-Kategori:** Kategori buku (Fiksi, Non-Fiksi, Akademik) dan kategori ATK (Alat Lukis, Kertas & Buku Tulis, Pen, dll.).
* **CRUD Data Produk (Buku & ATK):**
  * Atribut Buku: Judul, Penulis, Penerbit, Nomor ISBN, Sinopsis, Harga, Stok, dan Gambar Sampul.
  * Atribut ATK: Nama Produk, Merek, Spesifikasi, Harga, Stok, dan Foto Produk.
* **CRUD Data Supplier & Riwayat Restok:** Pencatatan distributor/penerbit dan log penambahan stok barang.

### 3. Modul Etalase & Eksplorasi Produk (Pelanggan)
* **Halaman Beranda (Landing Page) & Katalog Dinamis:** Tampilan buku terlaris (*best seller*), rilisan terbaru, dan detail spesifikasi produk.
* **Pencarian Pintar & Multi-Filter:** Pencarian instan (judul/penulis/ISBN/merek), filter rentang harga, filter kategori, status ketersediaan stok, dan sorting (termurah, termahal, terlaris).
* **Wishlist & Ulasan Pembeli:** Simpan produk favorit ke daftar keinginan dan fitur review/rating bintang setelah bertransaksi.

### 4. Modul Keranjang & Transaksi Penjualan
* **Sistem Shopping Cart (Keranjang Belanja):** Tambah barang, update kuantitas dinamis, pengecekan batas stok otomatis, dan kalkulasi subtotal.
* **Proses Checkout & Pembayaran:** Input alamat pengiriman, pemilihan metode pembayaran/kurir, dan upload bukti transfer.
* **Pelacakan Status & Cetak Invoice:** Pelacakan pesanan (*Menunggu Konfirmasi*, *Diproses*, *Dikirim*, *Selesai*) dan cetak nota/invoice resmi.

### 5. Modul Dashboard Analitik & Laporan Penjualan (Admin)
* **Dashboard Statistik:** Ringkasan total pendapatan, total pesanan, grafik penjualan bulanan, serta alarm indikator stok yang menipis.
* **Laporan Transaksi & Ekspor:** Filter laporan berdasarkan rentang tanggal dan tombol cetak/ekspor untuk pembukuan.

---

## 📁 Struktur Folder Proyek

```text
projek_web/
├── assets/            
│   ├── css/
│   │   ├── style.css                      
│   ├── js/
│   │   ├── main.js               
│   │   └── cart.js               
│   └── uploads/                  
│       ├── produk/              
│       ├── bukti_bayar/         
│       └── profil/               
├── config/                       
│   ├── database.php             
│   └── session.php               
├── includes/                     
│   ├── header.php                
│   ├── footer.php               
│   ├── navbar.php                
│   ├── sidebar_admin.php        
│   └── alerts.php                
├── auth/                         
│   ├── login.php
│   ├── register.php
│   └── logout.php
├── admin/                        
│   ├── index.php                 
│   ├── kategori/                 
│   ├── produk/                   
│   ├── supplier/                              
│   ├── pesanan/                  
│   └── laporan/                  
├── customer/                     
│   ├── profil.php                
│   ├── wishlist.php              
│   ├── riwayat_pesanan.php       
│   └── cetak_invoice.php         
├── database/                     
│   └── pustakaku.sql             
├── index.php                     
├── katalog.php                   
├── detail_produk.php             
├── keranjang.php                 
├── checkout.php                  
└── README.MD                    
```

---

