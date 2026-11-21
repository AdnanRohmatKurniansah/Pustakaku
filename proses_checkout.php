<?php
    session_start();

    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/cart/proses_keranjang.php';
    require_once __DIR__ . '/services/midtrans_service.php';
    require_once __DIR__ . '/services/email_service.php';

    $is_ajax = (!empty($_GET['ajax']) || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false));

    if (!isset($_SESSION['user_id'])) {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'message' => 'Silakan masuk ke akun Anda terlebih dahulu.', 'redirect' => 'auth/login.php']);
            exit();
        }
        $_SESSION['error'] = 'Silakan masuk ke akun Anda terlebih dahulu.';
        header('Location: auth/login.php');
        exit();
    }

    if (($_SESSION['role'] ?? '') === 'admin') {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'message' => 'Akun admin tidak dapat melakukan pesanan.', 'redirect' => 'admin/index.php']);
            exit();
        }
        $_SESSION['error'] = 'Akun admin tidak dapat melakukan pesanan.';
        header('Location: admin/index.php');
        exit();
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'message' => 'Metode request tidak diizinkan.']);
            exit();
        }
        header('Location: keranjang.php');
        exit();
    }

    $user_id = (int) $_SESSION['user_id'];
    $q_user = mysqli_query($conn, "SELECT id, nama_lengkap, email, no_telepon FROM users WHERE id = '$user_id' LIMIT 1");
    $user_data = $q_user ? mysqli_fetch_assoc($q_user) : null;

    $alamat_id = (int) ($_POST['alamat_id'] ?? 0);
    $nama_penerima = trim($_POST['nama_penerima'] ?? '');
    $no_telepon = trim($_POST['no_telepon'] ?? '');
    $alamat_pengiriman = trim($_POST['alamat_pengiriman'] ?? '');
    $kurir_service = trim($_POST['kurir_service'] ?? 'Kurir Reguler');
    $ongkos_kirim = (float) ($_POST['ongkos_kirim'] ?? 0);
    $metode_pembayaran = trim($_POST['metode_pembayaran'] ?? 'Transfer Bank / QRIS');
    $catatan_pembeli = trim($_POST['catatan_pembeli'] ?? '');

    if ($alamat_id <= 0) {
        $q_al_def = mysqli_query($conn, "SELECT id FROM user_alamat WHERE user_id = '$user_id' ORDER BY is_primary DESC, id ASC LIMIT 1");
        if ($q_al_def && $r_def = mysqli_fetch_assoc($q_al_def)) {
            $alamat_id = (int)$r_def['id'];
        }
    }

    if ($alamat_id <= 0) {
        $err_msg = 'Silakan tambahkan alamat pengiriman terlebih dahulu.';
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'message' => $err_msg]);
            exit();
        }
        $_SESSION['error'] = $err_msg;
        header('Location: checkout.php');
        exit();
    }

    $q_al = mysqli_query($conn, "SELECT * FROM user_alamat WHERE id = '$alamat_id' AND user_id = '$user_id' LIMIT 1");
    if ($q_al && mysqli_num_rows($q_al) > 0) {
        $al_row = mysqli_fetch_assoc($q_al);
        $nama_penerima = $al_row['nama_penerima'];
        $no_telepon = $al_row['no_telepon'];
        $kec_txt = !empty($al_row['kecamatan']) ? 'Kec. ' . $al_row['kecamatan'] . ', ' : '';
        $alamat_pengiriman = $al_row['alamat_lengkap'] . ', ' . $kec_txt . $al_row['kota_nama'] . ', ' . $al_row['provinsi_nama'] . ' ' . $al_row['kode_pos'];
    }

    if (empty($nama_penerima) || empty($no_telepon) || empty($alamat_pengiriman)) {
        $err_msg = 'Nama penerima, nomor telepon, dan alamat pengiriman wajib diisi!';
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'message' => $err_msg]);
            exit();
        }
        $_SESSION['error'] = $err_msg;
        header('Location: checkout.php');
        exit();
    }

    $keranjang = cart_items($conn);
    $all_items = $keranjang['items'];

    if (empty($all_items)) {
        $err_msg = 'Keranjang belanja Anda kosong.';
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'message' => $err_msg, 'redirect' => 'keranjang.php']);
            exit();
        }
        $_SESSION['error'] = $err_msg;
        header('Location: keranjang.php');
        exit();
    }

    $items = [];
    if (!empty($_SESSION['checkout_buku_ids'])) {
        foreach ($all_items as $item) {
            if (in_array((int) $item['id'], $_SESSION['checkout_buku_ids'], true)) {
                $items[] = $item;
            }
        }
    }

    if (empty($items)) {
        $items = $all_items;
    }

    foreach ($items as $item) {
        $buku_id = (int) $item['id'];
        $qty = (int) $item['qty'];
        $q_stok = mysqli_query($conn, "SELECT judul, stok FROM buku WHERE id = '$buku_id'");
        $buku_row = $q_stok ? mysqli_fetch_assoc($q_stok) : null;

        if (!$buku_row) {
            $err_msg = 'Salah satu buku yang Anda pesan tidak ditemukan.';
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['ok' => false, 'message' => $err_msg]);
                exit();
            }
            $_SESSION['error'] = $err_msg;
            header('Location: checkout.php');
            exit();
        }

        if ($qty > (int) $buku_row['stok']) {
            $err_msg = 'Stok untuk buku "' . $buku_row['judul'] . '" tidak mencukupi (sisa: ' . $buku_row['stok'] . '). Silakan sesuaikan jumlah di keranjang.';
            if ($is_ajax) {
                header('Content-Type: application/json');
                echo json_encode(['ok' => false, 'message' => $err_msg]);
                exit();
            }
            $_SESSION['error'] = $err_msg;
            header('Location: keranjang.php');
            exit();
        }
    }

    $total_normal = 0.0;
    $total_buku = 0.0;

    foreach ($items as $it) {
        $total_normal += $it['harga'] * $it['qty'];
        $total_buku += $it['satuan'] * $it['qty'];
    }

    $potongan_diskon = max(0.0, $total_normal - $total_buku);
    $total_bayar = $total_buku + $ongkos_kirim;

    $nomor_pesanan = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -5));
    $alamat_pengiriman_lengkap = $nama_penerima . " (" . $no_telepon . ")\n" . $alamat_pengiriman;

    $status_pesanan = 'menunggu_pembayaran';

    $customer_details = [
        'nama' => $nama_penerima,
        'email' => $user_data['email'] ?? 'pelanggan@pustakaku.com',
        'telepon' => $no_telepon
    ];

    $item_details = [];
    foreach ($items as $it) {
        $item_details[] = [
            'id' => (string) $it['id'],
            'price' => (int) $it['satuan'],
            'quantity' => (int) $it['qty'],
            'name' => mb_substr($it['judul'], 0, 45)
        ];
    }

    if ($ongkos_kirim > 0) {
        $item_details[] = [
            'id' => 'SHIPPING',
            'price' => (int) $ongkos_kirim,
            'quantity' => 1,
            'name' => 'Ongkir (' . mb_substr($kurir_service, 0, 30) . ')'
        ];
    }

    $snap_token = midtrans_create_snap_token($nomor_pesanan, $total_bayar, $customer_details, $item_details);

    $nomor_pesanan_esc = mysqli_real_escape_string($conn, $nomor_pesanan);
    $alamat_esc = mysqli_real_escape_string($conn, $alamat_pengiriman_lengkap);
    $metode_esc = mysqli_real_escape_string($conn, !empty($metode_pembayaran) ? $metode_pembayaran : 'Transfer Bank / QRIS');
    $kurir_esc = mysqli_real_escape_string($conn, $kurir_service);
    $catatan_esc = mysqli_real_escape_string($conn, $catatan_pembeli);
    $alamat_id_val = ($alamat_id > 0) ? "'$alamat_id'" : "NULL";
    $snap_token_val = !empty($snap_token) ? "'" . mysqli_real_escape_string($conn, $snap_token) . "'" : "NULL";

    $q_pesanan = "INSERT INTO pesanan (
        nomor_pesanan, user_id, alamat_id, voucher_id, total_buku,
        potongan_diskon, ongkos_kirim, kurir_service, total_bayar,
        status_pesanan, alamat_pengiriman, metode_pembayaran,
        snap_token, catatan_pembeli, created_at, updated_at
    ) VALUES (
        '$nomor_pesanan_esc', '$user_id', $alamat_id_val, NULL, '$total_buku',
        '$potongan_diskon', '$ongkos_kirim', '$kurir_esc', '$total_bayar',
        '$status_pesanan', '$alamat_esc', '$metode_esc',
        $snap_token_val, '$catatan_esc', NOW(), NOW()
    )";

    $insert_pesanan = mysqli_query($conn, $q_pesanan);

    if (!$insert_pesanan) {
        $err_msg = 'Terjadi kesalahan saat memproses pesanan Anda: ' . mysqli_error($conn);
        if ($is_ajax) {
            header('Content-Type: application/json');
            echo json_encode(['ok' => false, 'message' => $err_msg]);
            exit();
        }
        $_SESSION['error'] = $err_msg;
        header('Location: checkout.php');
        exit();
    }

    $pesanan_id = mysqli_insert_id($conn);

    foreach ($items as $item) {
        $buku_id = (int) $item['id'];
        $satuan = (float) $item['satuan'];
        $qty = (int) $item['qty'];
        $subtotal = $satuan * $qty;

        mysqli_query($conn, "INSERT INTO detail_pesanan (pesanan_id, buku_id, harga_satuan, jumlah, subtotal) VALUES ('$pesanan_id', '$buku_id', '$satuan', '$qty', '$subtotal')");
        mysqli_query($conn, "UPDATE buku SET stok = GREATEST(0, stok - $qty), updated_at = NOW() WHERE id = '$buku_id'");
        mysqli_query($conn, "DELETE FROM keranjang WHERE user_id = '$user_id' AND buku_id = '$buku_id'");
    }

    kirim_email_konfirmasi_pesanan($pesanan_id, $conn);

    unset($_SESSION['checkout_buku_ids']);

    $_SESSION['success'] = 'Pesanan ' . $nomor_pesanan . ' berhasil dibuat!';

    if ($is_ajax) {
        header('Content-Type: application/json');
        echo json_encode([
            'ok' => true,
            'pesanan_id' => $pesanan_id,
            'nomor_pesanan' => $nomor_pesanan,
            'snap_token' => $snap_token,
            'redirect_url' => 'pesanan_sukses.php?id=' . $pesanan_id
        ]);
        exit();
    }

    header('Location: pesanan_sukses.php?id=' . $pesanan_id);
    exit();
?>