<?php
    session_start();
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../cart/proses_keranjang.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $_SESSION['old_email'] = $email;

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = "Email dan password wajib diisi!";
            header("Location: login.php");
            exit;
        }

        $email_esc = mysqli_real_escape_string($conn, $email);
        $res = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email_esc' LIMIT 1");
        $user = $res ? mysqli_fetch_assoc($res) : null;

        if ($user) {
            if (($user['provider'] ?? '') === 'google') {
                $_SESSION['error'] = "Akun ini terdaftar menggunakan akun Google. Silakan masuk menggunakan tombol 'Masuk dengan Google'!";
                header("Location: login.php");
                exit;
            }

            if (password_verify($password, $user['password']) || md5($password) === $user['password']) {
                unset($_SESSION['old_email']);
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['provider'] = 'email';

                cart_merge_session($conn, (int) $user['id']);

                if ($user['role'] === 'admin') {
                    $_SESSION['success'] = "Selamat datang kembali, Administrator!";
                    session_write_close();
                    header("Location: ../admin/index.php");
                    exit;
                } else {
                    $_SESSION['success'] = "Selamat datang kembali, " . htmlspecialchars($user['nama_lengkap']) . "!";
                    $redirect = $_SESSION['redirect_after_login'] ?? '../index.php';
                    unset($_SESSION['redirect_after_login']);

                    $redirect = trim((string) $redirect);
                    if ($redirect === '' || preg_match('#^(https?:)?//#i', $redirect) || strpos($redirect, "\n") !== false) {
                        $redirect = '../index.php';
                    } elseif (strpos($redirect, '../') !== 0 && strpos($redirect, '/') !== 0) {
                        $redirect = '../' . ltrim($redirect, '/');
                    }

                    session_write_close();
                    header("Location: " . $redirect);
                    exit;
                }
            } else {
                $_SESSION['error'] = "Password yang Anda masukkan salah!";
                header("Location: login.php");
                exit;
            }
        } else {
            $_SESSION['error'] = "Akun dengan email tersebut tidak ditemukan!";
            header("Location: login.php");
            exit;
        }
    } else {
        header("Location: login.php");
        exit;
    }
