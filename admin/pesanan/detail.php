<?php
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../services/midtrans_service.php';

    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    $pesanan_id = (int) ($_GET['id'] ?? 0);

    if ($pesanan_id <= 0) {
        header("Location: index.php");
        exit;
    }

    $q_pesanan = mysqli_query($conn, "SELECT p.*, u.nama_lengkap AS nama_pelanggan, u.email AS email_pelanggan, u.no_telepon AS telp_pelanggan FROM pesanan p LEFT JOIN users u ON p.user_id = u.id WHERE p.id = '$pesanan_id'");
    $pesanan = $q_pesanan ? mysqli_fetch_assoc($q_pesanan) : null;

    if (!$pesanan) {
        $_SESSION['error'] = 'Pesanan tidak ditemukan.';
        header("Location: index.php");
        exit;
    }

    if ($pesanan['status_pesanan'] === 'menunggu_pembayaran') {
        $pesanan = midtrans_sinkronisasi_pesanan($conn, $pesanan);
    }

    $q_items = mysqli_query($conn, "SELECT dp.*, b.judul, b.penulis, b.penerbit, b.gambar FROM detail_pesanan dp LEFT JOIN buku b ON dp.buku_id = b.id WHERE dp.pesanan_id = '$pesanan_id'");
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
    <title>Detail Pesanan <?= htmlspecialchars($pesanan['nomor_pesanan']); ?> - PustakaKu</title>
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
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <span class="font-monospace fw-bold text-dark fs-6"><?= htmlspecialchars($pesanan['nomor_pesanan']); ?></span>
                            <?= status_pesanan_badge($pesanan['status_pesanan']); ?>
                        </div>
                        <p class="text-muted small mb-0">Dipesan pada <?= date('d F Y, H:i', strtotime($pesanan['created_at'])); ?> WIB &bull; Terakhir diperbarui <?= date('d F Y, H:i', strtotime($pesanan['updated_at'])); ?> WIB</p>
                    </div>
                </div>

                <div class="row g-3 g-lg-4">
                    <div class="col-12 col-lg-8">
                        <div class="card p-3 p-md-4 mb-3">
                            <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-3">
                                <h5 class="fw-bold text-dark mb-0"><i class="bi bi-book-half text-success me-2"></i>Daftar Buku Dipesan</h5>
                                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 fw-semibold"><?= count($items); ?> Judul</span>
                            </div>

                            <div class="table-responsive rounded-2 mb-3">
                                <table class="table align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th>Buku</th>
                                            <th class="text-end" style="width: 140px;">Harga Satuan</th>
                                            <th class="text-center" style="width: 90px;">Jumlah</th>
                                            <th class="text-end" style="width: 140px;">Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($items as $it): ?>
                                            <tr>
                                                <td>
                                                    <div class="d-flex align-items-center gap-3">
                                                        <img src="<?= (!empty($it['gambar']) && file_exists(__DIR__ . '/../../assets/uploads/buku/' . $it['gambar'])) ? ('../../assets/uploads/buku/' . htmlspecialchars($it['gambar'])) : '../../assets/images/placeholder.png'; ?>" alt="<?= htmlspecialchars($it['judul'] ?? 'Buku'); ?>" class="rounded-2 border object-fit-cover" style="width: 48px; height: 64px;" onerror="this.onerror=null;this.src='../../assets/images/placeholder.png';">
                                                        <div>
                                                            <div class="fw-semibold text-dark"><?= htmlspecialchars($it['judul'] ?? 'Buku Dihapus'); ?></div>
                                                            <div class="text-muted small"><?= htmlspecialchars($it['penulis'] ?? '-'); ?><?php if (!empty($it['penerbit'])): ?> &bull; <?= htmlspecialchars($it['penerbit']); ?><?php endif; ?></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-end small"><?= formatRupiah($it['harga_satuan']); ?></td>
                                                <td class="text-center fw-semibold"><?= (int)$it['jumlah']; ?></td>
                                                <td class="text-end fw-semibold text-success"><?= formatRupiah($it['subtotal']); ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-end text-muted small">Total Harga Buku:</td>
                                            <td class="text-end fw-semibold"><?= formatRupiah($pesanan['total_buku']); ?></td>
                                        </tr>
                                        <?php if ((float)$pesanan['potongan_diskon'] > 0): ?>
                                            <tr>
                                                <td colspan="3" class="text-end text-success small">Potongan Diskon:</td>
                                                <td class="text-end text-success fw-semibold">-<?= formatRupiah($pesanan['potongan_diskon']); ?></td>
                                            </tr>
                                        <?php endif; ?>
                                        <tr>
                                            <td colspan="3" class="text-end text-muted small">Ongkos Kirim:</td>
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

                        <div class="card p-3 p-md-4 mb-3">
                            <div class="d-flex align-items-center gap-2 pb-3 border-bottom mb-3">
                                <i class="bi bi-geo-alt-fill text-success fs-5"></i>
                                <h5 class="fw-bold text-dark mb-0">Alamat & Tujuan Pengiriman</h5>
                            </div>
                            <div class="bg-light p-3 rounded-3 border mb-3">
                                <div class="text-dark small lh-lg"><?= nl2br(htmlspecialchars($pesanan['alamat_pengiriman'])); ?></div>
                            </div>
                            <?php if (!empty($pesanan['catatan_pembeli'])): ?>
                                <div class="p-3 rounded-3 border border-warning-subtle bg-warning-subtle text-dark small">
                                    <strong class="d-block mb-1"><i class="bi bi-chat-left-text me-1 text-warning"></i>Catatan Pembeli:</strong>
                                    <span><?= htmlspecialchars($pesanan['catatan_pembeli']); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-12 col-lg-4">
                        <div class="card p-3 p-md-4 mb-3">
                            <div class="d-flex align-items-center gap-2 pb-3 border-bottom mb-3">
                                <i class="bi bi-sliders text-success fs-5"></i>
                                <h5 class="fw-bold text-dark mb-0">Ubah Status & Resi</h5>
                            </div>

                            <form action="proses_status.php" method="POST">
                                <input type="hidden" name="pesanan_id" value="<?= (int)$pesanan['id']; ?>">

                                <div class="mb-3">
                                    <label for="status_pesanan" class="form-label small fw-semibold text-secondary">Status Pesanan <span class="text-danger">*</span></label>
                                    <select class="form-select rounded-3" id="status_pesanan" name="status_pesanan" required>
                                        <option value="menunggu_pembayaran" <?= $pesanan['status_pesanan'] === 'menunggu_pembayaran' ? 'selected' : ''; ?>>Menunggu Pembayaran</option>
                                        <option value="menunggu_verifikasi" <?= $pesanan['status_pesanan'] === 'menunggu_verifikasi' ? 'selected' : ''; ?>>Menunggu Verifikasi</option>
                                        <option value="diproses" <?= $pesanan['status_pesanan'] === 'diproses' ? 'selected' : ''; ?>>Diproses (Sedang Dikemas)</option>
                                        <option value="dikirim" <?= $pesanan['status_pesanan'] === 'dikirim' ? 'selected' : ''; ?>>Dikirim (Dalam Pengiriman)</option>
                                        <option value="selesai" <?= $pesanan['status_pesanan'] === 'selesai' ? 'selected' : ''; ?>>Selesai</option>
                                        <option value="dibatalkan" <?= $pesanan['status_pesanan'] === 'dibatalkan' ? 'selected' : ''; ?>>Dibatalkan</option>
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label for="nomor_resi" class="form-label small fw-semibold text-secondary">Nomor Resi Pengiriman</label>
                                    <input type="text" class="form-control rounded-3 font-monospace" id="nomor_resi" name="nomor_resi" placeholder="Contoh: JNE88291039401" value="<?= htmlspecialchars($pesanan['nomor_resi'] ?? ''); ?>">
                                    <div class="form-text small" style="font-size: 11px;">Wajib diisi saat pesanan berstatus Dikirim atau Selesai.</div>
                                </div>

                                <button type="submit" class="btn btn-success w-100 rounded-pill py-2 fw-semibold d-flex align-items-center justify-content-center gap-2 shadow-sm">
                                    <i class="bi bi-save"></i>
                                    <span>Simpan Perubahan</span>
                                </button>
                            </form>
                        </div>

                        <div class="card p-3 p-md-4 mb-3">
                            <div class="d-flex align-items-center gap-2 pb-3 border-bottom mb-3">
                                <i class="bi bi-person-fill text-success fs-5"></i>
                                <h5 class="fw-bold text-dark mb-0">Informasi Pembeli</h5>
                            </div>

                            <div class="small">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Nama:</span>
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($pesanan['nama_pelanggan'] ?? 'Pengguna Dihapus'); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Email:</span>
                                    <span class="text-dark"><?= htmlspecialchars($pesanan['email_pelanggan'] ?? '-'); ?></span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Telepon:</span>
                                    <span class="text-dark"><?= htmlspecialchars($pesanan['telp_pelanggan'] ?? '-'); ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="card p-3 p-md-4">
                            <div class="d-flex align-items-center gap-2 pb-3 border-bottom mb-3">
                                <i class="bi bi-credit-card-fill text-success fs-5"></i>
                                <h5 class="fw-bold text-dark mb-0">Detail Pembayaran</h5>
                            </div>

                            <div class="small">
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Metode:</span>
                                    <span class="fw-bold text-dark"><?= htmlspecialchars($pesanan['metode_pembayaran']); ?></span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span class="text-muted">Total Bayar:</span>
                                    <span class="fw-bold text-success fs-6"><?= formatRupiah($pesanan['total_bayar']); ?></span>
                                </div>
                                <div class="d-flex justify-content-between">
                                    <span class="text-muted">Status:</span>
                                    <div><?= status_pesanan_badge($pesanan['status_pesanan']); ?></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <?php include(__DIR__ . '/../../layouts/admin/footer.php'); ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
