<?php
    require_once __DIR__ . '/../../config/database.php';

    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    $q_buku = mysqli_query($conn, "SELECT id, judul, stok FROM buku ORDER BY judul ASC");
    $q_supplier = mysqli_query($conn, "SELECT id, nama_supplier FROM supplier ORDER BY nama_supplier ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Restok Buku - PustakaKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="icon" href="../../assets/images/favicon.png" type="image/png">
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
                <div class="d-flex justify-content-between align-items-center mb-4 pb-4 border-bottom">
                    <div>
                        <h4 class="fw-bold text-dark mb-1">Tambah Data Restok Buku</h4>
                        <p class="text-muted small mb-0">Catat penambahan stok buku masuk dari penerbit atau supplier</p>
                    </div>
                    <div>
                        <a href="index.php" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                            <i class="bi bi-arrow-left"></i>
                            <span>Kembali</span>
                        </a>
                    </div>
                </div>

                <div class="card p-4">
                    <form action="proses_tambah.php" method="POST">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="buku_id" class="form-label small fw-semibold text-secondary">Pilih Buku <span class="text-danger">*</span></label>
                                <select class="form-select" id="buku_id" name="buku_id" required autofocus>
                                    <option value="" disabled selected>-- Pilih Buku yang Direstok --</option>
                                    <?php while ($b = mysqli_fetch_assoc($q_buku)): ?>
                                        <option value="<?= $b['id']; ?>"><?= htmlspecialchars($b['judul']); ?> (Stok saat ini: <?= $b['stok']; ?>)</option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="supplier_id" class="form-label small fw-semibold text-secondary">Supplier / Penerbit</label>
                                <select class="form-select" id="supplier_id" name="supplier_id">
                                    <option value="">-- Pilih Supplier (Opsional) --</option>
                                    <?php while ($s = mysqli_fetch_assoc($q_supplier)): ?>
                                        <option value="<?= $s['id']; ?>"><?= htmlspecialchars($s['nama_supplier']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="jumlah_masuk" class="form-label small fw-semibold text-secondary">Jumlah Masuk (pcs) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="jumlah_masuk" name="jumlah_masuk" placeholder="Contoh: 50" min="1" required>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="biaya_satuan" class="form-label small fw-semibold text-secondary">Biaya Beli Satuan (Rp)</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light">Rp</span>
                                    <input type="number" class="form-control" id="biaya_satuan" name="biaya_satuan" placeholder="Contoh: 35000" min="0" value="0">
                                </div>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="tanggal_restok" class="form-label small fw-semibold text-secondary">Tanggal & Waktu Restok <span class="text-danger">*</span></label>
                                <input type="datetime-local" class="form-control" id="tanggal_restok" name="tanggal_restok" value="<?= date('Y-m-d\TH:i'); ?>" required>
                            </div>

                            <div class="col-12">
                                <label for="catatan" class="form-label small fw-semibold text-secondary">Catatan Restok (Opsional)</label>
                                <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Contoh: No Faktur / Surat Jalan INV-2026-001, buku cetakan ke-3..."></textarea>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-2">
                                <i class="bi bi-save"></i>
                                <span>Simpan Data Restok</span>
                            </button>
                            <a href="index.php" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
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
