<?php
    session_start();

    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/cart/proses_keranjang.php';

    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = 'Silakan masuk ke akun Anda terlebih dahulu untuk melihat keranjang belanja.';
        header('Location: auth/login.php');
        exit;
    }

    if (($_SESSION['role'] ?? '') === 'admin') {
        $_SESSION['error'] = 'Akun admin tidak dapat menggunakan keranjang belanja pelanggan.';
        header('Location: admin/index.php');
        exit;
    }

    const BATAS_GRATIS_ONGKIR = 150000;

    $keranjang = cart_items($conn);
    $items     = $keranjang['items'];
    $jumlah    = $keranjang['jumlah'];

    $total_normal = 0.0;
    $total_bayar  = 0.0;

    foreach ($items as $it) {
        $total_normal += $it['harga'] * $it['qty'];
        $total_bayar  += $it['satuan'] * $it['qty'];
    }
    
    $total_hemat = $total_normal - $total_bayar;
    $sisa_gratis = max(0, BATAS_GRATIS_ONGKIR - $total_bayar);
    $persen_gratis = BATAS_GRATIS_ONGKIR > 0 ? min(100, round($total_bayar / BATAS_GRATIS_ONGKIR * 100)) : 100;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - PustakaKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php include(__DIR__ . '/layouts/navbar.php'); ?>
    
    <main class="container my-3 my-md-4">

        <section class="mb-3 mb-lg-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-2">
                    <li class="breadcrumb-item"><a href="index.php" class="text-success text-decoration-none"><i class="bi bi-house-door me-1"></i>Beranda</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Keranjang Belanja</li>
                </ol>
            </nav>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <h2 class="fw-bold text-dark mb-0">Keranjang Belanja Saya</h2>
                <span class="badge bg-success-subtle text-success rounded-pill px-3 py-2 fw-semibold" id="headerCount"><i class="bi bi-bag me-1"></i><?= $jumlah; ?> Item</span>
            </div>
            <p class="text-muted small mb-0 mt-1">Kurasi literatur bermutu dan peranti menulis siap dikirimkan ke meja kerja Anda.</p>
        </section>

        <?php if (empty($items)): ?>
            <section class="bg-white border rounded-4 shadow-sm text-center p-4 p-md-5 cart-empty">
                <div class="cart-empty-icon mx-auto mb-3"><i class="bi bi-bag"></i></div>
                <h4 class="fw-bold text-dark mb-1">Keranjang Anda masih kosong</h4>
                <p class="text-muted small mb-4">Yuk, jelajahi koleksi buku dan alat tulis pilihan kami.</p>
                <a href="katalog.php" class="btn btn-success rounded-pill px-4 fw-semibold"><i class="bi bi-grid me-1"></i> Lihat Katalog</a>
            </section>
        <?php else: ?>

        <?php if (BATAS_GRATIS_ONGKIR > 0): ?>
        <section class="bg-white border rounded-4 shadow-sm p-3 p-md-4 mb-3 mb-lg-4">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="ship-icon"><i class="bi bi-truck"></i></div>
                <div class="flex-grow-1">
                    <div class="fw-bold text-dark small" id="shipText">
                        <?php if ($sisa_gratis > 0): ?>
                            Tambahkan <span class="text-danger"><?= formatRupiah($sisa_gratis); ?></span> lagi untuk mendapat gratis ongkir!
                        <?php else: ?>
                            Selamat! Pesanan Anda memenuhi syarat gratis ongkir.
                        <?php endif; ?>
                    </div>
                    <div class="text-muted" style="font-size: 0.75rem;">Dikemas aman dengan kardus tebal dan bubble wrap tanpa biaya tambahan.</div>
                </div>
                <span class="small fw-semibold text-muted" id="shipPct"><?= $persen_gratis; ?>%</span>
            </div>
            <div class="progress" style="height: 8px;">
                <div class="progress-bar bg-success" id="shipBar" role="progressbar" style="width: <?= $persen_gratis; ?>%;"></div>
            </div>
        </section>
        <?php endif; ?>

        <div class="row g-3 g-lg-4">
            <div class="col-12 col-lg-8">
                <div class="bg-white border rounded-4 shadow-sm p-3 mb-3 d-flex align-items-center justify-content-between gap-2">
                    <div class="form-check m-0 d-flex align-items-center gap-2">
                        <input class="form-check-input cart-check m-0" type="checkbox" id="checkAll" checked>
                        <label class="form-check-label fw-semibold text-dark small" for="checkAll">Pilih Semua (<span id="allCount"><?= count($items); ?></span> Produk)</label>
                    </div>
                    <button type="button" class="btn btn-link text-danger text-decoration-none fw-semibold small p-0" id="btnHapusTerpilih"><i class="bi bi-trash3 me-1"></i>Hapus Terpilih</button>
                </div>

                <div class="bg-white border rounded-4 shadow-sm overflow-hidden" id="cartList" data-batas="<?= (int) BATAS_GRATIS_ONGKIR; ?>">
                    <div class="cart-store-head d-flex align-items-center justify-content-between gap-2 px-3 py-2">
                        <div class="d-flex align-items-center gap-2 fw-bold small text-dark">
                            <i class="bi bi-shop text-success"></i> PustakaKu Official Store
                            <span class="badge bg-dark rounded-pill" style="font-size: 0.62rem;"><i class="bi bi-patch-check-fill me-1"></i>TERVERIFIKASI</span>
                        </div>
                    </div>

                    <?php foreach ($items as $it): ?>
                        <?php
                            $ada_diskon = $it['diskon'] !== null;
                            $persen = $ada_diskon ? round((($it['harga'] - $it['satuan']) / $it['harga']) * 100) : 0;
                            $tipis = $it['stok'] <= 10;
                        ?>
                        <article class="cart-item" data-id="<?= (int) $it['id']; ?>" data-satuan="<?= $it['satuan']; ?>" data-harga="<?= $it['harga']; ?>" data-stok="<?= (int) $it['stok']; ?>">
                            <div class="cart-item-check">
                                <input class="form-check-input cart-check item-check m-0" type="checkbox" checked aria-label="Pilih <?= htmlspecialchars($it['judul']); ?>">
                            </div>

                            <a href="#" class="cart-item-img">
                                <?php if ($ada_diskon): ?><span class="badge bg-danger">-<?= $persen; ?>%</span><?php endif; ?>
                                <img src="<?= htmlspecialchars($it['gambar']); ?>" alt="<?= htmlspecialchars($it['judul']); ?>" loading="lazy" onerror="this.onerror=null;this.src='https://placehold.co/160x210/eaf6ef/198754?text=Buku';">
                            </a>

                            <div class="cart-item-body">
                                <div class="d-flex align-items-start justify-content-between gap-2">
                                    <div class="min-w-0">
                                        <h6 class="product-title mb-1" title="<?= htmlspecialchars($it['judul']); ?>"><?= htmlspecialchars($it['judul']); ?></h6>
                                        <div class="text-muted small"><?= htmlspecialchars($it['penulis']); ?><?php if (!empty($it['penerbit'])): ?> &bull; <?= htmlspecialchars($it['penerbit']); ?><?php endif; ?></div>
                                        <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                                            <?php if ($tipis): ?>
                                                <span class="badge bg-danger-subtle text-danger fw-semibold"><i class="bi bi-exclamation-circle me-1"></i>Sisa <?= (int) $it['stok']; ?> eksemplar</span>
                                            <?php else: ?>
                                                <span class="badge bg-success-subtle text-success fw-semibold"><i class="bi bi-circle-fill me-1" style="font-size: 0.45rem;"></i>Stok Siap Kirim</span>
                                            <?php endif; ?>
                                            <?php if (!empty($it['berat'])): ?>
                                                <span class="text-muted" style="font-size: 0.72rem;">Berat: <?= (int) $it['berat']; ?> gr</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <button type="button" class="cart-remove" data-aksi="hapus" title="Hapus dari keranjang" aria-label="Hapus <?= htmlspecialchars($it['judul']); ?>"><i class="bi bi-x-lg"></i></button>
                                </div>

                                <div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mt-3">
                                    <div>
                                        <div class="fw-bold text-success fs-5 lh-1"><?= formatRupiah($it['satuan']); ?></div>
                                        <?php if ($ada_diskon): ?>
                                            <del class="text-muted small" style="font-size: 0.72rem;"><?= formatRupiah($it['harga']); ?></del>
                                        <?php endif; ?>
                                        <div class="text-muted item-subtotal" style="font-size: 0.72rem;">Subtotal: <strong><?= formatRupiah($it['subtotal']); ?></strong></div>
                                    </div>

                                    <div class="qty-stepper" role="group" aria-label="Jumlah">
                                        <button type="button" class="qty-btn" data-aksi="kurang" aria-label="Kurangi"><i class="bi bi-dash-lg"></i></button>
                                        <input type="text" class="qty-input" value="<?= (int) $it['qty']; ?>" readonly aria-label="Jumlah">
                                        <button type="button" class="qty-btn" data-aksi="tambah" aria-label="Tambah"><i class="bi bi-plus-lg"></i></button>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>

                <a href="katalog.php" class="d-inline-flex align-items-center gap-1 text-success fw-semibold text-decoration-none small mt-3"><i class="bi bi-arrow-left"></i> Lanjutkan Berbelanja</a>
            </div>

            <aside class="col-12 col-lg-4">
                <div class="cart-summary">
                    <div class="bg-white border rounded-4 shadow-sm p-3 p-md-4">
                        <h5 class="fw-bold text-dark mb-3">Ringkasan Belanja</h5>

                        <div class="summary-row"><span>Total Harga (<span id="sumItems"><?= $jumlah; ?></span> barang)</span><span id="sumNormal"><?= formatRupiah($total_normal); ?></span></div>
                        <div class="summary-row"><span>Total Diskon Produk</span><span class="text-success" id="sumHemat">-<?= formatRupiah($total_hemat); ?></span></div>
                        <div class="summary-row"><span>Ongkos Kirim</span><span class="text-muted small">Dihitung saat checkout</span></div>

                        <hr class="my-3">

                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fw-bold text-dark">Total Tagihan</span>
                            <span class="fw-bold text-success fs-4" id="sumTotal"><?= formatRupiah($total_bayar); ?></span>
                        </div>
                        <div class="small text-success fw-semibold mt-1" id="sumHematNote" <?= $total_hemat > 0 ? '' : 'hidden'; ?>>
                            <i class="bi bi-piggy-bank me-1"></i>Anda hemat <span id="sumHematNominal"><?= formatRupiah($total_hemat); ?></span> pada pesanan ini!
                        </div>

                        <form action="checkout.php" method="POST" id="checkoutForm" class="mt-3">
                            <div id="checkoutIds"></div>
                            <button type="submit" class="btn btn-success w-100 rounded-pill fw-bold py-2 d-flex align-items-center justify-content-center gap-2" id="btnCheckout">
                                Lanjut ke Pembayaran <i class="bi bi-arrow-right"></i>
                            </button>
                        </form>
                    </div>

                    <div class="bg-success-subtle border border-success-subtle rounded-4 p-3 p-md-4 mt-3">
                        <h6 class="fw-bold text-dark mb-3">Jaminan Belanja di PustakaKu</h6>
                        <div class="d-flex gap-2 mb-2"><i class="bi bi-shield-check text-success"></i><div class="small text-muted"><strong class="text-dark">100% Produk Asli:</strong> Seluruh buku bersumber langsung dari penerbit resmi.</div></div>
                        <div class="d-flex gap-2 mb-2"><i class="bi bi-truck text-success"></i><div class="small text-muted"><strong class="text-dark">Pengiriman Aman:</strong> Dikemas rapi dan berpelindung agar buku tiba mulus.</div></div>
                        <div class="d-flex gap-2"><i class="bi bi-arrow-repeat text-success"></i><div class="small text-muted"><strong class="text-dark">Garansi Ganti:</strong> Penggantian gratis jika buku rusak saat diterima.</div></div>
                    </div>
                </div>
            </aside>
        </div>
<?php endif; ?>

    </main>

    <?php include(__DIR__ . '/layouts/footer.php'); ?>
    <div class="modal fade" id="modalHapus" tabindex="-1" aria-labelledby="modalHapusLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow">
                <div class="modal-body text-center p-4">
                    <div class="rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px; font-size: 2rem;">
                        <i class="bi bi-trash3"></i>
                    </div>
                    <h5 class="fw-bold mb-2" id="modalHapusLabel">Hapus Buku?</h5>
                    <p class="text-muted small mb-4" id="pesanHapus">Apakah kamu yakin ingin menghapus buku ini dari keranjang?</p>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-light border rounded-pill flex-fill fw-semibold" data-bs-dismiss="modal">Batal</button>
                        <button type="button" class="btn btn-danger rounded-pill flex-fill fw-semibold" id="btnKonfirmasiHapus"><i class="bi bi-trash3 me-1"></i>Ya, Hapus</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const filterButtons = document.querySelectorAll('.filter-btn');
            const bookCards = document.querySelectorAll('.book-card');
            filterButtons.forEach(button => {
                button.addEventListener('click', function () {
                    const selectedCategory = this.dataset.filter;
                    filterButtons.forEach(btn => {
                        btn.classList.remove('active', 'btn-success');
                        btn.classList.add('btn-outline-success');
                    });
                    this.classList.add('active', 'btn-success');
                    this.classList.remove('btn-outline-success');
                    bookCards.forEach(card => {
                        const bookCategory = card.dataset.category;
                        if (selectedCategory === 'all' || bookCategory === selectedCategory) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });
        });
    </script>
</body>
</html>