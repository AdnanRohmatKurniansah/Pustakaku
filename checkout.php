<?php
    session_start();

    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/cart/proses_keranjang.php';

    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = 'Silakan masuk ke akun Anda terlebih dahulu untuk melanjutkan checkout.';
        $_SESSION['redirect_after_login'] = 'checkout.php';
        header('Location: auth/login.php');
        exit;
    }

    if (($_SESSION['role'] ?? '') === 'admin') {
        $_SESSION['error'] = 'Akun admin tidak dapat melakukan pembelian.';
        header('Location: admin/index.php');
        exit;
    }

    $user_id = (int) $_SESSION['user_id'];
    $q_user = mysqli_query($conn, "SELECT * FROM users WHERE id = '$user_id'");
    $user = $q_user ? mysqli_fetch_assoc($q_user) : null;

    if (!$user) {
        $_SESSION['error'] = 'Data pengguna tidak ditemukan.';
        header('Location: auth/login.php');
        exit;
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cart_ids']) && is_array($_POST['cart_ids'])) {
        $_SESSION['checkout_buku_ids'] = array_values(array_filter(array_map('intval', $_POST['cart_ids'])));
    }

    $keranjang = cart_items($conn);
    $all_items = $keranjang['items'];

    if (empty($all_items)) {
        $_SESSION['error'] = 'Keranjang belanja Anda masih kosong. Silakan pilih buku terlebih dahulu.';
        header('Location: keranjang.php');
        exit;
    }

    $items = [];
    if (!empty($_SESSION['checkout_buku_ids'])) {
        foreach ($all_items as $item) {
            if (in_array((int) $item['id'], $_SESSION['checkout_buku_ids'], true)) {
                $items[] = $item;
            }
        }
    }

    if (empty($items)) {
        $items = $all_items;
    }

    $total_normal = 0.0;
    $total_buku = 0.0;
    $total_berat = 0;
    $total_qty = 0;

    foreach ($items as $it) {
        $total_normal += $it['harga'] * $it['qty'];
        $total_buku += $it['satuan'] * $it['qty'];
        $total_berat += ($it['berat'] > 0 ? $it['berat'] : 250) * $it['qty'];
        $total_qty += $it['qty'];
    }

    $total_hemat = $total_normal - $total_buku;
    $dapat_gratis_ongkir = ($total_buku >= 150000);
    $potongan_ongkir_subsidi = $dapat_gratis_ongkir ? 15000 : 0;
    $ongkir_reguler = max(0, 15000 - $potongan_ongkir_subsidi);
    $ongkir_express = max(0, 25000 - $potongan_ongkir_subsidi);
    $total_bayar = $total_buku + $ongkir_reguler;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout Pesanan - PustakaKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <?php include(__DIR__ . '/layouts/navbar.php'); ?>

    <main class="container my-3 my-md-4">
        <section class="mb-3 mb-lg-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-2">
                    <li class="breadcrumb-item"><a href="index.php" class="text-success text-decoration-none">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="keranjang.php" class="text-success text-decoration-none">Keranjang</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Checkout Pesanan</li>
                </ol>
            </nav>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <h2 class="fw-bold text-dark mb-0">Checkout Pesanan</h2>
            </div>
            <p class="text-muted small mb-0 mt-1">Lengkapi alamat pengiriman dan pilih metode pembayaran untuk menyelesaikan pesanan Anda.</p>
        </section>

        <form action="proses_checkout.php" method="POST" id="formCheckout">
            <div class="row g-3 g-lg-4">
                <div class="col-12 col-lg-8">
                    <div class="bg-white border rounded-4 shadow-sm p-3 p-md-4 mb-3">
                        <div class="d-flex align-items-center gap-2 pb-3 border-bottom mb-3">
                            <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;"><i class="bi bi-geo-alt-fill fs-5"></i></div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Alamat Pengiriman</h5>
                                <div class="text-muted small">Pesanan buku akan dikirimkan ke alamat ini</div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="nama_penerima" class="form-label small fw-semibold text-secondary">Nama Penerima <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-3" id="nama_penerima" name="nama_penerima" value="<?= htmlspecialchars($user['nama_lengkap']); ?>" required>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="no_telepon" class="form-label small fw-semibold text-secondary">Nomor Telepon / WhatsApp <span class="text-danger">*</span></label>
                                <input type="tel" class="form-control rounded-3" id="no_telepon" name="no_telepon" placeholder="Contoh: 08123456789" value="<?= htmlspecialchars($user['no_telepon'] ?? ''); ?>" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="alamat_pengiriman" class="form-label small fw-semibold text-secondary">Alamat Lengkap Pengiriman <span class="text-danger">*</span></label>
                            <textarea class="form-control rounded-3" id="alamat_pengiriman" name="alamat_pengiriman" rows="3" placeholder="Nama jalan, nomor rumah/kantor, RT/RW, kelurahan, kecamatan, kota/kabupaten, dan kode pos" required><?= htmlspecialchars($user['alamat_lengkap'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="simpan_ke_profil" value="1" id="simpanKeProfil" <?= empty($user['alamat_lengkap']) ? 'checked' : ''; ?>>
                            <label class="form-check-label small text-muted" for="simpanKeProfil">
                                Simpan / perbarui nomor telepon dan alamat ini sebagai alamat default profil akun saya
                            </label>
                        </div>
                    </div>

                    <div class="bg-white border rounded-4 shadow-sm p-3 p-md-4 mb-3">
                        <div class="d-flex align-items-center gap-2 pb-3 border-bottom mb-3">
                            <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;"><i class="bi bi-truck fs-5"></i></div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Opsi Pengiriman</h5>
                                <div class="text-muted small">Estimasi total berat paket: <?= number_format($total_berat / 1000, 1, ',', '.'); ?> kg (<?= $total_berat; ?> gram)</div>
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-2" id="shippingOptions">
                            <label class="border rounded-4 p-3 d-flex align-items-center justify-content-between gap-3 cursor-pointer shipping-card border-success bg-success-subtle bg-opacity-25" style="cursor: pointer;">
                                <div class="d-flex align-items-center gap-3">
                                    <input class="form-check-input m-0 shipping-radio" type="radio" name="opsi_pengiriman" value="reguler" data-ongkir="<?= $ongkir_reguler; ?>" checked>
                                    <div>
                                        <div class="fw-bold text-dark">Kurir Reguler (JNE / J&T / SiCepat)</div>
                                        <div class="text-muted small">Estimasi tiba 2-4 hari kerja</div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <?php if ($dapat_gratis_ongkir): ?>
                                        <span class="badge bg-success rounded-pill px-3 py-1 fw-bold">GRATIS</span>
                                        <div class="text-muted small" style="font-size: 11px;"><del>Rp 15.000</del></div>
                                    <?php else: ?>
                                        <div class="fw-bold text-dark">Rp 15.000</div>
                                    <?php endif; ?>
                                </div>
                            </label>

                            <label class="border rounded-4 p-3 d-flex align-items-center justify-content-between gap-3 cursor-pointer shipping-card" style="cursor: pointer;">
                                <div class="d-flex align-items-center gap-3">
                                    <input class="form-check-input m-0 shipping-radio" type="radio" name="opsi_pengiriman" value="express" data-ongkir="<?= $ongkir_express; ?>">
                                    <div>
                                        <div class="fw-bold text-dark">Kurir Kilat Express (JNE YES / SiCepat BEST)</div>
                                        <div class="text-muted small">Estimasi tiba 1-2 hari kerja</div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <?php if ($dapat_gratis_ongkir): ?>
                                        <div class="fw-bold text-success"><?= formatRupiah($ongkir_express); ?></div>
                                        <div class="text-muted small" style="font-size: 11px;"><del>Rp 25.000</del> <span class="text-success fw-semibold">(-Rp 15.000)</span></div>
                                    <?php else: ?>
                                        <div class="fw-bold text-dark">Rp 25.000</div>
                                    <?php endif; ?>
                                </div>
                            </label>
                        </div>
                    </div>

                    <div class="bg-white border rounded-4 shadow-sm p-3 p-md-4 mb-3">
                        <div class="d-flex align-items-center gap-2 pb-3 border-bottom mb-3">
                            <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;"><i class="bi bi-wallet2 fs-5"></i></div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Metode Pembayaran <span class="text-danger">*</span></h5>
                                <div class="text-muted small">Pilih metode pembayaran yang paling nyaman untuk Anda</div>
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-2" id="paymentOptions">
                            <label class="border rounded-4 p-3 d-flex align-items-center justify-content-between gap-3 payment-card border-success bg-success-subtle bg-opacity-25" style="cursor: pointer;">
                                <div class="d-flex align-items-center gap-3">
                                    <input class="form-check-input m-0 payment-radio" type="radio" name="metode_pembayaran" value="Transfer Bank BCA" checked>
                                    <div>
                                        <div class="fw-bold text-dark"><i class="bi bi-bank me-2 text-primary"></i>Transfer Bank BCA</div>
                                        <div class="text-muted small">No. Rek: 8830-1928-33 a.n. PT PustakaKu Indonesia</div>
                                    </div>
                                </div>
                                <span class="badge bg-light text-dark border">Otomatis / Manual</span>
                            </label>

                            <label class="border rounded-4 p-3 d-flex align-items-center justify-content-between gap-3 payment-card" style="cursor: pointer;">
                                <div class="d-flex align-items-center gap-3">
                                    <input class="form-check-input m-0 payment-radio" type="radio" name="metode_pembayaran" value="Transfer Bank Mandiri">
                                    <div>
                                        <div class="fw-bold text-dark"><i class="bi bi-bank me-2 text-warning"></i>Transfer Bank Mandiri</div>
                                        <div class="text-muted small">No. Rek: 137-00-9821-443 a.n. PT PustakaKu Indonesia</div>
                                    </div>
                                </div>
                                <span class="badge bg-light text-dark border">Transfer Bank</span>
                            </label>

                            <label class="border rounded-4 p-3 d-flex align-items-center justify-content-between gap-3 payment-card" style="cursor: pointer;">
                                <div class="d-flex align-items-center gap-3">
                                    <input class="form-check-input m-0 payment-radio" type="radio" name="metode_pembayaran" value="Transfer Bank BRI">
                                    <div>
                                        <div class="fw-bold text-dark"><i class="bi bi-bank me-2 text-info"></i>Transfer Bank BRI</div>
                                        <div class="text-muted small">No. Rek: 0206-01-002341-30 a.n. PT PustakaKu Indonesia</div>
                                    </div>
                                </div>
                                <span class="badge bg-light text-dark border">Transfer Bank</span>
                            </label>

                            <label class="border rounded-4 p-3 d-flex align-items-center justify-content-between gap-3 payment-card" style="cursor: pointer;">
                                <div class="d-flex align-items-center gap-3">
                                    <input class="form-check-input m-0 payment-radio" type="radio" name="metode_pembayaran" value="E-Wallet (GoPay / OVO / Dana / QRIS)">
                                    <div>
                                        <div class="fw-bold text-dark"><i class="bi bi-qr-code-scan me-2 text-success"></i>E-Wallet & QRIS</div>
                                        <div class="text-muted small">GoPay, OVO, ShopeePay, Dana & Seluruh Bank QRIS</div>
                                    </div>
                                </div>
                                <span class="badge bg-success-subtle text-success border border-success-subtle">Instan</span>
                            </label>

                            <label class="border rounded-4 p-3 d-flex align-items-center justify-content-between gap-3 payment-card" style="cursor: pointer;">
                                <div class="d-flex align-items-center gap-3">
                                    <input class="form-check-input m-0 payment-radio" type="radio" name="metode_pembayaran" value="COD (Bayar di Tempat)">
                                    <div>
                                        <div class="fw-bold text-dark"><i class="bi bi-cash-stack me-2 text-secondary"></i>COD (Bayar di Tempat)</div>
                                        <div class="text-muted small">Bayar tunai langsung saat kurir mengantarkan paket ke rumah Anda</div>
                                    </div>
                                </div>
                                <span class="badge bg-secondary-subtle text-dark border">Bayar di Tempat</span>
                            </label>
                        </div>
                    </div>

                    <div class="bg-white border rounded-4 shadow-sm p-3 p-md-4">
                        <div class="d-flex align-items-center gap-2 pb-3 border-bottom mb-3">
                            <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;"><i class="bi bi-chat-left-text fs-5"></i></div>
                            <div>
                                <h5 class="fw-bold text-dark mb-0">Catatan untuk Penjual</h5>
                                <div class="text-muted small">Tuliskan pesan atau instruksi khusus untuk paket Anda (opsional)</div>
                            </div>
                        </div>

                        <textarea class="form-control rounded-3" id="catatan_pembeli" name="catatan_pembeli" rows="3" placeholder="Contoh: Titipkan di satpam jika tidak ada orang di rumah / tolong packing kardus tebal"></textarea>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="bg-white border rounded-4 shadow-sm p-3 p-md-4 mb-3">
                        <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-3">
                            <h5 class="fw-bold text-dark mb-0">Ringkasan Pesanan</h5>
                            <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1 fw-semibold"><?= count($items); ?> Judul</span>
                        </div>

                        <div class="checkout-items-list mb-3" style="max-height: 280px; overflow-y: auto;">
                            <?php foreach ($items as $it): ?>
                                <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                                    <img src="<?= htmlspecialchars($it['gambar']); ?>" alt="<?= htmlspecialchars($it['judul']); ?>" class="rounded-3 border object-fit-cover flex-shrink-0" style="width: 48px; height: 64px;" onerror="this.onerror=null;this.src='assets/images/placeholder.png';">
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="fw-semibold text-dark text-truncate small" title="<?= htmlspecialchars($it['judul']); ?>"><?= htmlspecialchars($it['judul']); ?></div>
                                        <div class="text-muted" style="font-size: 11px;"><?= (int) $it['qty']; ?> x <?= formatRupiah($it['satuan']); ?></div>
                                        <div class="fw-bold text-success small"><?= formatRupiah($it['subtotal']); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="d-flex justify-content-between mb-2 small text-secondary">
                            <span>Total Harga Buku</span>
                            <span class="text-dark fw-semibold"><?= formatRupiah($total_normal); ?></span>
                        </div>

                        <?php if ($total_hemat > 0): ?>
                            <div class="d-flex justify-content-between mb-2 small text-success">
                                <span>Total Diskon Produk</span>
                                <span class="fw-semibold">-<?= formatRupiah($total_hemat); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between mb-2 small text-secondary">
                            <span>Subtotal Buku</span>
                            <span class="text-dark fw-semibold" id="labelSubtotalBuku"><?= formatRupiah($total_buku); ?></span>
                        </div>

                        <div class="d-flex justify-content-between mb-2 small text-secondary">
                            <span>Ongkos Kirim</span>
                            <span class="text-dark fw-semibold" id="labelOngkir"><?= $ongkir_reguler === 0 ? 'GRATIS' : formatRupiah($ongkir_reguler); ?></span>
                        </div>

                        <?php if ($dapat_gratis_ongkir): ?>
                            <div class="d-flex justify-content-between mb-3 small text-success">
                                <span><i class="bi bi-tag-fill me-1"></i>Subsidi Gratis Ongkir</span>
                                <span class="fw-semibold">-Rp 15.000</span>
                            </div>
                        <?php endif; ?>

                        <hr class="my-3">

                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="fw-bold text-dark fs-6">Total Pembayaran</span>
                            <span class="fw-bold text-success fs-4" id="labelTotalBayar"><?= formatRupiah($total_bayar); ?></span>
                        </div>

                        <input type="hidden" name="ongkos_kirim" id="inputOngkir" value="<?= $ongkir_reguler; ?>">

                        <button type="submit" class="btn btn-success w-100 rounded-pill py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2" id="btnProsesPesanan">
                            <i class="bi bi-shield-check fs-5"></i>
                            <span>Bayar & Buat Pesanan</span>
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </main>

    <?php include(__DIR__ . '/layouts/footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const totalBuku = <?= (float) $total_buku; ?>;
            const shippingRadios = document.querySelectorAll('.shipping-radio');
            const labelOngkir = document.getElementById('labelOngkir');
            const labelTotalBayar = document.getElementById('labelTotalBayar');
            const inputOngkir = document.getElementById('inputOngkir');

            function formatRupiahJS(angka) {
                return 'Rp ' + angka.toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            shippingRadios.forEach(function (radio) {
                radio.addEventListener('change', function () {
                    document.querySelectorAll('.shipping-card').forEach(function (c) {
                        c.classList.remove('border-success', 'bg-success-subtle', 'bg-opacity-25');
                    });
                    const parentCard = this.closest('.shipping-card');
                    if (parentCard) {
                        parentCard.classList.add('border-success', 'bg-success-subtle', 'bg-opacity-25');
                    }

                    const ongkir = parseFloat(this.dataset.ongkir) || 0;
                    inputOngkir.value = ongkir;
                    labelOngkir.textContent = ongkir === 0 ? 'GRATIS' : formatRupiahJS(ongkir);
                    labelTotalBayar.textContent = formatRupiahJS(totalBuku + ongkir);
                });
            });

            const paymentRadios = document.querySelectorAll('.payment-radio');
            paymentRadios.forEach(function (radio) {
                radio.addEventListener('change', function () {
                    document.querySelectorAll('.payment-card').forEach(function (c) {
                        c.classList.remove('border-success', 'bg-success-subtle', 'bg-opacity-25');
                    });
                    const parentCard = this.closest('.payment-card');
                    if (parentCard) {
                        parentCard.classList.add('border-success', 'bg-success-subtle', 'bg-opacity-25');
                    }
                });
            });

            const formCheckout = document.getElementById('formCheckout');
            if (formCheckout) {
                formCheckout.addEventListener('submit', function (e) {
                    const btn = document.getElementById('btnProsesPesanan');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses Pesanan...';
                    }
                });
            }
        });
    </script>
</body>
</html>

