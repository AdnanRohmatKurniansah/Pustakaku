<?php
session_start();

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/proses_keranjang.php';

header('Content-Type: application/json; charset=utf-8');

function response($ok, $message, $conn, $code = 200) {
    http_response_code($code);

    echo json_encode([
        'ok'      => $ok,
        'message' => $message,
        'count'   => cart_count($conn)
    ]);

    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response(false, 'Metode tidak diizinkan.', $conn, 405);
}

$aksi = $_POST['aksi'] ?? '';
$id   = (int) ($_POST['id'] ?? 0);

switch ($aksi) {

    // TAMBAH ATAU UBAH JUMLAH
    case 'tambah':
    case 'ubah_jumlah':

        $qty = (int) ($_POST['qty'] ?? 1);

        if ($id <= 0 || $qty < 1) {
            response(false, 'Data buku tidak valid.', $conn, 400);
        }

        // Pastikan buku memang ada dalam keranjang
        $cart = cart_get($conn);

        if (!isset($cart[$id])) {
            response(false, 'Buku tidak ditemukan dalam keranjang.', $conn, 404);
        }

        // Periksa stok terbaru
        $stmt = mysqli_prepare($conn, "SELECT stok FROM buku WHERE id = ?");
        mysqli_stmt_bind_param($stmt, 'i', $id);
        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);
        $buku = mysqli_fetch_assoc($result);

        if (!$buku || (int)$buku['stok'] <= 0) {
            cart_remove($conn, $id);
            response(false, 'Stok buku sudah habis.', $conn);
        }

        $stok = (int)$buku['stok'];

        if ($qty > $stok) {
            response(false, 'Jumlah melebihi stok tersedia. Stok tersisa: ' . $stok, $conn);
        }

        cart_set($conn, $id, $qty);

        response(true, 'Jumlah buku berhasil diperbarui.', $conn);

    // HAPUS SATU BUKU
    case 'hapus':

        if ($id <= 0) {
            response(false, 'ID buku tidak valid.', $conn, 400);
        }

        cart_remove($conn, $id);

        response(true, 'Buku berhasil dihapus dari keranjang.', $conn);

    // HAPUS BUKU TERPILIH
    case 'hapus_terpilih':

        $ids = $_POST['ids'] ?? [];

        if (!is_array($ids) || empty($ids)) {
            response(false, 'Pilih minimal satu buku.', $conn);
        }

        $cart = cart_get($conn);

        foreach ($ids as $buku_id) {
            $buku_id = (int)$buku_id;

            if ($buku_id > 0 && isset($cart[$buku_id])) {
                cart_remove($conn, $buku_id);
            }
        }

        response(true, 'Buku terpilih berhasil dihapus.', $conn);

    default:
        response(false, 'Aksi tidak dikenal.', $conn, 400);
}