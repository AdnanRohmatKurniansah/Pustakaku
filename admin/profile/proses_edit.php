<?php
    require_once __DIR__ . '/../../config/database.php';

    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userId = $_SESSION['user_id'];
        $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
        $no_telepon = trim($_POST['no_telepon'] ?? '');
        $alamat_lengkap = trim($_POST['alamat_lengkap'] ?? '');

        if (empty($nama_lengkap)) {
            $_SESSION['error'] = "Data tidak lengkap";
            header("Location: index.php");
            exit;
        }

        $check_query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$userId' LIMIT 1");
        if (!$check_query || mysqli_num_rows($check_query) === 0) {
            $_SESSION['error'] = "User tidak ditemukan";
            header("Location: index.php");
            exit;
        }

        $profile_lama = mysqli_fetch_assoc($check_query);
        $profile_nama = $profile_lama['foto_profil'];

        if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['foto_profil']['tmp_name'];
            $file_name = $_FILES['foto_profil']['name'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'gif'];

            if (in_array($file_ext, $allowed_ext)) {
                $foto_profil_baru = time() . '_' . uniqid() . '.' . $file_ext;
                $target_dir = '../../assets/uploads/user/';
                if (!is_dir($target_dir)) {
                    mkdir($target_dir, 0777, true);
                }
                if (move_uploaded_file($file_tmp, $target_dir . $foto_profil_baru)) {
                    if (!empty($profile_nama) && file_exists($target_dir . $profile_nama)) {
                        unlink($target_dir . $profile_nama);
                    }
                    $profile_nama = $foto_profil_baru;
                }
            } else {
                $_SESSION['error'] = "Format foto_profil harus JPG, JPEG, PNG, WEBP, atau GIF";
                header("Location: index.php");
                exit;
            }
        }

        $nama_lengkap = mysqli_real_escape_string($conn, $nama_lengkap);
        $no_telepon = mysqli_real_escape_string($conn, $no_telepon);
        $alamat_lengkap = mysqli_real_escape_string($conn, $alamat_lengkap);

        $query = mysqli_query($conn, "UPDATE users SET nama_lengkap = '$nama_lengkap', no_telepon = '$no_telepon', alamat_lengkap = '$alamat_lengkap', foto_profil = '$profile_nama' WHERE id = '$userId'");

        if ($query) {
            $_SESSION['success'] = "Profil anda berhasil diperbarui";
            header("Location: ../index.php");
            exit;
        } else {
            $_SESSION['error'] = "Gagal memperbarui profil: " . mysqli_error($conn);
            header("Location: index.php");
            exit;
        }
    } else {
        header("Location: index.php");
        exit;
    }
?>
