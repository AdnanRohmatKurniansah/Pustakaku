CREATE DATABASE IF NOT EXISTS `pustakaku` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE `pustakaku`;

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_lengkap` VARCHAR(100) NOT NULL,
  `email` VARCHAR(100) NOT NULL UNIQUE,
  `password` VARCHAR(255) NOT NULL,
  `role` ENUM('admin', 'pelanggan') NOT NULL DEFAULT 'pelanggan',
  `no_telepon` VARCHAR(20) NULL,
  `alamat_lengkap` TEXT NULL,
  `foto_profil` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `kategori` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_kategori` VARCHAR(80) NOT NULL,
  `deskripsi` VARCHAR(255) NULL,
  `gambar` VARCHAR(255) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `supplier` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nama_supplier` VARCHAR(120) NOT NULL,
  `kontak_person` VARCHAR(80) NULL,
  `telepon` VARCHAR(25) NOT NULL,
  `email` VARCHAR(100) NULL,
  `alamat` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `buku` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `kategori_id` INT NOT NULL,
  `judul` VARCHAR(200) NOT NULL,
  `penulis` VARCHAR(100) NOT NULL,
  `penerbit` VARCHAR(100) NOT NULL,
  `tahun_terbit` INT NULL,
  `isbn` VARCHAR(30) NULL,
  `deskripsi` TEXT NULL,
  `harga` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `stok` INT NOT NULL DEFAULT 0,
  `berat_gram` INT NOT NULL DEFAULT 200,
  `gambar` VARCHAR(255) NOT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_buku_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `restok_log` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `buku_id` INT NOT NULL,
  `supplier_id` INT NULL,
  `jumlah_masuk` INT NOT NULL,
  `biaya_satuan` DECIMAL(12, 2) DEFAULT 0.00,
  `tanggal_restok` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `catatan` VARCHAR(255) NULL,
  CONSTRAINT `fk_restok_buku` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_restok_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `keranjang` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `buku_id` INT NOT NULL,
  `jumlah` INT UNSIGNED NOT NULL DEFAULT 1,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `user_buku_unique` (`user_id`, `buku_id`),
  CONSTRAINT `fk_keranjang_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_keranjang_buku` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `ulasan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `buku_id` INT NOT NULL,
  `rating` TINYINT NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
  `komentar` TEXT NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT `fk_ulasan_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ulasan_buku` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `pesanan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `nomor_pesanan` VARCHAR(50) NOT NULL UNIQUE,
  `user_id` INT NOT NULL,
  `voucher_id` INT NULL,
  `total_buku` DECIMAL(12, 2) NOT NULL,
  `potongan_diskon` DECIMAL(12, 2) DEFAULT 0.00,
  `ongkos_kirim` DECIMAL(12, 2) NOT NULL DEFAULT 0.00,
  `total_bayar` DECIMAL(12, 2) NOT NULL,
  `status_pesanan` ENUM('menunggu_pembayaran', 'menunggu_verifikasi', 'diproses', 'dikirim', 'selesai', 'dibatalkan') DEFAULT 'menunggu_pembayaran',
  `alamat_pengiriman` TEXT NOT NULL,
  `metode_pembayaran` VARCHAR(50) NOT NULL,
  `bukti_pembayaran` VARCHAR(255) NULL,
  `nomor_resi` VARCHAR(80) NULL,
  `catatan_pembeli` VARCHAR(255) NULL,
  `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT `fk_pesanan_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS `detail_pesanan` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `pesanan_id` INT NOT NULL,
  `buku_id` INT NOT NULL,
  `harga_satuan` DECIMAL(12, 2) NOT NULL,
  `jumlah` INT NOT NULL,
  `subtotal` DECIMAL(12, 2) NOT NULL,
  CONSTRAINT `fk_detail_pesanan` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_detail_buku` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON DELETE RESTRICT
) ENGINE=InnoDB;
