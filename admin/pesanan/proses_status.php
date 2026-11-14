<?php
    require_once __DIR__ . '/../../config/database.php';

    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: index.php");
        exit;
    }

    $pesanan_id = (int) ($_POST['pesanan_id'] ?? 0);
    $status_baru = trim($_POST['status_pesanan'] ?? '');
    $nomor_resi = trim($_POST['nomor_resi'] ?? '');

    $allowed_status = ['menunggu_pembayaran', 'menunggu_verifikasi', 'diproses', 'dikirim', 'selesai', 'dibatalkan'];

    if ($pesanan_id <= 0 || !in_array($status_baru, $allowed_status, true)) {
        $_SESSION['error'] = 'Data status pesanan tidak valid.';
        header("Location: index.php");
        exit;
    }

    $q_pesanan = mysqli_query($conn, "SELECT * FROM pesanan WHERE id = '$pesanan_id'");
    $pesanan = $q_pesanan ? mysqli_fetch_assoc($q_pesanan) : null;

    if (!$pesanan) {
        $_SESSION['error'] = 'Pesanan tidak ditemukan.';
        header("Location: index.php");
        exit;
    }

    $status_lama = $pesanan['status_pesanan'];

    if ($status_baru === 'dibatalkan' && $status_lama !== 'dibatalkan') {
        $q_detail = mysqli_query($conn, "SELECT buku_id, jumlah FROM detail_pesanan WHERE pesanan_id = '$pesanan_id'");
        if ($q_detail) {
            while ($d = mysqli_fetch_assoc($q_detail)) {
                $b_id = (int) $d['buku_id'];
                $b_qty = (int) $d['jumlah'];
                mysqli_query($conn, "UPDATE buku SET stok = stok + $b_qty, updated_at = NOW() WHERE id = '$b_id'");
            }
        }
    }

    if ($status_lama === 'dibatalkan' && $status_baru !== 'dibatalkan') {
        $q_detail = mysqli_query($conn, "SELECT buku_id, jumlah FROM detail_pesanan WHERE pesanan_id = '$pesanan_id'");
        if ($q_detail) {
            while ($d = mysqli_fetch_assoc($q_detail)) {
                $b_id = (int) $d['buku_id'];
                $b_qty = (int) $d['jumlah'];
                mysqli_query($conn, "UPDATE buku SET stok = GREATEST(0, stok - $b_qty), updated_at = NOW() WHERE id = '$b_id'");
            }
        }
    }

    $status_esc = mysqli_real_escape_string($conn, $status_baru);
    $resi_esc = mysqli_real_escape_string($conn, $nomor_resi);

    $update = mysqli_query($conn, "UPDATE pesanan SET status_pesanan = '$status_esc', nomor_resi = '$resi_esc', updated_at = NOW() WHERE id = '$pesanan_id'");

    if ($update) {
        $_SESSION['success'] = 'Status pesanan ' . $pesanan['nomor_pesanan'] . ' berhasil diperbarui!';
    } else {
        $_SESSION['error'] = 'Gagal memperbarui status: ' . mysqli_error($conn);
    }

    header("Location: detail.php?id=" . $pesanan_id);
    exit;
