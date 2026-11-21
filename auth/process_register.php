<?php
    session_start();
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../cart/proses_keranjang.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $no_telepon = trim($_POST['no_telepon'] ?? '');
        $password = $_POST['password'] ?? '';
        $konfirmasi = $_POST['konfirmasi_password'] ?? '';

        $_SESSION['old_nama'] = $nama_lengkap;
        $_SESSION['old_email'] = $email;
        $_SESSION['old_telepon'] = $no_telepon;

        if (empty($nama_lengkap) || empty($email) || empty($password) || empty($konfirmasi)) {
            $_SESSION['error'] = "Semua kolom bertanda wajib harus diisi!";
            header("Location: register.php");
            exit;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['error'] = "Format alamat email tidak valid!";
            header("Location: register.php");
            exit;
        }

        if (strlen($password) < 6) {
            $_SESSION['error'] = "Kata sandi minimal harus 6 karakter!";
            header("Location: register.php");
            exit;
        }

        if ($password !== $konfirmasi) {
            $_SESSION['error'] = "Konfirmasi kata sandi tidak cocok!";
            header("Location: register.php");
            exit;
        }

        $email_esc = mysqli_real_escape_string($conn, $email);
        $cek = mysqli_query($conn, "SELECT id, provider FROM users WHERE email = '$email_esc' LIMIT 1");
        if ($cek && mysqli_num_rows($cek) > 0) {
            $u_exist = mysqli_fetch_assoc($cek);
            if (($u_exist['provider'] ?? '') === 'google') {
                $_SESSION['error'] = "Email ini sudah terdaftar menggunakan akun Google. Silakan masuk menggunakan tombol 'Masuk dengan Google'!";
            } else {
                $_SESSION['error'] = "Alamat email tersebut sudah terdaftar. Silakan gunakan email lain atau langsung masuk.";
            }
            header("Location: register.php");
            exit;
        }

        $nama_esc = mysqli_real_escape_string($conn, $nama_lengkap);
        $telp_esc = mysqli_real_escape_string($conn, $no_telepon);
        $hashed = password_hash($password, PASSWORD_BCRYPT);
        $hashed_esc = mysqli_real_escape_string($conn, $hashed);

        $ins = mysqli_query($conn, "INSERT INTO users (nama_lengkap, email, password, role, no_telepon, provider) VALUES ('$nama_esc', '$email_esc', '$hashed_esc', 'pelanggan', '$telp_esc', 'email')");

        if ($ins) {
            $new_id = (int) mysqli_insert_id($conn);
            unset($_SESSION['old_nama'], $_SESSION['old_email'], $_SESSION['old_telepon']);
            $_SESSION['user_id'] = $new_id;
            $_SESSION['nama_lengkap'] = $nama_lengkap;
            $_SESSION['email'] = $email;
            $_SESSION['role'] = 'pelanggan';
            $_SESSION['provider'] = 'email';

            cart_merge_session($conn, $new_id);

            $_SESSION['success'] = "Pendaftaran berhasil! Selamat datang di PustakaKu.";
            session_write_close();
            header("Location: ../index.php");
            exit;
        } else {
            $_SESSION['error'] = "Gagal mendaftarkan akun. Silakan coba lagi.";
            header("Location: register.php");
            exit;
        }
    } else {
        header("Location: register.php");
        exit;
    }
