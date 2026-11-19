<?php
/**
 * PustakaKu - Skrip migrasi untuk melengkapi struktur database yang belum ada.
 *
 * Skrip ini idempoten: aman dijalankan berulang kali. Setiap objek hanya dibuat
 * jika belum ada, sehingga tidak akan menghapus atau menimpa data yang sudah ada.
 *
 * Cara pakai (CLI):
 *     php schema/migrate_missing_tables.php
 *
 * Atau lewat browser:
 *     http://localhost/Pustakaku/schema/migrate_missing_tables.php
 *
 * Yang dilengkapi:
 *   - Tabel  `user_alamat`  (dipakai akun.php, checkout.php, proses_alamat.php, admin/pesanan)
 *   - Tabel  `buku_gambar`  (galeri gambar buku)
 *   - Kolom  `pesanan`: alamat_id, kurir_service, snap_token,
 *                       midtrans_transaction_id, midtrans_payment_type
 *   - Kolom  `users`  : google_id, reset_token, reset_token_expires
 */

require_once __DIR__ . '/../config/database.php';

$is_cli = (php_sapi_name() === 'cli');

if (!$is_cli) {
    header('Content-Type: text/plain; charset=utf-8');
}

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$log = [];
function mig_log(array &$log, string $msg): void
{
    $log[] = $msg;
    echo $msg . PHP_EOL;
}

/** Cek apakah sebuah tabel ada. */
function table_exists(mysqli $conn, string $table): bool
{
    $esc = mysqli_real_escape_string($conn, $table);
    $res = mysqli_query($conn, "SHOW TABLES LIKE '$esc'");
    return $res && mysqli_num_rows($res) > 0;
}

/** Ambil daftar nama kolom pada sebuah tabel. */
function columns_of(mysqli $conn, string $table): array
{
    $cols = [];
    $esc  = mysqli_real_escape_string($conn, $table);
    $res  = mysqli_query($conn, "SHOW COLUMNS FROM `$esc`");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $cols[$row['Field']] = true;
        }
    }
    return $cols;
}

echo "=== PustakaKu: melengkapi struktur database ===" . PHP_EOL;
echo "Target: " . mysqli_get_host_info($conn) . PHP_EOL;
echo PHP_EOL;

/* ------------------------------------------------------------------ *
 * 1. Tabel `user_alamat`
 * ------------------------------------------------------------------ */
if (!table_exists($conn, 'user_alamat')) {
    mysqli_query($conn, "
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    mig_log($log, "[BUAT] tabel `user_alamat`");
} else {
    mig_log($log, "[ADA]  tabel `user_alamat` sudah tersedia");
}

/* ------------------------------------------------------------------ *
 * 2. Tabel `buku_gambar`
 * ------------------------------------------------------------------ */
if (!table_exists($conn, 'buku_gambar')) {
    mysqli_query($conn, "
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
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci
    ");
    mig_log($log, "[BUAT] tabel `buku_gambar`");
} else {
    mig_log($log, "[ADA]  tabel `buku_gambar` sudah tersedia");
}

/* ------------------------------------------------------------------ *
 * 3. Kolom tambahan pada tabel `pesanan`
 * ------------------------------------------------------------------ */
if (table_exists($conn, 'pesanan')) {
    $pesanan_cols = [
        'alamat_id'                => "ALTER TABLE `pesanan` ADD COLUMN `alamat_id` int DEFAULT NULL",
        'kurir_service'            => "ALTER TABLE `pesanan` ADD COLUMN `kurir_service` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL",
        'snap_token'               => "ALTER TABLE `pesanan` ADD COLUMN `snap_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL",
        'midtrans_transaction_id'  => "ALTER TABLE `pesanan` ADD COLUMN `midtrans_transaction_id` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL",
        'midtrans_payment_type'    => "ALTER TABLE `pesanan` ADD COLUMN `midtrans_payment_type` varchar(50) COLLATE utf8mb4_general_ci DEFAULT NULL",
    ];
    $existing = columns_of($conn, 'pesanan');
    foreach ($pesanan_cols as $col => $sql) {
        if (!isset($existing[$col])) {
            mysqli_query($conn, $sql);
            mig_log($log, "[BUAT] kolom `pesanan`.`$col`");
        } else {
            mig_log($log, "[ADA]  kolom `pesanan`.`$col`");
        }
    }
} else {
    mig_log($log, "[LEWATI] tabel `pesanan` tidak ditemukan (impor schema/pustakaku.sql dahulu)");
}

/* ------------------------------------------------------------------ *
 * 4. Kolom tambahan pada tabel `users`
 * ------------------------------------------------------------------ */
if (table_exists($conn, 'users')) {
    $users_cols = [
        'google_id'            => "ALTER TABLE `users` ADD COLUMN `google_id` varchar(100) COLLATE utf8mb4_general_ci DEFAULT NULL",
        'reset_token'          => "ALTER TABLE `users` ADD COLUMN `reset_token` varchar(255) COLLATE utf8mb4_general_ci DEFAULT NULL",
        'reset_token_expires'  => "ALTER TABLE `users` ADD COLUMN `reset_token_expires` datetime DEFAULT NULL",
    ];
    $existing = columns_of($conn, 'users');
    foreach ($users_cols as $col => $sql) {
        if (!isset($existing[$col])) {
            mysqli_query($conn, $sql);
            mig_log($log, "[BUAT] kolom `users`.`$col`");
        } else {
            mig_log($log, "[ADA]  kolom `users`.`$col`");
        }
    }
} else {
    mig_log($log, "[LEWATI] tabel `users` tidak ditemukan (impor schema/pustakaku.sql dahulu)");
}

echo PHP_EOL . "=== Selesai. Struktur database sudah lengkap. ===" . PHP_EOL;
