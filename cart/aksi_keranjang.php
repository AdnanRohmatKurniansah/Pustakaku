<?php
    session_start();

    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/proses_keranjang.php';

    header('Content-Type: application/json; charset=utf-8');

    function response($ok, $message, $conn, $code = 200) {
        http_response_code($code);
        echo json_encode([
            'ok' => $ok,
            'message' => $message,
            'count' => cart_count($conn)
        ]);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        response(false, 'Metode tidak diizinkan.', $conn, 405);
    }

    if (!isset($_SESSION['user_id'])) {
        response(false, 'Silakan masuk ke akun Anda terlebih dahulu.', $conn, 401);
    }

    if (($_SESSION['role'] ?? '') === 'admin') {
        response(false, 'Akun admin tidak dapat menggunakan keranjang.', $conn, 403);
    }

    $aksi = $_POST['aksi'] ?? '';
    $id = (int) ($_POST['id'] ?? 0);

    switch ($aksi) {
        case 'tambah':
        case 'ubah_jumlah':
            $qty = (int) ($_POST['qty'] ?? 1);
            if ($id <= 0 || $qty < 1) {
                response(false, 'Data buku tidak valid.', $conn, 400);
            }

            $cart = cart_get($conn);
            if (!isset($cart[$id])) {
                response(false, 'Buku tidak ditemukan dalam keranjang.', $conn, 404);
            }

            $res = mysqli_query($conn, "SELECT stok FROM buku WHERE id = '$id'");
            $buku = $res ? mysqli_fetch_assoc($res) : null;
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

        case 'hapus':
            if ($id <= 0) {
                response(false, 'ID buku tidak valid.', $conn, 400);
            }
            cart_remove($conn, $id);
            response(true, 'Buku berhasil dihapus dari keranjang.', $conn);

        case 'hapus_terpilih':
            $ids = $_POST['ids'] ?? [];
            if (!is_array($ids) || empty($ids)) {
                response(false, 'Tidak ada buku yang dipilih.', $conn, 400);
            }
            foreach ($ids as $del_id) {
                cart_remove($conn, (int) $del_id);
            }
            response(true, 'Buku yang dipilih berhasil dihapus.', $conn);

        case 'kosongkan':
            cart_clear($conn);
            response(true, 'Keranjang berhasil dikosongkan.', $conn);

        default:
            response(false, 'Aksi tidak dikenal.', $conn, 400);
    }
