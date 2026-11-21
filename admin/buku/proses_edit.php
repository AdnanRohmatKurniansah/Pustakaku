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

    $action = $_GET['action'] ?? '';

    if ($action === 'set_primary') {
        $buku_id = isset($_GET['buku_id']) ? intval($_GET['buku_id']) : 0;
        $gambar_id = isset($_GET['gambar_id']) ? intval($_GET['gambar_id']) : 0;

        if ($buku_id > 0 && $gambar_id > 0) {
            $q_target = mysqli_query($conn, "SELECT url_gambar FROM buku_gambar WHERE id = '$gambar_id' AND buku_id = '$buku_id' LIMIT 1");
            if ($q_target && mysqli_num_rows($q_target) > 0) {
                $row_img = mysqli_fetch_assoc($q_target);
                $new_url = mysqli_real_escape_string($conn, $row_img['url_gambar']);

                mysqli_query($conn, "UPDATE buku_gambar SET is_primary = 0 WHERE buku_id = '$buku_id'");
                mysqli_query($conn, "UPDATE buku_gambar SET is_primary = 1 WHERE id = '$gambar_id' AND buku_id = '$buku_id'");
                mysqli_query($conn, "UPDATE buku SET gambar = '$new_url', updated_at = NOW() WHERE id = '$buku_id'");
                $_SESSION['success'] = "Foto utama berhasil diubah.";
            }
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
                $_SESSION['error'] = "Tidak dapat menghapus foto terakhir. Minimal buku memiliki satu foto.";
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
                    mysqli_query($conn, "UPDATE buku SET gambar = '$next_url', updated_at = NOW() WHERE id = '$buku_id'");
                }
            }
            $_SESSION['success'] = "Foto tambahan berhasil dihapus.";
        }
        header("Location: edit.php?id=" . $buku_id);
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $aksi = $_GET['aksi'] ?? $_POST['aksi'] ?? 'update_buku';

        if ($aksi === 'tambah_gambar_tambahan') {
            $buku_id = isset($_POST['buku_id']) ? intval($_POST['buku_id']) : 0;
            if ($buku_id <= 0) {
                $_SESSION['error'] = "ID buku tidak valid.";
                header("Location: index.php");
                exit;
            }

            if (!isset($_FILES['gambar_tambahan']) || empty($_FILES['gambar_tambahan']['name']) || (is_array($_FILES['gambar_tambahan']['name']) && empty($_FILES['gambar_tambahan']['name'][0]))) {
                $_SESSION['error'] = "Silakan pilih minimal 1 foto tambahan untuk diunggah.";
                header("Location: edit.php?id=" . $buku_id);
                exit;
            }

            $files = $_FILES['gambar_tambahan'];
            $count = is_array($files['name']) ? count($files['name']) : 1;
            $success_count = 0;

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
                        mysqli_query($conn, "INSERT INTO buku_gambar (buku_id, url_gambar, public_id, is_primary, urutan) VALUES ('$buku_id', '$url_esc', $pid_esc, 0, 99)");
                        $success_count++;
                    }
                }
            }

            if ($success_count > 0) {
                $_SESSION['success'] = "Berhasil menambahkan foto tambahan ke galeri.";
            } else {
                $_SESSION['error'] = "Gagal mengunggah foto tambahan.";
            }

            header("Location: edit.php?id=" . $buku_id);
            exit;
        }

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

        $buku_lama = mysqli_fetch_assoc($check_query);
        $gambar_utama_url = $buku_lama['gambar'];

        if (isset($_FILES['gambar_utama']) && $_FILES['gambar_utama']['error'] === UPLOAD_ERR_OK && is_uploaded_file($_FILES['gambar_utama']['tmp_name'])) {
            $upload_res = uploadFileCloudinary($_FILES['gambar_utama'], 'buku');
            if ($upload_res['ok']) {
                $gambar_utama_url = $upload_res['url'];
                $prim_esc = mysqli_real_escape_string($conn, $gambar_utama_url);
                $pid_esc = !empty($upload_res['public_id']) ? "'" . mysqli_real_escape_string($conn, $upload_res['public_id']) . "'" : "NULL";

                mysqli_query($conn, "UPDATE buku_gambar SET is_primary = 0 WHERE buku_id = '$id'");
                mysqli_query($conn, "INSERT INTO buku_gambar (buku_id, url_gambar, public_id, is_primary, urutan) VALUES ('$id', '$prim_esc', $pid_esc, 1, 0)");
            } else {
                $_SESSION['error'] = "Gagal mengunggah foto utama: " . ($upload_res['message'] ?? '');
                header("Location: edit.php?id=" . $id);
                exit;
            }
        }

        $judul = mysqli_real_escape_string($conn, $judul);
        $penulis = mysqli_real_escape_string($conn, $penulis);
        $penerbit = mysqli_real_escape_string($conn, $penerbit);
        $isbn = mysqli_real_escape_string($conn, $isbn);
        $deskripsi = mysqli_real_escape_string($conn, $deskripsi);
        $gambar_utama_esc = mysqli_real_escape_string($conn, $gambar_utama_url);

        $query_str = "UPDATE buku SET kategori_id = '$kategori_id', judul = '$judul', penulis = '$penulis', penerbit = '$penerbit', tahun_terbit = $tahun_terbit, isbn = '$isbn', harga = '$harga', harga_diskon = $harga_diskon, stok = '$stok', berat_gram = '$berat_gram', deskripsi = '$deskripsi', gambar = '$gambar_utama_esc', updated_at = NOW() WHERE id = '$id'";
        $update = mysqli_query($conn, $query_str);

        if ($update) {
            $_SESSION['success'] = "Data buku dan foto utama berhasil diperbarui!";
            header("Location: edit.php?id=" . $id);
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
