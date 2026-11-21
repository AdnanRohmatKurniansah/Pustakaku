<?php
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../services/cloudinary_service.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $kategori_id = isset($_POST['kategori_id']) ? intval($_POST['kategori_id']) : 0;
        $judul = trim($_POST['judul'] ?? '');
        $penulis = trim($_POST['penulis'] ?? '');
        $penerbit = trim($_POST['penerbit'] ?? '');
        $tahun_terbit = !empty($_POST['tahun_terbit']) ? intval($_POST['tahun_terbit']) : 'NULL';
        $isbn = trim($_POST['isbn'] ?? '');
        $harga = isset($_POST['harga']) ? floatval($_POST['harga']) : 0;
        $harga_diskon = (!empty($_POST['harga_diskon']) && floatval($_POST['harga_diskon']) > 0) ? floatval($_POST['harga_diskon']) : 'NULL';
        $stok = isset($_POST['stok']) ? intval($_POST['stok']) : 0;
        $berat_gram = isset($_POST['berat_gram']) ? intval($_POST['berat_gram']) : 200;
        $deskripsi = trim($_POST['deskripsi'] ?? '');

        if ($kategori_id <= 0 || empty($judul) || empty($penulis) || empty($penerbit) || $harga < 0 || $stok < 0 || empty($isbn) || empty($deskripsi)) {
            $_SESSION['error'] = "Mohon lengkapi semua data wajib bertanda bintang (*)";
            header("Location: tambah.php");
            exit;
        }

        if (!isset($_FILES['gambar_utama']) || $_FILES['gambar_utama']['error'] !== UPLOAD_ERR_OK) {
            $_SESSION['error'] = "Foto sampul utama wajib diunggah.";
            header("Location: tambah.php");
            exit;
        }

        $upload_utama = uploadFileCloudinary($_FILES['gambar_utama'], 'buku');
        if (!$upload_utama['ok']) {
            $_SESSION['error'] = "Gagal mengunggah foto utama: " . ($upload_utama['message'] ?? '');
            header("Location: tambah.php");
            exit;
        }

        $primary_image = $upload_utama['url'];
        $primary_public_id = $upload_utama['public_id'] ?? null;

        $uploaded_tambahan = [];
        if (isset($_FILES['gambar_tambahan'])) {
            $files = $_FILES['gambar_tambahan'];
            $count = is_array($files['name']) ? count($files['name']) : 1;

            for ($i = 0; $i < $count; $i++) {
                $tmp_name = is_array($files['tmp_name']) ? $files['tmp_name'][$i] : $files['tmp_name'];
                $name = is_array($files['name']) ? $files['name'][$i] : $files['name'];
                $error = is_array($files['error']) ? $files['error'][$i] : $files['error'];

                if ($error === UPLOAD_ERR_OK && is_uploaded_file($tmp_name)) {
                    $single_file = [
                        'tmp_name' => $tmp_name,
                        'name' => $name,
                        'error' => $error
                    ];
                    $upload_res = uploadFileCloudinary($single_file, 'buku');
                    if ($upload_res['ok']) {
                        $uploaded_tambahan[] = [
                            'url' => $upload_res['url'],
                            'public_id' => $upload_res['public_id'] ?? null
                        ];
                    }
                }
            }
        }

        if (empty($uploaded_tambahan)) {
            $_SESSION['error'] = "Minimal wajib mengunggah 1 foto tambahan untuk galeri buku.";
            header("Location: tambah.php");
            exit;
        }

        $judul = mysqli_real_escape_string($conn, $judul);
        $penulis = mysqli_real_escape_string($conn, $penulis);
        $penerbit = mysqli_real_escape_string($conn, $penerbit);
        $isbn = mysqli_real_escape_string($conn, $isbn);
        $deskripsi = mysqli_real_escape_string($conn, $deskripsi);
        $primary_image_esc = mysqli_real_escape_string($conn, $primary_image);

        $query_str = "INSERT INTO buku (kategori_id, judul, penulis, penerbit, tahun_terbit, isbn, harga, harga_diskon, stok, berat_gram, deskripsi, gambar) VALUES ('$kategori_id', '$judul', '$penulis', '$penerbit', $tahun_terbit, '$isbn', '$harga', $harga_diskon, '$stok', '$berat_gram', '$deskripsi', '$primary_image_esc')";
        $insert = mysqli_query($conn, $query_str);

        if ($insert) {
            $buku_id = mysqli_insert_id($conn);

            $pid_primary_esc = !empty($primary_public_id) ? "'" . mysqli_real_escape_string($conn, $primary_public_id) . "'" : "NULL";
            mysqli_query($conn, "INSERT INTO buku_gambar (buku_id, url_gambar, public_id, is_primary, urutan) VALUES ('$buku_id', '$primary_image_esc', $pid_primary_esc, 1, 0)");

            foreach ($uploaded_tambahan as $index => $img) {
                $url_esc = mysqli_real_escape_string($conn, $img['url']);
                $pid_esc = !empty($img['public_id']) ? "'" . mysqli_real_escape_string($conn, $img['public_id']) . "'" : "NULL";
                $urutan = $index + 1;
                mysqli_query($conn, "INSERT INTO buku_gambar (buku_id, url_gambar, public_id, is_primary, urutan) VALUES ('$buku_id', '$url_esc', $pid_esc, 0, $urutan)");
            }

            $_SESSION['success'] = "Buku, foto utama, dan seluruh foto galeri tambahan berhasil ditambahkan.";
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['error'] = "Gagal menambahkan buku: " . mysqli_error($conn);
            header("Location: tambah.php");
            exit;
        }
    } else {
        header("Location: index.php");
        exit;
    }
?>
