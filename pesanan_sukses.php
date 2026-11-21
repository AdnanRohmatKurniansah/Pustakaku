<?php
    session_start();

    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/cart/proses_keranjang.php';
    require_once __DIR__ . '/services/cloudinary_service.php';
    require_once __DIR__ . '/services/midtrans_service.php';

    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = 'Silakan masuk ke akun Anda terlebih dahulu.';
        header('Location: auth/login.php');
        exit;
    }

    $user_id = (int) $_SESSION['user_id'];
    $pesanan_id = (int) ($_GET['id'] ?? 0);
    $order_number = trim($_GET['order_id'] ?? '');

    if ($pesanan_id <= 0 && !empty($order_number)) {
        $order_esc = mysqli_real_escape_string($conn, $order_number);
        $q_find = mysqli_query($conn, "SELECT id FROM pesanan WHERE nomor_pesanan = '$order_esc' AND user_id = '$user_id' LIMIT 1");
        if ($q_find && $r_find = mysqli_fetch_assoc($q_find)) {
            $pesanan_id = (int) $r_find['id'];
        }
    }

    if ($pesanan_id <= 0) {
        header('Location: index.php');
        exit;
    }

    $q_pesanan = mysqli_query($conn, "SELECT * FROM pesanan WHERE id = '$pesanan_id' AND user_id = '$user_id'");
    $pesanan = $q_pesanan ? mysqli_fetch_assoc($q_pesanan) : null;

    if (!$pesanan) {
        $_SESSION['error'] = 'Pesanan tidak ditemukan.';
        header('Location: index.php');
        exit;
    }

    $pay_type_param = $_GET['payment_type'] ?? '';
    $bank_param = $_GET['bank'] ?? '';
    if (!empty($pay_type_param)) {
        $clean_metode = format_nama_metode_pembayaran($pay_type_param, $bank_param);
        $clean_metode_esc = mysqli_real_escape_string($conn, $clean_metode);
        mysqli_query($conn, "UPDATE pesanan SET metode_pembayaran = '$clean_metode_esc', updated_at = NOW() WHERE id = '$pesanan_id'");
        $pesanan['metode_pembayaran'] = $clean_metode;
    }

    $status_param = strtolower($_GET['transaction_status'] ?? '');
    if (in_array($status_param, ['settlement', 'capture', 'success'], true) && $pesanan['status_pesanan'] === 'menunggu_pembayaran') {
        mysqli_query($conn, "UPDATE pesanan SET status_pesanan = 'diproses', updated_at = NOW() WHERE id = '$pesanan_id'");
        $pesanan['status_pesanan'] = 'diproses';
    } elseif ($pesanan['status_pesanan'] === 'menunggu_pembayaran') {
        $api_data = midtrans_get_transaction_data($pesanan['nomor_pesanan']);
        if ($api_data && is_array($api_data)) {
            $api_status = strtolower($api_data['transaction_status'] ?? '');
            $api_metode = format_nama_metode_pembayaran($api_data['payment_type'] ?? '', $api_data['bank'] ?? '', $api_data['va_numbers'] ?? []);
            $api_metode_esc = mysqli_real_escape_string($conn, $api_metode);

            if (in_array($api_status, ['settlement', 'capture', 'success'], true)) {
                mysqli_query($conn, "UPDATE pesanan SET status_pesanan = 'diproses', metode_pembayaran = '$api_metode_esc', updated_at = NOW() WHERE id = '$pesanan_id'");
                $pesanan['status_pesanan'] = 'diproses';
                $pesanan['metode_pembayaran'] = $api_metode;
            } elseif (!empty($api_data['payment_type'])) {
                mysqli_query($conn, "UPDATE pesanan SET metode_pembayaran = '$api_metode_esc', updated_at = NOW() WHERE id = '$pesanan_id'");
                $pesanan['metode_pembayaran'] = $api_metode;
            }
        }
    }

    $q_items = mysqli_query($conn, "SELECT dp.*, b.judul, b.penulis, b.gambar FROM detail_pesanan dp JOIN buku b ON dp.buku_id = b.id WHERE dp.pesanan_id = '$pesanan_id'");
    $items = [];
    while ($row = mysqli_fetch_assoc($q_items)) {
        $items[] = $row;
    }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Berhasil - PustakaKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/checkout_custom.css">
    <link rel="icon" href="assets/images/favicon.png" type="image/png">
</head>
<body class="bg-light">
    <?php include(__DIR__ . '/layouts/navbar.php'); ?>

    <main class="container my-3 my-md-4">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="index.php" class="text-success text-decoration-none">Beranda</a></li>
                <li class="breadcrumb-item"><a href="akun.php?tab=pesanan" class="text-success text-decoration-none">Akun Saya</a></li>
                <li class="breadcrumb-item active" aria-current="page">Riwayat Pesanan</li>
            </ol>
        </nav>

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <div class="row justify-content-center">
            <div class="col-12 col-lg-12">
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4 bg-white">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pb-3 border-bottom mb-3">
                        <div>
                            <h5 class="fw-bold text-dark mb-0">Rincian & Status Pesanan</h5>
                            <div class="text-muted small">Waktu Pemesanan: <?= date('d M Y, H:i', strtotime($pesanan['created_at'])); ?> WIB</div>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-light border rounded-pill px-3 py-1 small">
                                <span class="text-muted small">No. Pesanan:</span>
                                <strong class="text-dark font-monospace"><?= htmlspecialchars($pesanan['nomor_pesanan']); ?></strong>
                            </div>
                            <?= status_pesanan_badge($pesanan['status_pesanan']); ?>
                        </div>
                    </div>

                    <?php if ($pesanan['status_pesanan'] === 'menunggu_pembayaran'): ?>
                        <div class="alert alert-warning border-warning-subtle rounded-4 d-flex align-items-center gap-3 p-3 mb-4">
                            <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                                <i class="bi bi-clock-history fs-4"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark mb-1">Pesanan Dibuat - Menunggu Konfirmasi Pembayaran</div>
                                <div class="text-secondary small">Total tagihan Anda adalah <strong><?= formatRupiah($pesanan['total_bayar']); ?></strong>. Sistem kami secara otomatis memperbarui status pesanan Anda begitu pembayaran terverifikasi.</div>
                            </div>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success border-success-subtle rounded-4 d-flex align-items-center gap-3 p-3 mb-4">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                                <i class="bi bi-check2-circle fs-4"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-success mb-1">Pembayaran Berhasil! Pesanan Sedang Diproses Penjual</div>
                                <div class="text-secondary small">Pembayaran Anda sebesar <strong><?= formatRupiah($pesanan['total_bayar']); ?></strong> melalui <strong><?= htmlspecialchars($pesanan['metode_pembayaran']); ?></strong> telah kami terima. Penjual sedang menyiapkan pesanan Anda.</div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="p-3 rounded-4 bg-light border mb-4">
                        <div class="row g-2">
                            <div class="col-12 col-sm-6 col-md-3">
                                <span class="text-secondary small d-block">Metode Pembayaran:</span>
                                <strong class="text-dark small"><i class="bi bi-wallet2 me-1 text-success"></i><?= htmlspecialchars($pesanan['metode_pembayaran']); ?></strong>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <span class="text-secondary small d-block">Layanan Pengiriman:</span>
                                <strong class="text-dark small"><i class="bi bi-truck me-1 text-primary"></i><?= htmlspecialchars($pesanan['kurir_service'] ?? 'Kurir Reguler'); ?></strong>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <span class="text-secondary small d-block">Total Pembayaran:</span>
                                <strong class="text-success fs-6"><?= formatRupiah($pesanan['total_bayar']); ?></strong>
                            </div>
                            <div class="col-12 col-sm-6 col-md-3">
                                <span class="text-secondary small d-block">Status Pesanan:</span>
                                <div><?= status_pesanan_badge($pesanan['status_pesanan']); ?></div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-geo-alt-fill text-success me-2"></i>Alamat Pengiriman</h6>
                        <div class="bg-light border rounded-3 p-3 small text-dark">
                            <?= nl2br(htmlspecialchars($pesanan['alamat_pengiriman'])); ?>
                            <?php if (!empty($pesanan['catatan_pembeli'])): ?>
                                <div class="mt-2 pt-2 border-top text-muted">
                                    <strong>Catatan:</strong> <?= htmlspecialchars($pesanan['catatan_pembeli']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-book-half text-success me-2"></i>Daftar Buku Dipesan</h6>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle small mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Buku</th>
                                        <th class="text-center" style="width: 100px;">Jumlah</th>
                                        <th class="text-end" style="width: 140px;">Harga Satuan</th>
                                        <th class="text-end" style="width: 140px;">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($items as $it): ?>
                                        <?php $img_url = resolveImageUrl($it['gambar'], 'buku'); ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="<?= htmlspecialchars($img_url); ?>" alt="<?= htmlspecialchars($it['judul']); ?>" class="rounded border object-fit-cover" style="width: 40px; height: 55px;" onerror="this.onerror=null;this.src='assets/images/placeholder.png';">
                                                    <div>
                                                        <div class="fw-semibold text-dark"><?= htmlspecialchars($it['judul']); ?></div>
                                                        <div class="text-muted" style="font-size: 11px;"><?= htmlspecialchars($it['penulis']); ?></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="text-center"><?= (int) $it['jumlah']; ?></td>
                                            <td class="text-end"><?= formatRupiah($it['harga_satuan']); ?></td>
                                            <td class="text-end fw-semibold text-success"><?= formatRupiah($it['subtotal']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" class="text-end text-muted">Subtotal Buku:</td>
                                        <td class="text-end fw-semibold"><?= formatRupiah($pesanan['total_buku']); ?></td>
                                    </tr>
                                    <?php if ((float)$pesanan['potongan_diskon'] > 0): ?>
                                        <tr>
                                            <td colspan="3" class="text-end text-success">Diskon:</td>
                                            <td class="text-end text-success fw-semibold">-<?= formatRupiah($pesanan['potongan_diskon']); ?></td>
                                        </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td colspan="3" class="text-end text-muted">Ongkos Kirim (<?= htmlspecialchars($pesanan['kurir_service'] ?? 'Kurir'); ?>):</td>
                                        <td class="text-end fw-semibold"><?= ((float)$pesanan['ongkos_kirim'] == 0) ? 'GRATIS' : formatRupiah($pesanan['ongkos_kirim']); ?></td>
                                    </tr>
                                    <tr class="table-light">
                                        <td colspan="3" class="text-end fw-bold text-dark fs-6">Total Pembayaran:</td>
                                        <td class="text-end fw-bold text-success fs-5"><?= formatRupiah($pesanan['total_bayar']); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap gap-2 justify-content-between pt-2">
                        <a href="katalog.php" class="btn btn-outline-success rounded-pill px-4 fw-semibold"><i class="bi bi-grid me-1"></i>Belanja Lagi</a>
                        <a href="akun.php?tab=pesanan" class="btn btn-success rounded-pill px-4 fw-semibold"><i class="bi bi-receipt me-1"></i>Lihat Pesanan Saya</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include(__DIR__ . '/layouts/footer.php'); ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>