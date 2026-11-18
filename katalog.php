<?php
    session_start();

    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/services/cloudinary_service.php';

    function katalog_url(array $params): string {
        $params = array_filter($params, fn($v) => $v !== '' && $v !== null && $v !== []);
        return 'katalog.php' . ($params ? '?' . http_build_query($params) : '');
    }

    $per_page = 12;
    $q        = trim($_GET['q'] ?? '');
    $sel_kat  = array_values(array_unique(array_filter(array_map('intval', (array)($_GET['kategori'] ?? [])), fn($v) => $v > 0)));
    $min      = (isset($_GET['min']) && $_GET['min'] !== '') ? max(0, (int)$_GET['min']) : null;
    $max      = (isset($_GET['max']) && $_GET['max'] !== '') ? max(0, (int)$_GET['max']) : null;
    $f_stok   = ($_GET['stok'] ?? '') === '1';
    $f_promo  = ($_GET['promo'] ?? '') === '1';
    $page     = max(1, (int)($_GET['page'] ?? 1));

    $sort_options = [
        'terbaru'    => ['Terbaru',          'b.id DESC'],
        'harga-asc'  => ['Harga Terendah',   'harga_efektif ASC'],
        'harga-desc' => ['Harga Tertinggi',  'harga_efektif DESC'],
        'diskon'     => ['Diskon Terbesar',  'persen_diskon DESC, b.id DESC'],
        'judul'      => ['Judul A - Z',      'b.judul ASC'],
    ];
    $sort = $_GET['sort'] ?? 'terbaru';
    if (!isset($sort_options[$sort])) $sort = 'terbaru';

    $kategori_list = [];
    $cat_res = mysqli_query($conn, "SELECT k.id, k.nama_kategori, COUNT(b.id) AS jumlah FROM kategori k LEFT JOIN buku b ON b.kategori_id = k.id GROUP BY k.id, k.nama_kategori ORDER BY k.id ASC");
    if ($cat_res) {
        while ($row = mysqli_fetch_assoc($cat_res)) {
            $kategori_list[(int)$row['id']] = ['nama' => $row['nama_kategori'], 'jumlah' => (int)$row['jumlah']];
        }
    }
    $total_semua = array_sum(array_column($kategori_list, 'jumlah'));


    $price_expr = "COALESCE(NULLIF(b.harga_diskon, 0), b.harga)";
    $where = [];

    if ($q !== '') {
        $q_esc = mysqli_real_escape_string($conn, $q);
        $where[] = "(b.judul LIKE '%$q_esc%' OR b.penulis LIKE '%$q_esc%' OR b.penerbit LIKE '%$q_esc%' OR k.nama_kategori LIKE '%$q_esc%')";
    }
    if ($sel_kat) {
        $where[] = "b.kategori_id IN (" . implode(',', $sel_kat) . ")";
    }
    if ($min !== null) { $where[] = "$price_expr >= $min"; }
    if ($max !== null) { $where[] = "$price_expr <= $max"; }
    if ($f_stok)  $where[] = "b.stok > 0";
    if ($f_promo) $where[] = "b.harga_diskon IS NOT NULL AND b.harga_diskon > 0 AND b.harga_diskon < b.harga";
    $where_sql = $where ? 'WHERE ' . implode(' AND ', $where) : '';

    $total = 0;
    $cnt_res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM buku b JOIN kategori k ON b.kategori_id = k.id $where_sql");
    if ($cnt_res) $total = (int)mysqli_fetch_assoc($cnt_res)['total'];

    $total_pages = max(1, (int)ceil($total / $per_page));
    if ($page > $total_pages) $page = $total_pages;
    $offset = ($page - 1) * $per_page;

    $order_sql = $sort_options[$sort][1];
    $sql = "SELECT b.id, b.judul, b.penulis, b.harga, b.harga_diskon, b.stok, b.gambar, b.kategori_id, k.nama_kategori,
                   $price_expr AS harga_efektif,
                   CASE WHEN b.harga_diskon > 0 AND b.harga > 0 THEN (b.harga - b.harga_diskon) / b.harga ELSE 0 END AS persen_diskon
            FROM buku b JOIN kategori k ON b.kategori_id = k.id
            $where_sql ORDER BY $order_sql LIMIT $per_page OFFSET $offset";

    $produk = [];
    $res = mysqli_query($conn, $sql);
    
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $harga = (float)$row['harga'];
            $diskon = (float)$row['harga_diskon'];
            $ada_diskon = $diskon > 0 && $diskon < $harga;
            $produk[] = [
                'id'         => $row['id'],
                'judul'      => $row['judul'],
                'penulis'    => $row['penulis'],
                'kategori'   => $row['nama_kategori'],
                'harga'      => $harga,
                'diskon'     => $ada_diskon ? $diskon : null,
                'persen'     => $ada_diskon ? round((($harga - $diskon) / $harga) * 100) : 0,
                'stok'       => (int)$row['stok'],
                'gambar'     => resolveImageUrl($row['gambar'] ?? '', 'buku')
            ];
        }
    }

    $dari   = $total ? $offset + 1 : 0;
    $sampai = $offset + count($produk);

    $base = [
        'q' => $q, 'kategori' => $sel_kat, 'min' => $min, 'max' => $max,
        'stok' => $f_stok ? '1' : '', 'promo' => $f_promo ? '1' : '',
        'sort' => $sort === 'terbaru' ? '' : $sort
    ];

    $chips = [];
    foreach ($sel_kat as $kid) {
        if (!isset($kategori_list[$kid])) continue;
        $p = $base; $p['kategori'] = array_values(array_diff($sel_kat, [$kid]));
        $chips[] = ['label' => $kategori_list[$kid]['nama'], 'url' => katalog_url($p)];
    }
    if ($q !== '')      { $p = $base; $p['q'] = '';     $chips[] = ['label' => 'Cari: "' . $q . '"', 'url' => katalog_url($p)]; }
    if ($min !== null)  { $p = $base; $p['min'] = null; $chips[] = ['label' => 'Min ' . formatRupiah($min), 'url' => katalog_url($p)]; }
    if ($max !== null)  { $p = $base; $p['max'] = null; $chips[] = ['label' => 'Maks ' . formatRupiah($max), 'url' => katalog_url($p)]; }
    if ($f_stok)        { $p = $base; $p['stok'] = '';  $chips[] = ['label' => 'Stok Tersedia', 'url' => katalog_url($p)]; }
    if ($f_promo)       { $p = $base; $p['promo'] = ''; $chips[] = ['label' => 'Sedang Diskon', 'url' => katalog_url($p)]; }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Buku & Alat Tulis - PustakaKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">

</head>
<body>
    <?php include(__DIR__ . '/layouts/navbar.php'); ?>

    <main class="container my-3 my-md-4">

        
        <section class="bg-success-subtle border border-success-subtle rounded-4 p-3 p-md-4 mb-3 mb-lg-4">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb small mb-2">
                    <li class="breadcrumb-item"><a href="index.php" class="text-success text-decoration-none">Beranda</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Semua Produk</li>
                </ol>
            </nav>
            <div class="d-flex flex-column flex-md-row align-items-md-end justify-content-between gap-3">
                <div>
                    <h2 class="fw-bold text-dark mb-1">Katalog Buku & Alat Tulis PustakaKu</h2>
                    <p class="text-muted small mb-0">Telusuri koleksi buku sastra, non-fiksi, pengembangan diri, hingga perlengkapan alat tulis pilihan yang dikurasi untuk kenyamanan membaca dan berkarya Anda.</p>
                </div>
                <span class="badge bg-white text-success border border-success-subtle rounded-pill px-3 py-2 fw-semibold flex-shrink-0">
                    <i class="bi bi-collection me-1"></i> <?= number_format($total_semua, 0, ',', '.'); ?> Produk Tersedia
                </span>
            </div>
        </section>

        <div class="row g-3 g-lg-4">
            
            <aside class="col-12 col-lg-3">
                <button class="btn btn-outline-success rounded-pill w-100 d-lg-none mb-3 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#filterPanel" aria-expanded="false" aria-controls="filterPanel">
                    <i class="bi bi-sliders2 me-1"></i> Tampilkan Filter
                </button>

                <div class="collapse d-lg-block" id="filterPanel">
                    <form method="GET" action="katalog.php" id="filterForm" class="filter-card bg-white border shadow-sm p-3 p-md-4">
                        <input type="hidden" name="q" value="<?= htmlspecialchars($q); ?>">
                        <input type="hidden" name="sort" value="<?= htmlspecialchars($sort); ?>">

                        <div class="d-flex align-items-center justify-content-between">
                            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-sliders2 text-success me-1"></i> Filter Pencarian</h6>
                            <a href="katalog.php" class="small text-success fw-semibold text-decoration-none">Reset</a>
                        </div>

                        <div class="filter-heading">Kategori Produk</div>
                        <?php foreach ($kategori_list as $kid => $kat): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="kategori[]" value="<?= $kid; ?>" id="kat<?= $kid; ?>" <?= in_array($kid, $sel_kat) ? 'checked' : ''; ?> onchange="this.form.submit()">
                                <label class="form-check-label" for="kat<?= $kid; ?>">
                                    <span><?= htmlspecialchars($kat['nama']); ?></span>
                                    <span class="count-pill"><?= $kat['jumlah']; ?></span>
                                </label>
                            </div>
                        <?php endforeach; ?>

                        <div class="filter-heading">Rentang Harga (Rp)</div>
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <input type="number" min="0" step="1000" name="min" class="form-control form-control-sm" placeholder="Min" value="<?= $min !== null ? $min : ''; ?>">
                            </div>
                            <div class="col-6">
                                <input type="number" min="0" step="1000" name="max" class="form-control form-control-sm" placeholder="Maks" value="<?= $max !== null ? $max : ''; ?>">
                            </div>
                        </div>
                        <div class="d-flex flex-wrap gap-1 mb-3">
                            <?php
                                $quick = [['&lt; 50 rb', null, 50000], ['50 - 150 rb', 50000, 150000], ['&gt; 150 rb', 150000, null]];
                                foreach ($quick as $qp):
                                    $p = $base; $p['min'] = $qp[1]; $p['max'] = $qp[2];
                            ?>
                                <a href="<?= htmlspecialchars(katalog_url($p)); ?>" class="quick-price"><?= $qp[0]; ?></a>
                            <?php endforeach; ?>
                        </div>
                        <button type="submit" class="btn btn-success btn-sm w-100 rounded-pill fw-semibold">Terapkan Harga</button>

                        <div class="filter-heading">Ketersediaan & Promo</div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="stok" value="1" id="fStok" <?= $f_stok ? 'checked' : ''; ?> onchange="this.form.submit()">
                            <label class="form-check-label" for="fStok">Stok Tersedia</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="promo" value="1" id="fPromo" <?= $f_promo ? 'checked' : ''; ?> onchange="this.form.submit()">
                            <label class="form-check-label" for="fPromo">Sedang Diskon</label>
                        </div>
                    </form>

                    <div class="promo-card bg-success text-white p-3 p-md-4 shadow-sm mt-3">
                        <span class="badge bg-white bg-opacity-25 text-white fw-bold text-uppercase px-2 py-1 mb-2" style="font-size: 0.65rem;">Rekomendasi Editor</span>
                        <h5 class="fw-bold text-white mb-1">Butuh Kurasi Buku Khusus?</h5>
                        <p class="text-white-50 small mb-3">Konsultasikan preferensi bacaan Anda dengan pustakawan kami via WhatsApp.</p>
                        <a href="https://wa.me/6285842617278" target="_blank" rel="noopener" class="btn btn-warning btn-sm text-dark fw-bold rounded-pill px-3 w-100"><i class="bi bi-whatsapp me-1"></i> Tanya Pustakawan</a>
                    </div>
                </div>
            </aside>

            
            <section class="col-12 col-lg-9" id="katalog">
                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-3">
                    <div class="small text-muted">
                        Menampilkan <strong class="text-dark"><?= $dari; ?>–<?= $sampai; ?></strong> dari <strong class="text-dark"><?= number_format($total, 0, ',', '.'); ?></strong> produk
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        
                        <select id="sortSelect" class="form-select form-select-sm rounded-pill ps-3 w-auto" aria-label="Urutkan produk">
                            <?php foreach ($sort_options as $key => $opt): ?>
                                <option value="<?= $key; ?>" <?= $sort === $key ? 'selected' : ''; ?>><?= $opt[0]; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <?php if ($chips): ?>
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <span class="small text-muted"><i class="bi bi-funnel me-1"></i>Filter aktif:</span>
                        <?php foreach ($chips as $c): ?>
                            <a href="<?= htmlspecialchars($c['url']); ?>" class="chip-filter" title="Hapus filter"><?= htmlspecialchars($c['label']); ?> <i class="bi bi-x-circle-fill"></i></a>
                        <?php endforeach; ?>
                        <a href="katalog.php" class="small text-danger fw-semibold text-decoration-none ms-1">Hapus semua</a>
                    </div>
                <?php endif; ?>

                <?php if (empty($produk)): ?>
                    <div class="bg-white border rounded-4 text-center p-5 shadow-sm">
                        <i class="bi bi-search text-muted" style="font-size: 2.5rem;"></i>
                        <h5 class="fw-bold text-dark mt-3 mb-1">Produk tidak ditemukan</h5>
                        <p class="text-muted small mb-3">Coba ubah kata kunci atau hapus beberapa filter yang aktif.</p>
                        <a href="katalog.php" class="btn btn-success rounded-pill px-4 fw-semibold">Lihat Semua Produk</a>
                    </div>
                <?php else: ?>
                    <div class="row row-cols-2 row-cols-md-3 row-cols-xl-4 g-3" id="productGrid">
                        <?php foreach ($produk as $b): ?>
                            <?php $habis = $b['stok'] <= 0; $tipis = !$habis && $b['stok'] <= 10; ?>
                            <div class="col">
                                <div class="product-card">
                                    <div class="product-img-box">
                                        <?php if ($b['diskon']): ?>
                                            <span class="badge bg-danger badge-discount shadow-sm">-<?= $b['persen']; ?>%</span>
                                        <?php endif; ?>
                                        <a href="detail_buku.php?id=<?= (int)$b['id']; ?>" class="d-block text-center">
                                            <img src="<?= htmlspecialchars($b['gambar']); ?>" alt="<?= htmlspecialchars($b['judul']); ?>" loading="lazy" onerror="this.onerror=null;this.src='assets/images/placeholder.png';">
                                        </a>
                                        <span class="stock-tag <?= ($habis || $tipis) ? 'low' : ''; ?>">
                                            <?= $habis ? 'Stok Habis' : ($tipis ? 'Sisa ' . $b['stok'] : 'Stok Tersedia'); ?>
                                        </span>
                                    </div>

                                    <div class="p-3 d-flex flex-column flex-grow-1">
                                        <span class="text-muted text-uppercase fw-semibold" style="font-size: 0.68rem;"><?= htmlspecialchars($b['kategori']); ?></span>
                                        <a href="detail_buku.php?id=<?= (int)$b['id']; ?>" class="text-decoration-none text-dark">
                                            <h6 class="product-title" title="<?= htmlspecialchars($b['judul']); ?>"><?= htmlspecialchars($b['judul']); ?></h6>
                                        </a>
                                        <p class="product-author"><?= htmlspecialchars($b['penulis']); ?></p>

                                        <div class="mt-auto d-flex align-items-center justify-content-between pt-2 border-top">
                                            <div>
                                                <?php if ($b['diskon']): ?>
                                                    <div class="fw-bold text-success fs-6"><?= formatRupiah($b['diskon']); ?></div>
                                                    <del class="text-muted small" style="font-size: 0.72rem;"><?= formatRupiah($b['harga']); ?></del>
                                                <?php else: ?>
                                                    <div class="fw-bold text-dark fs-6"><?= formatRupiah($b['harga']); ?></div>
                                                <?php endif; ?>
                                            </div>
                                            <?php if (($_SESSION['role'] ?? '') !== 'admin'): ?>
                                                <button class="btn btn-outline-success btn-sm rounded-circle d-flex align-items-center justify-content-center p-2" style="width: 36px; height: 36px;" title="<?= $habis ? 'Stok habis' : 'Tambah ke Keranjang'; ?>" <?= $habis ? 'disabled' : ''; ?> onclick="tambahKeKeranjang(<?= (int)$b['id']; ?>, this)">
                                                    <i class="bi bi-bag-plus fs-6"></i>
                                                </button>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    
                    <?php if ($total_pages > 1): ?>
                        <?php
                            $pg = fn($n) => katalog_url(array_merge($base, ['page' => $n > 1 ? $n : '']));
                            $pages = array_unique(array_filter([1, $page - 1, $page, $page + 1, $total_pages], fn($n) => $n >= 1 && $n <= $total_pages));
                            sort($pages);
                        ?>
                        <div class="pagination-wrap d-flex flex-column flex-sm-row align-items-center justify-content-between gap-2 mt-4">
                            <small class="text-muted">Halaman <?= $page; ?> dari <?= $total_pages; ?></small>
                            <nav aria-label="Navigasi halaman">
                                <ul class="pagination mb-0">
                                    <li class="page-item <?= $page <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?= htmlspecialchars($pg($page - 1)); ?>#katalog"><i class="bi bi-chevron-left me-1"></i><span class="d-none d-sm-inline">Sebelumnya</span></a>
                                    </li>
                                    <?php $prev = 0; foreach ($pages as $n): ?>
                                        <?php if ($prev && $n - $prev > 1): ?>
                                            <li class="page-item disabled"><span class="page-link">…</span></li>
                                        <?php endif; ?>
                                        <li class="page-item <?= $n === $page ? 'active' : ''; ?>"><a class="page-link" href="<?= htmlspecialchars($pg($n)); ?>#katalog"><?= $n; ?></a></li>
                                        <?php $prev = $n; ?>
                                    <?php endforeach; ?>
                                    <li class="page-item <?= $page >= $total_pages ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="<?= htmlspecialchars($pg($page + 1)); ?>#katalog"><span class="d-none d-sm-inline">Selanjutnya</span><i class="bi bi-chevron-right ms-1"></i></a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
                <?php endif; ?>
            </section>
        </div>

        
        <section class="bg-white rounded-4 p-3 p-md-4 border shadow-sm my-4 my-lg-5">
            <div class="row g-3 g-md-4">
                <div class="col-12 col-md-4">
                    <div class="d-flex align-items-start gap-3 feature-box">
                        <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;"><i class="bi bi-patch-check fs-4"></i></div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Jaminan 100% Keaslian Buku</h6>
                            <p class="text-muted small mb-0">Seluruh koleksi PustakaKu didapatkan langsung melalui distribusi resmi bersama penerbit nasional maupun internasional.</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="d-flex align-items-start gap-3 feature-box">
                        <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;"><i class="bi bi-recycle fs-4"></i></div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Kemasan Ramah Lingkungan</h6>
                            <p class="text-muted small mb-0">Setiap buku dibungkus kertas pelindung dan karton kokoh sehingga sudut buku tetap tajam, tanpa limbah plastik berlebih.</p>
                        </div>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="d-flex align-items-start gap-3 feature-box">
                        <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center flex-shrink-0" style="width: 48px; height: 48px;"><i class="bi bi-truck fs-4"></i></div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1">Pengiriman Cepat Nusantara</h6>
                            <p class="text-muted small mb-0">Pesanan sebelum pukul 15.00 WIB dikirim di hari yang sama, dengan garansi ganti jika buku rusak saat diterima.</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        
        <section class="mb-4 mb-lg-5">
            <div class="mb-3">
                <span class="text-success fw-bold text-uppercase small tracking-wide" style="font-size: 0.75rem;">Bantuan Belanja</span>
                <h3 class="fw-bold text-dark mb-0">Pertanyaan Umum Belanja di Katalog</h3>
            </div>
            <div class="accordion accordion-faq" id="faqAccordion">
                <div class="accordion-item">
                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">Apakah buku yang dikirim masih bersegel rapat?</button></h2>
                    <div id="faq1" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Ya, seluruh buku berstatus baru dikirim dalam segel plastik asli dari penerbit.</div></div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">Bagaimana jika ingin memesan buku untuk institusi atau perpustakaan?</button></h2>
                    <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Kami melayani pengadaan buku dalam jumlah besar dengan harga khusus. Silakan hubungi tim kami melalui WhatsApp atau surel salam@pustakaku.id.</div></div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">Apakah bisa meminta pembatas buku (bookmark) ekstra?</button></h2>
                    <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Setiap pengiriman buku otomatis menyertakan 2 buah pembatas buku ilustrasi tanpa biaya tambahan.</div></div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header"><button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">Berapa lama estimasi pengiriman ke luar Pulau Jawa?</button></h2>
                    <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion"><div class="accordion-body">Pengiriman reguler ke kota-kota besar di Sumatera, Bali, Kalimantan, dan Sulawesi rata-rata tiba dalam 2–4 hari kerja.</div></div>
                </div>
            </div>
        </section>

    </main>

    <?php include(__DIR__ . '/layouts/footer.php'); ?>

    <script>
        (function () {
            var s = document.getElementById('searchHeaderInput');
            if (s) s.value = <?= json_encode($q); ?>;
        })();

        document.getElementById('sortSelect').addEventListener('change', function () {
            var u = new URL(window.location.href);
            u.searchParams.set('sort', this.value);
            u.searchParams.delete('page');
            window.location.href = u.toString();
        });

        (function () {
            var grid = document.getElementById('productGrid');
            var gBtn = document.getElementById('gridBtn');
            var lBtn = document.getElementById('listBtn');
            if (!grid) return;
            function setView(v) {
                grid.classList.toggle('list-view', v === 'list');
                lBtn.classList.toggle('active', v === 'list');
                gBtn.classList.toggle('active', v !== 'list');
                try { localStorage.setItem('katalogView', v); } catch (e) {}
            }
            gBtn.addEventListener('click', function () { setView('grid'); });
            lBtn.addEventListener('click', function () { setView('list'); });
            try { setView(localStorage.getItem('katalogView') || 'grid'); } catch (e) {}
        })();

        (function () {
            var KEY = 'katalogScrollY';

            try {
                var y = sessionStorage.getItem(KEY);
                if (y !== null) {
                    sessionStorage.removeItem(KEY);
                    window.scrollTo(0, parseInt(y, 10));
                }
            } catch (e) {}

            function simpan() {
                try { sessionStorage.setItem(KEY, window.scrollY); } catch (e) {}
            }

            var form = document.getElementById('filterForm');
            if (form) form.addEventListener('submit', simpan);

            document.querySelectorAll('.chip-filter, .quick-price').forEach(function (a) {
                a.addEventListener('click', simpan);
            });

            var sort = document.getElementById('sortSelect');
            if (sort) sort.addEventListener('change', simpan);
        })();
    </script>
</body>
</html>