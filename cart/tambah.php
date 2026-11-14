<?php
    session_start();
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/proses_keranjang.php';

    header('Content-Type: application/json; charset=utf-8');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(['ok' => false, 'message' => 'Metode tidak diizinkan.']);
        exit;
    }

    if (!isset($_SESSION['user_id'])) {
        echo json_encode([
            'ok' => false,
            'login_required' => true,
            'message' => 'Silakan masuk ke akun Anda terlebih dahulu untuk belanja.'
        ]);
        exit;
    }

    if (($_SESSION['role'] ?? '') === 'admin') {
        echo json_encode([
            'ok' => false,
            'message' => 'Akun admin tidak dapat berbelanja atau menambahkan produk ke keranjang.'
        ]);
        exit;
    }

    $id = (int) ($_POST['id'] ?? 0);
    $qty = (int) ($_POST['qty'] ?? 1);

    $hasil = cart_add($conn, $id, $qty);
    $hasil['count'] = isset($conn) ? cart_count($conn) : 0;

    echo json_encode($hasil);
