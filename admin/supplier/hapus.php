<?php
    include('../../config/database.php');

    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id <= 0) {
        $_SESSION['error'] = "ID Supplier tidak valid!";
        header("Location: index.php");
        exit;
    }

    $del = mysqli_query($conn, "DELETE FROM supplier WHERE id = '$id'");
    if ($del && mysqli_affected_rows($conn) > 0) {
        $_SESSION['success'] = "Supplier berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Supplier tidak ditemukan atau gagal dihapus!";
    }

    header("Location: index.php");
    exit;
?>
