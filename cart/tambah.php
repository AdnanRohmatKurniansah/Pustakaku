<?php
/**
 * Endpoint AJAX: tambah buku ke keranjang.
 * Dipanggil dari assets/js/cart.js dengan method POST (id, qty).
 */
session_start();
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/proses_keranjang.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'message' => 'Metode tidak diizinkan.']);
    exit;
}

$id  = (int) ($_POST['id'] ?? 0);
$qty = (int) ($_POST['qty'] ?? 1);

$hasil = cart_add($conn, $id, $qty);
$hasil['count'] = $cart_count = isset($conn) ? cart_count($conn) : 0;;

echo json_encode($hasil);