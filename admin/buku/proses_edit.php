<?php
    include('../../config/database.php');

    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $kategori_id = isset($_POST['kategori_id']) ? intval($_POST['kategori_id']) : 0;
        $judul = trim($_POST['judul'] ?? '');
        $penulis = trim($_POST['penulis'] ?? '');
        $penerbit = trim($_POST['penerbit'] ?? '');
        $tahun_terbit = !empty($_POST['tahun_terbit']) ? intval($_POST['tahun_terbit']) : 'NULL';
        $isbn = trim($_POST['isbn'] ?? '');
        $harga = isset($_POST['harga']) ? floatval($_POST['harga']) : 0;
        $harga_diskon = (!empty($_POST['harga_diskon']) && floatval($_POST['harga_diskon']) > 0) ? floatval($_POST['harga_diskon']) : 'NULL';
        $stok = isset($_POST['stok']) ? intval($_POST['stok']) : 0;
        $berat_gram = isset($_POST['berat_gram']) ? intval($_POST['berat_gram']) : 200;
        $deskripsi = trim($_POST['deskripsi'] ?? '');

        if ($id <= 0 || $kategori_id <= 0 || empty($judul) || empty($penulis) || empty($penerbit) || $harga < 0 || $stok < 0) {
            $_SESSION['error'] = "Mohon lengkapi semua data wajib bertanda bintang (*)!";
            header("Location: edit.php?id=" . $id);
            exit;
        }

        $check_query = mysqli_query($conn, "SELECT * FROM buku WHERE id = '$id' LIMIT 1");
        if (!$check_query || mysqli_num_rows($check_query) === 0) {
            $_SESSION['error'] = "Data buku tidak ditemukan!";
            header("Location: index.php");
            exit;
        }

        $buku_lama = mysqli_fetch_assoc($check_query);
        $gambar_nama = $buku_lama['gambar'];

        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['gambar']['tmp_name'];
            $file_name = $_FILES['gambar']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($file_ext, $allowed_ext)) {
                $gambar_baru = time() . '_' . uniqid() . '.' . $file_ext;
                $target_dir = '../../assets/uploads/buku/';
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                if (move_uploaded_file($file_tmp, $target_dir . $gambar_baru)) {
                    if (!empty($gambar_nama) && file_exists($target_dir . $gambar_nama)) {
                        unlink($target_dir . $gambar_nama);
                    }
                    $gambar_nama = $gambar_baru;
                }
            } else {
                $_SESSION['error'] = "Format sampul harus JPG, JPEG, PNG, atau WEBP!";
                header("Location: edit.php?id=" . $id);
                exit;
            }
        }

        $judul = mysqli_real_escape_string($conn, $judul);
        $penulis = mysqli_real_escape_string($conn, $penulis);
        $penerbit = mysqli_real_escape_string($conn, $penerbit);
        $isbn = mysqli_real_escape_string($conn, $isbn);
        $deskripsi = mysqli_real_escape_string($conn, $deskripsi);
        $gambar_nama = mysqli_real_escape_string($conn, $gambar_nama);

        $query_str = "UPDATE buku SET kategori_id = '$kategori_id', judul = '$judul', penulis = '$penulis', penerbit = '$penerbit', tahun_terbit = $tahun_terbit, isbn = '$isbn', harga = '$harga', harga_diskon = $harga_diskon, stok = '$stok', berat_gram = '$berat_gram', deskripsi = '$deskripsi', gambar = '$gambar_nama' WHERE id = '$id'";
        $update = mysqli_query($conn, $query_str);

        if ($update) {
            $_SESSION['success'] = "Data buku berhasil diperbarui!";
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['error'] = "Gagal memperbarui data buku: " . mysqli_error($conn);
            header("Location: edit.php?id=" . $id);
            exit;
        }
    } else {
        header("Location: index.php");
        exit;
    }
?>
