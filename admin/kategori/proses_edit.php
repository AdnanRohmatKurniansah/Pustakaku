<?php
    include('../../config/database.php');

    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
        $nama_kategori = trim($_POST['nama_kategori'] ?? '');
        $deskripsi = trim($_POST['deskripsi'] ?? '');

        if ($id <= 0 || empty($nama_kategori) || empty($deskripsi)) {
            $_SESSION['error'] = "Data tidak lengkap";
            header("Location: edit.php?id=" . $id);
            exit;
        }

        $check_query = mysqli_query($conn, "SELECT * FROM kategori WHERE id = '$id' LIMIT 1");
        if (!$check_query || mysqli_num_rows($check_query) === 0) {
            $_SESSION['error'] = "Kategori tidak ditemukan";
            header("Location: index.php");
            exit;
        }

        $kategori_lama = mysqli_fetch_assoc($check_query);
        $gambar_nama = $kategori_lama['gambar'];

        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['gambar']['tmp_name'];
            $file_name = $_FILES['gambar']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (in_array($file_ext, $allowed_ext)) {
                $gambar_baru = time() . '_' . uniqid() . '.' . $file_ext;
                $target_dir = '../../assets/uploads/kategori/';
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
                $_SESSION['error'] = "Format gambar harus JPG, JPEG, PNG, WEBP, atau GIF";
                header("Location: edit.php?id=" . $id);
                exit;
            }
        }

        $nama_kategori = mysqli_real_escape_string($conn, $nama_kategori);
        $deskripsi = mysqli_real_escape_string($conn, $deskripsi);
        $gambar_nama = mysqli_real_escape_string($conn, $gambar_nama);

        $query = mysqli_query($conn, "UPDATE kategori SET nama_kategori = '$nama_kategori', deskripsi = '$deskripsi', gambar = '$gambar_nama' WHERE id = '$id'");

        if ($query) {
            $_SESSION['success'] = "Kategori berhasil diperbarui";
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['error'] = "Gagal memperbarui kategori: " . mysqli_error($conn);
            header("Location: edit.php?id=" . $id);
            exit;
        }
    } else {
        header("Location: index.php");
        exit;
    }
?>
