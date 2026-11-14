<?php
    require_once __DIR__ . '/../../config/database.php';

    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    $query = mysqli_query($conn, "
        SELECT restok_log.*, buku.judul, buku.gambar, buku.isbn, supplier.nama_supplier 
        FROM restok_log 
        LEFT JOIN buku ON restok_log.buku_id = buku.id 
        LEFT JOIN supplier ON restok_log.supplier_id = supplier.id 
        ORDER BY restok_log.id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Restok Buku - PustakaKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <div class="d-flex">
        <?php 
            include('../../layouts/admin/sidebar.php');
        ?>

        <div class="main-wrapper">
            <?php 
                include('../../layouts/admin/navbar.php');
            ?>
            <main class="p-3 p-md-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-4 border-bottom">
                    <div>
                        <h4 class="fw-bold text-dark mb-1">Manajemen Restok Buku</h4>
                        <p class="text-muted small mb-0">Kelola riwayat penambahan stok buku dari supplier / penerbit</p>
                    </div>
                    <div>
                        <a href="tambah.php" class="btn btn-success d-inline-flex align-items-center gap-2">
                            <i class="bi bi-plus-circle"></i>
                            <span>Tambah Data Restok</span>
                        </a>
                    </div>
                </div>

                <div class="card p-4">
                    <div class="table-responsive rounded-2">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr class="small text-muted">
                                    <th style="width: 50px;">No</th>
                                    <th>Tanggal Restok</th>
                                    <th>Buku</th>
                                    <th>Supplier</th>
                                    <th>Jumlah</th>
                                    <th>Biaya Satuan</th>
                                    <th>Total Biaya</th>
                                    <th style="width: 90px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($query && mysqli_num_rows($query) > 0): ?>
                                    <?php $no = 1; while ($row = mysqli_fetch_assoc($query)): ?>
                                        <?php 
                                            $total_biaya = $row['jumlah_masuk'] * $row['biaya_satuan']; 
                                        ?>
                                        <tr>
                                            <td class="fw-semibold text-muted"><?= $no++; ?></td>
                                            <td>
                                                <div class="text-dark fw-medium small"><?= date('d M Y', strtotime($row['tanggal_restok'])); ?></div>
                                                <div class="text-muted small" style="font-size: 0.75rem;"><?= date('H:i', strtotime($row['tanggal_restok'])); ?> WIB</div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <?php if (!empty($row['gambar']) && file_exists('../../assets/uploads/buku/' . $row['gambar'])): ?>
                                                        <img src="../../assets/uploads/buku/<?= htmlspecialchars($row['gambar']); ?>" alt="Sampul" class="rounded object-fit-cover border" style="width: 38px; height: 50px;">
                                                    <?php else: ?>
                                                        <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted border" style="width: 38px; height: 50px;">
                                                            <i class="bi bi-book"></i>
                                                        </div>
                                                    <?php endif; ?>
                                                    <div>
                                                        <div class="fw-bold text-dark small"><?= htmlspecialchars($row['judul'] ?? 'Buku Telah Dihapus'); ?></div>
                                                        <?php if (!empty($row['isbn'])): ?>
                                                            <div class="text-muted small" style="font-size: 0.75rem;">ISBN: <?= htmlspecialchars($row['isbn']); ?></div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <?php if (!empty($row['nama_supplier'])): ?>
                                                    <span class="badge bg-secondary-subtle text-secondary-emphasis border border-secondary-subtle px-2 py-1">
                                                        <?= htmlspecialchars($row['nama_supplier']); ?>
                                                    </span>
                                                <?php else: ?>
                                                    <span class="text-muted small fst-italic">-</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="text-success small">
                                                    +<?= number_format($row['jumlah_masuk']); ?> pcs
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-dark small">Rp <?= number_format($row['biaya_satuan'], 0, ',', '.'); ?></span>
                                            </td>
                                            <td>
                                                <span class="fw-semibold text-dark small">Rp <?= number_format($total_biaya, 0, ',', '.'); ?></span>
                                            </td>
                                            <td class="text-center">
                                                <a href="detail.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-outline-info text-info-emphasis d-inline-flex align-items-center gap-1" title="Lihat Detail">
                                                    <i class="bi bi-eye"></i>
                                                    <span>Detail</span>
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <i class="bi bi-archive fs-1 d-block mb-2 text-secondary"></i>
                                            <span style="font-size: 15px;">Belum ada data riwayat restok buku yang tersedia.</span>
                                            <div class="mt-2">
                                                <a href="tambah.php" class="btn btn-sm btn-success">Tambah Sekarang</a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>

            <?php
                include('../../layouts/admin/footer.php');
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
