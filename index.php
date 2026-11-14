<?php
    session_start();
    require_once __DIR__ . '/config/database.php';

    $kategori_filters = [
        1 => 'Fiksi',
        2 => 'Non-Fiksi',
        3 => 'Pengembangan',
        4 => 'all',
        5 => 'all',
        6 => 'all',
        7 => 'atk',
        8 => 'atk'
    ];
    $kategori_display = [];
    $cat_res = mysqli_query($conn, "SELECT id, nama_kategori, gambar FROM kategori ORDER BY id ASC");
    if ($cat_res) {
        while ($row = mysqli_fetch_assoc($cat_res)) {
            $kategori_display[] = [
                'id' => $row['id'],
                'nama' => $row['nama_kategori'],
                'gambar' => $row['gambar'],
                'filter' => $kategori_filters[$row['id']] ?? 'all'
            ];
        }
    }

    $flash_sale_items = [];
    $fs_res = mysqli_query($conn, "SELECT b.id, b.judul, b.penulis, b.harga, b.harga_diskon, b.stok, b.gambar, k.nama_kategori FROM buku b JOIN kategori k ON b.kategori_id = k.id WHERE b.harga_diskon IS NOT NULL AND b.harga_diskon > 0 ORDER BY b.id ASC");
    if ($fs_res) {
        while ($row = mysqli_fetch_assoc($fs_res)) {
            $harga_normal = (float)$row['harga'];
            $harga_diskon = (float)$row['harga_diskon'];
            $disc_pct = ($harga_normal > 0) ? round((($harga_normal - $harga_diskon) / $harga_normal) * 100) : 0;
            $flash_sale_items[] = [
                'id' => $row['id'],
                'judul' => $row['judul'],
                'penulis' => $row['penulis'],
                'kategori' => $row['nama_kategori'],
                'harga' => $harga_diskon,
                'harga_coret' => $harga_normal,
                'diskon' => '-' . $disc_pct . '%',
                'stok' => (int)$row['stok'],
                'gambar' => 'assets/uploads/buku/' . $row['gambar']
            ];
        }
    }

    $buku_baru = [];
    $bb_cat_filter = [
        1 => 'Fiksi',
        2 => 'Non-Fiksi',
        3 => 'Pengembangan',
        4 => 'all',
        5 => 'all',
        6 => 'all',
        7 => 'atk',
        8 => 'atk'
    ];
    $bb_res = mysqli_query($conn, "SELECT b.id, b.judul, b.penulis, b.harga, b.harga_diskon, b.gambar, b.kategori_id, k.nama_kategori FROM buku b JOIN kategori k ON b.kategori_id = k.id WHERE b.kategori_id != 8 ORDER BY b.id DESC LIMIT 4");
    if ($bb_res) {
        while ($row = mysqli_fetch_assoc($bb_res)) {
            $buku_baru[] = [
                'id' => $row['id'],
                'category' => $bb_cat_filter[$row['kategori_id']] ?? 'all',
                'kategori' => $row['nama_kategori'],
                'judul' => $row['judul'],
                'penulis' => $row['penulis'],
                'harga' => (float)$row['harga'],
                'harga_diskon' => !empty($row['harga_diskon']) ? (float)$row['harga_diskon'] : null,
                'gambar' => 'assets/uploads/buku/' . $row['gambar']
            ];
        }
    }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PustakaKu - Toko Buku & Alat Tulis Terpercaya Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include(__DIR__ . '/layouts/navbar.php'); ?>

    <main class="container my-3 my-md-4">
        <section class="row g-3 g-lg-4 align-items-stretch mb-4 mb-lg-5">
            <div class="col-12 col-lg-8">
                <div id="heroCarousel" class="carousel slide hero-slider-wrap shadow-sm bg-success h-100" data-bs-ride="carousel" data-bs-interval="4500">
                    <div class="carousel-indicators mb-2 mb-md-3">
                        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
                    </div>

                    <div class="carousel-inner h-100">
                        <div class="carousel-item active h-100">
                            <div class="hero-slide-item d-flex align-items-center h-100">
                                <div class="row align-items-center g-4 w-100 mx-auto">
                                    <div class="col-12 col-sm-7 text-white text-center text-sm-start">
                                        <div class="mb-2"><span class="badge bg-white text-success rounded-pill px-3 py-2 fw-semibold shadow-sm"><i class="bi bi-stars text-warning me-1"></i> Pre-Order Eksklusif</span></div>
                                        <h1 class="display-6 fw-bold text-white mb-2 mb-md-3 lh-sm">Rilisan Karya Terkini Penulis Terkemuka</h1>
                                        <p class="text-white-50 small fs-6 mb-3 mb-md-4">Dapatkan kesempatan emas memiliki edisi khusus cetakan pertama dengan potongan pre-order eksklusif 20%.</p>
                                        <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-sm-start">
                                            <a href="#flash-sale" class="btn btn-warning text-dark fw-bold rounded-pill px-3 px-md-4 py-2 shadow-sm d-flex align-items-center gap-1"><span>Pre-Order Sekarang</span></a>
                                            <a href="#spotlight" class="btn btn-outline-light rounded-pill px-3 py-2">Lihat Sinopsis</a>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-5 text-center hero-book-wrap">
                                        <div class="hero-book-frame"><img src="assets/images/banner/laut_bercerita.jpg" alt="Laut Bercerita" class="hero-book-img img-fluid"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="carousel-item h-100">
                            <div class="hero-slide-item d-flex align-items-center h-100">
                                <div class="row align-items-center g-4 w-100 mx-auto">
                                    <div class="col-12 col-sm-7 text-white text-center text-sm-start">
                                        <div class="mb-2"><span class="badge bg-white text-success rounded-pill px-3 py-2 fw-semibold shadow-sm"><i class="bi bi-fire text-danger me-1"></i> Best Seller Internasional</span></div>
                                        <h1 class="display-6 fw-bold text-white mb-2 mb-md-3 lh-sm">Bangun Kebiasaan Positif Atomic Habits</h1>
                                        <p class="text-white-50 small fs-6 mb-3 mb-md-4">Perubahan kecil yang memberikan hasil luar biasa dalam hidup, karier, dan produktivitas harian Anda.</p>
                                        <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-sm-start">
                                            <a href="#terlaris" class="btn btn-warning text-dark fw-bold rounded-pill px-3 px-md-4 py-2 shadow-sm d-flex align-items-center gap-1"><span>Beli Sekarang</span></a>
                                            <a href="#terlaris" class="btn btn-outline-light rounded-pill px-3 py-2">Lihat Detail</a>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-5 text-center hero-book-wrap">
                                        <div class="hero-book-frame"><img src="assets/images/banner/atomic_habits.jpg" alt="Atomic Habits" class="hero-book-img img-fluid"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="carousel-item h-100">
                            <div class="hero-slide-item d-flex align-items-center h-100">
                                <div class="row align-items-center g-4 w-100 mx-auto">
                                    <div class="col-12 col-sm-7 text-white text-center text-sm-start">
                                        <div class="mb-2"><span class="badge bg-white text-success rounded-pill px-3 py-2 fw-semibold shadow-sm"><i class="bi bi-lightbulb text-warning me-1"></i> Inspirasi Kedamaian Hidup</span></div>
                                        <h1 class="display-6 fw-bold text-white mb-2 mb-md-3 lh-sm">Ketenangan Batin Filosofi Teras</h1>
                                        <p class="text-white-50 small fs-6 mb-3 mb-md-4">Penerapan stoisisme praktis untuk mental yang lebih tangguh menghadapi berbagai tantangan zaman modern.</p>
                                        <div class="d-flex flex-wrap gap-2 justify-content-center justify-content-sm-start">
                                            <a href="#flash-sale" class="btn btn-warning text-dark fw-bold rounded-pill px-3 px-md-4 py-2 shadow-sm d-flex align-items-center gap-1"><span>Beli Sekarang</span></a>
                                            <a href="#terlaris" class="btn btn-outline-light rounded-pill px-3 py-2">Lihat Sinopsis</a>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-5 text-center hero-book-wrap">
                                        <div class="hero-book-frame"><img src="assets/images/banner/filosofi_teras.jpg" alt="Filosofi Teras" class="hero-book-img img-fluid"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button class="carousel-control-prev" type="button" data-bs-target="#heroCarousel" data-bs-slide="prev">
                        <span class="hero-control-btn"><i class="bi bi-chevron-left"></i></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#heroCarousel" data-bs-slide="next">
                        <span class="hero-control-btn"><i class="bi bi-chevron-right"></i></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>

            <div class="col-12 col-lg-4">
                <div class="d-flex flex-column gap-3 h-100 justify-content-between">
                    <div class="promo-card bg-white p-3 p-md-4 border shadow-sm flex-fill d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-danger-subtle text-danger fw-bold text-uppercase px-2 py-1" style="font-size: 0.7rem;">Promosi Spesial</span>
                            </div>
                            <h5 class="fw-bold text-dark mb-1">Paket Mahasiswa Hemat</h5>
                            <p class="text-muted small mb-3">Diskon 25% untuk buku kuliah akademik & perlengkapan ATK harian.</p>
                        </div>
                        <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                            <div>
                                <span class="text-muted d-block" style="font-size: 0.72rem;">Mulai dari</span>
                                <span class="fw-bold text-success fs-6">Rp 15.000</span>
                            </div>
                            <a href="#atk" class="text-success fw-bold text-decoration-none small d-flex align-items-center gap-1"><span>Lihat Paket</span><i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>

                    <div class="promo-card bg-success text-white p-3 p-md-4 shadow-sm flex-fill d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-white bg-opacity-25 text-white fw-bold text-uppercase px-2 py-1" style="font-size: 0.7rem;">Skala Penghematan</span>
                                <i class="bi bi-bookmark-star text-white-50 fs-6"></i>
                            </div>
                            <h5 class="fw-bold text-white mb-1">Katalog Self-Growth & Filosofi</h5>
                            <p class="text-white-50 small mb-3">Persediaan edisi eksklusif edisi terbitan terbatas.</p>
                        </div>
                        <div class="d-flex align-items-center justify-content-between pt-2 border-top border-white border-opacity-25">
                            <div>
                                <span class="text-white-50 d-block" style="font-size: 0.72rem;">Potongan Spesial</span>
                                <span class="fw-bold text-warning fs-5">Disc s/d 40%</span>
                            </div>
                            <a href="#terlaris" class="btn btn-light btn-sm text-success fw-bold rounded-pill px-3 shadow-sm d-flex align-items-center gap-1"><span>Lihat Buku</span><i class="bi bi-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-white rounded-4 p-3 p-md-4 border shadow-sm mb-4 mb-lg-5">
            <div class="row g-3 g-md-4 text-center text-md-start">
                <div class="col-6 col-lg-3">
                    <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start gap-3 feature-box">
                        <div class="rounded-circle bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;"><i class="bi bi-shield-check fs-4"></i></div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">100% Asli & Orisinal</h6>
                            <p class="text-muted small mb-0">Langsung dari penerbit resmi</p>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start gap-3 feature-box">
                        <div class="rounded-circle bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;"><i class="bi bi-truck fs-4"></i></div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Pengiriman Cepat & Aman</h6>
                            <p class="text-muted small mb-0">Garansi ganti jika rusak 24 jam</p>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start gap-3 feature-box">
                        <div class="rounded-circle bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;"><i class="bi bi-credit-card-2-front fs-4"></i></div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Pembayaran Terlengkap</h6>
                            <p class="text-muted small mb-0">QRIS, VA Transfer & E-Wallet</p>
                        </div>
                    </div>
                </div>

                <div class="col-6 col-lg-3">
                    <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start gap-3 feature-box">
                        <div class="rounded-circle bg-success-subtle text-success p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;"><i class="bi bi-headset fs-4"></i></div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Layanan Responsif</h6>
                            <p class="text-muted small mb-0">Siap melayani 7 hari seminggu</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section id="kategori" class="mb-4 mb-lg-5">
            <div class="d-flex align-items-end justify-content-between mb-3">
                <div>
                    <span class="text-success fw-bold text-uppercase small tracking-wide" style="font-size: 0.75rem;">Eksplorasi Katalog</span>
                    <h3 class="fw-bold text-dark mb-0">Kategori Populer Pilihan</h3>
                </div>
                <a href="#kategori" class="text-success fw-semibold text-decoration-none small d-flex align-items-center gap-1"><span>Semua Kategori</span><i class="bi bi-chevron-right" style="font-size: 0.8rem;"></i></a>
            </div>

            <div class="row row-cols-2 row-cols-sm-4 row-cols-lg-8 g-2 g-md-3">
                <?php foreach ($kategori_display as $kat): ?>
                    <div class="col">
                        <a href="#terlaris" class="category-card shadow-sm h-100" data-filter="<?= $kat['filter']; ?>" onclick="const tab = document.querySelector('.filter-tab-btn[data-filter=\'<?= $kat['filter']; ?>\']'); if(tab) tab.click();">
                            <img src="assets/uploads/kategori/<?= htmlspecialchars($kat['gambar']); ?>" alt="<?= htmlspecialchars($kat['nama']); ?>" class="category-circle-img">
                            <p class="category-name"><?= htmlspecialchars($kat['nama']); ?></p>
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section id="flash-sale" class="bg-success-subtle border border-success-subtle rounded-4 p-3 p-md-4 mb-4 mb-lg-5">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-4 pb-3 border-bottom border-success-subtle">
                <div class="d-flex align-items-center gap-3">
                    <div>
                        <h4 class="fw-bold text-dark mb-0">Kilat Flash Sale Buku & ATK</h4>
                        <p class="text-muted small mb-0">Penawaran terbaik berlangsung terbatas! Jangan sampai kehabisan.</p>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3 align-self-start align-self-md-auto">
                    <div class="d-flex gap-1">
                        <button type="button" class="slider-nav-btn shadow-sm" onclick="slideProduk(-1)" title="Geser ke kiri"><i class="bi bi-chevron-left"></i></button>
                        <button type="button" class="slider-nav-btn shadow-sm" onclick="slideProduk(1)" title="Geser ke kanan"><i class="bi bi-chevron-right"></i></button>
                    </div>
                </div>
            </div>

            <div class="product-slider-wrapper">
                <div class="product-slider" id="flashSaleSlider">
                    <?php foreach ($flash_sale_items as $fs): ?>
                        <div class="product-slide-card">
                            <div class="product-card">
                                <div class="product-img-box">
                                    <span class="badge bg-danger badge-discount shadow-sm"><?= $fs['diskon']; ?></span>
                                    <a href="detail_buku.php?id=<?= (int)$fs['id']; ?>" class="d-block text-center">
                                        <img src="<?= $fs['gambar']; ?>" alt="<?= htmlspecialchars($fs['judul']); ?>" loading="lazy">
                                    </a>
                                </div>

                                <div class="p-3 d-flex flex-column flex-grow-1">
                                    <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;"><?= htmlspecialchars($fs['kategori']); ?></span>
                                    <a href="detail_buku.php?id=<?= (int)$fs['id']; ?>" class="text-decoration-none text-dark">
                                        <h6 class="product-title" title="<?= htmlspecialchars($fs['judul']); ?>"><?= htmlspecialchars($fs['judul']); ?></h6>
                                    </a>
                                    <p class="product-author"><?= htmlspecialchars($fs['penulis']); ?></p>
                                    
                                    <div class="mb-2">
                                        <div class="fw-bold text-success fs-6"><?= formatRupiah($fs['harga']); ?></div>
                                        <del class="text-muted small" style="font-size: 0.72rem;"><?= formatRupiah($fs['harga_coret']); ?></del>
                                    </div>

                                    <div class="mt-auto">
                                        <div class="d-flex justify-content-between small text-muted mb-1" style="font-size: 0.7rem;">
                                            <span>Tersedia</span>
                                            <span class="fw-semibold text-danger"><?= $fs['stok']; ?> pcs</span>
                                        </div>
                                        <div class="progress mb-3" style="height: 5px;">
                                            <div class="progress-bar bg-danger" role="progressbar" style="width: <?= min(100, $fs['stok'] * 1.5); ?>%;"></div>
                                        </div>

                                        <?php if (($_SESSION['role'] ?? '') !== 'admin'): ?>
                                            <button class="btn btn-success btn-sm w-100 rounded-pill fw-semibold py-2 d-flex align-items-center justify-content-center gap-1 shadow-sm" onclick="tambahKeKeranjang(<?= (int)$fs['id']; ?>, this)"><i class="bi bi-bag-plus"></i><span> Tambah Keranjang</span></button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

        <section id="terlaris" class="mb-4 mb-lg-5">
            <div class="d-flex flex-column flex-sm-row align-items-sm-end justify-content-between gap-2 mb-3">
                <div>
                    <span class="text-success fw-bold text-uppercase small tracking-wide" style="font-size: 0.75rem;">Rilisan Terbaru</span>
                    <h3 class="fw-bold text-dark mb-0">Buku Baru</h3>
                </div>

                <div class="d-flex gap-1 overflow-x-auto no-scrollbar">
                    <a href="index.php" class="subnav-link<?= nav_aktif($halaman_ini === 'index.php' && !$kat_dipilih && !$promo_aktif); ?>">Semua</a>
                    <a href="index.php?kategori=1" class="subnav-link<?= nav_aktif($kat_dipilih === [1]); ?>">Buku Fiksi</a>
                    <a href="index.php?kategori=2" class="subnav-link<?= nav_aktif($kat_dipilih === [2]); ?>">Buku Non-Fiksi</a>
                    <a href="index.php?kategori=4" class="subnav-link<?= nav_aktif($kat_dipilih === [4]); ?>">Buku Pelajaran</a>
                    <a href="index.php?kategori=5" class="subnav-link<?= nav_aktif($kat_dipilih === [5]); ?>">Buku Anak</a>
                    <a href="index.php?kategori=3" class="subnav-link<?= nav_aktif($kat_dipilih === [3]); ?>">Pengembangan Diri</a>
                    <a href="index.php?kategori=6" class="subnav-link<?= nav_aktif($kat_dipilih === [6]); ?>">Alat Tulis & ATK</a>
                </div>
            </div>

            <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-3 g-md-4" id="bestSellerGrid">
                <?php foreach ($buku_baru as $b): ?>
                    <div class="col bestseller-item" data-category="<?= $b['kategori']; ?>">
                        <div class="product-card">
                            <div class="product-img-box">
                                <span class="rank-badge bg-success text-white shadow-sm"><i class="bi bi-stars me-1"></i>Baru</span>
                                <a href="detail_buku.php?id=<?= (int)$b['id']; ?>" class="d-block text-center">
                                    <img src="<?= $b['gambar']; ?>" alt="<?= htmlspecialchars($b['judul']); ?>" loading="lazy">
                                </a>
                            </div>

                            <div class="p-3 d-flex flex-column flex-grow-1">
                                <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;"><?= htmlspecialchars($b['kategori']); ?></span>
                                <a href="detail_buku.php?id=<?= (int)$b['id']; ?>" class="text-decoration-none text-dark">
                                    <h6 class="product-title" title="<?= htmlspecialchars($b['judul']); ?>"><?= htmlspecialchars($b['judul']); ?></h6>
                                </a>
                                <p class="product-author"><?= htmlspecialchars($b['penulis']); ?></p>

                                <div class="mt-auto d-flex align-items-center justify-content-between pt-2 border-top">
                                    <div>
                                        <?php if (!empty($b['harga_diskon']) && $b['harga_diskon'] > 0 && $b['harga_diskon'] < $b['harga']): ?>
                                            <div class="fw-bold text-success fs-6"><?= formatRupiah($b['harga_diskon']); ?></div>
                                            <del class="text-muted small" style="font-size: 0.72rem;"><?= formatRupiah($b['harga']); ?></del>
                                        <?php else: ?>
                                            <div class="fw-bold text-dark fs-6"><?= formatRupiah($b['harga']); ?></div>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (($_SESSION['role'] ?? '') !== 'admin'): ?>
                                        <button class="btn btn-outline-success btn-sm rounded-circle d-flex align-items-center justify-content-center p-2" style="width: 36px; height: 36px;" title="Tambah ke Keranjang" onclick="tambahKeKeranjang(<?= (int)$b['id']; ?>, this)"><i class="bi bi-bag-plus fs-6"></i></button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="mb-4 mb-lg-5">
            <div class="text-center mb-4">
                <span class="text-success fw-bold text-uppercase small tracking-wide" style="font-size: 0.75rem;">Ulasan Pelanggan</span>
                <h3 class="fw-bold text-dark mb-1">Pengalaman Sahabat PustakaKu</h3>
                <p class="text-muted small mb-0">Kesan jujur & cerita membahagiakan dari pembaca setia kami di seluruh Indonesia.</p>
            </div>

            <div class="row row-cols-1 row-cols-md-3 g-3 g-md-4">
                <div class="col">
                    <div class="testimonial-card d-flex flex-column justify-content-between">
                        <div>
                            <div class="text-warning small mb-3">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <p class="text-muted small fst-italic mb-3 lh-base">"Pengiriman cepat dan packing sangat aman dengan bubble wrap tebal. Buku sampai mulus tanpa cacat sama sekali! Pasti bakal langganan terus."</p>
                        </div>
                        <div class="d-flex align-items-center gap-2 pt-3 border-top">
                            <div class="rounded-circle bg-success text-white fw-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; font-size: 0.85rem;">DS</div>
                            <div class="lh-sm">
                                <div class="fw-bold small text-dark">Danang Saputra</div>
                                <div class="text-muted" style="font-size: 0.72rem;">Mahasiswa, Jakarta</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="testimonial-card d-flex flex-column justify-content-between">
                        <div>
                            <div class="text-warning small mb-3">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <p class="text-muted small fst-italic mb-3 lh-base">"Sangat puas belanja alat tulis di PustakaKu. Koleksi notebook dan pulpennya estetik dan berkualitas. Pas banget buat teman kuliah & journaling harian!"</p>
                        </div>
                        <div class="d-flex align-items-center gap-2 pt-3 border-top">
                            <div class="rounded-circle bg-success text-white fw-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; font-size: 0.85rem;">RP</div>
                            <div class="lh-sm">
                                <div class="fw-bold small text-dark">Rizky Pratama</div>
                                <div class="text-muted" style="font-size: 0.72rem;">Content Creator, Bandung</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col">
                    <div class="testimonial-card d-flex flex-column justify-content-between">
                        <div>
                            <div class="text-warning small mb-3">
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                                <i class="bi bi-star-fill"></i>
                            </div>
                            <p class="text-muted small fst-italic mb-3 lh-base">"Layanan ramah dan koleksi bukunya lengkap banget dari yang klasik sampai terbaru. Harganya juga bersaing. Tempat favorit beli buku online."</p>
                        </div>
                        <div class="d-flex align-items-center gap-2 pt-3 border-top">
                            <div class="rounded-circle bg-success text-white fw-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px; font-size: 0.85rem;">AK</div>
                            <div class="lh-sm">
                                <div class="fw-bold small text-dark">Arina Kusuma</div>
                                <div class="text-muted" style="font-size: 0.72rem;">Pengajar & Book Reviewer, Solo</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-success-subtle border border-success-subtle rounded-4 p-4 p-md-5 mb-2 shadow-sm">
            <div class="d-flex text-center justify-content-center align-items-center g-4">
                <div class="col-md-8">
                    <h3 class="fw-bold text-dark mb-2">Dapatkan Voucher Diskon Rp 25.000 untuk Pesanan Pertama Anda</h3>
                    <p class="text-muted small mb-3">Daftar buletin dan jadilah orang pertama yang tahu tentang rilisan buku terbaru, diskon musiman, serta promo eksklusif member PustakaKu setiap akhir pekan.</p>
                    <a href="#" class="btn btn-success rounded-pill px-4 fw-semibold" type="submit">Daftar Sekarang</a>
                </div>
            </div>
        </section>

    </main>

    <?php include(__DIR__ . '/layouts/footer.php'); ?>

</body>
</html>
