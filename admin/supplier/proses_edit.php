<?php
    include('../../config/database.php');

    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $nama_supplier = trim($_POST['nama_supplier'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $telepon = trim($_POST['telepon'] ?? '');
        $email = trim($_POST['email'] ?? '');
        

        if ($id <= 0 || empty($nama_supplier) || empty($alamat)|| empty($email) || empty($telepon)) {
            $_SESSION['error'] = "Data tidak lengkap";
            header("Location: edit.php?id=" . $id);
            exit;
        }

        $check_query = mysqli_query($conn, "SELECT * FROM supplier WHERE id = '$id' LIMIT 1");
        if (!$check_query || mysqli_num_rows($check_query) === 0) {
            $_SESSION['error'] = "supplier tidak ditemukan";
            header("Location: index.php");
            exit;
        }

        $supplier_lama = mysqli_fetch_assoc($check_query);

        $nama_supplier = mysqli_real_escape_string($conn, $nama_supplier);
        $alamat = mysqli_real_escape_string($conn, $alamat);
        $telepon = mysqli_real_escape_string($conn, $telepon);
        $email = mysqli_real_escape_string($conn, $email);

        $query = mysqli_query($conn, "UPDATE supplier SET nama_supplier = '$nama_supplier', alamat = '$alamat', telepon = '$telepon', email = '$email' WHERE id = '$id'");

        if ($query) {
            $_SESSION['success'] = "supplier berhasil diperbarui";
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['error'] = "Gagal memperbarui supplier: " . mysqli_error($conn);
            header("Location: edit.php?id=" . $id);
            exit;
        }
    } else {
        header("Location: index.php");
        exit;
    }
?>