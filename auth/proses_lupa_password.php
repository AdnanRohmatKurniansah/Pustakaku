<?php
    require_once dirname(__DIR__) . '/config/database.php';
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    require_once dirname(__DIR__) . '/services/email_service.php';

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: lupa_password.php");
        exit();
    }

    $email = strtolower(trim($_POST['email'] ?? ''));
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = 'Silakan masukkan alamat email yang valid.';
        $_SESSION['old_email'] = $email;
        header("Location: lupa_password.php");
        exit();
    }

    $email_esc = mysqli_real_escape_string($conn, $email);
    $q_user = mysqli_query($conn, "SELECT id, nama_lengkap, email, provider FROM users WHERE email = '$email_esc' LIMIT 1");

    if (!$q_user || mysqli_num_rows($q_user) === 0) {
        $_SESSION['error'] = 'Alamat email tidak terdaftar dalam sistem kami.';
        $_SESSION['old_email'] = $email;
        header("Location: lupa_password.php");
        exit();
    }

    $user = mysqli_fetch_assoc($q_user);
    if (($user['provider'] ?? '') === 'google') {
        $_SESSION['error'] = 'Akun ini terdaftar menggunakan akun Google dan tidak memiliki kata sandi. Silakan langsung masuk menggunakan akun Google Anda.';
        $_SESSION['old_email'] = $email;
        header("Location: lupa_password.php");
        exit();
    }
    $user_id = $user['id'];
    $token = bin2hex(random_bytes(32));
    $token_esc = mysqli_real_escape_string($conn, $token);
    $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));

    $update = mysqli_query($conn, "UPDATE users SET reset_token = '$token_esc', reset_token_expires = '$expires', updated_at = NOW() WHERE id = '$user_id'");

    if ($update) {
        $send = kirim_email_reset_password($user['email'], $user['nama_lengkap'], $token);
        if (!empty($send['is_mock'])) {
            $_SESSION['success'] = 'Tautan reset kata sandi telah dibuat. Anda dapat langsung mengaksesnya: ' . $send['reset_link'];
        } else if (!empty($send['ok'])) {
            $_SESSION['success'] = 'Tautan atur ulang kata sandi telah dikirim ke email Anda. Silakan periksa kotak masuk atau spam.';
        } else {
            $_SESSION['error'] = 'Gagal mengirim email: ' . ($send['message'] ?? 'Terjadi kesalahan SMTP.');
        }
    } else {
        $_SESSION['error'] = 'Terjadi kesalahan sistem saat memproses token.';
    }

    header("Location: lupa_password.php");
    exit();
?>

