<?php
    date_default_timezone_set('Asia/Jakarta');
    if (ob_get_level() === 0) {
        ob_start();
    }

    $autoload_path = dirname(__DIR__) . '/vendor/autoload.php';
    if (file_exists($autoload_path)) {
        require_once $autoload_path;
    }

    $env_file = dirname(__DIR__) . '/.env';
    if (file_exists($env_file) && class_exists('Dotenv\Dotenv')) {
        $dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));
        $dotenv->safeLoad();
    }

    foreach ($_SERVER as $k => $v) {
        if (!isset($_ENV[$k]) && is_string($v)) {
            $_ENV[$k] = $v;
        }
    }
    $all_keys = [
        'DB_HOST', 'DB_PORT', 'DB_USER', 'DB_PASS', 'DB_NAME',
        'APP_URL', 'MIDTRANS_SERVER_KEY', 'MIDTRANS_CLIENT_KEY', 'MIDTRANS_IS_PRODUCTION',
        'RAJAONGKIR_API_KEY', 'RAJAONGKIR_ORIGIN_CITY_ID',
        'CLOUDINARY_CLOUD_NAME', 'CLOUDINARY_API_KEY', 'CLOUDINARY_API_SECRET',
        'SMTP_HOST', 'SMTP_PORT', 'SMTP_USER', 'SMTP_PASS',
        'GOOGLE_CLIENT_ID', 'GOOGLE_CLIENT_SECRET', 'GOOGLE_REDIRECT_URI'
    ];
    foreach ($all_keys as $key) {
        if (!isset($_ENV[$key]) || $_ENV[$key] === '') {
            $val = getenv($key);
            if ($val !== false && $val !== '') {
                $_ENV[$key] = $val;
            }
        }
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
    mysqli_query($conn, "SET time_zone = '+07:00'");

    if (!class_exists('PustakakuSessionHandler')) {
        class PustakakuSessionHandler implements SessionHandlerInterface {
            private $conn;

            public function __construct($conn) {
                $this->conn = $conn;
            }

            public function open($path, $name): bool {
                return true;
            }

            public function close(): bool {
                return true;
            }

            public function read($id): string|false {
                if (!$this->conn) return '';
                $id_esc = mysqli_real_escape_string($this->conn, $id);
                $res = mysqli_query($this->conn, "SELECT data FROM sessions WHERE id = '$id_esc' AND access > " . (time() - 604800) . " LIMIT 1");
                if ($res && $row = mysqli_fetch_assoc($res)) {
                    return (string) $row['data'];
                }
                return '';
            }

            public function write($id, $data): bool {
                if (!$this->conn) return false;
                $id_esc = mysqli_real_escape_string($this->conn, $id);
                $data_esc = mysqli_real_escape_string($this->conn, $data);
                $time = time();
                $q = "INSERT INTO sessions (id, access, data) VALUES ('$id_esc', $time, '$data_esc')
                      ON DUPLICATE KEY UPDATE access = $time, data = '$data_esc'";
                return (bool) mysqli_query($this->conn, $q);
            }

            public function destroy($id): bool {
                if (!$this->conn) return false;
                $id_esc = mysqli_real_escape_string($this->conn, $id);
                return (bool) mysqli_query($this->conn, "DELETE FROM sessions WHERE id = '$id_esc'");
            }

            public function gc($max_lifetime): int|false {
                if (!$this->conn) return false;
                $old = time() - (int) $max_lifetime;
                $res = mysqli_query($this->conn, "DELETE FROM sessions WHERE access < $old");
                return $res ? mysqli_affected_rows($this->conn) : false;
            }
        }
    }

    if (!function_exists('pustakaku_init_session')) {
        function pustakaku_init_session($conn = null) {
            if (session_status() === PHP_SESSION_NONE) {
                $is_vercel = (!empty($_ENV['VERCEL'])) || (getenv('VERCEL') !== false) || (strpos($_SERVER['HTTP_HOST'] ?? '', 'vercel.app') !== false);
                $is_https = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
                    || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https')
                    || (strpos($_SERVER['HTTP_HOST'] ?? '', 'vercel.app') !== false);

                ini_set('session.cookie_httponly', '1');
                ini_set('session.cookie_samesite', 'Lax');
                if ($is_https) {
                    ini_set('session.cookie_secure', '1');
                } else {
                    ini_set('session.cookie_secure', '0');
                }

                session_set_cookie_params([
                    'lifetime' => 604800,
                    'path' => '/',
                    'domain' => '',
                    'secure' => $is_https,
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]);

                if ($conn && $is_vercel) {
                    $handler = new PustakakuSessionHandler($conn);
                    session_set_save_handler($handler, true);
                }
            }
        }
    }

    pustakaku_init_session($conn);

    require_once __DIR__ . '/helper.php';
?>