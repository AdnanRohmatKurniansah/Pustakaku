<?php
    require_once dirname(__DIR__) . '/config/database.php';

    session_start();

    $client_id = $_ENV['GOOGLE_CLIENT_ID'] ?? '';
    $client_secret = $_ENV['GOOGLE_CLIENT_SECRET'] ?? '';

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $http_host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $detected_uri = $scheme . '://' . $http_host . '/projek_web/auth/google_callback.php';

    $redirect_uri = !empty($_ENV['GOOGLE_REDIRECT_URI']) ? $_ENV['GOOGLE_REDIRECT_URI'] : $detected_uri;

    if (strpos($http_host, '127.0.0.1') !== false && strpos($redirect_uri, 'localhost') !== false) {
        $redirect_uri = str_replace('localhost', '127.0.0.1', $redirect_uri);
    } elseif (strpos($http_host, 'localhost') !== false && strpos($redirect_uri, '127.0.0.1') !== false) {
        $redirect_uri = str_replace('127.0.0.1', 'localhost', $redirect_uri);
    }

    $_SESSION['google_oauth_redirect_uri'] = $redirect_uri;

    if (empty($client_id) || empty($client_secret) || !class_exists('Google_Client')) {
        $_SESSION['error'] = 'Kredensial Google OAuth belum dikonfigurasi di file .env. Silakan lengkapi GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET.';
        header('Location: login.php');
        exit();
    }

    $client = new Google_Client();
    $client->setClientId($client_id);
    $client->setClientSecret($client_secret);
    $client->setRedirectUri($redirect_uri);
    $client->addScope('email');
    $client->addScope('profile');

    $auth_url = $client->createAuthUrl();
    header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
    exit();
?>
