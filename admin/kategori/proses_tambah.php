<?php
    include('../../config/database.php');

    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nama_kategori = trim($_POST['nama_kategori'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');

        if (empty($nama_kategori) || empty($deskripsi)) {
            $_SESSION['error'] = "Nama kategori dan deskripsi wajib diisi!";
            header("Location: tambah.php");
            exit;
        }

        $gambar_nama = '';
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['gambar']['tmp_name'];
            $file_name = $_FILES['gambar']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (in_array($file_ext, $allowed_ext)) {
                $gambar_nama = time() . '_' . uniqid() . '.' . $file_ext;
                $target_dir = '../../assets/uploads/kategori/';
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                move_uploaded_file($file_tmp, $target_dir . $gambar_nama);
            } else {
                $_SESSION['error'] = "Format gambar harus JPG, JPEG, PNG, WEBP, atau GIF";
                header("Location: tambah.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "Gambar kategori wajib diunggah";
            header("Location: tambah.php");
            exit;
        }

        $nama_kategori = mysqli_real_escape_string($conn, $nama_kategori);
        $deskripsi = mysqli_real_escape_string($conn, $deskripsi);
        $gambar_nama = mysqli_real_escape_string($conn, $gambar_nama);

        $query = mysqli_query($conn, "INSERT INTO kategori (nama_kategori, deskripsi, gambar) VALUES ('$nama_kategori', '$deskripsi', '$gambar_nama')");

        if ($query) {
            $_SESSION['success'] = "Kategori berhasil ditambahkan";
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['error'] = "Gagal menambahkan kategori: " . mysqli_error($conn);
            header("Location: tambah.php");
            exit;
        }
    } else {
        header("Location: index.php");
        exit;
    }
?>
