<?php
    session_start();
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/services/cloudinary_service.php';

    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Silakan masuk terlebih dahulu.";
        header("Location: auth/login.php");
        exit();
    }

    $user_id = (int) $_SESSION['user_id'];
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'update_profile') {
        $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
        $no_telepon = trim($_POST['no_telepon'] ?? '');

        if (empty($nama_lengkap)) {
            $_SESSION['error'] = "Nama lengkap tidak boleh kosong!";
            header("Location: akun.php?tab=profil");
            exit();
        }

        $foto_baru = null;
        if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
            $cld = uploadFileCloudinary($_FILES['foto_profil'], 'user');
            if ($cld['ok']) {
                $foto_baru = $cld['url'];
            } else {
                $_SESSION['error'] = $cld['message'] ?? "Gagal mengunggah foto profil.";
                header("Location: akun.php?tab=profil");
                exit();
            }
        }

        $nama_esc = mysqli_real_escape_string($conn, $nama_lengkap);
        $telp_esc = mysqli_real_escape_string($conn, $no_telepon);

        if ($foto_baru !== null) {
            $foto_esc = mysqli_real_escape_string($conn, $foto_baru);
            mysqli_query($conn, "UPDATE users SET nama_lengkap = '$nama_esc', no_telepon = '$telp_esc', foto_profil = '$foto_esc', updated_at = NOW() WHERE id = '$user_id'");
        } else {
            mysqli_query($conn, "UPDATE users SET nama_lengkap = '$nama_esc', no_telepon = '$telp_esc', updated_at = NOW() WHERE id = '$user_id'");
        }

        $_SESSION['nama_lengkap'] = $nama_lengkap;
        $_SESSION['success'] = "Data profil berhasil diperbarui!";
        header("Location: akun.php?tab=profil");
        exit();
    }

    if ($aksi === 'update_password') {
        $password_lama = $_POST['password_lama'] ?? '';
        $password_baru = $_POST['password_baru'] ?? '';
        $konfirmasi = $_POST['konfirmasi_password'] ?? '';

        if (empty($password_lama) || empty($password_baru) || empty($konfirmasi)) {
            $_SESSION['error'] = "Semua kolom kata sandi wajib diisi!";
            header("Location: akun.php?tab=keamanan");
            exit();
        }

        if (strlen($password_baru) < 6) {
            $_SESSION['error'] = "Kata sandi baru minimal 6 karakter!";
            header("Location: akun.php?tab=keamanan");
            exit();
        }

        if ($password_baru !== $konfirmasi) {
            $_SESSION['error'] = "Konfirmasi kata sandi baru tidak cocok!";
            header("Location: akun.php?tab=keamanan");
            exit();
        }

        $q_pass = mysqli_query($conn, "SELECT password FROM users WHERE id = '$user_id' LIMIT 1");
        $user_db = $q_pass ? mysqli_fetch_assoc($q_pass) : null;

        if ($user_db && (password_verify($password_lama, $user_db['password']) || md5($password_lama) === $user_db['password'])) {
            $hashed_new = password_hash($password_baru, PASSWORD_BCRYPT);
            $hashed_esc = mysqli_real_escape_string($conn, $hashed_new);
            mysqli_query($conn, "UPDATE users SET password = '$hashed_esc', updated_at = NOW() WHERE id = '$user_id'");
            $_SESSION['success'] = "Kata sandi berhasil diubah!";
            header("Location: akun.php?tab=keamanan");
            exit();
        } else {
            $_SESSION['error'] = "Kata sandi saat ini tidak sesuai!";
            header("Location: akun.php?tab=keamanan");
            exit();
        }
    }

    if ($aksi === 'selesaikan_pesanan') {
        $pesanan_id_konfirmasi = (int) ($_POST['pesanan_id'] ?? 0);
        if ($pesanan_id_konfirmasi > 0) {
            mysqli_query($conn, "UPDATE pesanan SET status_pesanan = 'selesai', updated_at = NOW() WHERE id = '$pesanan_id_konfirmasi' AND user_id = '$user_id'");
            $_SESSION['success'] = "Terima kasih! Pesanan Anda telah ditandai selesai.";
        }
        header("Location: akun.php?tab=pesanan");
        exit();
    }

    header("Location: akun.php");
    exit();
?>
