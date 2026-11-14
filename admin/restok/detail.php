<?php
    require_once __DIR__ . '/../../config/database.php';

    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    $query = mysqli_query($conn, "
        SELECT restok_log.*, buku.judul, buku.penulis, buku.penerbit, buku.isbn, buku.harga, buku.stok, buku.gambar, 
        kategori.nama_kategori, supplier.nama_supplier, supplier.telepon, supplier.email AS email_supplier, supplier.alamat AS alamat_supplier
        FROM restok_log 
        LEFT JOIN buku ON restok_log.buku_id = buku.id 
        LEFT JOIN kategori ON buku.kategori_id = kategori.id
        LEFT JOIN supplier ON restok_log.supplier_id = supplier.id 
        WHERE restok_log.id = '$id' LIMIT 1");

    if (!$query || mysqli_num_rows($query) === 0) {
        $_SESSION['error'] = "Data restok tidak ditemukan!";
        header("Location: index.php");
        exit;
    }

    $restok = mysqli_fetch_assoc($query);
    $total_biaya = $restok['jumlah_masuk'] * $restok['biaya_satuan'];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Restok     - PustakaKu</title>
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
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h4 class="fw-bold text-dark mb-0">Detail Riwayat Restok</h4>
                        </div>
                        <p class="text-muted small mb-0">Informasi lengkap transaksi penerimaan stok buku</p>
                    </div>
                    <div>
                        <a href="index.php" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                            <i class="bi bi-arrow-left"></i>
                            <span>Kembali ke Daftar</span>
                        </a>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-12 col-lg-7">
                        <div class="card p-4 mb-4">
                            <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                                <i class="bi bi-box-seam text-success"></i>
                                <span>Informasi Restok</span>
                            </h6>

                            <div class="table-responsive">
                                <table class="table table-borderless align-middle mb-0">
                                    <tbody>
                                        <tr>
                                            <th class="text-muted small fw-semibold">Tanggal & Waktu</th>
                                            <td class="text-dark">
                                                <?= date('d F Y', strtotime($restok['tanggal_restok'])); ?> 
                                                <span class="text-muted small">(<?= date('H:i', strtotime($restok['tanggal_restok'])); ?> WIB)</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted small fw-semibold">Jumlah Masuk</th>
                                            <td>
                                                <span class="fw-bold text-success fs-6">
                                                    +<?= number_format($restok['jumlah_masuk']); ?> pcs
                                                </span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted small fw-semibold">Biaya Beli Satuan</th>
                                            <td class="text-dark">Rp <?= number_format($restok['biaya_satuan'], 0, ',', '.'); ?></td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted small fw-semibold">Total Biaya Restok</th>
                                            <td class="fw-bold text-success fs-6">Rp <?= number_format($total_biaya, 0, ',', '.'); ?></td>
                                        </tr>
                                        <tr>
                                            <th class="text-muted small fw-semibold align-top pt-2">Catatan</th>
                                            <td class="text-secondary small pt-2">
                                                <?= !empty($restok['catatan']) ? nl2br(htmlspecialchars($restok['catatan'])) : '<span class="text-muted fst-italic">Tidak ada catatan</span>'; ?>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="card p-4">
                            <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                                <i class="bi bi-truck text-primary"></i>
                                <span>Informasi Supplier / Penerbit</span>
                            </h6>

                            <?php if (!empty($restok['nama_supplier'])): ?>
                                <div class="table-responsive">
                                    <table class="table table-borderless align-middle mb-0">
                                        <tbody>
                                            <tr>
                                                <th class="text-muted small fw-semibold" style="width: 180px;">Nama Supplier</th>
                                                <td class="text-dark"><?= htmlspecialchars($restok['nama_supplier']); ?></td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted small fw-semibold">Nomor Telepon</th>
                                                <td class="text-dark"><?= htmlspecialchars($restok['telepon'] ?? '-'); ?></td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted small fw-semibold">Alamat Email</th>
                                                <td class="text-dark"><?= htmlspecialchars($restok['email_supplier'] ?? '-'); ?></td>
                                            </tr>
                                            <tr>
                                                <th class="text-muted small fw-semibold align-top pt-2">Alamat Lengkap</th>
                                                <td class="text-dark pt-2">
                                                    <?= !empty($restok['alamat_supplier']) ? nl2br(htmlspecialchars($restok['alamat_supplier'])) : '-'; ?>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="p-3 bg-light rounded text-center text-muted small">
                                    <i class="bi bi-info-circle me-1"></i> Data supplier tidak ditentukan pada restok ini.
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-12 col-lg-5">
                        <div class="card p-4">
                            <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                                <i class="bi bi-book text-info"></i>
                                <span>Buku Terkait</span>
                            </h6>

                            <?php if (!empty($restok['judul'])): ?>
                                <div class="text-center mb-3">
                                    <?php if (!empty($restok['gambar']) && file_exists('../../assets/uploads/buku/' . $restok['gambar'])): ?>
                                        <img src="../../assets/uploads/buku/<?= htmlspecialchars($restok['gambar']); ?>" alt="Sampul Buku" class="rounded-3 border shadow-sm object-fit-cover" style="width: 120px; height: 160px;">
                                    <?php else: ?>
                                        <div class="bg-light rounded-3 d-inline-flex align-items-center justify-content-center text-muted border" style="width: 120px; height: 160px;">
                                            <i class="bi bi-book fs-1"></i>
                                        </div>
                                    <?php endif; ?>
                                    <h5 class="fw-bold text-dark mt-3 mb-1"><?= htmlspecialchars($restok['judul']); ?></h5>
                                    <?php if (!empty($restok['nama_kategori'])): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                            <?= htmlspecialchars($restok['nama_kategori']); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <ul class="list-group list-group-flush small">
                                    <li class="list-group-item d-flex justify-content-between px-0">
                                        <span class="text-muted">Penulis</span>
                                        <span class="fw-semibold text-dark"><?= htmlspecialchars($restok['penulis'] ?? '-'); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between px-0">
                                        <span class="text-muted">Penerbit</span>
                                        <span class="fw-semibold text-dark"><?= htmlspecialchars($restok['penerbit'] ?? '-'); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between px-0">
                                        <span class="text-muted">Nomor ISBN</span>
                                        <span class="fw-semibold text-dark"><?= htmlspecialchars($restok['isbn'] ?? '-'); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between px-0">
                                        <span class="text-muted">Harga Jual Toko</span>
                                        <span class="fw-semibold text-dark">Rp <?= number_format($restok['harga'] ?? 0, 0, ',', '.'); ?></span>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between px-0">
                                        <span class="text-muted">Stok Buku Sekarang</span>
                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle px-2 py-1">
                                            <?= number_format($restok['stok'] ?? 0); ?> unit
                                        </span>
                                    </li>
                                </ul>
                            <?php else: ?>
                                <div class="p-3 bg-light rounded text-center text-muted small">
                                    <i class="bi bi-exclamation-triangle me-1"></i> Data buku terkait sudah tidak ada atau telah dihapus.
                                </div>
                            <?php endif; ?>
                        </div>
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
