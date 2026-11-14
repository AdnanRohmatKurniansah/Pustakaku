<?php
    require_once __DIR__ . '/../../config/database.php';

    session_start();
    if(!isset($_SESSION['user_id'])|| !isset($_SESSION['role'])|| $_SESSION['role'] !== 'admin'){
        header("location: ../../auth/login.php");
        exit();
    };


    if($_SERVER['REQUEST_METHOD'] === 'POST'){
        $nama_supplier = trim($_POST['nama_supplier'] ?? '');
        $alamat = trim($_POST['alamat'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $telepon = trim($_POST['telepon'] ?? '');

        
        if (empty($nama_supplier) || empty($alamat) || empty($telepon) || empty($email)) {
            $_SESSION['error'] = "Nama supplier, alamat, nomor telepon, dan email wajib diisi!";
            header("Location: tambah.php");
            exit;
        }
        

        $nama_supplier = mysqli_real_escape_string($conn, $nama_supplier);
        $alamat = mysqli_real_escape_string($conn, $alamat);
        $email = mysqli_real_escape_string($conn, $email);
        $telepon = mysqli_real_escape_string($conn, $telepon);

        $query = mysqli_query($conn, "INSERT INTO supplier (nama_supplier, email, alamat, telepon) VALUES ('$nama_supplier',  '$email', '$alamat', '$telepon')");
        if ($query) {
            $_SESSION['success'] = "supplier berhasil ditambahkan";
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['error'] = "Gagal menambahkan supplier: " . mysqli_error($conn);
            header("Location: tambah.php");
            exit;
        }
    } else {
        header("Location: index.php");
        exit;
    }
?>