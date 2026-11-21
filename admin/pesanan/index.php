<?php
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../services/midtrans_service.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    $status = trim($_GET['status'] ?? 'semua');
    $search = trim($_GET['q'] ?? '');

    $where_clauses = ["1=1"];

    if (!empty($status) && $status !== 'semua') {
        $status_esc = mysqli_real_escape_string($conn, $status);
        $where_clauses[] = "p.status_pesanan = '$status_esc'";
    }

    if (!empty($search)) {
        $search_esc = mysqli_real_escape_string($conn, $search);
        $where_clauses[] = "(p.nomor_pesanan LIKE '%$search_esc%' OR u.nama_lengkap LIKE '%$search_esc%' OR p.nomor_resi LIKE '%$search_esc%')";
    }

    $where_sql = implode(' AND ', $where_clauses);

    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    $c_q = mysqli_query($conn, "SELECT COUNT(*) AS total FROM pesanan p LEFT JOIN users u ON p.user_id = u.id WHERE $where_sql");
    $total_data = $c_q ? (int) mysqli_fetch_assoc($c_q)['total'] : 0;
    $total_pages = max(1, (int) ceil($total_data / $limit));

    $params = $_GET;
    unset($params['page']);
    $qs = http_build_query($params);
    $page_link_prefix = !empty($qs) ? 'index.php?' . $qs . '&page=' : 'index.php?page=';

    $q_pesanan = mysqli_query($conn, "SELECT p.*, u.nama_lengkap AS nama_pelanggan, u.email AS email_pelanggan, (SELECT COUNT(*) FROM detail_pesanan dp WHERE dp.pesanan_id = p.id) AS total_item FROM pesanan p LEFT JOIN users u ON p.user_id = u.id WHERE $where_sql ORDER BY p.id DESC LIMIT $limit OFFSET $offset");

    $c_total = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM pesanan"))['c'] ?? 0;
    $c_menunggu = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM pesanan WHERE status_pesanan = 'menunggu_pembayaran'"))['c'] ?? 0;
    $c_proses = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM pesanan WHERE status_pesanan IN ('menunggu_verifikasi', 'diproses')"))['c'] ?? 0;
    $c_kirim = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM pesanan WHERE status_pesanan = 'dikirim'"))['c'] ?? 0;
    $c_selesai = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) AS c FROM pesanan WHERE status_pesanan = 'selesai'"))['c'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pesanan Masuk - PustakaKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="icon" href="../../assets/images/favicon.png" type="image/png">
</head>
<body>
    <div class="d-flex">
        <?php include(__DIR__ . '/../../layouts/admin/sidebar.php'); ?>

        <div class="main-wrapper">
            <?php include(__DIR__ . '/../../layouts/admin/navbar.php'); ?>

            <main class="p-3 p-md-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-4 border-bottom">
                    <div>
                        <h4 class="fw-bold text-dark mb-1">Manajemen Pesanan Masuk</h4>
                        <p class="text-muted small mb-0">Pantau transaksi pelanggan, verifikasi pembayaran, ubah status pesanan, dan input resi pengiriman</p>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card-stat">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <div class="text-muted small fw-semibold">Total Pesanan</div>
                                    <h4 class="fw-bold mb-0 mt-1"><?= number_format($c_total, 0, ',', '.'); ?></h4>
                                </div>
                                <div class="stat-icon bg-success-subtle text-success"><i class="bi bi-bag-check-fill"></i></div>
                            </div>
                            <div class="small text-muted"><span>Seluruh transaksi</span></div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card-stat">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <div class="text-muted small fw-semibold">Menunggu Pembayaran</div>
                                    <h4 class="fw-bold mb-0 mt-1 text-warning"><?= number_format($c_menunggu, 0, ',', '.'); ?></h4>
                                </div>
                                <div class="stat-icon bg-warning-subtle text-warning"><i class="bi bi-clock-history"></i></div>
                            </div>
                            <div class="small text-muted"><span>Menunggu transfer pembeli</span></div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card-stat">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <div class="text-muted small fw-semibold">Perlu Diproses</div>
                                    <h4 class="fw-bold mb-0 mt-1 text-primary"><?= number_format($c_proses, 0, ',', '.'); ?></h4>
                                </div>
                                <div class="stat-icon bg-primary-subtle text-primary"><i class="bi bi-box-seam-fill"></i></div>
                            </div>
                            <div class="small text-muted"><span>Siap dikemas & dikirim</span></div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card-stat">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <div class="text-muted small fw-semibold">Sedang Dikirim</div>
                                    <h4 class="fw-bold mb-0 mt-1 text-secondary"><?= number_format($c_kirim, 0, ',', '.'); ?></h4>
                                </div>
                                <div class="stat-icon bg-secondary-subtle text-dark"><i class="bi bi-truck"></i></div>
                            </div>
                            <div class="small text-muted"><span>Dalam pengiriman kurir</span></div>
                        </div>
                    </div>
                </div>

                <div class="card p-3 p-md-4 mb-4">
                    <div class="d-flex flex-column flex-lg-row align-items-lg-center justify-content-between gap-3 mb-3">
                        <div class="d-flex flex-wrap gap-2">
                            <a href="index.php?status=semua<?= !empty($search) ? '&q=' . urlencode($search) : ''; ?>" class="btn btn-sm <?= ($status === 'semua' || empty($status)) ? 'btn-success' : 'btn-outline-secondary'; ?> rounded-pill px-3">Semua (<?= $c_total; ?>)</a>
                            <a href="index.php?status=diproses<?= !empty($search) ? '&q=' . urlencode($search) : ''; ?>" class="btn btn-sm <?= ($status === 'diproses') ? 'btn-primary' : 'btn-outline-secondary'; ?> rounded-pill px-3">Perlu Diproses (<?= $c_proses; ?>)</a>
                            <a href="index.php?status=dikirim<?= !empty($search) ? '&q=' . urlencode($search) : ''; ?>" class="btn btn-sm <?= ($status === 'dikirim') ? 'btn-secondary text-white' : 'btn-outline-secondary'; ?> rounded-pill px-3">Dikirim (<?= $c_kirim; ?>)</a>
                            <a href="index.php?status=selesai<?= !empty($search) ? '&q=' . urlencode($search) : ''; ?>" class="btn btn-sm <?= ($status === 'selesai') ? 'btn-success' : 'btn-outline-secondary'; ?> rounded-pill px-3">Selesai (<?= $c_selesai; ?>)</a>
                            <a href="index.php?status=dibatalkan<?= !empty($search) ? '&q=' . urlencode($search) : ''; ?>" class="btn btn-sm <?= ($status === 'dibatalkan') ? 'btn-danger' : 'btn-outline-secondary'; ?> rounded-pill px-3">Dibatalkan</a>
                        </div>

                        <form action="index.php" method="GET" class="d-flex gap-2">
                            <?php if (!empty($status) && $status !== 'semua'): ?>
                                <input type="hidden" name="status" value="<?= htmlspecialchars($status); ?>">
                            <?php endif; ?>
                            <div class="input-group input-group-sm" style="min-width: 250px;">
                                <input type="text" name="q" class="form-control rounded-start-pill ps-3" placeholder="No. Pesanan / Pembeli / Resi..." value="<?= htmlspecialchars($search); ?>">
                                <button type="submit" class="btn btn-success rounded-end-pill px-3"><i class="bi bi-search"></i></button>
                            </div>
                            <?php if (!empty($search)): ?>
                                <a href="index.php?status=<?= urlencode($status); ?>" class="btn btn-sm btn-outline-danger rounded-pill" title="Reset Pencarian"><i class="bi bi-x-lg"></i></a>
                            <?php endif; ?>
                        </form>
                    </div>

                    <div class="table-responsive rounded-2">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 50px;">No</th>
                                    <th>No. Pesanan</th>
                                    <th>Tanggal</th>
                                    <th>Total Bayar</th>
                                    <th>Metode Bayar</th>
                                    <th>Status</th>
                                    <th>No. Resi</th>
                                    <th class="text-center" style="width: 120px;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (mysqli_num_rows($q_pesanan) === 0): ?>
                                    <tr>
                                        <td colspan="10" class="text-center py-5 text-muted">
                                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                                            <span>Belum ada pesanan yang sesuai dengan filter.</span>
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = $offset + 1; while ($row = mysqli_fetch_assoc($q_pesanan)): ?>
                                        <?php 
                                            if ($row['status_pesanan'] === 'menunggu_pembayaran') {
                                                $row = midtrans_sinkronisasi_pesanan($conn, $row);
                                            }
                                        ?>
                                        <tr>
                                            <td class="text-center fw-semibold text-muted"><?= $no++; ?></td>
                                            <td>
                                                <a href="detail.php?id=<?= (int)$row['id']; ?>" class="fw-bold text-success text-decoration-none font-monospace small">
                                                    <?= htmlspecialchars($row['nomor_pesanan']); ?>
                                                </a>
                                            </td>
                                            <td class="small text-muted" style="white-space: nowrap;">
                                                <?= date('d/m/Y H:i', strtotime($row['created_at'])); ?>
                                            </td>
                                            <td class="fw-bold text-dark small">
                                                <?= formatRupiah($row['total_bayar']); ?>
                                            </td>
                                            <td class="small text-muted">
                                                <?= htmlspecialchars($row['metode_pembayaran']); ?>
                                            </td>
                                            <td>
                                                <?= status_pesanan_badge($row['status_pesanan']); ?>
                                            </td>
                                            <td class="small font-monospace">
                                                <?= !empty($row['nomor_resi']) ? '<span class="text-primary fw-semibold">' . htmlspecialchars($row['nomor_resi']) . '</span>' : '<span class="text-muted">-</span>'; ?>
                                            </td>
                                            <td class="text-center">
                                                <a href="detail.php?id=<?= (int)$row['id']; ?>" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1">
                                                    <i class="bi bi-eye"></i>
                                                    <span>Detail</span>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <?php if ($total_pages > 1): ?>
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mt-4 pt-3 border-top">
                            <div class="small text-muted">
                                Menampilkan <?= min($offset + 1, $total_data); ?> - <?= min($offset + $limit, $total_data); ?> dari <?= number_format($total_data, 0, ',', '.'); ?> data
                            </div>
                            <nav aria-label="Navigasi Halaman Pesanan">
                                <ul class="pagination pagination-sm mb-0 gap-1">
                                    <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link rounded-3 px-3 py-1 <?= ($page <= 1) ? 'text-muted' : 'text-dark'; ?>" href="<?= $page_link_prefix . max(1, $page - 1); ?>">
                                            <i class="bi bi-chevron-left me-1"></i>Sebelumnya
                                        </a>
                                    </li>
                                    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                                        <li class="page-item <?= ($p === $page) ? 'active' : ''; ?>">
                                            <a class="page-link rounded-3 px-3 py-1 fw-semibold <?= ($p === $page) ? 'bg-success border-success text-white' : 'text-dark'; ?>" href="<?= $page_link_prefix . $p; ?>">
                                                <?= $p; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                        <a class="page-link rounded-3 px-3 py-1 <?= ($page >= $total_pages) ? 'text-muted' : 'text-dark'; ?>" href="<?= $page_link_prefix . min($total_pages, $page + 1); ?>">
                                            Berikutnya<i class="bi bi-chevron-right ms-1"></i>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                </div>
            </main>

            <?php include(__DIR__ . '/../../layouts/admin/footer.php'); ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
