<?php
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = urldecode($uri);

$root = dirname(__DIR__);

if ($uri === '/' || $uri === '' || $uri === '/index.php') {
    require $root . '/index.php';
    exit;
}

$clean_uri = ltrim($uri, '/');
$target_file = $root . '/' . $clean_uri;

if (is_file($target_file)) {
    $ext = pathinfo($target_file, PATHINFO_EXTENSION);
    if ($ext === 'php') {
        chdir(dirname($target_file));
        require $target_file;
        exit;
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
    chdir(dirname($target_file));
    require $target_file . '.php';
    exit;
}

if (is_dir($target_file) && is_file($target_file . '/index.php')) {
    chdir($target_file);
    require $target_file . '/index.php';
    exit;
}

http_response_code(404);
echo "<!DOCTYPE html><html><head><title>404 Not Found</title></head><body style='font-family:sans-serif;text-align:center;padding:50px;'><h2>404 - Halaman Tidak Ditemukan</h2><p><a href='/'>Kembali ke Beranda</a></p></body></html>";
exit;
