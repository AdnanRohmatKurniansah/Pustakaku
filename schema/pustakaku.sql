-- MySQL dump 10.13  Distrib 8.0.46, for Linux (x86_64)
--
-- Host: mysql-1a761855-atnandimas-7e84.a.aivencloud.com    Database: pustakaku
-- ------------------------------------------------------
-- Server version	8.4.11

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;
SET @MYSQLDUMP_TEMP_LOG_BIN = @@SESSION.SQL_LOG_BIN;
SET @@SESSION.SQL_LOG_BIN= 0;

--
-- GTID state at the beginning of the backup 
--

SET @@GLOBAL.GTID_PURGED=/*!80000 '+'*/ '60983310-d8dc-11f1-96b9-bead61dd9ee4:1-37,
b657e4d2-e294-11f1-a619-7ee893201bfa:1-112';

--
-- Table structure for table `buku`
--

DROP TABLE IF EXISTS `buku`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `buku` (
  `id` int NOT NULL AUTO_INCREMENT,
  `kategori_id` int NOT NULL,
  `judul` varchar(200) COLLATE utf8mb4_general_ci NOT NULL,
  `penulis` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `penerbit` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `tahun_terbit` int DEFAULT NULL,
  `isbn` varchar(30) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `deskripsi` text COLLATE utf8mb4_general_ci,
  `harga` decimal(12,2) NOT NULL DEFAULT '0.00',
  `harga_diskon` decimal(12,2) DEFAULT NULL,
  `stok` int NOT NULL DEFAULT '0',
  `berat_gram` int NOT NULL DEFAULT '200',
  `gambar` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_buku_kategori` (`kategori_id`),
  CONSTRAINT `fk_buku_kategori` FOREIGN KEY (`kategori_id`) REFERENCES `kategori` (`id`) ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `buku`
--

LOCK TABLES `buku` WRITE;
/*!40000 ALTER TABLE `buku` DISABLE KEYS */;
INSERT INTO `buku` VALUES (1,1,'Laut Bercerita','Leila S. Chudori','Kepustakaan Populer Gramedia',2022,'978-602-424-694-5','Novel karya Leila S. Chudori menceritakan tentang persahabatan, cinta, keluarga, dan kehilangan para aktivis mahasiswa tahun 1998 yang diculik dan tak pernah kembali. Menjadi salah satu karya sastra kontemporer paling berpengaruh di Indonesia.',105000.00,84200.00,40,350,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794988911/pustakaku/buku/jvqvmump1iakhrme5ox1.jpg','2026-11-05 13:09:01','2026-11-18 08:01:53'),(2,5,'One Piece Vol. 106','Eiichiro Oda','Elex Media Komputindo',2024,'978-623-004-981-2','Petualangan kru Bajak Laut Topi Jerami di Pulau Masa Depan Egghead bersama Dr. Vegapunk semakin memanas menghadapi ancaman Pemerintah Dunia dan Armada Angkatan Laut!',45000.00,NULL,60,180,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794987931/pustakaku/buku/eqjhbloot9bp5pmlspjb.jpg','2026-10-29 13:36:19','2026-11-18 07:45:33'),(3,3,'Filosofi Teras','Henry Manampiring','Penerbit Buku Kompas',2023,'978-602-412-518-9','Penerapan filsafat Stoisisme Yunani-Romawi kuno untuk membangun mental tangguh dan tenang menghadapi kecemasan, overthinking, serta emosi negatif di era modern.',98000.00,78400.00,38,300,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794988913/pustakaku/buku/fedzbtmsgadgefkhb9pp.jpg','2026-11-05 13:09:01','2026-11-18 08:01:54'),(4,3,'Atomic Habits','James Clear','Gramedia Pustaka Utama',2023,'978-602-063-317-6','Buku panduan revolusioner yang membuktikan bahwa perubahan kecil 1% setiap hari mampu memberikan lompatan hasil luar biasa bagi karier, finansial, dan kesehatan mental Anda.',103750.00,83000.00,44,350,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794988915/pustakaku/buku/iqgeg11w5zixguljgboj.jpg','2026-11-05 13:09:01','2026-11-18 08:01:56'),(5,1,'Gadis Kretek','Ratih Kumala','Gramedia Pustaka Utama',2023,'978-602-031-897-4','Sebuah kisah asmara, persaingan bisnis pabrik rokok kretek, dan rahasia keluarga tempo dulu di Jawa yang sarat dengan aroma cengkih dan tradisi nusantara.',95000.00,NULL,25,280,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794987933/pustakaku/buku/sacvt858pozwe4yk1jmr.jpg','2026-11-05 13:09:01','2026-11-18 07:45:35'),(6,1,'Cantik Itu Luka','Eka Kurniawan','Gramedia Pustaka Utama',2021,'978-602-031-258-3','Mahakarya realisme magis Indonesia berkisah tentang Dewi Ayu dan pergulatan sejarah epik kota Halimunda melintasi era kolonial, pendudukan, hingga kemerdekaan.',128000.00,NULL,30,450,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794987936/pustakaku/buku/nvr6nvrs3cijo6oajnzu.jpg','2026-11-05 13:09:01','2026-11-18 07:45:37'),(7,3,'Berani Tidak Disukai','Ichiro Kishimi & F. Koga','Gramedia Pustaka Utama',2022,'978-602-063-328-2','Dialog filosofis berdasarkan teori psikologi Alfred Adler yang membuka rahasia bagaimana membebaskan diri dari ekspektasi orang lain dan menemukan kebahagiaan sejati.',98000.00,NULL,40,320,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794987937/pustakaku/buku/krjpzul7jcmlzormlwoh.jpg','2026-11-05 13:09:01','2026-11-18 07:45:38'),(8,2,'Sapiens: Riwayat Singkat Umat Manusia','Yuval Noah Harari','Kepustakaan Populer Gramedia',2020,'978-602-424-416-3','Eksplorasi memukau tentang perjalanan spesies Homo sapiens dari kera tak berarti di Afrika hingga menjadi penguasa bumi yang menciptakan uang, agama, dan kecerdasan buatan.',145000.00,NULL,34,520,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794987938/pustakaku/buku/ij6fkkhniuom3pfri9kx.jpg','2026-11-05 13:09:01','2026-11-18 07:45:39'),(9,1,'Hujan','Tere Liye','Gramedia Pustaka Utama',2023,'978-602-032-478-4','Kisah fiksi ilmiah berlatar tahun 2042 tentang persahabatan, cinta pertama, dan teknologi penghapus memori pasca terjadinya bencana alam dahsyat di bumi.',89000.00,NULL,54,320,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794987939/pustakaku/buku/gzupqqnrwur2xvilgmyy.jpg','2026-11-05 13:09:01','2026-11-18 07:45:40'),(10,1,'Bumi Manusia','Pramoedya Ananta Toer','Lentera Dipantara',2020,'978-979-973-123-4','Roman pertama dari Tetralogi Buru yang melegenda, mengisahkan Minke, pemuda pribumi yang tercerahkan di tengah kungkungan diskriminasi kolonial Hindia Belanda.',135000.00,NULL,28,500,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794987941/pustakaku/buku/xzw91befq13dmcfin4ma.jpg','2026-11-05 13:09:01','2026-11-18 07:45:41'),(11,8,'Tombow Dual Brush Pen Set 10 Warna','Tombow Japan','PT Faber-Castell International',2024,'490-199-190-128','Set 10 spidol kuas dua sisi berkualitas tinggi (ujung kuas fleksibel dan ujung fine tip) yang sangat ideal untuk hand lettering, bullet journaling, dan ilustrasi seni.',300000.00,219000.00,17,200,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794987942/pustakaku/buku/zkvxsrzngqkrtdvm1v7s.jpg','2026-11-05 13:09:01','2026-11-18 07:45:42'),(12,8,'Hardcover Dot Notebook A5 Forest Green','PustakaKu Craft','PustakaKu Stationery',2024,'978-881-209-11','Buku catatan bersampul tebal tahan air bertekstur kulit lembut, berisi 192 halaman kertas 100 GSM dotted grid yang ramah fountain pen tanpa tembus tinta (no bleeding).',81250.00,65000.00,60,250,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794987943/pustakaku/buku/udgbe37mrn7k9lyszmzx.jpg','2026-11-05 13:09:01','2026-11-18 07:45:43'),(13,8,'Cotman Watercolor Travel Pocket Set 12','Winsor & Newton','PT Faber-Castell International',2024,'884-902-110-33','Set cat air saku portabel buatan Inggris berisi 12 warna pigmen seniman berkualitas tinggi lengkap dengan kuas saku dan palet pencampur warna terpadu.',230000.00,NULL,14,350,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794987944/pustakaku/buku/h8mrfbh4cajmpaz5ezqr.jpg','2026-11-05 13:09:01','2026-11-18 07:45:45'),(14,8,'Fineliner Drawing Pen Set 0.1 - 0.8','Sakura Japan','PT Faber-Castell International',2024,'084-511-309-88','Pena gambar berujung presisi dengan tinta pigment arsip tahan air dan pudar, andalan ilustrator manga, arsitek, dan desainer.',78000.00,NULL,38,150,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794987945/pustakaku/buku/ilmhy21vhsunojkadayu.jpg','2026-11-05 13:09:01','2026-11-18 07:45:46'),(15,8,'Highlighter Pastel 6 Warna Aesthetic','Stabilo Boss','PustakaKu Stationery',2024,'400-638-133-21','Highlighter stabilo warna pastel lembut yang estetik, anti kering hingga 4 jam terbuka, dan membuat catatan kuliah atau buku bacaan lebih menarik dan rapi.',42000.00,NULL,80,120,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794987946/pustakaku/buku/o8qxt7jaucaw4iatgrec.jpg','2026-11-05 13:09:01','2026-11-18 07:45:48');
/*!40000 ALTER TABLE `buku` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `buku_gambar`
--

DROP TABLE IF EXISTS `buku_gambar`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `buku_gambar` (
  `id` int NOT NULL AUTO_INCREMENT,
  `buku_id` int NOT NULL,
  `url_gambar` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `public_id` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `urutan` int NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_buku_gambar_buku` (`buku_id`),
  CONSTRAINT `fk_gambar_buku` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `buku_gambar`
--

LOCK TABLES `buku_gambar` WRITE;
/*!40000 ALTER TABLE `buku_gambar` DISABLE KEYS */;
INSERT INTO `buku_gambar` VALUES (1,1,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794988911/pustakaku/buku/jvqvmump1iakhrme5ox1.jpg',NULL,1,0,'2026-11-17 20:20:06'),(2,2,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794987931/pustakaku/buku/eqjhbloot9bp5pmlspjb.jpg','pustakaku/buku/eqjhbloot9bp5pmlspjb',1,0,'2026-11-17 20:20:06'),(3,3,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794988913/pustakaku/buku/fedzbtmsgadgefkhb9pp.jpg',NULL,1,0,'2026-11-17 20:20:06'),(4,4,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794988915/pustakaku/buku/iqgeg11w5zixguljgboj.jpg',NULL,1,0,'2026-11-17 20:20:06'),(5,5,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794987933/pustakaku/buku/sacvt858pozwe4yk1jmr.jpg','pustakaku/buku/sacvt858pozwe4yk1jmr',1,0,'2026-11-17 20:20:06'),(6,6,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794987936/pustakaku/buku/nvr6nvrs3cijo6oajnzu.jpg','pustakaku/buku/nvr6nvrs3cijo6oajnzu',1,0,'2026-11-17 20:20:06'),(7,7,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794987937/pustakaku/buku/krjpzul7jcmlzormlwoh.jpg','pustakaku/buku/krjpzul7jcmlzormlwoh',1,0,'2026-11-17 20:20:06'),(8,8,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794987938/pustakaku/buku/ij6fkkhniuom3pfri9kx.jpg','pustakaku/buku/ij6fkkhniuom3pfri9kx',1,0,'2026-11-17 20:20:06'),(9,9,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794987939/pustakaku/buku/gzupqqnrwur2xvilgmyy.jpg','pustakaku/buku/gzupqqnrwur2xvilgmyy',1,0,'2026-11-17 20:20:06'),(10,10,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794987941/pustakaku/buku/xzw91befq13dmcfin4ma.jpg','pustakaku/buku/xzw91befq13dmcfin4ma',1,0,'2026-11-17 20:20:06'),(11,11,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794987942/pustakaku/buku/zkvxsrzngqkrtdvm1v7s.jpg','pustakaku/buku/zkvxsrzngqkrtdvm1v7s',1,0,'2026-11-17 20:20:06'),(12,12,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794987943/pustakaku/buku/udgbe37mrn7k9lyszmzx.jpg','pustakaku/buku/udgbe37mrn7k9lyszmzx',1,0,'2026-11-17 20:20:06'),(13,13,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794987944/pustakaku/buku/h8mrfbh4cajmpaz5ezqr.jpg','pustakaku/buku/h8mrfbh4cajmpaz5ezqr',1,0,'2026-11-17 20:20:06'),(14,14,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794987945/pustakaku/buku/ilmhy21vhsunojkadayu.jpg','pustakaku/buku/ilmhy21vhsunojkadayu',1,0,'2026-11-17 20:20:06'),(15,15,'https://res.cloudinary.com/cxkyw5om/image/upload/v1794987946/pustakaku/buku/o8qxt7jaucaw4iatgrec.jpg','pustakaku/buku/o8qxt7jaucaw4iatgrec',1,0,'2026-11-17 20:20:06'),(16,1,'https://res.cloudinary.com/cxkyw5om/image/upload/v1795242240/pustakaku/buku/buku_1_gallery_1.jpg','pustakaku/buku/buku_1_gallery_1',0,1,'2026-11-21 13:24:05'),(17,2,'https://res.cloudinary.com/cxkyw5om/image/upload/v1795242245/pustakaku/buku/buku_2_gallery_1.jpg','pustakaku/buku/buku_2_gallery_1',0,1,'2026-11-21 13:24:08'),(18,3,'https://res.cloudinary.com/cxkyw5om/image/upload/v1795242248/pustakaku/buku/buku_3_gallery_1.jpg','pustakaku/buku/buku_3_gallery_1',0,1,'2026-11-21 13:24:10'),(19,4,'https://res.cloudinary.com/cxkyw5om/image/upload/v1795242251/pustakaku/buku/buku_4_gallery_1.jpg','pustakaku/buku/buku_4_gallery_1',0,1,'2026-11-21 13:24:11'),(20,5,'https://res.cloudinary.com/cxkyw5om/image/upload/v1795242251/pustakaku/buku/buku_5_gallery_1.jpg','pustakaku/buku/buku_5_gallery_1',0,1,'2026-11-21 13:24:12'),(21,6,'https://res.cloudinary.com/cxkyw5om/image/upload/v1795242253/pustakaku/buku/buku_6_gallery_1.jpg','pustakaku/buku/buku_6_gallery_1',0,1,'2026-11-21 13:24:13'),(22,7,'https://res.cloudinary.com/cxkyw5om/image/upload/v1795242254/pustakaku/buku/buku_7_gallery_1.jpg','pustakaku/buku/buku_7_gallery_1',0,1,'2026-11-21 13:24:15'),(23,8,'https://res.cloudinary.com/cxkyw5om/image/upload/v1795242255/pustakaku/buku/buku_8_gallery_1.jpg','pustakaku/buku/buku_8_gallery_1',0,1,'2026-11-21 13:24:16'),(24,9,'https://res.cloudinary.com/cxkyw5om/image/upload/v1795242257/pustakaku/buku/buku_9_gallery_1.jpg','pustakaku/buku/buku_9_gallery_1',0,1,'2026-11-21 13:24:17'),(25,10,'https://res.cloudinary.com/cxkyw5om/image/upload/v1795242258/pustakaku/buku/buku_10_gallery_1.jpg','pustakaku/buku/buku_10_gallery_1',0,1,'2026-11-21 13:24:19'),(26,12,'https://res.cloudinary.com/cxkyw5om/image/upload/v1795242260/pustakaku/buku/buku_12_gallery_1.jpg','pustakaku/buku/buku_12_gallery_1',0,1,'2026-11-21 13:24:21'),(27,14,'https://res.cloudinary.com/cxkyw5om/image/upload/v1795242265/pustakaku/buku/buku_14_gallery_1.jpg','pustakaku/buku/buku_14_gallery_1',0,1,'2026-11-21 13:24:25'),(28,15,'https://res.cloudinary.com/cxkyw5om/image/upload/v1795242266/pustakaku/buku/buku_15_gallery_1.jpg','pustakaku/buku/buku_15_gallery_1',0,1,'2026-11-21 13:24:27'),(29,11,'https://res.cloudinary.com/cxkyw5om/image/upload/v1795242353/pustakaku/buku/buku_11_gallery_1.jpg','pustakaku/buku/buku_11_gallery_1',0,1,'2026-11-21 13:25:54'),(30,13,'https://res.cloudinary.com/cxkyw5om/image/upload/v1795242355/pustakaku/buku/buku_13_gallery_1.jpg','pustakaku/buku/buku_13_gallery_1',0,1,'2026-11-21 13:25:56');
/*!40000 ALTER TABLE `buku_gambar` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `detail_pesanan`
--

DROP TABLE IF EXISTS `detail_pesanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `detail_pesanan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pesanan_id` int NOT NULL,
  `buku_id` int NOT NULL,
  `harga_satuan` decimal(12,2) NOT NULL,
  `jumlah` int NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_detail_pesanan` (`pesanan_id`),
  KEY `fk_detail_buku` (`buku_id`),
  CONSTRAINT `fk_detail_buku` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`),
  CONSTRAINT `fk_detail_pesanan` FOREIGN KEY (`pesanan_id`) REFERENCES `pesanan` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `detail_pesanan`
--

LOCK TABLES `detail_pesanan` WRITE;
/*!40000 ALTER TABLE `detail_pesanan` DISABLE KEYS */;
INSERT INTO `detail_pesanan` VALUES (2,2,1,84200.00,2,168400.00),(3,2,11,219000.00,2,438000.00),(4,3,4,83000.00,1,83000.00),(5,4,4,83000.00,1,83000.00),(6,4,14,78000.00,1,78000.00),(7,5,4,83000.00,2,166000.00),(8,6,1,84200.00,2,168400.00),(9,6,8,145000.00,1,145000.00),(10,6,9,89000.00,1,89000.00),(11,6,14,78000.00,1,78000.00),(12,7,13,230000.00,1,230000.00),(13,8,1,84200.00,1,84200.00),(14,9,4,83000.00,1,83000.00),(15,10,4,83000.00,1,83000.00),(16,11,11,219000.00,1,219000.00);
/*!40000 ALTER TABLE `detail_pesanan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kategori`
--

DROP TABLE IF EXISTS `kategori`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kategori` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_kategori` varchar(80) COLLATE utf8mb4_general_ci NOT NULL,
  `deskripsi` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `gambar` varchar(255) COLLATE utf8mb4_general_ci NOT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kategori`
--

LOCK TABLES `kategori` WRITE;
/*!40000 ALTER TABLE `kategori` DISABLE KEYS */;
INSERT INTO `kategori` VALUES (1,'Fiksi & Sastra','Kumpulan novel sastra, roman, misteri, dan fiksi populer Indonesia & dunia.','https://res.cloudinary.com/cxkyw5om/image/upload/v1795242225/pustakaku/kategori/kategori_fiksi_sastra.jpg','2026-11-05 13:07:52','2026-11-21 13:23:46'),(2,'Non-Fiksi & Sains','Buku sains, sejarah, biografi, dan literatur pengetahuan umum terlengkap.','https://res.cloudinary.com/cxkyw5om/image/upload/v1795242346/pustakaku/kategori/kategori_nonfiksi_sains.jpg','2026-11-05 13:07:52','2026-11-21 13:25:52'),(3,'Pengembangan Diri','Panduan motivasi, psikologi praktis, kebiasaan baik, dan self-growth terbaik.','https://res.cloudinary.com/cxkyw5om/image/upload/v1795242229/pustakaku/kategori/kategori_pengembangan_diri.jpg','2026-11-05 13:07:52','2026-11-21 13:23:51'),(4,'Anak & Edukasi','Buku cerita bergambar anak, ensiklopedia edukasi, dan kurikulum sekolah.','https://res.cloudinary.com/cxkyw5om/image/upload/v1795242231/pustakaku/kategori/kategori_anak_edukasi.jpg','2026-11-05 13:07:52','2026-11-21 13:23:52'),(5,'Komik & Manga','Manga Jepang terjemahan resmi, webtoon populer, dan komik grafis nusantara.','https://res.cloudinary.com/cxkyw5om/image/upload/v1795242232/pustakaku/kategori/kategori_komik_manga.jpg','2026-10-29 12:31:00','2026-11-21 13:23:54'),(6,'Bisnis & Ekonomi','Buku strategi bisnis modern, manajemen, literasi finansial, dan investasi.','https://res.cloudinary.com/cxkyw5om/image/upload/v1795242235/pustakaku/kategori/kategori_bisnis_ekonomi.jpg','2026-11-05 13:07:52','2026-11-21 13:23:55'),(7,'Seni & Desain','Buku inspirasi visual, teknik menggambar seni rupa, dan desain kreatif.','https://res.cloudinary.com/cxkyw5om/image/upload/v1795242236/pustakaku/kategori/kategori_seni_desain.jpg','2026-11-05 13:07:52','2026-11-21 13:23:58'),(8,'Alat Tulis & ATK','Perlengkapan jurnal, notebook estetik, highlighter pastel, dan brush pen premium.','https://res.cloudinary.com/cxkyw5om/image/upload/v1795242238/pustakaku/kategori/kategori_alat_tulis_atk.jpg','2026-11-05 13:07:52','2026-11-21 13:23:59');
/*!40000 ALTER TABLE `kategori` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `keranjang`
--

DROP TABLE IF EXISTS `keranjang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `keranjang` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `buku_id` int NOT NULL,
  `jumlah` int unsigned NOT NULL DEFAULT '1',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `user_buku_unique` (`user_id`,`buku_id`),
  KEY `fk_keranjang_buku` (`buku_id`),
  CONSTRAINT `fk_keranjang_buku` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_keranjang_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `keranjang`
--

LOCK TABLES `keranjang` WRITE;
/*!40000 ALTER TABLE `keranjang` DISABLE KEYS */;
INSERT INTO `keranjang` VALUES (20,3,9,1,'2026-11-18 07:17:08');
/*!40000 ALTER TABLE `keranjang` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pesanan`
--

DROP TABLE IF EXISTS `pesanan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pesanan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nomor_pesanan` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `user_id` int NOT NULL,
  `alamat_id` int DEFAULT NULL,
  `voucher_id` int DEFAULT NULL,
  `total_buku` decimal(12,2) NOT NULL,
  `potongan_diskon` decimal(12,2) DEFAULT '0.00',
  `ongkos_kirim` decimal(12,2) NOT NULL DEFAULT '0.00',
  `kurir_service` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `total_bayar` decimal(12,2) NOT NULL,
  `status_pesanan` enum('menunggu_pembayaran','menunggu_verifikasi','diproses','dikirim','selesai','dibatalkan') COLLATE utf8mb4_general_ci DEFAULT 'menunggu_pembayaran',
  `alamat_pengiriman` text COLLATE utf8mb4_general_ci NOT NULL,
  `metode_pembayaran` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `snap_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `midtrans_transaction_id` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `midtrans_payment_type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `bukti_pembayaran` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `nomor_resi` varchar(80) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `catatan_pembeli` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `nomor_pesanan` (`nomor_pesanan`),
  KEY `fk_pesanan_user` (`user_id`),
  CONSTRAINT `fk_pesanan_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pesanan`
--

LOCK TABLES `pesanan` WRITE;
/*!40000 ALTER TABLE `pesanan` DISABLE KEYS */;
INSERT INTO `pesanan` VALUES (2,'ORD-20261114-DDB44',3,NULL,NULL,606400.00,203600.00,0.00,NULL,606400.00,'selesai','Budi Santoso (081298765432)\nJl. Sudirman No. 45, RT 02 / RW 05, Menteng, Jakarta Pusat 10310','Transfer Bank BCA',NULL,NULL,NULL,NULL,'JNE9918237462','Tolong bungkus rapi','2026-11-14 11:22:53','2026-11-14 11:24:24'),(3,'ORD-20261114-A8A76',3,NULL,NULL,83000.00,20750.00,15000.00,NULL,98000.00,'menunggu_pembayaran','asdad (081298765432)\nJl. Sudirman No. 45, RT 02 / RW 05, Menteng, Jakarta Pusat 10310','COD (Bayar di Tempat)',NULL,NULL,NULL,NULL,NULL,'','2026-11-14 11:28:51','2026-11-14 11:28:51'),(4,'ORD-20261114-96231',3,NULL,NULL,161000.00,20750.00,0.00,NULL,161000.00,'menunggu_pembayaran','Adnan (081298765432)\nJl. Sudirman No. 45, RT 02 / RW 05, Menteng, Jakarta Pusat 10310','Transfer Bank BCA',NULL,NULL,NULL,NULL,NULL,'Halo','2026-11-14 11:43:50','2026-11-14 11:43:50'),(5,'ORD-20261114-72BF2',3,NULL,NULL,166000.00,41500.00,0.00,NULL,166000.00,'menunggu_pembayaran','Rusdi (081298765432)\nJl. Sudirman No. 45, RT 02 / RW 05, Menteng, Jakarta Pusat 10310','E-Wallet (GoPay / OVO / Dana / QRIS)',NULL,NULL,NULL,NULL,NULL,'','2026-11-15 00:19:53','2026-11-15 00:19:53'),(6,'ORD-20261114-9AB0F',3,NULL,NULL,480400.00,41600.00,10000.00,NULL,490400.00,'diproses','Budi Santoso (081298765432)\nJl. Thamrin No. 12, Jakarta Pusat','Transfer Bank Mandiri',NULL,NULL,NULL,NULL,NULL,'Kirim cepat ya','2026-11-15 01:02:37','2026-11-15 01:02:37'),(7,'ORD-20261114-79E14',3,NULL,NULL,230000.00,0.00,0.00,NULL,230000.00,'diproses','asdad (081298765432)\nJl. Sudirman No. 45, RT 02 / RW 05, Menteng, Jakarta Pusat 10310','Transfer Bank BCA',NULL,NULL,NULL,NULL,NULL,'','2026-11-15 01:04:20','2026-11-15 01:04:20'),(8,'ORD-20261114-B96D6',3,NULL,NULL,84200.00,20800.00,15000.00,NULL,99200.00,'diproses','asdad (081298765432)\nJl. Sudirman No. 45, RT 02 / RW 05, Menteng, Jakarta Pusat 10310','Transfer Bank BCA',NULL,NULL,NULL,NULL,NULL,'','2026-11-15 01:05:49','2026-11-15 01:05:49'),(9,'ORD-20261115-67915',3,NULL,NULL,83000.00,20750.00,15000.00,NULL,98000.00,'selesai','asdad (081298765432)\nJl. Sudirman No. 45, RT 02 / RW 05, Menteng, Jakarta Pusat 10310','Transfer Bank BCA',NULL,NULL,NULL,NULL,'jadasdada','','2026-11-15 08:50:10','2026-11-15 08:58:57'),(10,'ORD-20261117-243AC',3,2,NULL,83000.00,20750.00,20000.00,'POS - Next Day',103000.00,'menunggu_pembayaran','asdad (081298765432)\nJl. Sudirman No. 45, RT 02 / RW 05, Menteng, Jakarta Pusat 10310, Yogyakarta, DI Yogyakarta 55271','Midtrans','c1f904d1-6ef4-4799-b7e3-6f9a65621b86',NULL,NULL,NULL,NULL,'','2026-11-17 18:12:17','2026-11-17 18:12:17'),(11,'ORD-20261118-5383D',3,2,NULL,219000.00,81000.00,11000.00,'POS - Kilat Khusus',230000.00,'menunggu_pembayaran','asdad (081298765432)\nJl. Sudirman No. 45, RT 02 / RW 05, Menteng, Jakarta Pusat 10310, Yogyakarta, DI Yogyakarta 55271','Midtrans','7e29b637-7cee-4ba9-919b-7fa8131e16a0',NULL,NULL,NULL,NULL,'','2026-11-18 07:14:14','2026-11-18 07:14:14');
/*!40000 ALTER TABLE `pesanan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `restok_log`
--

DROP TABLE IF EXISTS `restok_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `restok_log` (
  `id` int NOT NULL AUTO_INCREMENT,
  `buku_id` int NOT NULL,
  `supplier_id` int DEFAULT NULL,
  `jumlah_masuk` int NOT NULL,
  `biaya_satuan` decimal(12,2) DEFAULT '0.00',
  `tanggal_restok` datetime DEFAULT CURRENT_TIMESTAMP,
  `catatan` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_restok_buku` (`buku_id`),
  KEY `fk_restok_supplier` (`supplier_id`),
  CONSTRAINT `fk_restok_buku` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_restok_supplier` FOREIGN KEY (`supplier_id`) REFERENCES `supplier` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `restok_log`
--

LOCK TABLES `restok_log` WRITE;
/*!40000 ALTER TABLE `restok_log` DISABLE KEYS */;
INSERT INTO `restok_log` VALUES (1,2,1,20,30000.00,'2026-11-02 15:30:00','Restok batch awal dari Penerbit Rusdi Wijaya'),(2,1,1,50,60000.00,'2026-11-05 13:09:01','Penerimaan stok buku baru Laut Bercerita dari Gramedia Pustaka Utama'),(3,3,1,40,55000.00,'2026-11-05 13:09:01','Restok novel Filosofi Teras edisi cetak ulang ke-45'),(4,4,1,50,62000.00,'2026-11-05 13:09:01','Pengiriman batch Atomic Habits best seller'),(5,11,5,25,160000.00,'2026-11-05 13:09:01','Restok perlengkapan alat tulis Tombow Brush Pen dari Faber-Castell'),(6,12,5,80,40000.00,'2026-11-05 13:09:01','Batch baru Hardcover Dot Notebook edisi Forest Green');
/*!40000 ALTER TABLE `restok_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `supplier`
--

DROP TABLE IF EXISTS `supplier`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `supplier` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_supplier` varchar(120) COLLATE utf8mb4_general_ci NOT NULL,
  `telepon` varchar(25) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `supplier`
--

LOCK TABLES `supplier` WRITE;
/*!40000 ALTER TABLE `supplier` DISABLE KEYS */;
INSERT INTO `supplier` VALUES (1,'PT Gramedia Pustaka Utama','021-53650110','kontak@gramediapustaka.id','Gedung Kompas Gramedia, Jl. Palmerah Selatan No. 22-28, Jakarta Pusat','2026-11-02 21:22:00','2026-11-05 13:09:01'),(2,'PT Mizan Pustaka','022-7834310','cs@mizan.com','Jl. Cinambo No. 135, Cisaranten Wetan, Kota Bandung, Jawa Barat','2026-11-05 13:09:01','2026-11-05 13:09:01'),(3,'Penerbit Erlangga Group','021-8717085','layanan@erlangga.co.id','Jl. H. Baping Raya No. 100, Ciracas, Jakarta Timur','2026-11-05 13:09:01','2026-11-05 13:09:01'),(4,'Bentang Pustaka Media','0274-517093','redaksi@bentangpustaka.com','Jl. Pandega Marta No. D-19, Caturtunggal, Depok, Sleman, D.I. Yogyakarta','2026-11-05 13:09:01','2026-11-05 13:09:01'),(5,'PT Faber-Castell International','021-56968000','info@fabercastell.co.id','Sentra Niaga Puri Indah Blok T1 No. 12, Kembangan, Jakarta Barat','2026-11-05 13:09:01','2026-11-05 13:09:01'),(6,'Elex Media Komputindo','021-53650111','redaksi@elexmedia.id','Gedung Kompas Gramedia Lt. 2, Jl. Palmerah Selatan, Jakarta Pusat','2026-11-05 13:09:01','2026-11-05 13:09:01');
/*!40000 ALTER TABLE `supplier` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `ulasan`
--

DROP TABLE IF EXISTS `ulasan`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `ulasan` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `buku_id` int NOT NULL,
  `rating` tinyint NOT NULL,
  `komentar` text COLLATE utf8mb4_general_ci,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_ulasan_user` (`user_id`),
  KEY `fk_ulasan_buku` (`buku_id`),
  CONSTRAINT `fk_ulasan_buku` FOREIGN KEY (`buku_id`) REFERENCES `buku` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ulasan_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `ulasan_chk_1` CHECK ((`rating` between 1 and 5))
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `ulasan`
--

LOCK TABLES `ulasan` WRITE;
/*!40000 ALTER TABLE `ulasan` DISABLE KEYS */;
INSERT INTO `ulasan` VALUES (2,3,4,4,'Baik Sudah Bagus','2026-11-15 09:01:13');
/*!40000 ALTER TABLE `ulasan` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_alamat`
--

DROP TABLE IF EXISTS `user_alamat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_alamat` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `label_alamat` varchar(50) COLLATE utf8mb4_general_ci NOT NULL,
  `nama_penerima` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `no_telepon` varchar(20) COLLATE utf8mb4_general_ci NOT NULL,
  `provinsi_id` int NOT NULL,
  `provinsi_nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `kota_id` int NOT NULL,
  `kota_nama` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `kecamatan` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `kode_pos` varchar(10) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `alamat_lengkap` text COLLATE utf8mb4_general_ci NOT NULL,
  `is_primary` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_user_alamat_user` (`user_id`),
  CONSTRAINT `fk_alamat_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_alamat`
--

LOCK TABLES `user_alamat` WRITE;
/*!40000 ALTER TABLE `user_alamat` DISABLE KEYS */;
INSERT INTO `user_alamat` VALUES (1,1,'Alamat Utama','Administrator PustakaKu','089898981',5,'DI Yogyakarta',501,'Yogyakarta','Gedong Tengen','55271','Nambangan',1,'2026-11-17 20:20:06','2026-11-17 20:20:06'),(2,3,'Alamat Utama','asdad','081298765432',5,'DI Yogyakarta',501,'Yogyakarta','Gedong Tengen','55271','Jl. Sudirman No. 45, RT 02 / RW 05, Menteng, Jakarta Pusat 10310',1,'2026-11-17 20:20:06','2026-11-17 20:20:06');
/*!40000 ALTER TABLE `user_alamat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
CREATE TABLE `sessions` (
  `id` varchar(128) NOT NULL,
  `access` int unsigned NOT NULL,
  `data` mediumtext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `access` (`access`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nama_lengkap` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_general_ci NOT NULL,
  `google_id` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `provider` enum('email','google') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'email',
  `password` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `role` enum('admin','pelanggan') COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'pelanggan',
  `no_telepon` varchar(20) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `foto_profil` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reset_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL,
  `reset_token_expires` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Administrator PustakaKu','admin@pustakaku.com',NULL,'0192023a7bbd73250516f069df18b500','admin','089898981','Nambangan','https://res.cloudinary.com/cxkyw5om/image/upload/v1794988917/pustakaku/user/zzzs8kzuhanem7wmc7co.jpg',NULL,NULL,'2026-10-27 23:40:50','2026-11-18 08:01:57'),(3,'asdad','atnandimas@gmail.com',NULL,'$2y$10$47ZZJvLKz1nFCu/qOR23Keg3jenCm2aqzz.8p7fok/Lawiow51js.','pelanggan','081298765432','Jl. Sudirman No. 45, RT 02 / RW 05, Menteng, Jakarta Pusat 10310',NULL,NULL,NULL,'2026-11-10 11:08:50','2026-11-15 00:19:53');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
SET @@SESSION.SQL_LOG_BIN = @MYSQLDUMP_TEMP_LOG_BIN;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-11-18 15:02:46
