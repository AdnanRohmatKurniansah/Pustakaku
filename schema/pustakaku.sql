CREATE DATABASE IF NOT EXISTS `pustakaku`
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `pustakaku`;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS `detail_pesanan`;
DROP TABLE IF EXISTS `pesanan`;
DROP TABLE IF EXISTS `ulasan`;
DROP TABLE IF EXISTS `keranjang`;
DROP TABLE IF EXISTS `restok_log`;
DROP TABLE IF EXISTS `buku`;
DROP TABLE IF EXISTS `supplier`;
DROP TABLE IF EXISTS `kategori`;
DROP TABLE IF EXISTS `users`;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','pelanggan') NOT NULL DEFAULT 'pelanggan',
  `no_telepon` varchar(20) DEFAULT NULL,
  `alamat_lengkap` text DEFAULT NULL,
  `foto_profil` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `users` (`id`, `nama_lengkap`, `email`, `password`, `role`, `no_telepon`, `alamat_lengkap`, `foto_profil`, `created_at`, `updated_at`) VALUES
(1, 'Administrator PustakaKu', 'admin@pustakaku.com', '0192023a7bbd73250516f069df18b500', 'admin', '089898981000', 'Jl. Kaliurang KM 9, Sleman, Daerah Istimewa Yogyakarta 55581', 'user_1_1794622924.jpeg', '2026-10-27 23:40:50', '2026-11-14 09:22:04'),
(2, 'Budi Santoso', 'budi@gmail.com', '$2y$12$NXWKA21PIHaKEzUYM2JHG.2chNcN/joePgs3beugdfgSdZ6iat4U.', 'pelanggan', '081234567890', 'Jl. Malioboro No. 12, Sosromenduran, Gedong Tengen, Kota Yogyakarta 55271', NULL, '2026-11-01 10:00:00', '2026-11-01 10:00:00'),
(3, 'Adnan Rohmat Kurniansah', 'atnandimas@gmail.com', '$2y$10$47ZZJvLKz1nFCu/qOR23Keg3jenCm2aqzz.8p7fok/Lawiow51js.', 'pelanggan', '081298765432', 'Jl. Sudirman No. 45, RT 02 / RW 05, Menteng, Jakarta Pusat 10310', NULL, '2026-11-10 11:08:50', '2026-11-15 00:19:53');

CREATE TABLE IF NOT EXISTS `kategori` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(80) NOT NULL,
  `deskripsi` varchar(255) DEFAULT NULL,
  `gambar` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `kategori` (`id`, `nama_kategori`, `deskripsi`, `gambar`, `created_at`, `updated_at`) VALUES (1,'Fiksi & Sastra','Kumpulan novel sastra, roman, misteri, dan fiksi populer Indonesia & dunia.','cat_fiksi.jpg','2026-11-05 13:07:52','2026-11-05 13:07:52'),(2,'Non-Fiksi & Sains','Buku sains, sejarah, biografi, dan literatur pengetahuan umum terlengkap.','cat_nonfiksi.jpg','2026-11-05 13:07:52','2026-11-05 13:07:52'),(3,'Pengembangan Diri','Panduan motivasi, psikologi praktis, kebiasaan baik, dan self-growth terbaik.','cat_selfdev.jpg','2026-11-05 13:07:52','2026-11-05 13:07:52'),(4,'Anak & Edukasi','Buku cerita bergambar anak, ensiklopedia edukasi, dan kurikulum sekolah.','cat_anak.jpg','2026-11-05 13:07:52','2026-11-05 13:07:52'),(5,'Komik & Manga','Manga Jepang terjemahan resmi, webtoon populer, dan komik grafis nusantara.','cat_manga.jpg','2026-10-29 12:31:00','2026-11-05 13:07:52'),(6,'Bisnis & Ekonomi','Buku strategi bisnis modern, manajemen, literasi finansial, dan investasi.','cat_bisnis.jpg','2026-11-05 13:07:52','2026-11-05 13:07:52'),(7,'Seni & Desain','Buku inspirasi visual, teknik menggambar seni rupa, dan desain kreatif.','cat_seni.jpg','2026-11-05 13:07:52','2026-11-05 13:07:52'),(8,'Alat Tulis & ATK','Perlengkapan jurnal, notebook estetik, highlighter pastel, dan brush pen premium.','cat_atk.jpg','2026-11-05 13:07:52','2026-11-05 13:07:52');

CREATE TABLE IF NOT EXISTS `supplier` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_supplier` varchar(120) NOT NULL,
  `telepon` varchar(25) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `alamat` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `supplier` (`id`, `nama_supplier`, `telepon`, `email`, `alamat`, `created_at`, `updated_at`) VALUES (1,'PT Gramedia Pustaka Utama','021-53650110','kontak@gramediapustaka.id','Gedung Kompas Gramedia, Jl. Palmerah Selatan No. 22-28, Jakarta Pusat','2026-11-02 21:22:00','2026-11-05 13:09:01'),(2,'PT Mizan Pustaka','022-7834310','cs@mizan.com','Jl. Cinambo No. 135, Cisaranten Wetan, Kota Bandung, Jawa Barat','2026-11-05 13:09:01','2026-11-05 13:09:01'),(3,'Penerbit Erlangga Group','021-8717085','layanan@erlangga.co.id','Jl. H. Baping Raya No. 100, Ciracas, Jakarta Timur','2026-11-05 13:09:01','2026-11-05 13:09:01'),(4,'Bentang Pustaka Media','0274-517093','redaksi@bentangpustaka.com','Jl. Pandega Marta No. D-19, Caturtunggal, Depok, Sleman, D.I. Yogyakarta','2026-11-05 13:09:01','2026-11-05 13:09:01'),(5,'PT Faber-Castell International','021-56968000','info@fabercastell.co.id','Sentra Niaga Puri Indah Blok T1 No. 12, Kembangan, Jakarta Barat','2026-11-05 13:09:01','2026-11-05 13:09:01'),(6,'Elex Media Komputindo','021-53650111','redaksi@elexmedia.id','Gedung Kompas Gramedia Lt. 2, Jl. Palmerah Selatan, Jakarta Pusat','2026-11-05 13:09:01','2026-11-05 13:09:01');

CREATE TABLE IF NOT EXISTS `buku` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `kategori_id` int(11) NOT NULL,
  `judul` varchar(200) NOT NULL,
  `penulis` varchar(100) NOT NULL,
  `penerbit` varchar(100) NOT NULL,
  `tahun_terbit` int(11) DEFAULT NULL,
  `isbn` varchar(30) DEFAULT NULL,
  `deskripsi` text DEFAULT NULL,
  `harga` decimal(12,2) NOT NULL DEFAULT 0.00,
  `harga_diskon` decimal(12,2) DEFAULT NULL,
  `stok` int(11) NOT NULL DEFAULT 0,
  `berat_gram` int(11) NOT NULL DEFAULT 200,
  `gambar` varchar(255) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_buku_kategori` (`kategori_id`),
  CONSTRAINT `fk_buku_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `buku` (`id`, `kategori_id`, `judul`, `penulis`, `penerbit`, `tahun_terbit`, `isbn`, `deskripsi`, `harga`, `harga_diskon`, `stok`, `berat_gram`, `gambar`, `created_at`, `updated_at`) VALUES (1,1,'Laut Bercerita','Leila S. Chudori','Kepustakaan Populer Gramedia',2022,'978-602-424-694-5','Novel karya Leila S. Chudori menceritakan tentang persahabatan, cinta, keluarga, dan kehilangan para aktivis mahasiswa tahun 1998 yang diculik dan tak pernah kembali. Menjadi salah satu karya sastra kontemporer paling berpengaruh di Indonesia.',105000.00,84200.00,40,350,'laut_bercerita.jpg','2026-11-05 13:09:01','2026-11-15 01:05:49'),(2,5,'One Piece Vol. 106','Eiichiro Oda','Elex Media Komputindo',2024,'978-623-004-981-2','Petualangan kru Bajak Laut Topi Jerami di Pulau Masa Depan Egghead bersama Dr. Vegapunk semakin memanas menghadapi ancaman Pemerintah Dunia dan Armada Angkatan Laut!',45000.00,NULL,60,180,'one_piece_106.jpg','2026-10-29 13:36:19','2026-11-05 13:09:01'),(3,3,'Filosofi Teras','Henry Manampiring','Penerbit Buku Kompas',2023,'978-602-412-518-9','Penerapan filsafat Stoisisme Yunani-Romawi kuno untuk membangun mental tangguh dan tenang menghadapi kecemasan, overthinking, serta emosi negatif di era modern.',98000.00,78400.00,38,300,'filosofi_teras.jpg','2026-11-05 13:09:01','2026-11-05 17:18:16'),(4,3,'Atomic Habits','James Clear','Gramedia Pustaka Utama',2023,'978-602-063-317-6','Buku panduan revolusioner yang membuktikan bahwa perubahan kecil 1% setiap hari mampu memberikan lompatan hasil luar biasa bagi karier, finansial, dan kesehatan mental Anda.',103750.00,83000.00,45,350,'atomic_habits.jpg','2026-11-05 13:09:01','2026-11-15 08:50:10'),(5,1,'Gadis Kretek','Ratih Kumala','Gramedia Pustaka Utama',2023,'978-602-031-897-4','Sebuah kisah asmara, persaingan bisnis pabrik rokok kretek, dan rahasia keluarga tempo dulu di Jawa yang sarat dengan aroma cengkih dan tradisi nusantara.',95000.00,NULL,25,280,'gadis_kretek.jpg','2026-11-05 13:09:01','2026-11-05 13:09:01'),(6,1,'Cantik Itu Luka','Eka Kurniawan','Gramedia Pustaka Utama',2021,'978-602-031-258-3','Mahakarya realisme magis Indonesia berkisah tentang Dewi Ayu dan pergulatan sejarah epik kota Halimunda melintasi era kolonial, pendudukan, hingga kemerdekaan.',128000.00,NULL,30,450,'cantik_itu_luka.jpg','2026-11-05 13:09:01','2026-11-05 13:09:01'),(7,3,'Berani Tidak Disukai','Ichiro Kishimi & F. Koga','Gramedia Pustaka Utama',2022,'978-602-063-328-2','Dialog filosofis berdasarkan teori psikologi Alfred Adler yang membuka rahasia bagaimana membebaskan diri dari ekspektasi orang lain dan menemukan kebahagiaan sejati.',98000.00,NULL,40,320,'berani_tidak_disukai.jpg','2026-11-05 13:09:01','2026-11-05 13:09:01'),(8,2,'Sapiens: Riwayat Singkat Umat Manusia','Yuval Noah Harari','Kepustakaan Populer Gramedia',2020,'978-602-424-416-3','Eksplorasi memukau tentang perjalanan spesies Homo sapiens dari kera tak berarti di Afrika hingga menjadi penguasa bumi yang menciptakan uang, agama, dan kecerdasan buatan.',145000.00,NULL,34,520,'sapiens.jpg','2026-11-05 13:09:01','2026-11-15 01:02:37'),(9,1,'Hujan','Tere Liye','Gramedia Pustaka Utama',2023,'978-602-032-478-4','Kisah fiksi ilmiah berlatar tahun 2042 tentang persahabatan, cinta pertama, dan teknologi penghapus memori pasca terjadinya bencana alam dahsyat di bumi.',89000.00,NULL,54,320,'hujan.jpg','2026-11-05 13:09:01','2026-11-15 01:02:37'),(10,1,'Bumi Manusia','Pramoedya Ananta Toer','Lentera Dipantara',2020,'978-979-973-123-4','Roman pertama dari Tetralogi Buru yang melegenda, mengisahkan Minke, pemuda pribumi yang tercerahkan di tengah kungkungan diskriminasi kolonial Hindia Belanda.',135000.00,NULL,28,500,'bumi_manusia.jpg','2026-11-05 13:09:01','2026-11-05 13:09:01'),(11,8,'Tombow Dual Brush Pen Set 10 Warna','Tombow Japan','PT Faber-Castell International',2024,'490-199-190-128','Set 10 spidol kuas dua sisi berkualitas tinggi (ujung kuas fleksibel dan ujung fine tip) yang sangat ideal untuk hand lettering, bullet journaling, dan ilustrasi seni.',300000.00,219000.00,18,200,'tombow_brush.jpg','2026-11-05 13:09:01','2026-11-14 11:22:53'),(12,8,'Hardcover Dot Notebook A5 Forest Green','PustakaKu Craft','PustakaKu Stationery',2024,'978-881-209-11','Buku catatan bersampul tebal tahan air bertekstur kulit lembut, berisi 192 halaman kertas 100 GSM dotted grid yang ramah fountain pen tanpa tembus tinta (no bleeding).',81250.00,65000.00,60,250,'hardcover_notebook.jpg','2026-11-05 13:09:01','2026-11-05 17:18:17'),(13,8,'Cotman Watercolor Travel Pocket Set 12','Winsor & Newton','PT Faber-Castell International',2024,'884-902-110-33','Set cat air saku portabel buatan Inggris berisi 12 warna pigmen seniman berkualitas tinggi lengkap dengan kuas saku dan palet pencampur warna terpadu.',230000.00,NULL,14,350,'winsor_watercolor.jpg','2026-11-05 13:09:01','2026-11-15 01:04:20'),(14,8,'Fineliner Drawing Pen Set 0.1 - 0.8','Sakura Japan','PT Faber-Castell International',2024,'084-511-309-88','Pena gambar berujung presisi dengan tinta pigment arsip tahan air dan pudar, andalan ilustrator manga, arsitek, dan desainer.',78000.00,NULL,38,150,'drawing_pen_set.jpg','2026-11-05 13:09:01','2026-11-15 01:02:37'),(15,8,'Highlighter Pastel 6 Warna Aesthetic','Stabilo Boss','PustakaKu Stationery',2024,'400-638-133-21','Highlighter stabilo warna pastel lembut yang estetik, anti kering hingga 4 jam terbuka, dan membuat catatan kuliah atau buku bacaan lebih menarik dan rapi.',42000.00,NULL,80,120,'pastel_highlighter.jpg','2026-11-05 13:09:01','2026-11-05 13:09:01');

CREATE TABLE IF NOT EXISTS `restok_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `buku_id` int(11) NOT NULL,
  `supplier_id` int(11) DEFAULT NULL,
  `jumlah_masuk` int(11) NOT NULL,
  `biaya_satuan` decimal(12,2) DEFAULT 0.00,
  `tanggal_restok` datetime DEFAULT current_timestamp(),
  `catatan` varchar(255) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_restok_buku` (`buku_id`),
  KEY `fk_restok_supplier` (`supplier_id`),
  CONSTRAINT `fk_restok_buku` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_restok_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `restok_log` (`id`, `buku_id`, `supplier_id`, `jumlah_masuk`, `biaya_satuan`, `tanggal_restok`, `catatan`) VALUES (1,2,1,20,30000.00,'2026-11-02 15:30:00','Restok batch awal dari Penerbit Rusdi Wijaya'),(2,1,1,50,60000.00,'2026-11-05 13:09:01','Penerimaan stok buku baru Laut Bercerita dari Gramedia Pustaka Utama'),(3,3,1,40,55000.00,'2026-11-05 13:09:01','Restok novel Filosofi Teras edisi cetak ulang ke-45'),(4,4,1,50,62000.00,'2026-11-05 13:09:01','Pengiriman batch Atomic Habits best seller'),(5,11,5,25,160000.00,'2026-11-05 13:09:01','Restok perlengkapan alat tulis Tombow Brush Pen dari Faber-Castell'),(6,12,5,80,40000.00,'2026-11-05 13:09:01','Batch baru Hardcover Dot Notebook edisi Forest Green');

CREATE TABLE IF NOT EXISTS `keranjang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `buku_id` int(11) NOT NULL,
  `jumlah` int(10) unsigned NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_buku_unique` (`user_id`,`buku_id`),
  KEY `fk_keranjang_buku` (`buku_id`),
  CONSTRAINT `fk_keranjang_buku` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_keranjang_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=18 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `keranjang` (`id`, `user_id`, `buku_id`, `jumlah`, `created_at`) VALUES
(1, 3, 3, 1, '2026-11-15 09:30:00'),
(2, 3, 15, 2, '2026-11-15 09:35:00'),
(3, 2, 1, 1, '2026-11-15 10:00:00');

CREATE TABLE IF NOT EXISTS `ulasan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `buku_id` int(11) NOT NULL,
  `rating` tinyint(4) NOT NULL CHECK (`rating` between 1 and 5),
  `komentar` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fk_ulasan_user` (`user_id`),
  KEY `fk_ulasan_buku` (`buku_id`),
  CONSTRAINT `fk_ulasan_buku` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ulasan_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `ulasan` (`id`, `user_id`, `buku_id`, `rating`, `komentar`, `created_at`) VALUES
(1, 2, 1, 5, 'Buku Laut Bercerita luar biasa bagus dan menyentuh hati. Kualitas cetak dan pengemasan sangat aman!', '2026-11-14 12:00:00'),
(2, 3, 4, 4, 'Buku Atomic Habits sangat membantu dalam membangun kebiasaan produktif harian.', '2026-11-15 09:01:13');

CREATE TABLE IF NOT EXISTS `pesanan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nomor_pesanan` varchar(50) NOT NULL,
  `user_id` int(11) NOT NULL,
  `voucher_id` int(11) DEFAULT NULL,
  `total_buku` decimal(12,2) NOT NULL,
  `potongan_diskon` decimal(12,2) DEFAULT 0.00,
  `ongkos_kirim` decimal(12,2) NOT NULL DEFAULT 0.00,
  `total_bayar` decimal(12,2) NOT NULL,
  `status_pesanan` enum('menunggu_pembayaran','menunggu_verifikasi','diproses','dikirim','selesai','dibatalkan') DEFAULT 'menunggu_pembayaran',
  `alamat_pengiriman` text NOT NULL,
  `metode_pembayaran` varchar(50) NOT NULL,
  `bukti_pembayaran` varchar(255) DEFAULT NULL,
  `nomor_resi` varchar(80) DEFAULT NULL,
  `catatan_pembeli` varchar(255) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_pesanan` (`nomor_pesanan`),
  KEY `fk_pesanan_user` (`user_id`),
  CONSTRAINT `fk_pesanan_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `pesanan` (`id`, `nomor_pesanan`, `user_id`, `voucher_id`, `total_buku`, `potongan_diskon`, `ongkos_kirim`, `total_bayar`, `status_pesanan`, `alamat_pengiriman`, `metode_pembayaran`, `bukti_pembayaran`, `nomor_resi`, `catatan_pembeli`, `created_at`, `updated_at`) VALUES
(2, 'ORD-20261114-DDB44', 3, NULL, 606400.00, 203600.00, 0.00, 606400.00, 'selesai', 'Budi Santoso (081298765432)
Jl. Sudirman No. 45, RT 02 / RW 05, Menteng, Jakarta Pusat 10310', 'Transfer Bank BCA', NULL, 'JNE9918237462', 'Tolong bungkus rapi', '2026-11-14 11:22:53', '2026-11-14 11:24:24'),
(3, 'ORD-20261114-A8A76', 3, NULL, 83000.00, 20750.00, 15000.00, 98000.00, 'menunggu_pembayaran', 'Adnan Rohmat (081298765432)
Jl. Sudirman No. 45, RT 02 / RW 05, Menteng, Jakarta Pusat 10310', 'COD (Bayar di Tempat)', NULL, NULL, '', '2026-11-14 11:28:51', '2026-11-14 11:28:51'),
(4, 'ORD-20261114-96231', 3, NULL, 161000.00, 20750.00, 0.00, 161000.00, 'menunggu_pembayaran', 'Adnan Rohmat (081298765432)
Jl. Sudirman No. 45, RT 02 / RW 05, Menteng, Jakarta Pusat 10310', 'Transfer Bank BCA', NULL, NULL, 'Packing kardus', '2026-11-14 11:43:50', '2026-11-14 11:43:50'),
(5, 'ORD-20261114-72BF2', 3, NULL, 166000.00, 41500.00, 0.00, 166000.00, 'menunggu_pembayaran', 'Rusdi (081298765432)
Jl. Sudirman No. 45, RT 02 / RW 05, Menteng, Jakarta Pusat 10310', 'E-Wallet (GoPay / OVO / Dana / QRIS)', NULL, NULL, '', '2026-11-15 00:19:53', '2026-11-15 00:19:53'),
(6, 'ORD-20261114-9AB0F', 3, NULL, 480400.00, 41600.00, 10000.00, 490400.00, 'diproses', 'Budi Santoso (081298765432)
Jl. Thamrin No. 12, Jakarta Pusat', 'Transfer Bank Mandiri', NULL, NULL, 'Kirim cepat ya', '2026-11-15 01:02:37', '2026-11-15 01:02:37'),
(7, 'ORD-20261114-79E14', 3, NULL, 230000.00, 0.00, 0.00, 230000.00, 'diproses', 'Adnan Rohmat (081298765432)
Jl. Sudirman No. 45, RT 02 / RW 05, Menteng, Jakarta Pusat 10310', 'Transfer Bank BCA', NULL, NULL, '', '2026-11-15 01:04:20', '2026-11-15 01:04:20'),
(8, 'ORD-20261114-B96D6', 3, NULL, 84200.00, 20800.00, 15000.00, 99200.00, 'diproses', 'Adnan Rohmat (081298765432)
Jl. Sudirman No. 45, RT 02 / RW 05, Menteng, Jakarta Pusat 10310', 'Transfer Bank BCA', NULL, NULL, '', '2026-11-15 01:05:49', '2026-11-15 01:05:49'),
(9, 'ORD-20261115-67915', 3, NULL, 83000.00, 20750.00, 15000.00, 98000.00, 'selesai', 'Adnan Rohmat (081298765432)
Jl. Sudirman No. 45, RT 02 / RW 05, Menteng, Jakarta Pusat 10310', 'Transfer Bank BCA', NULL, 'SOC1029384756', '', '2026-11-15 08:50:10', '2026-11-15 08:58:57');

CREATE TABLE IF NOT EXISTS `detail_pesanan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pesanan_id` int(11) NOT NULL,
  `buku_id` int(11) NOT NULL,
  `harga_satuan` decimal(12,2) NOT NULL,
  `jumlah` int(11) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_detail_pesanan` (`pesanan_id`),
  KEY `fk_detail_buku` (`buku_id`),
  CONSTRAINT `fk_detail_buku` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`),
  CONSTRAINT `fk_detail_pesanan` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `detail_pesanan` (`id`, `pesanan_id`, `buku_id`, `harga_satuan`, `jumlah`, `subtotal`) VALUES (2,2,1,84200.00,2,168400.00),(3,2,11,219000.00,2,438000.00),(4,3,4,83000.00,1,83000.00),(5,4,4,83000.00,1,83000.00),(6,4,14,78000.00,1,78000.00),(7,5,4,83000.00,2,166000.00),(8,6,1,84200.00,2,168400.00),(9,6,8,145000.00,1,145000.00),(10,6,9,89000.00,1,89000.00),(11,6,14,78000.00,1,78000.00),(12,7,13,230000.00,1,230000.00),(13,8,1,84200.00,1,84200.00),(14,9,4,83000.00,1,83000.00);
