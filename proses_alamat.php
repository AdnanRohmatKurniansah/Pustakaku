<?php
    session_start();
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/services/rajaongkir_service.php';

    $user_id = (int)($_SESSION['user_id'] ?? 0);
    if ($user_id <= 0) {
        header("Location: auth/login.php");
        exit();
    }

    $aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';

    if ($aksi === 'set_primary') {
        $alamat_id = (int)($_GET['id'] ?? 0);
        if ($alamat_id > 0) {
            mysqli_query($conn, "UPDATE user_alamat SET is_primary = 0 WHERE user_id = '$user_id'");
            mysqli_query($conn, "UPDATE user_alamat SET is_primary = 1, updated_at = NOW() WHERE id = '$alamat_id' AND user_id = '$user_id'");
            $_SESSION['success'] = "Alamat utama berhasil diubah.";
        }
        $redirect = $_GET['redirect'] ?? 'akun.php?tab=alamat';
        header("Location: " . $redirect);
        exit();
    }

    if ($aksi === 'hapus') {
        $alamat_id = (int)($_GET['id'] ?? 0);
        if ($alamat_id > 0) {
            $q_check = mysqli_query($conn, "SELECT is_primary FROM user_alamat WHERE id = '$alamat_id' AND user_id = '$user_id' LIMIT 1");
            $was_primary = false;
            if ($q_check && mysqli_num_rows($q_check) > 0) {
                $was_primary = (mysqli_fetch_assoc($q_check)['is_primary'] == 1);
            }

            mysqli_query($conn, "DELETE FROM user_alamat WHERE id = '$alamat_id' AND user_id = '$user_id'");

            if ($was_primary) {
                $q_next = mysqli_query($conn, "SELECT id FROM user_alamat WHERE user_id = '$user_id' ORDER BY id ASC LIMIT 1");
                if ($q_next && mysqli_num_rows($q_next) > 0) {
                    $next_id = mysqli_fetch_assoc($q_next)['id'];
                    mysqli_query($conn, "UPDATE user_alamat SET is_primary = 1 WHERE id = '$next_id'");
                }
            }
            $_SESSION['success'] = "Alamat berhasil dihapus.";
        }
        header("Location: akun.php?tab=alamat");
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $aksi === 'tambah') {
        $label = mysqli_real_escape_string($conn, trim($_POST['label_alamat'] ?? 'Alamat'));
        $nama = mysqli_real_escape_string($conn, trim($_POST['nama_penerima'] ?? ''));
        $telepon = mysqli_real_escape_string($conn, trim($_POST['no_telepon'] ?? ''));
        $provinsi_id = (int)($_POST['provinsi_id'] ?? 0);
        $provinsi_nama = mysqli_real_escape_string($conn, trim($_POST['provinsi_nama'] ?? ''));
        $kota_id = (int)($_POST['kota_id'] ?? 0);
        $kota_nama = mysqli_real_escape_string($conn, trim($_POST['kota_nama'] ?? ''));
        $kecamatan = mysqli_real_escape_string($conn, trim($_POST['kecamatan'] ?? ''));
        $kode_pos = mysqli_real_escape_string($conn, trim($_POST['kode_pos'] ?? ''));
        $alamat = mysqli_real_escape_string($conn, trim($_POST['alamat_lengkap'] ?? ''));
        $is_primary = isset($_POST['is_primary']) ? 1 : 0;

        if (empty($nama) || empty($telepon) || empty($alamat) || $kota_id <= 0) {
            $_SESSION['error'] = "Mohon lengkapi semua data wajib pada formulir alamat.";
            header("Location: akun.php?tab=alamat");
            exit();
        }

        $q_count = mysqli_query($conn, "SELECT COUNT(*) AS total FROM user_alamat WHERE user_id = '$user_id'");
        $total_alamat = mysqli_fetch_assoc($q_count)['total'] ?? 0;
        if ($total_alamat == 0) {
            $is_primary = 1;
        }

        if ($is_primary == 1) {
            mysqli_query($conn, "UPDATE user_alamat SET is_primary = 0 WHERE user_id = '$user_id'");
        }

        $q_insert = "INSERT INTO user_alamat (user_id, label_alamat, nama_penerima, no_telepon, provinsi_id, provinsi_nama, kota_id, kota_nama, kecamatan, kode_pos, alamat_lengkap, is_primary) 
                    VALUES ('$user_id', '$label', '$nama', '$telepon', '$provinsi_id', '$provinsi_nama', '$kota_id', '$kota_nama', '$kecamatan', '$kode_pos', '$alamat', '$is_primary')";

        if (mysqli_query($conn, $q_insert)) {
            if ($is_primary == 1) {
                mysqli_query($conn, "UPDATE users SET no_telepon = COALESCE(no_telepon, '$telepon') WHERE id = '$user_id'");
            }
            $_SESSION['success'] = "Alamat baru berhasil ditambahkan.";
        } else {
            $_SESSION['error'] = "Gagal menyimpan alamat: " . mysqli_error($conn);
        }

        $redirect = $_POST['redirect'] ?? 'akun.php?tab=alamat';
        header("Location: " . $redirect);
        exit();
    }

    header("Location: akun.php");
    exit();
?>
