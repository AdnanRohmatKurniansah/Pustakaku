<?php
    require_once __DIR__ . '/../config/database.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../auth/login.php");
        exit;
    }

    $q_pesanan = mysqli_query($conn, "SELECT COUNT(*) AS total FROM pesanan");
    $row_pesanan = mysqli_fetch_assoc($q_pesanan);
    $total_pesanan = $row_pesanan['total'] ?? 0;

    $q_buku = mysqli_query($conn, "SELECT COUNT(*) AS total FROM buku");
    $row_buku = mysqli_fetch_assoc($q_buku);
    $total_buku = $row_buku['total'] ?? 0;

    $q_kategori = mysqli_query($conn, "SELECT COUNT(*) AS total FROM kategori");
    $row_kategori = mysqli_fetch_assoc($q_kategori);
    $total_kategori = $row_kategori['total'] ?? 0;

    $q_pengguna = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'pelanggan'");
    $row_pengguna = mysqli_fetch_assoc($q_pengguna);
    $total_pengguna = $row_pengguna['total'] ?? 0;

    $q_recent_orders = mysqli_query($conn, "SELECT p.*, u.nama_lengkap AS nama_pelanggan FROM pesanan p LEFT JOIN users u ON p.user_id = u.id ORDER BY p.id DESC LIMIT 5");

    $tanggal_mulai = trim($_GET['tanggal_mulai'] ?? date('Y-m-01'));
    $tanggal_selesai = trim($_GET['tanggal_selesai'] ?? date('Y-m-d'));
    $status_filter = trim($_GET['status'] ?? 'berhasil');

    $mulai_esc = mysqli_real_escape_string($conn, $tanggal_mulai);
    $selesai_esc = mysqli_real_escape_string($conn, $tanggal_selesai);

    $where_clauses = ["DATE(p.created_at) >= '$mulai_esc'", "DATE(p.created_at) <= '$selesai_esc'"];

    if ($status_filter === 'berhasil') {
        $where_clauses[] = "p.status_pesanan IN ('diproses', 'dikirim', 'selesai')";
    } elseif ($status_filter === 'selesai') {
        $where_clauses[] = "p.status_pesanan = 'selesai'";
    } elseif ($status_filter === 'menunggu') {
        $where_clauses[] = "p.status_pesanan = 'menunggu_pembayaran'";
    } elseif ($status_filter === 'dibatalkan') {
        $where_clauses[] = "p.status_pesanan = 'dibatalkan'";
    }

    $where_sql = implode(' AND ', $where_clauses);

    $q_rekap = mysqli_query($conn, "SELECT COUNT(p.id) AS total_transaksi, COALESCE(SUM(p.total_bayar), 0) AS total_omzet, COALESCE(SUM(p.total_buku), 0) AS total_buku_omzet, COALESCE(SUM(p.ongkos_kirim), 0) AS total_ongkir FROM pesanan p WHERE $where_sql");
    $rekap = mysqli_fetch_assoc($q_rekap);

    $total_transaksi = (int) ($rekap['total_transaksi'] ?? 0);
    $total_omzet = (float) ($rekap['total_omzet'] ?? 0);
    $rata_rata_order = $total_transaksi > 0 ? ($total_omzet / $total_transaksi) : 0;

    $q_buku_terjual = mysqli_query($conn, "SELECT COALESCE(SUM(dp.jumlah), 0) AS terjual FROM detail_pesanan dp JOIN pesanan p ON dp.pesanan_id = p.id WHERE $where_sql");
    $total_buku_terjual = (int) (mysqli_fetch_assoc($q_buku_terjual)['terjual'] ?? 0);

    $q_laporan_list = mysqli_query($conn, "SELECT p.*, u.nama_lengkap AS nama_pelanggan, (SELECT SUM(dp.jumlah) FROM detail_pesanan dp WHERE dp.pesanan_id = p.id) AS jumlah_eksemplar FROM pesanan p LEFT JOIN users u ON p.user_id = u.id WHERE $where_sql ORDER BY p.created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - PustakaKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <link rel="icon" href="../assets/images/favicon.png" type="image/png">
</head>
<body>
    <div class="d-flex">
        <?php 
            include('../layouts/admin/sidebar.php');
        ?>

        <div class="main-wrapper">
            <?php 
                include('../layouts/admin/navbar.php');
            ?>

            <main class="p-3 p-md-4">
                <div class="admin-banner bg-success mb-4">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <span class="badge bg-white bg-opacity-25 text-white px-3 py-2 rounded-pill mb-2"><i class="bi bi-grid-1x2-fill me-1"></i> Dashboard Pengelolaan</span>
                            <h2 class="fw-bold mb-2">Selamat Datang, <?= htmlspecialchars($_SESSION['nama_lengkap']); ?>!</h2>
                            <p class="mb-0 opacity-75">Pantau ringkasan penjualan buku, katalog buku, dan status transaksi toko PustakaKu dalam satu tempat.</p>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card-stat">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <div class="text-muted small fw-semibold">Total Pesanan</div>
                                    <h4 class="fw-bold mb-0 mt-1"><?= number_format($total_pesanan, 0, ',', '.'); ?></h4>
                                </div>
                                <div class="stat-icon bg-success-subtle text-success">
                                    <i class="bi bi-bag-check-fill"></i>
                                </div>
                            </div>
                            <div class="small text-muted">
                                <span>Seluruh riwayat pesanan</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card-stat">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <div class="text-muted small fw-semibold">Total Kategori</div>
                                    <h4 class="fw-bold mb-0 mt-1"><?= number_format($total_kategori, 0, ',', '.'); ?></h4>
                                </div>
                                <div class="stat-icon bg-primary-subtle text-primary">
                                    <i class="bi bi-tags-fill"></i>
                                </div>
                            </div>
                            <div class="small text-muted">
                                <span>Seluruh kategori buku</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card-stat">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <div class="text-muted small fw-semibold">Total Buku</div>
                                    <h4 class="fw-bold mb-0 mt-1"><?= number_format($total_buku, 0, ',', '.'); ?></h4>
                                </div>
                                <div class="stat-icon bg-info-subtle text-info">
                                    <i class="bi bi-journal-album"></i>
                                </div>
                            </div>
                            <div class="small text-muted">
                                <span>Seluruh data buku yg ada</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card-stat">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <div class="text-muted small fw-semibold">Pengguna</div>
                                    <h4 class="fw-bold mb-0 mt-1 text-secondary"><?= $total_pengguna; ?></h4>
                                </div>
                                <div class="stat-icon bg-secondary-subtle text-secondary">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                            </div>
                            <div class="small text-muted">
                                <span>Pengguna aktif</span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-3">
                        <div>
                            <h5 class="fw-bold text-dark mb-0">Pesanan Masuk Terbaru</h5>
                            <p class="text-muted small mb-0">Daftar transaksi pesanan terkini dari pelanggan</p>
                        </div>
                        <a href="pesanan/index.php" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold">
                            <span>Lihat Semua</span>
                            <i class="bi bi-arrow-right ms-1"></i>
                        </a>
                    </div>

                    <div class="table-responsive rounded-2">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>No. Pesanan</th>
                                    <th>Tanggal</th>
                                    <th>Pelanggan</th>
                                    <th>Total Bayar</th>
                                    <th>Metode</th>
                                    <th>Status</th>
                                    <th class="text-center" style="width: 100px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($q_recent_orders) === 0): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-2 d-block mb-1 text-secondary"></i>
                                            <span>Belum ada pesanan yang masuk.</span>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php while ($ord = mysqli_fetch_assoc($q_recent_orders)): ?>
                                        <tr>
                                            <td>
                                                <a href="pesanan/detail.php?id=<?= (int)$ord['id']; ?>" class="fw-bold text-success text-decoration-none font-monospace small">
                                                    <?= htmlspecialchars($ord['nomor_pesanan']); ?>
                                                </a>
                                            </td>
                                            <td class="small text-muted" style="white-space: nowrap;"><?= date('d/m/Y H:i', strtotime($ord['created_at'])); ?></td>
                                            <td class="small fw-semibold text-dark"><?= htmlspecialchars($ord['nama_pelanggan'] ?? 'Pengguna Dihapus'); ?></td>
                                            <td class="small fw-bold text-dark"><?= formatRupiah($ord['total_bayar']); ?></td>
                                            <td class="small text-muted"><?= htmlspecialchars($ord['metode_pembayaran']); ?></td>
                                            <td><?= status_pesanan_badge($ord['status_pesanan']); ?></td>
                                            <td class="text-center">
                                                <a href="pesanan/detail.php?id=<?= (int)$ord['id']; ?>" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 fw-semibold">Detail</a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card p-3 p-md-4 mt-4" id="laporan">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 pb-3 border-bottom mb-3">
                        <div>
                            <h5 class="fw-bold text-dark mb-0">Laporan Penjualan Buku</h5>
                            <p class="text-muted small mb-0">Rekapitulasi penjualan berdasarkan rentang tanggal dan status transaksi</p>
                        </div>
                        <a href="export_pdf.php?tanggal_mulai=<?= urlencode($tanggal_mulai); ?>&tanggal_selesai=<?= urlencode($tanggal_selesai); ?>&status=<?= urlencode($status_filter); ?>" target="_blank" class="btn btn-danger btn-sm rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-2">
                            <i class="bi bi-file-earmark-pdf-fill"></i>
                            <span>Unduh Laporan PDF</span>
                        </a>
                    </div>

                    <form action="index.php" method="GET" class="row g-3 align-items-end mb-4">
                        <div class="col-12 col-sm-6 col-md-3">
                            <label for="tanggal_mulai" class="form-label small fw-semibold text-secondary">Tanggal Mulai</label>
                            <input type="date" class="form-control rounded-3" id="tanggal_mulai" name="tanggal_mulai" value="<?= htmlspecialchars($tanggal_mulai); ?>">
                        </div>

                        <div class="col-12 col-sm-6 col-md-3">
                            <label for="tanggal_selesai" class="form-label small fw-semibold text-secondary">Tanggal Selesai</label>
                            <input type="date" class="form-control rounded-3" id="tanggal_selesai" name="tanggal_selesai" value="<?= htmlspecialchars($tanggal_selesai); ?>">
                        </div>

                        <div class="col-12 col-sm-6 col-md-4">
                            <label for="status" class="form-label small fw-semibold text-secondary">Filter Status</label>
                            <select class="form-select rounded-3" id="status" name="status">
                                <option value="berhasil" <?= $status_filter === 'berhasil' ? 'selected' : ''; ?>>Transaksi Valid (Diproses / Dikirim / Selesai)</option>
                                <option value="selesai" <?= $status_filter === 'selesai' ? 'selected' : ''; ?>>Hanya Pesanan Selesai</option>
                                <option value="semua" <?= $status_filter === 'semua' ? 'selected' : ''; ?>>Semua Status (Termasuk Dibatalkan)</option>
                                <option value="menunggu" <?= $status_filter === 'menunggu' ? 'selected' : ''; ?>>Menunggu Pembayaran</option>
                                <option value="dibatalkan" <?= $status_filter === 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                            </select>
                        </div>

                        <div class="col-12 col-sm-6 col-md-2 d-flex gap-2">
                            <button type="submit" class="btn btn-success rounded-pill flex-grow-1 fw-semibold d-inline-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-funnel-fill"></i>
                                <span>Filter</span>
                            </button>
                            <a href="index.php#laporan" class="btn btn-outline-secondary rounded-pill" title="Reset Filter">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                        </div>
                    </form>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="card-stat">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div>
                                        <div class="text-muted small fw-semibold">Total Pendapatan</div>
                                        <h5 class="fw-bold mb-0 mt-1 text-success"><?= formatRupiah($total_omzet); ?></h5>
                                    </div>
                                    <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-cash-coin"></i></div>
                                </div>
                                <div class="small text-muted">Periode terpilih</div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="card-stat">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div>
                                        <div class="text-muted small fw-semibold">Total Transaksi</div>
                                        <h5 class="fw-bold mb-0 mt-1"><?= number_format($total_transaksi, 0, ',', '.'); ?></h5>
                                    </div>
                                    <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-receipt"></i></div>
                                </div>
                                <div class="small text-muted">Transaksi tercatat</div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="card-stat">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div>
                                        <div class="text-muted small fw-semibold">Buku Terjual</div>
                                        <h5 class="fw-bold mb-0 mt-1"><?= number_format($total_buku_terjual, 0, ',', '.'); ?></h5>
                                    </div>
                                    <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-book"></i></div>
                                </div>
                                <div class="small text-muted">Eksemplar terjual</div>
                            </div>
                        </div>

                        <div class="col-12 col-sm-6 col-xl-3">
                            <div class="card-stat">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <div>
                                        <div class="text-muted small fw-semibold">Rata-rata Order</div>
                                        <h5 class="fw-bold mb-0 mt-1 text-info"><?= formatRupiah($rata_rata_order); ?></h5>
                                    </div>
                                    <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-graph-up-arrow"></i></div>
                                </div>
                                <div class="small text-muted">Nilai per transaksi</div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive rounded-2">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>No. Pesanan</th>
                                    <th>Tanggal</th>
                                    <th>Pelanggan</th>
                                    <th>Jml Buku</th>
                                    <th>Total Bayar</th>
                                    <th>Metode</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($q_laporan_list) === 0): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4 text-muted">
                                            <i class="bi bi-inbox fs-2 d-block mb-1 text-secondary"></i>
                                            <span>Tidak ada transaksi pada periode ini.</span>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php while ($lap = mysqli_fetch_assoc($q_laporan_list)): ?>
                                        <tr>
                                            <td>
                                                <a href="pesanan/detail.php?id=<?= (int)$lap['id']; ?>" class="fw-bold text-success text-decoration-none font-monospace small">
                                                    <?= htmlspecialchars($lap['nomor_pesanan']); ?>
                                                </a>
                                            </td>
                                            <td class="small text-muted" style="white-space: nowrap;"><?= date('d/m/Y H:i', strtotime($lap['created_at'])); ?></td>
                                            <td class="small fw-semibold text-dark"><?= htmlspecialchars($lap['nama_pelanggan'] ?? 'Pengguna Dihapus'); ?></td>
                                            <td class="small text-muted"><?= (int)($lap['jumlah_eksemplar'] ?? 0); ?> pcs</td>
                                            <td class="small fw-bold text-dark"><?= formatRupiah($lap['total_bayar']); ?></td>
                                            <td class="small text-muted"><?= htmlspecialchars($lap['metode_pembayaran']); ?></td>
                                            <td><?= status_pesanan_badge($lap['status_pesanan']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>

            <?php
                include('../layouts/admin/footer.php');
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
