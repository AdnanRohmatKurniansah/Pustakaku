<?php
    require_once dirname(__DIR__) . '/config/database.php';

    session_start();

    $client_id = $_ENV['GOOGLE_CLIENT_ID'] ?? $_SERVER['GOOGLE_CLIENT_ID'] ?? getenv('GOOGLE_CLIENT_ID') ?: '';
    $client_secret = $_ENV['GOOGLE_CLIENT_SECRET'] ?? $_SERVER['GOOGLE_CLIENT_SECRET'] ?? getenv('GOOGLE_CLIENT_SECRET') ?: '';

    $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
        || (strpos($_SERVER['HTTP_HOST'] ?? '', 'vercel.app') !== false);
    $scheme = $is_https ? 'https' : 'http';
    $http_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $path_prefix = (strpos($_SERVER['REQUEST_URI'] ?? '', '/projek_web') === 0) ? '/projek_web' : '';
    $detected_uri = $scheme . '://' . $http_host . $path_prefix . '/auth/google_callback.php';

    if (strpos($http_host, 'vercel.app') !== false) {
        $fallback_redirect_uri = 'https://' . $http_host . '/auth/google_callback.php';
    } else {
        $fallback_redirect_uri = !empty($_ENV['GOOGLE_REDIRECT_URI']) ? $_ENV['GOOGLE_REDIRECT_URI'] : $detected_uri;
    }

    $redirect_uri = $_SESSION['google_oauth_redirect_uri'] ?? $fallback_redirect_uri;

    if (isset($_GET['error'])) {
        $err_desc = $_GET['error_description'] ?? $_GET['error'];
        $_SESSION['error'] = 'Otorisasi Google dibatalkan / gagal: ' . htmlspecialchars($err_desc) . '. Pastikan URI redirect ' . htmlspecialchars($redirect_uri) . ' telah terdaftar di Authorized Redirect URIs Google Cloud Console.';
        header('Location: login.php');
        exit();
    }

    if (!isset($_GET['code'])) {
        $_SESSION['error'] = 'Kode otorisasi Google tidak ditemukan.';
        header('Location: login.php');
        exit();
    }

    if (!class_exists('Google_Client')) {
        $_SESSION['error'] = 'Library Google Client belum terpasang di server.';
        header('Location: login.php');
        exit();
    }

    if (empty($client_id) || empty($client_secret)) {
        $_SESSION['error'] = 'Kredensial Google OAuth belum lengkap di file .env.';
        header('Location: login.php');
        exit();
    }

    try {
        $client = new Google_Client();
        $client->setClientId($client_id);
        $client->setClientSecret($client_secret);
        $client->setRedirectUri($redirect_uri);

        $token = $client->fetchAccessTokenWithAuthCode($_GET['code']);
        if (isset($token['error'])) {
            $_SESSION['error'] = 'Otorisasi Google gagal: ' . htmlspecialchars($token['error_description'] ?? $token['error']) . '. URI: ' . htmlspecialchars($redirect_uri);
            header('Location: login.php');
            exit();
        }

        $client->setAccessToken($token['access_token']);
        $google_oauth = new Google_Service_Oauth2($client);
        $google_info = $google_oauth->userinfo->get();

        $google_id = mysqli_real_escape_string($conn, $google_info->id ?? '');
        $email = mysqli_real_escape_string($conn, strtolower(trim($google_info->email ?? '')));
        $nama = mysqli_real_escape_string($conn, trim($google_info->name ?? 'Pengguna Google'));
        $picture = mysqli_real_escape_string($conn, $google_info->picture ?? '');

        if (empty($email)) {
            $_SESSION['error'] = 'Email akun Google tidak ditemukan.';
            header('Location: login.php');
            exit();
        }

        $q_check = mysqli_query($conn, "SELECT * FROM users WHERE email = '$email' LIMIT 1");
        if (mysqli_num_rows($q_check) > 0) {
            $user = mysqli_fetch_assoc($q_check);
            if (($user['provider'] ?? 'email') === 'email') {
                $_SESSION['error'] = 'Email ini sudah terdaftar menggunakan kata sandi (email). Silakan masuk menggunakan email dan kata sandi Anda.';
                header('Location: login.php');
                exit();
            }
            $user_id = (int) $user['id'];
            mysqli_query($conn, "UPDATE users SET google_id = '$google_id', provider = 'google', foto_profil = COALESCE(foto_profil, '$picture'), updated_at = NOW() WHERE id = '$user_id'");
            $user['provider'] = 'google';
        } else {
            mysqli_query($conn, "INSERT INTO users (nama_lengkap, email, google_id, role, foto_profil, provider) VALUES ('$nama', '$email', '$google_id', 'pelanggan', '$picture', 'google')");
            $user_id = (int) mysqli_insert_id($conn);
            $user = [
                'id' => $user_id,
                'nama_lengkap' => $nama,
                'email' => $email,
                'role' => 'pelanggan',
                'provider' => 'google'
            ];
        }

        $_SESSION['user_id'] = (int) $user['id'];
        $_SESSION['nama_lengkap'] = $user['nama_lengkap'];
        $_SESSION['email'] = $user['email'];
        $_SESSION['role'] = $user['role'];
        $_SESSION['provider'] = 'google';
        $_SESSION['success'] = 'Berhasil masuk dengan akun Google.';

        require_once dirname(__DIR__) . '/cart/proses_keranjang.php';
        cart_merge_session($conn, (int) $user['id']);

        $redirect = $_SESSION['redirect_after_login'] ?? '../index.php';
        unset($_SESSION['redirect_after_login']);
        $redirect = trim((string) $redirect);
        if ($redirect === '' || preg_match('#^(https?:)?//#i', $redirect) || strpos($redirect, "\n") !== false) {
            $redirect = ($user['role'] === 'admin') ? '../admin/index.php' : '../index.php';
        } elseif (strpos($redirect, '../') !== 0 && strpos($redirect, '/') !== 0) {
            $redirect = '../' . ltrim($redirect, '/');
        }

        session_write_close();
        header('Location: ' . $redirect);
        exit();
    } catch (\Exception $e) {
        $_SESSION['error'] = 'Terjadi kesalahan saat memproses login Google: ' . $e->getMessage();
        header('Location: login.php');
        exit();
    }
?>
