<?php
    session_start();
    require_once dirname(__DIR__) . '/config/database.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: login.php");
        exit();
    }

    $token = trim($_POST['token'] ?? '');
    $password_baru = $_POST['password_baru'] ?? '';
    $konfirmasi = $_POST['konfirmasi_password'] ?? '';

    if (empty($token)) {
        $_SESSION['error'] = 'Token pemulihan tidak ditemukan.';
        header("Location: lupa_password.php");
        exit();
    }

    if (strlen($password_baru) < 6) {
        $_SESSION['error'] = 'Kata sandi baru minimal 6 karakter.';
        header("Location: reset_password.php?token=" . urlencode($token));
        exit();
    }

    if ($password_baru !== $konfirmasi) {
        $_SESSION['error'] = 'Konfirmasi kata sandi baru tidak cocok.';
        header("Location: reset_password.php?token=" . urlencode($token));
        exit();
    }

    $token_esc = mysqli_real_escape_string($conn, $token);
    $q_user = mysqli_query($conn, "SELECT id FROM users WHERE reset_token = '$token_esc' AND reset_token_expires >= NOW() LIMIT 1");

    if (!$q_user || mysqli_num_rows($q_user) === 0) {
        $_SESSION['error'] = 'Tautan pengaturan ulang sudah kedaluwarsa atau tidak valid.';
        header("Location: lupa_password.php");
        exit();
    }

    $user = mysqli_fetch_assoc($q_user);
    $user_id = $user['id'];
    $hashed_password = password_hash($password_baru, PASSWORD_BCRYPT);
    $hashed_esc = mysqli_real_escape_string($conn, $hashed_password);

    $update = mysqli_query($conn, "UPDATE users SET password = '$hashed_esc', reset_token = NULL, reset_token_expires = NULL, updated_at = NOW() WHERE id = '$user_id'");

    if ($update) {
        $_SESSION['success'] = 'Kata sandi Anda berhasil diperbarui! Silakan masuk dengan kata sandi baru.';
        header("Location: login.php");
    } else {
        $_SESSION['error'] = 'Gagal memperbarui kata sandi. Silakan coba kembali.';
        header("Location: reset_password.php?token=" . urlencode($token));
    }
    exit();
?>

