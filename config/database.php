<?php
    $autoload_path = dirname(__DIR__) . '/vendor/autoload.php';
    if (file_exists($autoload_path)) {
        require_once $autoload_path;
    }

    $env_file = dirname(__DIR__) . '/.env';
    if (file_exists($env_file) && class_exists('Dotenv\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->safeLoad();
    }

    $hostname = $_ENV['DB_HOST'] ?? 'localhost';
    $port = isset($_ENV['DB_PORT']) ? (int) $_ENV['DB_PORT'] : 3306;
    $username = $_ENV['DB_USER'] ?? 'root';
    $password = $_ENV['DB_PASS'] ?? '';
    $database = $_ENV['DB_NAME'] ?? 'pustakaku';

    $conn = false;
    $socket = file_exists('/opt/lampp/var/mysql/mysql.sock') ? '/opt/lampp/var/mysql/mysql.sock' : null;

    try {
        if ($socket && ($hostname === 'localhost' || $hostname === '127.0.0.1')) {
            $conn = @mysqli_connect($hostname, $username, $password, $database, $port, $socket);
        } else {
            $conn = @mysqli_connect($hostname, $username, $password, $database, $port);
        }
    } catch (\Throwable $e) {
        if ($hostname !== 'localhost' && $hostname !== '127.0.0.1') {
            try {
                if ($socket) {
                    $conn = @mysqli_connect('localhost', 'root', '', $database, 3306, $socket);
                } else {
                    $conn = @mysqli_connect('127.0.0.1', 'root', '', $database, 3306);
                }
            } catch (\Throwable $ex) {
                die("<div style='font-family:sans-serif;padding:30px;max-width:650px;margin:50px auto;border:1px solid #dc3545;border-radius:12px;background:#fff5f5;'>
                    <h3 style='color:#dc3545;margin-top:0;'>Gagal Terhubung ke Database</h3>
                    <p>Koneksi ke server database utama (<strong>" . htmlspecialchars($hostname) . "</strong>) gagal: " . htmlspecialchars($e->getMessage()) . "</p>
                    <p style='font-size:13px;color:#666;'>Pastikan service MySQL di cloud Aiven masih aktif (tidak dalam status Powered Off / Expired) atau sesuaikan konfigurasi di file <code>.env</code> Anda.</p>
                </div>");
            }
        } else {
            die("Koneksi database lokal gagal: " . $e->getMessage());
        }
    }

    if (!$conn) {
        die("Koneksi database gagal.");
    }

    require_once __DIR__ . '/helper.php';
?>