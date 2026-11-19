<?php
error_reporting(E_ALL);
ini_set('display_errors', '0');

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$uri = urldecode($uri);

$root = dirname(__DIR__);

function execute_php_file($path) {
    try {
        chdir(dirname($path));
        require $path;
        exit;
    } catch (\Throwable $e) {
        http_response_code(500);
        echo "<!DOCTYPE html><html><head><title>500 Internal Server Error</title></head><body style='font-family:sans-serif;padding:30px;max-width:650px;margin:auto;'><h3>Terjadi Kesalahan Server (500)</h3><p>" . htmlspecialchars($e->getMessage()) . "</p></body></html>";
        exit;
    }
}

if ($uri === '/' || $uri === '' || $uri === '/index.php') {
    execute_php_file($root . '/index.php');
}

$clean_uri = ltrim($uri, '/');
$target_file = $root . '/' . $clean_uri;

if (is_file($target_file)) {
    $ext = pathinfo($target_file, PATHINFO_EXTENSION);
    if ($ext === 'php') {
        execute_php_file($target_file);
    }

    $mimes = [
        'css'  => 'text/css',
        'js'   => 'application/javascript',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif'  => 'image/gif',
        'svg'  => 'image/svg+xml',
        'webp' => 'image/webp',
        'ico'  => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2'=> 'font/woff2',
        'ttf'  => 'font/ttf'
    ];
    if (isset($mimes[$ext])) {
        header('Content-Type: ' . $mimes[$ext]);
        header('Cache-Control: public, max-age=86400');
        readfile($target_file);
        exit;
    }
}

if (is_file($target_file . '.php')) {
    execute_php_file($target_file . '.php');
}

if (is_dir($target_file) && is_file($target_file . '/index.php')) {
    execute_php_file($target_file . '/index.php');
}

http_response_code(404);
echo "<!DOCTYPE html><html><head><title>404 Not Found</title></head><body style='font-family:sans-serif;text-align:center;padding:50px;'><h2>404 - Halaman Tidak Ditemukan</h2><p><a href='/'>Kembali ke Beranda</a></p></body></html>";
exit;
