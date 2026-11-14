<?php
    require_once __DIR__ . '/../../config/database.php';

    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    if ($id <= 0) {
        $_SESSION['error'] = "ID kategori tidak valid!";
        header("Location: index.php");
        exit;
    }

    $check_buku = mysqli_query($conn, "SELECT COUNT(*) AS total FROM buku WHERE kategori_id = '$id'");
    $row_buku = mysqli_fetch_assoc($check_buku);

    if ($row_buku && $row_buku['total'] > 0) {
        $_SESSION['error'] = "Kategori tidak dapat dihapus karena masih digunakan oleh " . $row_buku['total'] . " buku!";
        header("Location: index.php");
        exit;
    }

    $query_kategori = mysqli_query($conn, "SELECT gambar FROM kategori WHERE id = '$id' LIMIT 1");
    if ($query_kategori && mysqli_num_rows($query_kategori) > 0) {
        $kategori = mysqli_fetch_assoc($query_kategori);
        $gambar = $kategori['gambar'];

        if (!empty($gambar) && file_exists('../../assets/uploads/kategori/' . $gambar)) {
            unlink('../../assets/uploads/kategori/' . $gambar);
        }

        $delete = mysqli_query($conn, "DELETE FROM kategori WHERE id = '$id'");
        if ($delete) {
            $_SESSION['success'] = "Kategori berhasil dihapus!";
        } else {
            $_SESSION['error'] = "Gagal menghapus kategori: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = "Data kategori tidak ditemukan!";
    }

    header("Location: index.php");
    exit;
?>
