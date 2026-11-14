<?php
    session_start();

    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/cart/proses_keranjang.php';

    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = 'Silakan masuk ke akun Anda terlebih dahulu.';
        header('Location: auth/login.php');
        exit;
    }

    if (($_SESSION['role'] ?? '') === 'admin') {
        $_SESSION['error'] = 'Akun admin tidak dapat melakukan pesanan.';
        header('Location: admin/index.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header('Location: keranjang.php');
        exit;
    }

    $user_id = (int) $_SESSION['user_id'];

    $nama_penerima = trim($_POST['nama_penerima'] ?? '');
    $no_telepon = trim($_POST['no_telepon'] ?? '');
    $alamat_pengiriman = trim($_POST['alamat_pengiriman'] ?? '');
    $metode_pembayaran = trim($_POST['metode_pembayaran'] ?? '');
    $opsi_pengiriman = trim($_POST['opsi_pengiriman'] ?? 'reguler');
    $catatan_pembeli = trim($_POST['catatan_pembeli'] ?? '');
    $simpan_ke_profil = !empty($_POST['simpan_ke_profil']);

    if (empty($nama_penerima) || empty($no_telepon) || empty($alamat_pengiriman) || empty($metode_pembayaran)) {
        $_SESSION['error'] = 'Nama penerima, nomor telepon, alamat pengiriman, dan metode pembayaran wajib diisi!';
        header('Location: checkout.php');
        exit;
    }

    if ($simpan_ke_profil) {
        $telp_user_esc = mysqli_real_escape_string($conn, $no_telepon);
        $alamat_user_esc = mysqli_real_escape_string($conn, $alamat_pengiriman);
        mysqli_query($conn, "UPDATE users SET no_telepon = '$telp_user_esc', alamat_lengkap = '$alamat_user_esc', updated_at = NOW() WHERE id = '$user_id'");
    }

    $keranjang = cart_items($conn);
    $all_items = $keranjang['items'];

    if (empty($all_items)) {
        $_SESSION['error'] = 'Keranjang belanja Anda kosong.';
        header('Location: keranjang.php');
        exit;
    }

    $items = [];
    if (!empty($_SESSION['checkout_buku_ids'])) {
        foreach ($all_items as $item) {
            if (in_array((int) $item['id'], $_SESSION['checkout_buku_ids'], true)) {
                $items[] = $item;
            }
        }
    }

    if (empty($items)) {
        $items = $all_items;
    }

    foreach ($items as $item) {
        $buku_id = (int) $item['id'];
        $qty = (int) $item['qty'];
        $q_stok = mysqli_query($conn, "SELECT judul, stok FROM buku WHERE id = '$buku_id'");
        $buku_row = $q_stok ? mysqli_fetch_assoc($q_stok) : null;

        if (!$buku_row) {
            $_SESSION['error'] = 'Salah satu buku yang Anda pesan tidak ditemukan.';
            header('Location: checkout.php');
            exit;
        }

        if ($qty > (int) $buku_row['stok']) {
            $_SESSION['error'] = 'Stok untuk buku "' . $buku_row['judul'] . '" tidak mencukupi (sisa: ' . $buku_row['stok'] . '). Silakan kurangi jumlah di keranjang.';
            header('Location: keranjang.php');
            exit;
        }
    }

    $total_normal = 0.0;
    $total_buku = 0.0;

    foreach ($items as $it) {
        $total_normal += $it['harga'] * $it['qty'];
        $total_buku += $it['satuan'] * $it['qty'];
    }

    $potongan_diskon = max(0.0, $total_normal - $total_buku);

    $dapat_gratis_ongkir = ($total_buku >= 150000);
    $potongan_ongkir = $dapat_gratis_ongkir ? 15000.0 : 0.0;

    if ($opsi_pengiriman === 'express') {
        $ongkos_kirim = max(0.0, 25000.0 - $potongan_ongkir);
    } else {
        $ongkos_kirim = max(0.0, 15000.0 - $potongan_ongkir);
    }

    $total_bayar = $total_buku + $ongkos_kirim;

    $nomor_pesanan = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
    $alamat_pengiriman_lengkap = $nama_penerima . " (" . $no_telepon . ")\n" . $alamat_pengiriman;

    $nomor_pesanan_esc = mysqli_real_escape_string($conn, $nomor_pesanan);
    $alamat_esc = mysqli_real_escape_string($conn, $alamat_pengiriman_lengkap);
    $metode_esc = mysqli_real_escape_string($conn, $metode_pembayaran);
    $catatan_esc = mysqli_real_escape_string($conn, $catatan_pembeli);

    $q_pesanan = "INSERT INTO pesanan (nomor_pesanan, user_id, voucher_id, total_buku, potongan_diskon, ongkos_kirim, total_bayar, status_pesanan, alamat_pengiriman, metode_pembayaran, catatan_pembeli, created_at, updated_at) VALUES ('$nomor_pesanan_esc', '$user_id', NULL, '$total_buku', '$potongan_diskon', '$ongkos_kirim', '$total_bayar', 'diproses', '$alamat_esc', '$metode_esc', '$catatan_esc', NOW(), NOW())";

    $insert_pesanan = mysqli_query($conn, $q_pesanan);

    if (!$insert_pesanan) {
        $_SESSION['error'] = 'Terjadi kesalahan saat memproses pesanan Anda: ' . mysqli_error($conn);
        header('Location: checkout.php');
        exit;
    }

    $pesanan_id = mysqli_insert_id($conn);

    foreach ($items as $item) {
        $buku_id = (int) $item['id'];
        $satuan = (float) $item['satuan'];
        $qty = (int) $item['qty'];
        $subtotal = $satuan * $qty;

        mysqli_query($conn, "INSERT INTO detail_pesanan (pesanan_id, buku_id, harga_satuan, jumlah, subtotal) VALUES ('$pesanan_id', '$buku_id', '$satuan', '$qty', '$subtotal')");
        mysqli_query($conn, "UPDATE buku SET stok = GREATEST(0, stok - $qty), updated_at = NOW() WHERE id = '$buku_id'");
        mysqli_query($conn, "DELETE FROM keranjang WHERE user_id = '$user_id' AND buku_id = '$buku_id'");
    }

    unset($_SESSION['checkout_buku_ids']);

    $_SESSION['success'] = 'Pembayaran berhasil! Pesanan ' . $nomor_pesanan . ' telah lunas dan sedang diproses oleh penjual.';
    header('Location: pesanan_sukses.php?id=' . $pesanan_id);
    exit;

