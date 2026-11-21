<?php
    session_start();

    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/cart/proses_keranjang.php';
    require_once __DIR__ . '/services/cloudinary_service.php';
    require_once __DIR__ . '/services/rajaongkir_service.php';

    if (!isset($_SESSION['user_id'])) {
        $_SESSION['error'] = 'Silakan masuk ke akun Anda terlebih dahulu untuk melanjutkan checkout.';
        $_SESSION['redirect_after_login'] = '../checkout.php';
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

    $q_alamat = mysqli_query($conn, "SELECT * FROM user_alamat WHERE user_id = '$user_id' ORDER BY is_primary DESC, id ASC");
    $alamat_list = [];
    $selected_alamat = null;
    if ($q_alamat) {
        while ($al = mysqli_fetch_assoc($q_alamat)) {
            $alamat_list[] = $al;
            if ($al['is_primary'] == 1 && $selected_alamat === null) {
                $selected_alamat = $al;
            }
        }
    }
    if ($selected_alamat === null && !empty($alamat_list)) {
        $selected_alamat = $alamat_list[0];
    }

    $default_city_id = $selected_alamat['kota_id'] ?? 501;
    $initial_courier = 'jne';
    $initial_services = rajaongkir_calculate_cost($default_city_id, $total_berat, $initial_courier);
    $initial_cost = 0;
    $initial_service_name = 'JNE - Reguler';
    if (!empty($initial_services) && is_array($initial_services)) {
        $first_srv = $initial_services[0];
        $initial_cost = (int)($first_srv['cost'] ?? 0);
        $initial_service_name = $first_srv['service_name'] ?? ($first_srv['service'] ?? 'JNE - Reguler');
        if (!empty($first_srv['etd']) && strpos($initial_service_name, $first_srv['etd']) === false) {
            $initial_service_name .= ' (' . $first_srv['etd'] . ')';
        }
    }

    $total_bayar = $total_buku + $initial_cost;
    $provinces = rajaongkir_get_provinces();

    $midtrans_client_key = $_ENV['MIDTRANS_CLIENT_KEY'] ?? '';
    $midtrans_is_prod = (($_ENV['MIDTRANS_IS_PRODUCTION'] ?? 'false') === 'true');
    $snap_js_url = $midtrans_is_prod ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout & Pembayaran - PustakaKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="assets/images/favicon.png" type="image/png">
    <?php if (!empty($midtrans_client_key)): ?>
        <script src="<?= $snap_js_url; ?>" data-client-key="<?= htmlspecialchars($midtrans_client_key); ?>"></script>
    <?php endif; ?>
</head>
<body class="bg-light">
    <?php include(__DIR__ . '/layouts/navbar.php'); ?>

    <main class="container my-3 my-md-4">
        <ol class="breadcrumb small mb-3">
                    <li class="breadcrumb-item active"><a href="index.php" class="text-success text-decoration-none">Beranda</a></li>
                    <li class="breadcrumb-item"><a href="keranjang.php" class="text-success text-decoration-none">Keranjang Belanja</a></li>     
                    <li class="breadcrumb-item active" aria-current="page">Checkout</li>
        </ol>

        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <form action="proses_checkout.php" method="POST" id="formCheckout">
            <?php 
                $selected_alamat_full = '';
                if ($selected_alamat) {
                    $kec_str = !empty($selected_alamat['kecamatan']) ? 'Kec. ' . $selected_alamat['kecamatan'] . ', ' : '';
                    $selected_alamat_full = $selected_alamat['alamat_lengkap'] . ', ' . $kec_str . $selected_alamat['kota_nama'] . ', ' . $selected_alamat['provinsi_nama'] . ' ' . $selected_alamat['kode_pos'];
                }
            ?>
            <input type="hidden" name="alamat_id" id="inputAlamatId" value="<?= $selected_alamat ? (int)$selected_alamat['id'] : 0; ?>">
            <input type="hidden" name="nama_penerima" id="inputNamaPenerima" value="<?= $selected_alamat ? htmlspecialchars($selected_alamat['nama_penerima']) : htmlspecialchars($user['nama_lengkap']); ?>">
            <input type="hidden" name="no_telepon" id="inputNoTelepon" value="<?= $selected_alamat ? htmlspecialchars($selected_alamat['no_telepon']) : htmlspecialchars($user['no_telepon'] ?? ''); ?>">
            <input type="hidden" name="alamat_pengiriman" id="inputAlamatPengiriman" value="<?= $selected_alamat ? htmlspecialchars($selected_alamat_full) : ''; ?>">
            <input type="hidden" name="kurir_service" id="inputKurirService" value="<?= htmlspecialchars($initial_service_name); ?>">
            <input type="hidden" name="ongkos_kirim" id="inputOngkir" value="<?= $initial_cost; ?>">
            <input type="hidden" name="metode_pembayaran" value="Transfer Bank / QRIS">
            <input type="hidden" id="currentCityId" value="<?= $default_city_id; ?>">
            <input type="hidden" id="totalBeratPaket" value="<?= $total_berat; ?>">

            <div class="row g-3 g-lg-4">
                <div class="col-12 col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-3 bg-white">
                        <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i class="bi bi-geo-alt-fill fs-5"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-0 fs-6">Alamat Pengiriman</h6>
                            </div>
                            <?php if (!empty($alamat_list)): ?>
                                <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalPilihAlamat">
                                    <i class="bi bi-arrow-left-right me-1"></i>Pilih Alamat Lain
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn btn-success btn-sm rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalTambahAlamatCheckout">
                                    <i class="bi bi-plus-lg me-1"></i>Tambah Alamat
                                </button>
                            <?php endif; ?>
                        </div>

                        <?php if ($selected_alamat): ?>
                            <div class="p-3 rounded-4 bg-light border border-success-subtle" id="displayAlamatTerpilih">
                                <div class="d-flex align-items-center gap-2 mb-2">
                                    <span class="badge bg-success rounded-pill px-2 py-1 small"><?= htmlspecialchars($selected_alamat['label_alamat']); ?></span>
                                    <strong class="text-dark small" id="displayNamaTelp"><?= htmlspecialchars($selected_alamat['nama_penerima']); ?> (<?= htmlspecialchars($selected_alamat['no_telepon']); ?>)</strong>
                                </div>
                                <div class="text-secondary small mb-1" id="displayAlamatBaris1"><?= htmlspecialchars($selected_alamat['alamat_lengkap']); ?></div>
                                <div class="text-muted small" id="displayAlamatBaris2"><?= !empty($selected_alamat['kecamatan']) ? 'Kec. ' . htmlspecialchars($selected_alamat['kecamatan']) . ', ' : ''; ?><?= htmlspecialchars($selected_alamat['kota_nama']); ?>, <?= htmlspecialchars($selected_alamat['provinsi_nama']); ?> <?= htmlspecialchars($selected_alamat['kode_pos']); ?></div>
                            </div>
                        <?php else: ?>
                            <div class="p-4 rounded-4 bg-light border text-center" id="emptyAlamatNotice">
                                <div class="rounded-circle bg-white text-success shadow-sm d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 54px; height: 54px;">
                                    <i class="bi bi-geo-alt-fill fs-3"></i>
                                </div>
                                <h6 class="fw-bold text-dark mb-1">Belum Ada Alamat Pengiriman</h6>
                                <p class="text-muted small mb-3">Tambahkan alamat pengiriman Anda terlebih dahulu agar sistem dapat menghitung ongkos kirim dan mengirimkan pesanan ke lokasi Anda.</p>
                                <button type="button" class="btn btn-success rounded-pill px-4 py-2 fw-semibold btn-sm shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahAlamatCheckout">
                                    <i class="bi bi-plus-circle me-1"></i>Tambah Alamat Sekarang
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-3 bg-white">
                        <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-3">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                    <i class="bi bi-truck fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0 fs-6">Layanan Pengiriman</h6>
                                    <div class="text-muted small">Berat total paket: <?= number_format($total_berat / 1000, 1, ',', '.'); ?> kg (<?= $total_berat; ?> gram)</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mb-3">
                            <div class="flex-fill">
                                <input type="radio" class="btn-check courier-choice" name="pilih_kurir" id="kurir_jne" value="jne" checked>
                                <label class="btn btn-outline-success w-100 py-1 rounded-3 fw-semibold small" for="kurir_jne">JNE Express</label>
                            </div>
                            <div class="flex-fill">
                                <input type="radio" class="btn-check courier-choice" name="pilih_kurir" id="kurir_pos" value="pos">
                                <label class="btn btn-outline-success w-100 py-1 rounded-3 fw-semibold small" for="kurir_pos">POS Indonesia</label>
                            </div>
                            <div class="flex-fill">
                                <input type="radio" class="btn-check courier-choice" name="pilih_kurir" id="kurir_tiki" value="tiki">
                                <label class="btn btn-outline-success w-100 py-1 rounded-3 fw-semibold small" for="kurir_tiki">TIKI</label>
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-2" id="shippingServicesList">
                            <?php if (empty($selected_alamat)): ?>
                                <div class="text-muted small p-4 bg-light rounded-4 border text-center">
                                    <i class="bi bi-info-circle text-success fs-5 d-block mb-1"></i>
                                    Silakan tambahkan alamat pengiriman di atas terlebih dahulu untuk menampilkan pilihan kurir dan tarif ongkos kirim.
                                </div>
                            <?php elseif (!empty($initial_services)): ?>
                                <?php foreach ($initial_services as $idx => $srv): ?>
                                    <?php 
                                        $is_srv_checked = ($idx === 0);
                                        $display_title = $srv['service_name'] ?? ($srv['service'] ?? 'Layanan Pengiriman');
                                        $display_etd = $srv['etd'] ?? '-';
                                    ?>
                                    <label class="border rounded-4 p-3 d-flex align-items-center justify-content-between gap-3 cursor-pointer service-card <?= $is_srv_checked ? 'border-success bg-success-subtle bg-opacity-25' : ''; ?>" style="cursor: pointer;">
                                        <div class="d-flex align-items-center gap-3">
                                            <input class="form-check-input m-0 service-radio" type="radio" name="service_choice" value="<?= (int)$srv['cost']; ?>" data-name="<?= htmlspecialchars($display_title); ?>" <?= $is_srv_checked ? 'checked' : ''; ?>>
                                            <div>
                                                <div class="fw-bold text-dark small"><?= htmlspecialchars($display_title); ?></div>
                                                <div class="text-muted" style="font-size: 11px;"><i class="bi bi-clock-history me-1"></i>Estimasi tiba: <?= htmlspecialchars($display_etd); ?></div>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="fw-bold text-success"><?= formatRupiah($srv['cost']); ?></div>
                                        </div>
                                    </label>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div class="text-muted small p-2">Pilih kurir untuk menampilkan layanan pengiriman.</div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <div class="d-flex align-items-center gap-2 pb-2 mb-2">
                            <i class="bi bi-chat-left-text text-success"></i>
                            <h6 class="fw-bold text-dark mb-0 small">Catatan Pesanan (Opsional)</h6>
                        </div>
                        <textarea class="form-control rounded-3" id="catatan_pembeli" name="catatan_pembeli" rows="3" placeholder="Tuliskan catatan khusus untuk penjual (misal: mohon packing kardus tebal)"></textarea>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white checkout-sticky-summary">
                        <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-3">
                            <h6 class="fw-bold text-dark mb-0 fs-6">Ringkasan Pesanan</h6>
                            <span class="badge bg-success-subtle text-success rounded-pill px-2 py-1 small"><?= count($items); ?> Item</span>
                        </div>

                        <div class="checkout-items-list mb-3" style="max-height: 240px; overflow-y: auto;">
                            <?php foreach ($items as $it): ?>
                                <?php $thumb_img = resolveImageUrl($it['gambar'], 'buku'); ?>
                                <div class="d-flex align-items-center gap-3 py-2 border-bottom">
                                    <img src="<?= htmlspecialchars($thumb_img); ?>" alt="<?= htmlspecialchars($it['judul']); ?>" class="rounded-3 border object-fit-cover flex-shrink-0" style="width: 44px; height: 58px;" onerror="this.onerror=null;this.src='assets/images/placeholder.png';">
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="fw-semibold text-dark text-truncate small" title="<?= htmlspecialchars($it['judul']); ?>"><?= htmlspecialchars($it['judul']); ?></div>
                                        <div class="text-muted" style="font-size: 11px;"><?= (int) $it['qty']; ?> x <?= formatRupiah($it['satuan']); ?></div>
                                        <div class="fw-bold text-success small"><?= formatRupiah($it['subtotal']); ?></div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="d-flex justify-content-between mb-2 small text-secondary">
                            <span>Subtotal Buku</span>
                            <span class="text-dark fw-semibold" id="labelSubtotalBuku"><?= formatRupiah($total_buku); ?></span>
                        </div>

                        <?php if ($total_hemat > 0): ?>
                            <div class="d-flex justify-content-between mb-2 small text-success">
                                <span>Hemat Diskon</span>
                                <span class="fw-semibold">-<?= formatRupiah($total_hemat); ?></span>
                            </div>
                        <?php endif; ?>

                        <div class="d-flex justify-content-between mb-2 small text-secondary">
                            <span>Ongkos Kirim</span>
                            <span class="text-dark fw-semibold" id="labelOngkir"><?= formatRupiah($initial_cost); ?></span>
                        </div>

                        <div class="d-flex justify-content-between mb-3 small text-muted" style="font-size: 11px;">
                            <span>Layanan:</span>
                            <span class="text-dark fw-semibold text-truncate" id="labelLayananKurir" style="max-width: 170px;"><?= htmlspecialchars($initial_service_name); ?></span>
                        </div>

                        <hr class="my-3">

                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="fw-bold text-dark fs-6">Total Tagihan</span>
                            <span class="fw-bold text-success fs-4" id="labelTotalBayar"><?= formatRupiah($total_bayar); ?></span>
                        </div>

                        <button type="submit" class="btn btn-success w-100 rounded-pill py-2 fw-bold fs-6 shadow-sm d-flex align-items-center justify-content-center gap-2" id="btnSubmitCheckout" <?= empty($selected_alamat) ? 'disabled' : ''; ?>>
                            <span>Bayar Sekarang</span>
                        </button>
                        <?php if (empty($selected_alamat)): ?>
                            <div class="text-danger small mt-2 text-center" id="alertTambahAlamatDulu" style="font-size: 11px;">
                                <i class="bi bi-exclamation-circle me-1"></i>Silakan tambahkan alamat pengiriman terlebih dahulu untuk melanjutkan pembayaran.
                            </div>
                        <?php endif; ?>

                        <div class="text-center text-muted small mt-3 d-flex align-items-center justify-content-center gap-1" style="font-size: 11px;">
                            <i class="bi bi-shield-check text-success fs-6"></i>
                            <span>100% Pembayaran Aman & Terenkripsi</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </main>

    <?php if (!empty($alamat_list)): ?>
        <div class="modal fade" id="modalPilihAlamat" tabindex="-1" aria-labelledby="modalPilihAlamatLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content rounded-4 border-0 shadow">
                    <div class="modal-header border-bottom">
                        <h6 class="modal-title fw-bold text-dark" id="modalPilihAlamatLabel"><i class="bi bi-geo-alt-fill text-success me-2"></i>Pilih Alamat Pengiriman</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="d-flex flex-column gap-2 mb-3">
                            <?php foreach ($alamat_list as $al): ?>
                                <?php 
                                    $is_checked = ($selected_alamat && (int)$selected_alamat['id'] === (int)$al['id']); 
                                    $al_kec = !empty($al['kecamatan']) ? 'Kec. ' . $al['kecamatan'] . ', ' : '';
                                    $al_baris2 = $al_kec . $al['kota_nama'] . ', ' . $al['provinsi_nama'] . ' ' . $al['kode_pos'];
                                    $al_full = $al['alamat_lengkap'] . ', ' . $al_baris2;
                                ?>
                                <label class="border rounded-4 p-3 d-flex align-items-start gap-3 cursor-pointer modal-address-card <?= $is_checked ? 'border-success bg-success-subtle bg-opacity-25' : ''; ?>" style="cursor: pointer;">
                                    <input class="form-check-input mt-1 modal-address-radio" type="radio" name="modal_pilih_alamat" value="<?= (int)$al['id']; ?>" data-city-id="<?= (int)$al['kota_id']; ?>" data-label="<?= htmlspecialchars($al['label_alamat']); ?>" data-nama="<?= htmlspecialchars($al['nama_penerima']); ?>" data-telp="<?= htmlspecialchars($al['no_telepon']); ?>" data-baris1="<?= htmlspecialchars($al['alamat_lengkap']); ?>" data-baris2="<?= htmlspecialchars($al_baris2); ?>" data-full="<?= htmlspecialchars($al_full); ?>" <?= $is_checked ? 'checked' : ''; ?>>
                                    <div class="flex-grow-1">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge <?= ($al['is_primary'] == 1) ? 'bg-success' : 'bg-secondary'; ?> rounded-pill px-2 py-1 small"><?= htmlspecialchars($al['label_alamat']); ?></span>
                                            <span class="fw-bold text-dark small"><?= htmlspecialchars($al['nama_penerima']); ?> (<?= htmlspecialchars($al['no_telepon']); ?>)</span>
                                        </div>
                                        <div class="text-secondary small"><?= htmlspecialchars($al['alamat_lengkap']); ?></div>
                                        <div class="text-muted small"><?= htmlspecialchars($al_baris2); ?></div>
                                    </div>
                                </label>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light justify-content-between">
                        <button type="button" class="btn btn-outline-success rounded-pill btn-sm px-3 fw-semibold" id="btnBukaModalTambahAlamat">
                            <i class="bi bi-plus-lg me-1"></i>Tambah Alamat Baru
                        </button>
                        <button type="button" class="btn btn-success rounded-pill btn-sm px-4 fw-semibold" data-bs-dismiss="modal">Gunakan Alamat Ini</button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="modal fade" id="modalTambahAlamatCheckout" tabindex="-1" aria-labelledby="modalTambahAlamatCheckoutLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom">
                    <h6 class="modal-title fw-bold text-dark" id="modalTambahAlamatCheckoutLabel"><i class="bi bi-geo-alt-fill text-success me-2"></i>Tambah Alamat Pengiriman Baru</h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="proses_alamat.php" method="POST">
                    <input type="hidden" name="aksi" value="tambah">
                    <input type="hidden" name="redirect" value="checkout.php">
                    <input type="hidden" name="provinsi_nama" id="co_modal_provinsi_nama" value="">
                    <input type="hidden" name="kota_nama" id="co_modal_kota_nama" value="">

                    <div class="modal-body p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-semibold text-secondary">Label Alamat <span class="text-danger">*</span></label>
                                <input type="text" name="label_alamat" class="form-control rounded-3" placeholder="Contoh: Rumah, Kantor" required value="Rumah">
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
                                <select class="form-select rounded-3" name="provinsi_id" id="co_modal_provinsi_id" required>
                                    <option value="">-- Pilih Provinsi --</option>
                                    <?php foreach ($provinces as $prov): ?>
                                        <option value="<?= (int)$prov['province_id']; ?>" data-name="<?= htmlspecialchars($prov['province']); ?>"><?= htmlspecialchars($prov['province']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Kota / Kabupaten <span class="text-danger">*</span></label>
                                <select class="form-select rounded-3" name="kota_id" id="co_modal_kota_id" required disabled>
                                    <option value="">-- Pilih Provinsi Terlebih Dahulu --</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Kecamatan</label>
                                <input type="text" name="kecamatan" class="form-control rounded-3" placeholder="Contoh: Sukolilo / Gambir">
                            </div>
                            <div class="col-12 col-md-6">
                                <label class="form-label small fw-semibold text-secondary">Kode Pos</label>
                                <input type="text" name="kode_pos" id="co_modal_kode_pos" class="form-control rounded-3" placeholder="Contoh: 60111">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Alamat Lengkap <span class="text-danger">*</span></label>
                            <textarea name="alamat_lengkap" class="form-control rounded-3" rows="3" placeholder="Nama jalan, nomor rumah, patokan lokasi" required></textarea>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="is_primary" value="1" id="co_modal_is_primary" checked>
                            <label class="form-check-label small text-muted" for="co_modal_is_primary">
                                Jadikan sebagai alamat utama & gunakan untuk pesanan ini
                            </label>
                        </div>
                    </div>
                    <div class="modal-footer border-top bg-light">
                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold"><i class="bi bi-save me-1"></i>Simpan & Gunakan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <?php include(__DIR__ . '/layouts/footer.php'); ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const totalBuku = <?= (float) $total_buku; ?>;
            const inputAlamatId = document.getElementById('inputAlamatId');
            const inputNamaPenerima = document.getElementById('inputNamaPenerima');
            const inputNoTelepon = document.getElementById('inputNoTelepon');
            const inputAlamatPengiriman = document.getElementById('inputAlamatPengiriman');
            const currentCityId = document.getElementById('currentCityId');
            const totalBeratPaket = document.getElementById('totalBeratPaket');
            const inputOngkir = document.getElementById('inputOngkir');
            const inputKurirService = document.getElementById('inputKurirService');
            const labelOngkir = document.getElementById('labelOngkir');
            const labelTotalBayar = document.getElementById('labelTotalBayar');
            const labelLayananKurir = document.getElementById('labelLayananKurir');
            const shippingServicesList = document.getElementById('shippingServicesList');
            const formCheckout = document.getElementById('formCheckout');
            const btnSubmit = document.getElementById('btnSubmitCheckout');

            function formatRupiahJS(angka) {
                return 'Rp ' + Number(angka).toString().replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }

            function updateShippingCosts() {
                const cityId = currentCityId.value || '501';
                const weight = totalBeratPaket.value || '1000';
                const selectedCourier = document.querySelector('input[name="pilih_kurir"]:checked');
                const courier = selectedCourier ? selectedCourier.value : 'jne';

                shippingServicesList.innerHTML = '<div class="text-center py-3 text-muted"><span class="spinner-border spinner-border-sm me-2"></span>Menghitung ongkir...</div>';

                fetch('api/rajaongkir.php?action=cost&city_id=' + encodeURIComponent(cityId) + '&weight=' + encodeURIComponent(weight) + '&courier=' + encodeURIComponent(courier))
                    .then(res => res.json())
                    .then(res => {
                        if (res.ok && Array.isArray(res.data) && res.data.length > 0) {
                            shippingServicesList.innerHTML = '';
                            res.data.forEach((srv, index) => {
                                const isChecked = (index === 0);
                                const serviceCard = document.createElement('label');
                                serviceCard.className = 'border rounded-4 p-3 d-flex align-items-center justify-content-between gap-3 cursor-pointer service-card ' + (isChecked ? 'border-success bg-success-subtle bg-opacity-25' : '');
                                serviceCard.style.cursor = 'pointer';

                                const serviceTitle = srv.service_name || (srv.service + (srv.description ? ' - ' + srv.description : ''));
                                const serviceEtd = srv.etd || '-';

                                serviceCard.innerHTML = `
                                    <div class="d-flex align-items-center gap-3">
                                        <input class="form-check-input m-0 service-radio" type="radio" name="service_choice" value="${srv.cost}" data-name="${serviceTitle}" ${isChecked ? 'checked' : ''}>
                                        <div>
                                            <div class="fw-bold text-dark small">${serviceTitle}</div>
                                            <div class="text-muted" style="font-size: 11px;"><i class="bi bi-clock-history me-1"></i>Estimasi tiba: ${serviceEtd}</div>
                                        </div>
                                    </div>
                                    <div class="text-end">
                                        <div class="fw-bold text-success">${formatRupiahJS(srv.cost)}</div>
                                    </div>
                                `;

                                shippingServicesList.appendChild(serviceCard);

                                if (isChecked) {
                                    applySelectedService(srv.cost, serviceTitle);
                                }
                            });

                            bindServiceRadioEvents();
                        } else {
                            shippingServicesList.innerHTML = '<div class="alert alert-warning small rounded-3 p-2 mb-0">Layanan pengiriman standar digunakan.</div>';
                            applySelectedService(12000, 'Kurir Reguler Standar');
                        }
                    })
                    .catch(() => {
                        shippingServicesList.innerHTML = '<div class="alert alert-warning small rounded-3 p-2 mb-0">Tarif standar digunakan.</div>';
                        applySelectedService(12000, 'Kurir Reguler Standar');
                    });
            }

            function applySelectedService(cost, name) {
                const numericCost = parseFloat(cost) || 0;
                inputOngkir.value = numericCost;
                inputKurirService.value = name;
                labelOngkir.textContent = formatRupiahJS(numericCost);
                labelLayananKurir.textContent = name;
                labelTotalBayar.textContent = formatRupiahJS(totalBuku + numericCost);
            }

            function bindServiceRadioEvents() {
                const radios = document.querySelectorAll('.service-radio');
                radios.forEach(radio => {
                    radio.addEventListener('change', function () {
                        document.querySelectorAll('.service-card').forEach(c => {
                            c.classList.remove('border-success', 'bg-success-subtle', 'bg-opacity-25');
                        });
                        const parent = this.closest('.service-card');
                        if (parent) {
                            parent.classList.add('border-success', 'bg-success-subtle', 'bg-opacity-25');
                        }
                        applySelectedService(this.value, this.getAttribute('data-name'));
                    });
                });
            }

            const modalAddressRadios = document.querySelectorAll('.modal-address-radio');
            modalAddressRadios.forEach(radio => {
                radio.addEventListener('change', function () {
                    document.querySelectorAll('.modal-address-card').forEach(c => {
                        c.classList.remove('border-success', 'bg-success-subtle', 'bg-opacity-25');
                    });
                    const parent = this.closest('.modal-address-card');
                    if (parent) {
                        parent.classList.add('border-success', 'bg-success-subtle', 'bg-opacity-25');
                    }

                    if (inputAlamatId) inputAlamatId.value = this.value;
                    if (inputNamaPenerima) inputNamaPenerima.value = this.getAttribute('data-nama');
                    if (inputNoTelepon) inputNoTelepon.value = this.getAttribute('data-telp');
                    if (inputAlamatPengiriman) inputAlamatPengiriman.value = this.getAttribute('data-full');

                    const dispNamaTelp = document.getElementById('displayNamaTelp');
                    if (dispNamaTelp) dispNamaTelp.textContent = this.getAttribute('data-nama') + ' (' + this.getAttribute('data-telp') + ')';
                    const dispBaris1 = document.getElementById('displayAlamatBaris1');
                    if (dispBaris1) dispBaris1.textContent = this.getAttribute('data-baris1');
                    const dispBaris2 = document.getElementById('displayAlamatBaris2');
                    if (dispBaris2) dispBaris2.textContent = this.getAttribute('data-baris2');

                    const newCityId = this.getAttribute('data-city-id');
                    if (newCityId) {
                        currentCityId.value = newCityId;
                        updateShippingCosts();
                    }
                });
            });

            const courierRadios = document.querySelectorAll('.courier-choice');
            courierRadios.forEach(r => {
                r.addEventListener('change', function () {
                    updateShippingCosts();
                });
            });

            bindServiceRadioEvents();

            const coSelectProv = document.getElementById('co_modal_provinsi_id');
            const coSelectKota = document.getElementById('co_modal_kota_id');
            const coHiddenProv = document.getElementById('co_modal_provinsi_nama');
            const coHiddenKota = document.getElementById('co_modal_kota_nama');
            const coInputPos = document.getElementById('co_modal_kode_pos');

            if (coSelectProv && coSelectKota) {
                coSelectProv.addEventListener('change', function () {
                    const provId = this.value;
                    const opt = this.options[this.selectedIndex];
                    coHiddenProv.value = opt ? opt.getAttribute('data-name') : '';

                    coSelectKota.innerHTML = '<option value="">Memuat kota...</option>';
                    coSelectKota.disabled = true;

                    if (!provId) {
                        coSelectKota.innerHTML = '<option value="">-- Pilih Provinsi Terlebih Dahulu --</option>';
                        return;
                    }

                    fetch('api/rajaongkir.php?action=cities&province_id=' + encodeURIComponent(provId))
                        .then(r => r.json())
                        .then(res => {
                            if (res.ok && Array.isArray(res.data)) {
                                coSelectKota.innerHTML = '<option value="">-- Pilih Kota / Kabupaten --</option>';
                                res.data.forEach(item => {
                                    const o = document.createElement('option');
                                    o.value = item.city_id;
                                    o.setAttribute('data-name', item.type + ' ' + item.city_name);
                                    o.setAttribute('data-postal', item.postal_code || '');
                                    o.textContent = item.type + ' ' + item.city_name;
                                    coSelectKota.appendChild(o);
                                });
                                coSelectKota.disabled = false;
                            } else {
                                coSelectKota.innerHTML = '<option value="">Gagal memuat kota</option>';
                            }
                        })
                        .catch(() => {
                            coSelectKota.innerHTML = '<option value="">Gagal memuat kota</option>';
                        });
                });

                coSelectKota.addEventListener('change', function () {
                    const opt = this.options[this.selectedIndex];
                    if (opt) {
                        coHiddenKota.value = opt.getAttribute('data-name') || '';
                        const pos = opt.getAttribute('data-postal');
                        if (pos && coInputPos && !coInputPos.value) {
                            coInputPos.value = pos;
                        }
                    }
                });
            }

            const btnBukaTambah = document.getElementById('btnBukaModalTambahAlamat');
            if (btnBukaTambah) {
                btnBukaTambah.addEventListener('click', function () {
                    const modalPilihEl = document.getElementById('modalPilihAlamat');
                    const modalTambahEl = document.getElementById('modalTambahAlamatCheckout');
                    if (modalPilihEl) {
                        const modalPilihInst = bootstrap.Modal.getInstance(modalPilihEl) || new bootstrap.Modal(modalPilihEl);
                        modalPilihInst.hide();
                    }
                    setTimeout(() => {
                        if (modalTambahEl) {
                            const modalTambahInst = new bootstrap.Modal(modalTambahEl);
                            modalTambahInst.show();
                        }
                    }, 250);
                });
            }

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

            if (formCheckout && btnSubmit) {
                formCheckout.addEventListener('submit', function (e) {
                    e.preventDefault();

                    btnSubmit.disabled = true;
                    btnSubmit.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Memproses Pesanan...';

                    const formData = new FormData(formCheckout);

                    fetch('proses_checkout.php?ajax=1', {
                        method: 'POST',
                        body: formData,
                        headers: { 'Accept': 'application/json' }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.ok) {
                            alert(data.message || 'Terjadi kesalahan saat memproses pesanan.');
                            btnSubmit.disabled = false;
                            btnSubmit.innerHTML = '<i class="bi bi-shield-lock-fill fs-5"></i> <span>Bayar Sekarang</span>';
                            if (data.redirect) {
                                window.location.href = data.redirect;
                            }
                            return;
                        }

                        const snapToken = data.snap_token;
                        const pesananId = data.pesanan_id;
                        const nomorPesanan = data.nomor_pesanan || '';

                        if (typeof snap !== 'undefined' && snapToken && !snapToken.startsWith('MOCK-SNAP')) {
                            snap.pay(snapToken, {
                                onSuccess: function (result) {
                                    const orderNum = (result && result.order_id) ? result.order_id : nomorPesanan;
                                    const transStatus = (result && result.transaction_status) ? result.transaction_status : 'settlement';
                                    let payType = (result && result.payment_type) ? result.payment_type : '';
                                    let bankName = '';
                                    if (result && result.va_numbers && result.va_numbers[0] && result.va_numbers[0].bank) {
                                        bankName = result.va_numbers[0].bank;
                                    } else if (result && result.bank) {
                                        bankName = result.bank;
                                    }
                                    window.location.href = 'pesanan_sukses.php?id=' + pesananId + '&order_id=' + encodeURIComponent(orderNum) + '&transaction_status=' + encodeURIComponent(transStatus) + '&payment_type=' + encodeURIComponent(payType) + '&bank=' + encodeURIComponent(bankName);
                                },
                                onPending: function (result) {
                                    const orderNum = (result && result.order_id) ? result.order_id : nomorPesanan;
                                    let payType = (result && result.payment_type) ? result.payment_type : '';
                                    let bankName = '';
                                    if (result && result.va_numbers && result.va_numbers[0] && result.va_numbers[0].bank) {
                                        bankName = result.va_numbers[0].bank;
                                    } else if (result && result.bank) {
                                        bankName = result.bank;
                                    }
                                    window.location.href = 'pesanan_sukses.php?id=' + pesananId + '&order_id=' + encodeURIComponent(orderNum) + '&transaction_status=pending&payment_type=' + encodeURIComponent(payType) + '&bank=' + encodeURIComponent(bankName);
                                },
                                onError: function () {
                                    window.location.href = 'pesanan_sukses.php?id=' + pesananId + '&order_id=' + encodeURIComponent(nomorPesanan) + '&transaction_status=failure';
                                },
                                onClose: function () {
                                    window.location.href = 'pesanan_sukses.php?id=' + pesananId + '&order_id=' + encodeURIComponent(nomorPesanan);
                                }
                            });
                        } else {
                            window.location.href = 'pesanan_sukses.php?id=' + pesananId + '&order_id=' + encodeURIComponent(nomorPesanan) + '&transaction_status=settlement';
                        }
                    })
                    .catch(() => {
                        formCheckout.submit();
                    });
                });
            }

            <?php if (empty($selected_alamat)): ?>
            const modalAutoTambah = document.getElementById('modalTambahAlamatCheckout');
            if (modalAutoTambah) {
                const instModal = new bootstrap.Modal(modalAutoTambah);
                instModal.show();
            }
            <?php endif; ?>
        });
    </script>
</body>
</html>