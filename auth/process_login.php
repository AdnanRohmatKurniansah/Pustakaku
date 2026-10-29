<?php
    include('../config/database.php');

    session_start();

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $_SESSION['old_email'] = $email;

        if (empty($email) || empty($password)) {
            $_SESSION['error'] = "Email dan password wajib diisi!";
            header("Location: login.php");
            exit;
        } else {
            $query = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email' LIMIT 1");
            if ($query && mysqli_num_rows($query) === 1) {
                $user = mysqli_fetch_assoc($query);
                if (password_verify($password, $user['password']) || md5($password) === $user['password']) {
                    if ($user['role'] === 'admin') {
                        unset($_SESSION['old_email']);
                        $_SESSION['user_id'] = $user['id'];
                        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['role'] = $user['role'];

                        header("Location: ../admin/index.php");
                        exit;
                    } else {
                        $_SESSION['error'] = "Akses ditolak! Halaman ini hanya untuk Administrator.";
                        header("Location: login.php");
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
        }
    } else {
        header("Location: login.php");
        exit;
    }
?>
