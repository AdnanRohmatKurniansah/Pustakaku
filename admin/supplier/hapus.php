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

    $stmt = mysqli_prepare($conn,"DELETE FROM supplier WHERE id = ?");

    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        if (mysqli_stmt_affected_rows($stmt) > 0) {
            $_SESSION['success'] = "Supplier berhasil dihapus!";
        } else {
            $_SESSION['error'] = "Supplier tidak ditemukan!";
        }
    } else {
        $_SESSION['error'] = "Gagal menghapus supplier!";
    }

    mysqli_stmt_close($stmt);

    header("Location: index.php");
    exit;
?>