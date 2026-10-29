<?php
    include('../../config/database.php');

    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $kategori_id = isset($_POST['kategori_id']) ? intval($_POST['kategori_id']) : 0;
        $judul = trim($_POST['judul'] ?? '');
        $penulis = trim($_POST['penulis'] ?? '');
        $penerbit = trim($_POST['penerbit'] ?? '');
        $tahun_terbit = !empty($_POST['tahun_terbit']) ? intval($_POST['tahun_terbit']) : 'NULL';
        $isbn = trim($_POST['isbn'] ?? '');
        $harga = isset($_POST['harga']) ? floatval($_POST['harga']) : 0;
        $stok = isset($_POST['stok']) ? intval($_POST['stok']) : 0;
        $berat_gram = isset($_POST['berat_gram']) ? intval($_POST['berat_gram']) : 200;
        $deskripsi = trim($_POST['deskripsi'] ?? '');

        if ($kategori_id <= 0 || empty($judul) || empty($penulis) || empty($penerbit) || $harga < 0 || $stok < 0 || empty($tahun_terbit) || empty($isbn) || empty($deskripsi)) {
            $_SESSION['error'] = "Mohon lengkapi semua data wajib bertanda bintang (*)";
            header("Location: tambah.php");
            exit;
        }

        $gambar_nama = '';
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['gambar']['tmp_name'];
            $file_name = $_FILES['gambar']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

            if (in_array($file_ext, $allowed_ext)) {
                $gambar_nama = time() . '_' . uniqid() . '.' . $file_ext;
                $target_dir = '../../assets/uploads/buku/';
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                if (!move_uploaded_file($file_tmp, $target_dir . $gambar_nama)) {
                    $_SESSION['error'] = "Gagal mengunggah foto sampul buku";
                    header("Location: tambah.php");
                    exit;
                }
            } else {
                $_SESSION['error'] = "Format sampul harus JPG, JPEG, PNG, atau WEBP";
                header("Location: tambah.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "Foto sampul buku wajib diunggah";
            header("Location: tambah.php");
            exit;
        }

        $judul = mysqli_real_escape_string($conn, $judul);
        $penulis = mysqli_real_escape_string($conn, $penulis);
        $penerbit = mysqli_real_escape_string($conn, $penerbit);
        $isbn = mysqli_real_escape_string($conn, $isbn);
        $deskripsi = mysqli_real_escape_string($conn, $deskripsi);
        $gambar_nama = mysqli_real_escape_string($conn, $gambar_nama);

        $query_str = "INSERT INTO buku (kategori_id, judul, penulis, penerbit, tahun_terbit, isbn, harga, stok, berat_gram, deskripsi, gambar) VALUES ('$kategori_id', '$judul', '$penulis', '$penerbit', $tahun_terbit, '$isbn', '$harga', '$stok', '$berat_gram', '$deskripsi', '$gambar_nama')";
        $insert = mysqli_query($conn, $query_str);

        if ($insert) {
            $_SESSION['success'] = "Buku berhasil ditambahkan";
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['error'] = "Gagal menambahkan buku: " . mysqli_error($conn);
            header("Location: tambah.php");
            exit;
        }
    } else {
        header("Location: index.php");
        exit;
    }
?>
