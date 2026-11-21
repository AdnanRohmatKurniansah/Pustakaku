<?php
    session_start();
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/cart/proses_keranjang.php';
    require_once __DIR__ . '/services/cloudinary_service.php';
    require_once __DIR__ . '/services/rajaongkir_service.php';

    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = "Silakan masuk terlebih dahulu untuk mengakses akun Anda.";
        $_SESSION['redirect_after_login'] = '../akun.php';
        header("Location: auth/login.php");
        exit;
    }

    $user_id = (int) $_SESSION['user_id'];

    $user = null;
    $q_user = mysqli_query($conn, "SELECT id, nama_lengkap, email, role, no_telepon, foto_profil, created_at FROM users WHERE id = '$user_id' LIMIT 1");
    $user = $q_user ? mysqli_fetch_assoc($q_user) : null;

    if (!$user) {
        session_destroy();
        header("Location: auth/login.php");
        exit;
    }

    $cart_qty = cart_count($conn);
    $foto_profil_url = resolveImageUrl($user['foto_profil'] ?? '', 'user');
    $has_photo = !empty($user['foto_profil']) && $foto_profil_url !== 'assets/images/placeholder.png';
    $initials = strtoupper(substr($user['nama_lengkap'] ?? 'U', 0, 1));
    $joined_date = !empty($user['created_at']) ? date('d M Y', strtotime($user['created_at'])) : '-';

    $q_my_orders = mysqli_query($conn, "SELECT p.*, (SELECT COUNT(*) FROM detail_pesanan dp WHERE dp.pesanan_id = p.id) AS total_item FROM pesanan p WHERE p.user_id = '$user_id' ORDER BY p.created_at DESC");
    $orders = [];
    if ($q_my_orders) {
        while ($r_ord = mysqli_fetch_assoc($q_my_orders)) {
            $orders[] = $r_ord;
        }
    }
    $total_my_orders = count($orders);

    $q_alamat = mysqli_query($conn, "SELECT * FROM user_alamat WHERE user_id = '$user_id' ORDER BY is_primary DESC, id ASC");
    $alamat_list = [];
    if ($q_alamat) {
        while ($r_al = mysqli_fetch_assoc($q_alamat)) {
            $alamat_list[] = $r_al;
        }
    }

    $alamat_utama = null;
    foreach ($alamat_list as $al) {
        if ((int) $al['is_primary'] === 1) {
            $alamat_utama = $al;
            break;
        }
    }
    if (!$alamat_utama && !empty($alamat_list)) {
        $alamat_utama = $alamat_list[0];
    }

    $total_belanja = 0;
    $total_item_dibeli = 0;
    $total_ulasan = 0;

    $q_sum_belanja = mysqli_query($conn, "SELECT COALESCE(SUM(total_bayar), 0) AS total FROM pesanan WHERE user_id = '$user_id' AND status_pesanan NOT IN ('menunggu_pembayaran', 'dibatalkan')");
    if ($q_sum_belanja && $r_sum = mysqli_fetch_assoc($q_sum_belanja)) {
        $total_belanja = (float) $r_sum['total'];
    }

    $q_sum_item = mysqli_query($conn, "SELECT COALESCE(SUM(dp.jumlah), 0) AS qty FROM detail_pesanan dp JOIN pesanan p ON dp.pesanan_id = p.id WHERE p.user_id = '$user_id' AND p.status_pesanan NOT IN ('menunggu_pembayaran', 'dibatalkan')");
    if ($q_sum_item && $r_item = mysqli_fetch_assoc($q_sum_item)) {
        $total_item_dibeli = (int) $r_item['qty'];
    }

    $q_sum_ulasan = mysqli_query($conn, "SELECT COUNT(*) AS jml FROM ulasan WHERE user_id = '$user_id'");
    if ($q_sum_ulasan && $r_ul = mysqli_fetch_assoc($q_sum_ulasan)) {
        $total_ulasan = (int) $r_ul['jml'];
    }

    $total_pesanan_selesai   = 0;
    $total_pesanan_proses    = 0;
    $total_pesanan_menunggu  = 0;
    foreach ($orders as $ord) {
        if ($ord['status_pesanan'] === 'selesai') {
            $total_pesanan_selesai++;
        } elseif (in_array($ord['status_pesanan'], ['diproses', 'dikirim'], true)) {
            $total_pesanan_proses++;
        } elseif (in_array($ord['status_pesanan'], ['menunggu_pembayaran', 'menunggu_verifikasi'], true)) {
            $total_pesanan_menunggu++;
        }
    }

    $provinces = rajaongkir_get_provinces();
    $active_tab = $_GET['tab'] ?? 'pesanan';
    $valid_tabs = ['biodata', 'alamat', 'pembayaran', 'pesanan', 'manajemen'];
    if (!in_array($active_tab, $valid_tabs, true)) {
        $active_tab = 'pesanan';
    }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Saya - PustakaKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css?v=<?= @filemtime(__DIR__ . '/assets/css/style.css') ?: time(); ?>">
    <link rel="stylesheet" href="assets/css/checkout_custom.css?v=<?= @filemtime(__DIR__ . '/assets/css/checkout_custom.css') ?: time(); ?>">
    <link rel="icon" href="assets/images/favicon.png" type="image/png">
</head>
<body class="bg-light">
    <?php include(__DIR__ . '/layouts/navbar.php'); ?>

    <main class="container py-4 my-md-2">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="index.php" class="text-success text-decoration-none">Beranda</a></li>
                <li class="breadcrumb-item active" aria-current="page">Akun Saya</li>
            </ol>
        </nav>

        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <div class="row g-4">
            <div class="col-12 col-lg-4 col-xl-3">
                <aside class="account-side">
                    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                        <!-- Identitas -->
                        <div class="p-3 d-flex align-items-center gap-3 border-bottom">
                            <?php if ($has_photo): ?>
                                <img src="<?= htmlspecialchars($foto_profil_url); ?>" alt="<?= htmlspecialchars($user['nama_lengkap']); ?>" class="account-side-avatar-sm flex-shrink-0" onerror="this.onerror=null;this.src='assets/images/placeholder.png';">
                            <?php else: ?>
                                <div class="account-side-avatar-sm flex-shrink-0"><?= $initials; ?></div>
                            <?php endif; ?>
                            <div class="min-w-0">
                                <div class="fw-bold text-dark text-truncate"><?= htmlspecialchars($user['nama_lengkap']); ?></div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 mt-1" style="font-size: 10px;">
                                    <i class="bi bi-patch-check-fill me-1"></i><?= !empty($user['no_telepon']) ? htmlspecialchars($user['no_telepon']) : 'Lengkapi Nomor HP'; ?>
                                </span>
                            </div>
                        </div>
                        <div class="p-3 border-bottom">
                            <div class="row g-2">
                                <div class="col-6">
                                    <a href="akun.php?tab=pesanan" class="account-stat">
                                        <span class="stat-value"><?= $total_my_orders; ?></span>
                                        <span class="stat-label">Pesanan</span>
                                    </a>
                                </div>
                                
                                <div class="col-6">
                                    <a href="akun.php?tab=alamat" class="account-stat">
                                        <span class="stat-value"><?= count($alamat_list); ?></span>
                                        <span class="stat-label">Alamat</span>
                                    </a>
                                </div>
                                <div class="col-6">
                                    <a href="keranjang.php" class="account-stat">
                                        <span class="stat-value"><?= $cart_qty; ?></span>
                                        <span class="stat-label">Keranjang</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="p-3 border-bottom">
                            <div class="account-info-row pt-0">
                                <span><i class="bi bi-geo-alt me-2 text-success"></i>Alamat Utama</span>
                                <span class="text-truncate" style="max-width: 120px;">
                                    <?= $alamat_utama ? htmlspecialchars($alamat_utama['kota_nama']) . ', ' . htmlspecialchars($alamat_utama['provinsi_nama']) : '-'; ?>
                                </span>
                            </div>
                            <div class="account-info-row">
                                <span><i class="bi bi-book me-2 text-success"></i>Buku Dibeli</span>
                                <span><?= $total_item_dibeli; ?> eksemplar</span>
                            </div>
                            <div class="account-info-row">
                                <span><i class="bi bi-chat-square-text me-2 text-success"></i>Ulasan</span>
                                <span><?= $total_ulasan; ?></span>
                            </div>
                            <div class="account-info-row">
                                <span><i class="bi bi-calendar3 me-2 text-success"></i>Bergabung</span>
                                <span><?= $joined_date; ?></span>
                            </div>
                        </div>
                        <?php if ($total_my_orders > 0): ?>
                            <div class="p-3 d-flex flex-wrap gap-2 border-bottom">
                                <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1"><i class="bi bi-check-circle-fill me-1"></i><?= $total_pesanan_selesai; ?> Selesai</span>
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-1"><i class="bi bi-box-seam me-1"></i><?= $total_pesanan_proses; ?> Diproses</span>
                                <span class="badge bg-warning-subtle text-warning rounded-pill px-2 py-1"><i class="bi bi-clock-history me-1"></i><?= $total_pesanan_menunggu; ?> Menunggu</span>
                            </div>
                        <?php endif; ?>
                        <div class="p-3 d-grid gap-2">
                            <a href="keranjang.php" class="btn btn-outline-success rounded-pill btn-sm py-2 fw-semibold d-flex align-items-center justify-content-between px-3">
                                <span><i class="bi bi-bag-fill me-2"></i>Keranjang Belanja</span>
                                <span class="badge bg-success rounded-pill"><?= $cart_qty; ?></span>
                            </a>
                            <?php if ($user['role'] === 'admin'): ?>
                                <a href="admin/index.php" class="btn btn-success rounded-pill btn-sm py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                                    <i class="bi bi-speedometer2"></i>
                                    <span>Panel Administrator</span>
                                </a>
                            <?php endif; ?>
                            <a href="auth/logout.php" class="btn btn-outline-danger rounded-pill btn-sm py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-box-arrow-right"></i>
                                <span>Keluar</span>
                            </a>
                        </div>
                    </div>
                </aside>
            </div>

            <div class="col-12 col-lg-8 col-xl-9">

                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                    <ul class="nav account-tabs" id="accountTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link account-tab-link <?= ($active_tab === 'biodata') ? 'active' : ''; ?>" id="tab-biodata-btn" data-bs-toggle="pill" data-bs-target="#tab-biodata" type="button" role="tab" aria-controls="tab-biodata" aria-selected="<?= ($active_tab === 'biodata') ? 'true' : 'false'; ?>">Biodata Diri</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link account-tab-link <?= ($active_tab === 'alamat') ? 'active' : ''; ?>" id="tab-alamat-btn" data-bs-toggle="pill" data-bs-target="#tab-alamat" type="button" role="tab" aria-controls="tab-alamat" aria-selected="<?= ($active_tab === 'alamat') ? 'true' : 'false'; ?>">Daftar Alamat</button>
                        </li>
                        
                        <li class="nav-item" role="presentation">
                            <button class="nav-link account-tab-link <?= ($active_tab === 'pesanan') ? 'active' : ''; ?>" id="tab-pesanan-btn" data-bs-toggle="pill" data-bs-target="#tab-pesanan" type="button" role="tab" aria-controls="tab-pesanan" aria-selected="<?= ($active_tab === 'pesanan') ? 'true' : 'false'; ?>">Riwayat Pesanan</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link account-tab-link <?= ($active_tab === 'manajemen') ? 'active' : ''; ?>" id="tab-manajemen-btn" data-bs-toggle="pill" data-bs-target="#tab-manajemen" type="button" role="tab" aria-controls="tab-manajemen" aria-selected="<?= ($active_tab === 'manajemen') ? 'true' : 'false'; ?>">Keamanan</button>
                        </li>
                    </ul>

                    <div class="tab-content p-3 p-md-4" id="accountTabsContent">
                    <div class="tab-pane fade <?= ($active_tab === 'pesanan') ? 'show active' : ''; ?>" id="tab-pesanan" role="tabpanel" aria-labelledby="tab-pesanan-btn" tabindex="0">
                        <div class="account-pane-block">
                            <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-4">
                                <div>
                                    <h5 class="fw-bold text-dark mb-1"><i class="bi bi-clock-history text-success me-2"></i>Riwayat Pesanan Saya</h5>
                                    <p class="text-muted small mb-0">Daftar transaksi pembelian buku yang pernah Anda lakukan</p>
                                </div>
                                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 fw-semibold"><?= $total_my_orders; ?> Pesanan</span>
                            </div>

                            <?php if (empty($orders)): ?>
                                <div class="text-center py-5">
                                    <div class="rounded-circle bg-light text-muted d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                                        <i class="bi bi-receipt" style="font-size: 2rem;"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">Belum Ada Riwayat Pesanan</h6>
                                    <p class="text-muted small mb-3">Anda belum pernah membuat pesanan di PustakaKu. Jelajahi katalog buku pilihan kami sekarang!</p>
                                    <a href="katalog.php" class="btn btn-success btn-sm rounded-pill px-4 fw-semibold"><i class="bi bi-grid me-1"></i>Mulai Berbelanja</a>
                                </div>
                            <?php else: ?>
                                <div class="d-flex flex-column gap-3">
                                    <?php foreach ($orders as $ord): ?>
                                        <div class="border rounded-4 p-3 bg-white">
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pb-2 border-bottom">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="fw-bold text-dark font-monospace small"><?= htmlspecialchars($ord['nomor_pesanan']); ?></span>
                                                    <span class="text-muted" style="font-size: 11px;">&bull; <?= date('d M Y, H:i', strtotime($ord['created_at'])); ?> WIB</span>
                                                </div>
                                                <div><?= status_pesanan_badge($ord['status_pesanan']); ?></div>
                                            </div>

                                            <div class="row align-items-center py-3 g-2">
                                                <div class="col-12 col-md-7">
                                                    <div class="small text-muted mb-1"><i class="bi bi-book me-1 text-success"></i><strong><?= (int) $ord['total_item']; ?></strong> jenis buku dipesan</div>
                                                    <div class="small text-muted mb-1"><i class="bi bi-wallet2 me-1 text-success"></i>Metode: <?= htmlspecialchars($ord['metode_pembayaran']); ?></div>
                                                    <?php if (!empty($ord['nomor_resi'])): ?>
                                                        <div class="small text-dark mt-1"><i class="bi bi-truck me-1 text-primary"></i>No. Resi: <strong class="font-monospace text-primary"><?= htmlspecialchars($ord['nomor_resi']); ?></strong></div>
                                                    <?php endif; ?>
                                                </div>

                                                <div class="col-12 col-md-5 text-md-end">
                                                    <div class="small text-muted">Total Pembayaran</div>
                                                    <div class="fw-bold text-success fs-5"><?= formatRupiah($ord['total_bayar']); ?></div>
                                                </div>
                                            </div>

                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pt-2 border-top">
                                                <a href="pesanan_sukses.php?id=<?= (int)$ord['id']; ?>" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold"><i class="bi bi-file-earmark-text me-1"></i>Lihat Detail & Invoice</a>
                                                <?php if ($ord['status_pesanan'] === 'dikirim'): ?>
                                                    <form action="proses_akun.php" method="POST" class="m-0" onsubmit="return confirm('Apakah pesanan ini sudah Anda terima dengan baik?');">
                                                        <input type="hidden" name="aksi" value="selesaikan_pesanan">
                                                        <input type="hidden" name="pesanan_id" value="<?= (int)$ord['id']; ?>">
                                                        <button type="submit" class="btn btn-success btn-sm rounded-pill px-3 fw-semibold"><i class="bi bi-check2-circle me-1"></i>Konfirmasi Pesanan Diterima</button>
                                                    </form>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="tab-pane fade <?= ($active_tab === 'alamat') ? 'show active' : ''; ?>" id="tab-alamat" role="tabpanel" aria-labelledby="tab-alamat-btn" tabindex="0">
                        <div class="account-pane-block">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pb-3 border-bottom mb-4">
                                <div>
                                    <h5 class="fw-bold text-dark mb-1"><i class="bi bi-geo-alt-fill text-success me-2"></i>Daftar Alamat Pengiriman</h5>
                                    <p class="text-muted small mb-0">Kelola buku alamat pengiriman untuk mempercepat proses checkout Anda</p>
                                </div>
                                <button type="button" class="btn btn-success rounded-pill btn-sm px-3 fw-semibold d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalTambahAlamat">
                                    <i class="bi bi-plus-circle"></i>
                                    <span>Tambah Alamat Baru</span>
                                </button>
                            </div>

                            <?php if (empty($alamat_list)): ?>
                                <div class="text-center py-5 bg-light rounded-4 border">
                                    <div class="rounded-circle bg-white text-muted d-inline-flex align-items-center justify-content-center mx-auto mb-3 shadow-sm" style="width: 60px; height: 60px;">
                                        <i class="bi bi-geo-alt" style="font-size: 1.8rem;"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">Belum Ada Alamat Tersimpan</h6>
                                    <p class="text-muted small mb-3">Tambahkan alamat pengiriman Anda untuk memudahkan proses pemesanan buku.</p>
                                    <button type="button" class="btn btn-success btn-sm rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahAlamat">
                                        <i class="bi bi-plus-lg me-1"></i>Tambah Alamat Pertama
                                    </button>
                                </div>
                            <?php else: ?>
                                <div class="d-flex flex-column gap-3">
                                    <?php foreach ($alamat_list as $al): ?>
                                        <div class="border rounded-4 p-3 <?= ($al['is_primary'] == 1) ? 'border-success bg-success-subtle bg-opacity-10' : 'bg-white'; ?>">
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="badge <?= ($al['is_primary'] == 1) ? 'bg-success' : 'bg-secondary'; ?> rounded-pill px-3 py-1 fw-bold"><?= htmlspecialchars($al['label_alamat']); ?></span>
                                                    <?php if ($al['is_primary'] == 1): ?>
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1 small"><i class="bi bi-check-circle-fill me-1"></i>Alamat Utama</span>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <?php if ($al['is_primary'] != 1): ?>
                                                        <a href="proses_alamat.php?aksi=set_primary&id=<?= (int)$al['id']; ?>&redirect=akun.php%3Ftab%3Dalamat" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 small">
                                                            Jadikan Utama
                                                        </a>
                                                    <?php endif; ?>
                                                    <a href="proses_alamat.php?aksi=hapus&id=<?= (int)$al['id']; ?>&redirect=akun.php%3Ftab%3Dalamat" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 small" onclick="return confirm('Apakah Anda yakin ingin menghapus alamat ini?');">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
                                            </div>
                                            <div class="fw-bold text-dark"><?= htmlspecialchars($al['nama_penerima']); ?> <span class="text-muted fw-normal small">(<?= htmlspecialchars($al['no_telepon']); ?>)</span></div>
                                            <div class="text-secondary small mt-1"><?= htmlspecialchars($al['alamat_lengkap']); ?></div>
                                            <div class="text-muted small mt-1"><?= htmlspecialchars($al['kecamatan'] ?? ''); ?><?= !empty($al['kecamatan']) ? ', ' : ''; ?><?= htmlspecialchars($al['kota_nama']); ?>, <?= htmlspecialchars($al['provinsi_nama']); ?> <?= htmlspecialchars($al['kode_pos'] ?? ''); ?></div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="tab-pane fade <?= ($active_tab === 'biodata') ? 'show active' : ''; ?>" id="tab-biodata" role="tabpanel" aria-labelledby="tab-biodata-btn" tabindex="0">
                        <div class="account-pane-block">
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 pb-3 border-bottom mb-4">
                                <div>
                                    <h5 class="fw-bold text-dark mb-1"><i class="bi bi-person-vcard text-success me-2"></i>Biodata Saya</h5>
                                    <p class="text-muted small mb-0">Ringkasan data pribadi yang terdaftar pada akun PustakaKu Anda</p>
                                </div>
                                <button type="button" class="btn btn-success rounded-pill btn-sm px-3 fw-semibold d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#modalUbahProfil">
                                    <i class="bi bi-pencil-square"></i>
                                    <span>Ubah Data</span>
                                </button>
                            </div>

                            <div class="d-flex align-items-center gap-3 p-3 rounded-4 bg-light border mb-4">
                                <?php if ($has_photo): ?>
                                    <img src="<?= htmlspecialchars($foto_profil_url); ?>" alt="<?= htmlspecialchars($user['nama_lengkap']); ?>" class="rounded-circle object-fit-cover border border-3 border-success-subtle shadow-sm flex-shrink-0" style="width: 64px; height: 64px;" onerror="this.onerror=null;this.src='assets/images/placeholder.png';">
                                <?php else: ?>
                                    <div class="rounded-circle bg-success text-white fw-bold d-flex align-items-center justify-content-center border border-3 border-success-subtle shadow-sm flex-shrink-0" style="width: 64px; height: 64px; font-size: 1.5rem;"><?= $initials; ?></div>
                                <?php endif; ?>
                                <div class="min-w-0">
                                    <div class="fw-bold text-dark text-truncate"><?= htmlspecialchars($user['nama_lengkap']); ?></div>
                                    <div class="text-muted small text-truncate"><?= htmlspecialchars($user['email']); ?></div>
                                    <span class="badge <?= ($user['role'] === 'admin') ? 'bg-danger-subtle text-danger' : 'bg-success-subtle text-success'; ?> rounded-pill px-2 py-1 mt-1" style="font-size: 10px;">
                                        <?= ($user['role'] === 'admin') ? 'Administrator' : 'Pelanggan'; ?>
                                    </span>
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-12 col-md-6">
                                    <div class="biodata-item h-100">
                                        <div class="biodata-label">Nama Lengkap</div>
                                        <div class="biodata-value"><?= htmlspecialchars($user['nama_lengkap']); ?></div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="biodata-item h-100">
                                        <div class="biodata-label">Alamat Email</div>
                                        <div class="biodata-value"><?= htmlspecialchars($user['email']); ?>
                                            <span class="badge bg-success-subtle text-success rounded-pill ms-1" style="font-size: 9px;"><i class="bi bi-patch-check-fill me-1"></i>Terverifikasi</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="biodata-item h-100">
                                        <div class="biodata-label">Nomor Telepon / WhatsApp</div>
                                        <div class="biodata-value"><?= !empty($user['no_telepon']) ? htmlspecialchars($user['no_telepon']) : '-'; ?></div>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6">
                                    <div class="biodata-item h-100">
                                        <div class="biodata-label">Tanggal Bergabung</div>
                                        <div class="biodata-value"><?= $joined_date; ?></div>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="biodata-item h-100">
                                        <div class="biodata-label">Alamat Lengkap</div>
                                        <div class="biodata-value fw-normal">
                                            <?= !empty($user['alamat_lengkap']) ? nl2br(htmlspecialchars($user['alamat_lengkap'])) : '<span class="text-muted fst-italic">Belum diisi</span>'; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row g-2 mt-4 pt-3 border-top">
                                <div class="col-6 col-md-3">
                                    <div class="account-stat">
                                        <span class="stat-value"><?= $total_my_orders; ?></span>
                                        <span class="stat-label">Total Pesanan</span>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="account-stat">
                                        <span class="stat-value"><?= $total_ulasan; ?></span>
                                        <span class="stat-label">Ulasan</span>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="account-stat">
                                        <span class="stat-value"><?= $total_item_dibeli; ?></span>
                                        <span class="stat-label">Buku Dibeli</span>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="account-stat">
                                        <span class="stat-value" style="font-size: 0.72rem; padding-top: 3px;"><?= formatRupiah($total_belanja); ?></span>
                                        <span class="stat-label">Total Belanja</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tab-pane fade <?= ($active_tab === 'manajemen') ? 'show active' : ''; ?>" id="tab-manajemen" role="tabpanel" aria-labelledby="tab-manajemen-btn" tabindex="0">
                        <div class="account-pane-block">
                            <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-4">
                                <div>
                                    <h5 class="fw-bold text-dark mb-1"><i class="bi bi-shield-lock-fill text-success me-2"></i>Keamanan Akun</h5>
                                    <p class="text-muted small mb-0">Perbarui kata sandi secara berkala untuk menjaga keamanan akun Anda</p>
                                </div>
                                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 fw-semibold"><i class="bi bi-lock-fill me-1"></i>Aktif</span>
                            </div>

                            <form action="proses_akun.php" method="POST">
                                <input type="hidden" name="aksi" value="update_password">

                                <div class="mb-3">
                                    <label for="password_lama" class="form-label small fw-semibold text-secondary">Kata Sandi Saat Ini <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control rounded-3" id="password_lama" name="password_lama" placeholder="Masukkan kata sandi lama Anda" required>
                                </div>

                                <div class="row g-3 mb-4">
                                    <div class="col-12 col-md-6">
                                        <label for="password_baru" class="form-label small fw-semibold text-secondary">Kata Sandi Baru <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control rounded-3" id="password_baru" name="password_baru" placeholder="Minimal 6 karakter" required minlength="6">
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="konfirmasi_password" class="form-label small fw-semibold text-secondary">Ulangi Kata Sandi Baru <span class="text-danger">*</span></label>
                                        <input type="password" class="form-control rounded-3" id="konfirmasi_password" name="konfirmasi_password" placeholder="Ulangi kata sandi baru" required minlength="6">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-outline-success rounded-pill px-4 py-2 fw-semibold d-flex align-items-center gap-2">
                                        <i class="bi bi-key-fill"></i>
                                        <span>Perbarui Kata Sandi</span>
                                    </button>
                                </div>
                            </form>
                        </div>

                        <div class="account-pane-block">
                            <div class="pb-3 border-bottom mb-4">
                                <h6 class="fw-bold text-dark mb-1"><i class="bi bi-gear-fill text-success me-2"></i>Pengaturan & Tindakan Akun</h6>
                                <p class="text-muted small mb-0">Kelola preferensi akun dan sesi masuk Anda</p>
                            </div>

                            <div class="d-flex flex-column gap-2">
                                <button type="button" class="btn btn-light border rounded-4 py-2 px-3 text-start d-flex align-items-center justify-content-between fw-semibold text-dark" data-bs-toggle="modal" data-bs-target="#modalUbahProfil">
                                    <span><i class="bi bi-person-gear me-2 text-success"></i>Ubah Data Biodata</span>
                                    <i class="bi bi-chevron-right small text-muted"></i>
                                </button>
                                <a href="akun.php?tab=alamat" class="btn btn-light border rounded-4 py-2 px-3 text-start d-flex align-items-center justify-content-between fw-semibold text-dark text-decoration-none">
                                    <span><i class="bi bi-geo-alt me-2 text-success"></i>Kelola Alamat Pengiriman</span>
                                    <span class="badge bg-success-subtle text-success rounded-pill"><?= count($alamat_list); ?></span>
                                </a>
                                <a href="keranjang.php" class="btn btn-light border rounded-4 py-2 px-3 text-start d-flex align-items-center justify-content-between fw-semibold text-dark text-decoration-none">
                                    <span><i class="bi bi-bag me-2 text-success"></i>Keranjang Belanja</span>
                                    <span class="badge bg-success-subtle text-success rounded-pill"><?= $cart_qty; ?> item</span>
                                </a>
                                <?php if ($user['role'] === 'admin'): ?>
                                    <a href="admin/index.php" class="btn btn-light border rounded-4 py-2 px-3 text-start d-flex align-items-center justify-content-between fw-semibold text-dark text-decoration-none">
                                        <span><i class="bi bi-speedometer2 me-2 text-success"></i>Panel Administrator</span>
                                        <i class="bi bi-chevron-right small text-muted"></i>
                                    </a>
                                <?php endif; ?>
                                <a href="auth/logout.php" class="btn btn-outline-danger rounded-4 py-2 px-3 text-start d-flex align-items-center justify-content-between fw-semibold" onclick="return confirm('Apakah Anda yakin ingin keluar dari akun ini?');">
                                    <span><i class="bi bi-box-arrow-right me-2"></i>Keluar dari Akun</span>
                                    <i class="bi bi-chevron-right small"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </main>

    <div class="modal fade" id="modalUbahProfil" tabindex="-1" aria-labelledby="modalUbahProfilLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-dark" id="modalUbahProfilLabel"><i class="bi bi-person-lines-fill text-success me-2"></i>Perbarui Data Profil</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="proses_akun.php" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="aksi" value="update_profile">

                    <div class="modal-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="nama_lengkap" class="form-label small fw-semibold text-secondary">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-3" id="nama_lengkap" name="nama_lengkap" value="<?= htmlspecialchars($user['nama_lengkap']); ?>" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="email" class="form-label small fw-semibold text-secondary">Alamat Email</label>
                                <input type="email" class="form-control rounded-3 bg-light" id="email" value="<?= htmlspecialchars($user['email']); ?>" disabled readonly>
                                <div class="form-text small" style="font-size: 11px;">Email adalah identitas akun dan tidak dapat diubah.</div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="no_telepon" class="form-label small fw-semibold text-secondary">Nomor Telepon / WhatsApp</label>
                                <input type="tel" class="form-control rounded-3" id="no_telepon" name="no_telepon" placeholder="Contoh: 08123456789" value="<?= htmlspecialchars($user['no_telepon'] ?? ''); ?>">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="foto_profil" class="form-label small fw-semibold text-secondary">Ganti Foto Profil</label>
                                <input type="file" class="form-control rounded-3" id="foto_profil" name="foto_profil" accept="image/jpeg,image/png,image/webp">
                                <div class="form-text small" style="font-size: 11px;">Format: JPG, PNG, atau WEBP (Maksimal 2MB).</div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold"><i class="bi bi-save me-1"></i>Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTambahAlamat" tabindex="-1" aria-labelledby="modalTambahAlamatLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-dark" id="modalTambahAlamatLabel"><i class="bi bi-geo-alt-fill text-success me-2"></i>Tambah Alamat Pengiriman Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="proses_alamat.php" method="POST">
                    <input type="hidden" name="aksi" value="tambah">
                    <input type="hidden" name="redirect" value="akun.php?tab=alamat">
                    <input type="hidden" name="provinsi_nama" id="modal_provinsi_nama" value="">
                    <input type="hidden" name="kota_nama" id="modal_kota_nama" value="">

                    <div class="modal-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold text-secondary">Label Alamat <span class="text-danger">*</span></label>
                                <input type="text" name="label_alamat" class="form-control rounded-3" placeholder="Contoh: Rumah, Kantor, Kos" required value="Rumah">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold text-secondary">Nama Penerima <span class="text-danger">*</span></label>
                                <input type="text" name="nama_penerima" class="form-control rounded-3" value="<?= htmlspecialchars($user['nama_lengkap']); ?>" required>
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold text-secondary">Nomor Telepon <span class="text-danger">*</span></label>
                                <input type="tel" name="no_telepon" class="form-control rounded-3" value="<?= htmlspecialchars($user['no_telepon'] ?? ''); ?>" required placeholder="08xxxxxxxxxx">
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Provinsi <span class="text-danger">*</span></label>
                                <select class="form-select rounded-3" name="provinsi_id" id="modal_provinsi_id" required>
                                    <option value="">-- Pilih Provinsi --</option>
                                    <?php foreach ($provinces as $prov): ?>
                                        <option value="<?= (int)$prov['province_id']; ?>" data-name="<?= htmlspecialchars($prov['province']); ?>"><?= htmlspecialchars($prov['province']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Kota / Kabupaten <span class="text-danger">*</span></label>
                                <select class="form-select rounded-3" name="kota_id" id="modal_kota_id" required disabled>
                                    <option value="">-- Pilih Provinsi Terlebih Dahulu --</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Kecamatan</label>
                                <input type="text" name="kecamatan" class="form-control rounded-3" placeholder="Contoh: Sukolilo / Menteng">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Kode Pos</label>
                                <input type="text" name="kode_pos" id="modal_kode_pos" class="form-control rounded-3" placeholder="Contoh: 60111">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea name="alamat_lengkap" class="form-control rounded-3" rows="3" placeholder="Nama jalan, nomor rumah, RT/RW, dan patokan lokasi" required></textarea>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_primary" value="1" id="modal_is_primary" <?= empty($alamat_list) ? 'checked' : ''; ?>>
                            <label class="form-check-label small text-muted" for="modal_is_primary">
                                Jadikan sebagai alamat utama pengiriman
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold"><i class="bi bi-save me-1"></i>Simpan Alamat</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include(__DIR__ . '/layouts/footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectProv = document.getElementById('modal_provinsi_id');
            const selectKota = document.getElementById('modal_kota_id');
            const hiddenProvNama = document.getElementById('modal_provinsi_nama');
            const hiddenKotaNama = document.getElementById('modal_kota_nama');
            const inputKodePos = document.getElementById('modal_kode_pos');

            if (selectProv && selectKota) {
                selectProv.addEventListener('change', function () {
                    const provId = this.value;
                    const selectedOpt = this.options[this.selectedIndex];
                    hiddenProvNama.value = selectedOpt ? selectedOpt.getAttribute('data-name') : '';

                    selectKota.innerHTML = '<option value="">Memuat kota...</option>';
                    selectKota.disabled = true;

                    if (!provId) {
                        selectKota.innerHTML = '<option value="">-- Pilih Provinsi Terlebih Dahulu --</option>';
                        return;
                    }

                    fetch('api/rajaongkir.php?action=cities&province_id=' + encodeURIComponent(provId))
                        .then(res => res.json())
                        .then(res => {
                            if (res.ok && Array.isArray(res.data)) {
                                selectKota.innerHTML = '<option value="">-- Pilih Kota / Kabupaten --</option>';
                                res.data.forEach(item => {
                                    const opt = document.createElement('option');
                                    opt.value = item.city_id;
                                    opt.setAttribute('data-name', item.type + ' ' + item.city_name);
                                    opt.setAttribute('data-postal', item.postal_code || '');
                                    opt.textContent = item.type + ' ' + item.city_name;
                                    selectKota.appendChild(opt);
                                });
                                selectKota.disabled = false;
                            } else {
                                selectKota.innerHTML = '<option value="">Gagal memuat kota</option>';
                            }
                        })
                        .catch(() => {
                            selectKota.innerHTML = '<option value="">Gagal memuat kota</option>';
                        });
                });

                selectKota.addEventListener('change', function () {
                    const selectedOpt = this.options[this.selectedIndex];
                    if (selectedOpt) {
                        hiddenKotaNama.value = selectedOpt.getAttribute('data-name') || '';
                        const pos = selectedOpt.getAttribute('data-postal');
                        if (pos && inputKodePos && !inputKodePos.value) {
                            inputKodePos.value = pos;
                        }
                    }
                });
            }

            const urlParams = new URLSearchParams(window.location.search);
            const tabParam = urlParams.get('tab') || window.location.hash.replace('#', '');
            if (tabParam) {
                const targetBtn = document.getElementById('tab-' + tabParam + '-btn');
                if (targetBtn) {
                    const triggerEl = new bootstrap.Tab(targetBtn);
                    triggerEl.show();
                }
            }

            document.querySelectorAll('#accountTabs button[data-bs-toggle="pill"]').forEach(btn => {
                btn.addEventListener('shown.bs.tab', function (event) {
                    const targetId = event.target.getAttribute('data-bs-target').replace('#tab-', '');
                    const newUrl = window.location.protocol + "//" + window.location.host + window.location.pathname + '?tab=' + targetId;
                    window.history.replaceState({ path: newUrl }, '', newUrl);
                });
            });

            document.addEventListener('hidden.bs.modal', function () {
                setTimeout(() => {
                    const openModals = document.querySelectorAll('.modal.show');
                    if (openModals.length === 0) {
                        document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
                        document.body.classList.remove('modal-open');
                        document.body.style.removeProperty('padding-right');
                        document.body.style.removeProperty('overflow');
                    }
                }, 150);
            });
        });
    </script>
</body>
</html>
