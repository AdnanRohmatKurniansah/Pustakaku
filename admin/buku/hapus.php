<?php
    require_once __DIR__ . '/../../config/database.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id <= 0) {
        $_SESSION['error'] = "Id buku tidak valid";
        header("Location: index.php");
        exit;
    }

    $query_buku = mysqli_query($conn, "SELECT gambar FROM buku WHERE id = '$id' LIMIT 1");
    if ($query_buku && mysqli_num_rows($query_buku) > 0) {
        $buku = mysqli_fetch_assoc($query_buku);
        $gambar = $buku['gambar'];

        if (!empty($gambar) && file_exists('../../assets/uploads/buku/' . $gambar)) {
            unlink('../../assets/uploads/buku/' . $gambar);
        }

        $delete = mysqli_query($conn, "DELETE FROM buku WHERE id = '$id'");
        if ($delete) {
            $_SESSION['success'] = "Buku berhasil dihapus";
        } else {
            $_SESSION['error'] = "Gagal menghapus buku: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = "Data buku tidak ditemukan";
    }

    header("Location: index.php");
    exit;
?>
