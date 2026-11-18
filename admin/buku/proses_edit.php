<?php
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../services/cloudinary_service.php';

    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    $action = $_GET['action'] ?? '';

    if ($action === 'set_primary') {
        $buku_id = isset($_GET['buku_id']) ? intval($_GET['buku_id']) : 0;
        $gambar_id = isset($_GET['gambar_id']) ? intval($_GET['gambar_id']) : 0;

        if ($buku_id > 0 && $gambar_id > 0) {
            mysqli_query($conn, "UPDATE buku_gambar SET is_primary = 0 WHERE buku_id = '$buku_id'");
            mysqli_query($conn, "UPDATE buku_gambar SET is_primary = 1 WHERE id = '$gambar_id' AND buku_id = '$buku_id'");

            $q_target = mysqli_query($conn, "SELECT url_gambar FROM buku_gambar WHERE id = '$gambar_id' LIMIT 1");
            if ($q_target && mysqli_num_rows($q_target) > 0) {
                $row_img = mysqli_fetch_assoc($q_target);
                $new_url = mysqli_real_escape_string($conn, $row_img['url_gambar']);
                mysqli_query($conn, "UPDATE buku SET gambar = '$new_url' WHERE id = '$buku_id'");
            }
            $_SESSION['success'] = "Foto utama berhasil diubah.";
        }
        header("Location: edit.php?id=" . $buku_id);
        exit;
    }

    if ($action === 'delete_gambar') {
        $buku_id = isset($_GET['buku_id']) ? intval($_GET['buku_id']) : 0;
        $gambar_id = isset($_GET['gambar_id']) ? intval($_GET['gambar_id']) : 0;

        if ($buku_id > 0 && $gambar_id > 0) {
            $q_count = mysqli_query($conn, "SELECT COUNT(*) AS total FROM buku_gambar WHERE buku_id = '$buku_id'");
            $total = mysqli_fetch_assoc($q_count)['total'] ?? 0;

            if ($total <= 1) {
                $_SESSION['error'] = "Tidak dapat menghapus foto terakhir. Minimal produk memiliki satu foto.";
                header("Location: edit.php?id=" . $buku_id);
                exit;
            }

            $q_del_check = mysqli_query($conn, "SELECT is_primary FROM buku_gambar WHERE id = '$gambar_id' AND buku_id = '$buku_id' LIMIT 1");
            $del_img = mysqli_fetch_assoc($q_del_check);
            $was_primary = ($del_img && $del_img['is_primary'] == 1);

            mysqli_query($conn, "DELETE FROM buku_gambar WHERE id = '$gambar_id' AND buku_id = '$buku_id'");

            if ($was_primary) {
                $q_next = mysqli_query($conn, "SELECT id, url_gambar FROM buku_gambar WHERE buku_id = '$buku_id' ORDER BY id ASC LIMIT 1");
                if ($q_next && mysqli_num_rows($q_next) > 0) {
                    $next_img = mysqli_fetch_assoc($q_next);
                    $next_id = $next_img['id'];
                    $next_url = mysqli_real_escape_string($conn, $next_img['url_gambar']);
                    mysqli_query($conn, "UPDATE buku_gambar SET is_primary = 1 WHERE id = '$next_id'");
                    mysqli_query($conn, "UPDATE buku SET gambar = '$next_url' WHERE id = '$buku_id'");
                }
            }
            $_SESSION['success'] = "Foto galeri berhasil dihapus.";
        }
        header("Location: edit.php?id=" . $buku_id);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
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

        if ($id <= 0 || $kategori_id <= 0 || empty($judul) || empty($penulis) || empty($penerbit) || $harga < 0 || $stok < 0) {
            $_SESSION['error'] = "Mohon lengkapi semua data wajib bertanda bintang (*)!";
            header("Location: edit.php?id=" . $id);
            exit;
        }

        $check_query = mysqli_query($conn, "SELECT * FROM buku WHERE id = '$id' LIMIT 1");
        if (!$check_query || mysqli_num_rows($check_query) === 0) {
            $_SESSION['error'] = "Data buku tidak ditemukan!";
            header("Location: index.php");
            exit;
        }

        if (isset($_FILES['gambar_baru'])) {
            $files = $_FILES['gambar_baru'];
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
                        $url_esc = mysqli_real_escape_string($conn, $upload_res['url']);
                        $pid_esc = !empty($upload_res['public_id']) ? "'" . mysqli_real_escape_string($conn, $upload_res['public_id']) . "'" : "NULL";
                        mysqli_query($conn, "INSERT INTO buku_gambar (buku_id, url_gambar, public_id, is_primary, urutan) VALUES ('$id', '$url_esc', $pid_esc, 0, 99)");
                    }
                }
            }
        }

        $judul = mysqli_real_escape_string($conn, $judul);
        $penulis = mysqli_real_escape_string($conn, $penulis);
        $penerbit = mysqli_real_escape_string($conn, $penerbit);
        $isbn = mysqli_real_escape_string($conn, $isbn);
        $deskripsi = mysqli_real_escape_string($conn, $deskripsi);

        $query_str = "UPDATE buku SET kategori_id = '$kategori_id', judul = '$judul', penulis = '$penulis', penerbit = '$penerbit', tahun_terbit = $tahun_terbit, isbn = '$isbn', harga = '$harga', harga_diskon = $harga_diskon, stok = '$stok', berat_gram = '$berat_gram', deskripsi = '$deskripsi', updated_at = NOW() WHERE id = '$id'";
        $update = mysqli_query($conn, $query_str);

        if ($update) {
            $q_prim = mysqli_query($conn, "SELECT url_gambar FROM buku_gambar WHERE buku_id = '$id' AND is_primary = 1 LIMIT 1");
            if ($q_prim && mysqli_num_rows($q_prim) > 0) {
                $prim_url = mysqli_real_escape_string($conn, mysqli_fetch_assoc($q_prim)['url_gambar']);
                mysqli_query($conn, "UPDATE buku SET gambar = '$prim_url' WHERE id = '$id'");
            }
            $_SESSION['success'] = "Data buku dan foto galeri berhasil diperbarui!";
            header("Location: index.php");
            exit;
        } else {
            $_SESSION['error'] = "Gagal memperbarui data buku: " . mysqli_error($conn);
            header("Location: edit.php?id=" . $id);
            exit;
        }
    } else {
        header("Location: index.php");
        exit;
    }
?>
