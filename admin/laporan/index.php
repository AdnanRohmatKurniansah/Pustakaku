<?php
    require_once __DIR__ . '/../../config/database.php';

    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

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

    $q_top = mysqli_query($conn, "SELECT b.judul, b.penulis, b.gambar, k.nama_kategori, SUM(dp.jumlah) AS terjual, SUM(dp.subtotal) AS total_nominal FROM detail_pesanan dp JOIN pesanan p ON dp.pesanan_id = p.id JOIN buku b ON dp.buku_id = b.id LEFT JOIN kategori k ON b.kategori_id = k.id WHERE $where_sql GROUP BY dp.buku_id ORDER BY terjual DESC LIMIT 5");

    $q_list = mysqli_query($conn, "SELECT p.*, u.nama_lengkap AS nama_pelanggan, (SELECT SUM(dp.jumlah) FROM detail_pesanan dp WHERE dp.pesanan_id = p.id) AS jumlah_eksemplar FROM pesanan p LEFT JOIN users u ON p.user_id = u.id WHERE $where_sql ORDER BY p.created_at DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - PustakaKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <style>
        @media print {
            .sidebar, .navbar, .no-print, .btn, form {
                display: none !important;
            }
            .main-wrapper {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
            }
            .card {
                border: 1px solid #dee2e6 !important;
                box-shadow: none !important;
            }
            .print-header {
                display: block !important;
            }
        }
        .print-header {
            display: none;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <?php include(__DIR__ . '/../../layouts/admin/sidebar.php'); ?>

        <div class="main-wrapper">
            <?php include(__DIR__ . '/../../layouts/admin/navbar.php'); ?>

            <main class="p-3 p-md-4">
                <div class="print-header mb-4 pb-3 border-bottom text-center">
                    <h3 class="fw-bold mb-1">PustakaKu Indonesia</h3>
                    <h5 class="fw-bold text-secondary mb-1">Laporan Rekapitulasi Penjualan Buku</h5>
                    <div class="small text-muted">Periode: <?= date('d/m/Y', strtotime($tanggal_mulai)); ?> s.d. <?= date('d/m/Y', strtotime($tanggal_selesai)); ?> &bull; Dicetak: <?= date('d/m/Y H:i'); ?> WIB</div>
                </div>

                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-4 border-bottom no-print">
                    <div>
                        <h4 class="fw-bold text-dark mb-1">Laporan Penjualan</h4>
                        <p class="text-muted small mb-0">Analisis performa omzet penjualan buku, pergerakan transaksi, dan judul terlaris</p>
                    </div>
                    <div>
                        <button type="button" class="btn btn-outline-success d-inline-flex align-items-center gap-2 rounded-pill px-4" onclick="window.print()">
                            <i class="bi bi-printer-fill"></i>
                            <span>Cetak Laporan</span>
                        </button>
                    </div>
                </div>

                <div class="card p-3 p-md-4 mb-4 no-print">
                    <form action="index.php" method="GET" class="row g-3 align-items-end">
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
                            <a href="index.php" class="btn btn-outline-secondary rounded-pill" title="Reset Periode">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                        </div>
                    </form>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card-stat">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <div class="text-muted small fw-semibold">Total Pendapatan</div>
                                    <h4 class="fw-bold mb-0 mt-1 text-success"><?= formatRupiah($total_omzet); ?></h4>
                                </div>
                                <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-cash-coin"></i></div>
                            </div>
                            <div class="small text-muted"><span>Periode terpilih</span></div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card-stat">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <div class="text-muted small fw-semibold">Total Transaksi</div>
                                    <h4 class="fw-bold mb-0 mt-1"><?= number_format($total_transaksi, 0, ',', '.'); ?></h4>
                                </div>
                                <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-receipt"></i></div>
                            </div>
                            <div class="small text-muted"><span>Jumlah pesanan masuk</span></div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card-stat">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <div class="text-muted small fw-semibold">Buku Terjual</div>
                                    <h4 class="fw-bold mb-0 mt-1 text-info"><?= number_format($total_buku_terjual, 0, ',', '.'); ?></h4>
                                </div>
                                <div class="stat-icon bg-info-subtle text-info"><i class="bi bi-book-half"></i></div>
                            </div>
                            <div class="small text-muted"><span>Eksemplar buku terjual</span></div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card-stat">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <div class="text-muted small fw-semibold">Rata-rata Pesanan</div>
                                    <h4 class="fw-bold mb-0 mt-1 text-secondary"><?= formatRupiah($rata_rata_order); ?></h4>
                                </div>
                                <div class="stat-icon bg-secondary-subtle text-secondary"><i class="bi bi-graph-up-arrow"></i></div>
                            </div>
                            <div class="small text-muted"><span>Rata-rata nominal per order</span></div>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="card p-3 p-md-4">
                            <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-3">
                                <h5 class="fw-bold text-dark mb-0">5 Buku Terlaris Periode Ini</h5>
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-3 py-1 fw-semibold">Top Sellers</span>
                            </div>

                            <div class="table-responsive rounded-2">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 60px;">Rank</th>
                                            <th>Judul Buku</th>
                                            <th>Kategori</th>
                                            <th class="text-center" style="width: 130px;">Jumlah Terjual</th>
                                            <th class="text-end" style="width: 160px;">Total Penjualan</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (mysqli_num_rows($q_top) === 0): ?>
                                            <tr>
                                                <td colspan="5" class="text-center py-4 text-muted">Belum ada data buku terjual pada periode ini.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php $rank = 1; while ($top = mysqli_fetch_assoc($q_top)): ?>
                                                <tr>
                                                    <td class="text-center fw-bold">
                                                        <span class="badge bg-success rounded-circle" style="width: 26px; height: 26px; line-height: 18px;"><?= $rank++; ?></span>
                                                    </td>
                                                    <td>
                                                        <div class="d-flex align-items-center gap-2">
                                                            <img src="<?= (!empty($top['gambar']) && file_exists(__DIR__ . '/../../assets/uploads/buku/' . $top['gambar'])) ? ('../../assets/uploads/buku/' . htmlspecialchars($top['gambar'])) : '../../assets/images/placeholder.png'; ?>" alt="<?= htmlspecialchars($top['judul']); ?>" class="rounded border object-fit-cover" style="width: 36px; height: 50px;" onerror="this.onerror=null;this.src='../../assets/images/placeholder.png';">
                                                            <div>
                                                                <div class="fw-semibold text-dark small"><?= htmlspecialchars($top['judul']); ?></div>
                                                                <div class="text-muted" style="font-size: 11px;"><?= htmlspecialchars($top['penulis']); ?></div>
                                                            </div>
                                                        </div>
                                                    </td>
                                                    <td class="small text-muted"><?= htmlspecialchars($top['nama_kategori'] ?? '-'); ?></td>
                                                    <td class="text-center fw-bold text-dark"><?= (int) $top['terjual']; ?> eks</td>
                                                    <td class="text-end fw-bold text-success"><?= formatRupiah($top['total_nominal']); ?></td>
                                                </tr>
                                            <?php endwhile; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card p-3 p-md-4">
                    <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-3">
                        <div>
                            <h5 class="fw-bold text-dark mb-0">Rincian Transaksi Penjualan</h5>
                            <div class="text-muted small">Daftar transaksi yang tercatat dalam rentang tanggal yang dipilih</div>
                        </div>
                        <span class="badge bg-light text-dark border"><?= $total_transaksi; ?> Transaksi</span>
                    </div>

                    <div class="table-responsive rounded-2">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 50px;">No</th>
                                    <th>No. Pesanan</th>
                                    <th>Tanggal</th>
                                    <th>Pelanggan</th>
                                    <th class="text-center">Jml Buku</th>
                                    <th class="text-end">Subtotal</th>
                                    <th class="text-end">Ongkir</th>
                                    <th class="text-end">Total Bayar</th>
                                    <th>Metode</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($q_list) === 0): ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-5 text-muted">
                                            <i class="bi bi-clipboard-x fs-1 d-block mb-2 text-secondary"></i>
                                            <span>Tidak ada transaksi yang tercatat pada periode ini.</span>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; while ($tr = mysqli_fetch_assoc($q_list)): ?>
                                        <tr>
                                            <td class="text-center fw-semibold text-muted"><?= $no++; ?></td>
                                            <td class="font-monospace small fw-bold text-dark"><?= htmlspecialchars($tr['nomor_pesanan']); ?></td>
                                            <td class="small text-muted" style="white-space: nowrap;"><?= date('d/m/Y H:i', strtotime($tr['created_at'])); ?></td>
                                            <td class="small fw-semibold text-dark"><?= htmlspecialchars($tr['nama_pelanggan'] ?? 'Pengguna Dihapus'); ?></td>
                                            <td class="text-center small"><?= (int) ($tr['jumlah_eksemplar'] ?? 0); ?> eks</td>
                                            <td class="text-end small"><?= formatRupiah($tr['total_buku']); ?></td>
                                            <td class="text-end small"><?= ((float)$tr['ongkos_kirim'] == 0) ? '<span class="text-success">Gratis</span>' : formatRupiah($tr['ongkos_kirim']); ?></td>
                                            <td class="text-end fw-bold text-success small"><?= formatRupiah($tr['total_bayar']); ?></td>
                                            <td class="small text-muted"><?= htmlspecialchars($tr['metode_pembayaran']); ?></td>
                                            <td><?= status_pesanan_badge($tr['status_pesanan']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>

            <?php include(__DIR__ . '/../../layouts/admin/footer.php'); ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
