<?php
    include('../../config/database.php');

    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $buku_id = isset($_POST['buku_id']) ? intval($_POST['buku_id']) : 0;
        $supplier_id = !empty($_POST['supplier_id']) ? intval($_POST['supplier_id']) : null;
        $jumlah_masuk = isset($_POST['jumlah_masuk']) ? intval($_POST['jumlah_masuk']) : 0;
        $biaya_satuan = isset($_POST['biaya_satuan']) ? floatval($_POST['biaya_satuan']) : 0;
        $tanggal_restok = !empty($_POST['tanggal_restok']) ? date('Y-m-d H:i:s', strtotime($_POST['tanggal_restok'])) : date('Y-m-d H:i:s');
        $catatan = trim($_POST['catatan'] ?? '');

        if ($buku_id <= 0 || $jumlah_masuk <= 0) {
            $_SESSION['error'] = "Buku dan jumlah masuk wajib diisi dengan benar";
            header("Location: tambah.php");
            exit;
        }

        $check_buku = mysqli_query($conn, "SELECT id, judul FROM buku WHERE id = '$buku_id' LIMIT 1");
        if (!$check_buku || mysqli_num_rows($check_buku) === 0) {
            $_SESSION['error'] = "Data buku tidak ditemukan";
            header("Location: tambah.php");
            exit;
        }

        $buku = mysqli_fetch_assoc($check_buku);

        $catatan = mysqli_real_escape_string($conn, $catatan);
        $tanggal_restok = mysqli_real_escape_string($conn, $tanggal_restok);
        $supplier_val = $supplier_id !== null ? "'$supplier_id'" : "NULL";

        $insert_query = "INSERT INTO restok_log (buku_id, supplier_id, jumlah_masuk, biaya_satuan, tanggal_restok, catatan) VALUES ('$buku_id', $supplier_val, '$jumlah_masuk', '$biaya_satuan', '$tanggal_restok', '$catatan')";
        
        $insert = mysqli_query($conn, $insert_query);

        if ($insert) {
            mysqli_query($conn, "UPDATE buku SET stok = stok + $jumlah_masuk WHERE id = '$buku_id'");
            $_SESSION['success'] = "Data restok berhasil ditambahkan dan stok buku \"" . htmlspecialchars($buku['judul']) . "\" bertambah " . $jumlah_masuk . " pcs";
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['error'] = "Gagal menyimpan data restok: " . mysqli_error($conn);
            header("Location: tambah.php");
            exit;
        }
    } else {
        header("Location: index.php");
        exit;
    }
?>
