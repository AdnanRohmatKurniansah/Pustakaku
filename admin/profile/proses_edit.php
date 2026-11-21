<?php
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../services/cloudinary_service.php';

    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $userId = $_SESSION['user_id'];
        $aksi = $_POST['aksi'] ?? 'update_profile';

        if ($aksi === 'update_password') {
            $password_lama = $_POST['password_lama'] ?? '';
            $password_baru = $_POST['password_baru'] ?? '';
            $konfirmasi = $_POST['konfirmasi_password'] ?? '';

            if (empty($password_lama) || empty($password_baru) || empty($konfirmasi)) {
                $_SESSION['error'] = 'Semua bidang kata sandi wajib diisi.';
                header("Location: index.php");
                exit;
            }

            if (strlen($password_baru) < 6) {
                $_SESSION['error'] = 'Kata sandi baru minimal 6 karakter.';
                header("Location: index.php");
                exit;
            }

            if ($password_baru !== $konfirmasi) {
                $_SESSION['error'] = 'Konfirmasi kata sandi baru tidak cocok.';
                header("Location: index.php");
                exit;
            }

            $check_query = mysqli_query($conn, "SELECT password FROM users WHERE id = '$userId' LIMIT 1");
            $user_db = mysqli_fetch_assoc($check_query);

            if ($user_db && (password_verify($password_lama, $user_db['password']) || md5($password_lama) === $user_db['password'])) {
                $hashed_new = password_hash($password_baru, PASSWORD_BCRYPT);
                $hashed_esc = mysqli_real_escape_string($conn, $hashed_new);
                mysqli_query($conn, "UPDATE users SET password = '$hashed_esc', updated_at = NOW() WHERE id = '$userId'");
                $_SESSION['success'] = 'Kata sandi administrator berhasil diperbarui.';
            } else {
                $_SESSION['error'] = 'Kata sandi saat ini yang Anda masukkan salah.';
            }

            header("Location: index.php");
            exit;
        }

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
            $upload_res = uploadFileCloudinary($_FILES['foto_profil'], 'user');
            if ($upload_res['ok']) {
                $profile_nama = $upload_res['url'];
            } else {
                $_SESSION['error'] = $upload_res['message'];
                header("Location: index.php");
                exit;
            }
        }

        $nama_lengkap = mysqli_real_escape_string($conn, $nama_lengkap);
        $no_telepon = mysqli_real_escape_string($conn, $no_telepon);
        $alamat_lengkap = mysqli_real_escape_string($conn, $alamat_lengkap);
        $profile_nama_esc = mysqli_real_escape_string($conn, $profile_nama);

        $query = mysqli_query($conn, "UPDATE users SET nama_lengkap = '$nama_lengkap', no_telepon = '$no_telepon', alamat_lengkap = '$alamat_lengkap', foto_profil = '$profile_nama_esc', updated_at = NOW() WHERE id = '$userId'");

        if ($query) {
            $_SESSION['nama_lengkap'] = $nama_lengkap;
            $_SESSION['foto_profil'] = $profile_nama;
            $_SESSION['success'] = "Profil anda berhasil diperbarui";
            header("Location: index.php");
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
