<?php
    session_start();
    require_once __DIR__ . '/config/database.php';
    require_once __DIR__ . '/cart/proses_keranjang.php';


    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    if ($id <= 0) {
        header("Location: katalog.php");
        exit;
    }

    $res = mysqli_query($conn, "SELECT b.*, k.nama_kategori FROM buku b JOIN kategori k ON b.kategori_id = k.id WHERE b.id = '$id'");
    $buku = $res ? mysqli_fetch_assoc($res) : null;
    if (!$buku) {
        header("Location: katalog.php");
        exit;
    }

    $user_id = (int)($_SESSION['user_id'] ?? 0);
    $user_role = $_SESSION['role'] ?? '';
    $is_customer = ($user_id > 0 && $user_role === 'pelanggan');

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['aksi']) && $_POST['aksi'] === 'tambah_ulasan') {
        if (!$is_customer) {
            $_SESSION['error'] = 'Hanya pelanggan yang dapat mengirimkan ulasan.';
            header("Location: detail_buku.php?id=" . $id . "#ulasan");
            exit;
        }

        $cek_beli = mysqli_query($conn, "SELECT dp.id FROM detail_pesanan dp JOIN pesanan p ON dp.pesanan_id = p.id WHERE p.user_id = '$user_id' AND dp.buku_id = '$id' AND p.status_pesanan IN ('selesai', 'dikirim', 'diproses') LIMIT 1");
        if (!$cek_beli || mysqli_num_rows($cek_beli) == 0) {
            $_SESSION['ulasan_error'] = 'Anda hanya dapat memberikan ulasan setelah membeli buku ini.';
            header("Location: detail_buku.php?id=" . $id . "#ulasan");
            exit;
        }

        $rating = (int)($_POST['rating'] ?? 5);
        if ($rating < 1) $rating = 1;
        if ($rating > 5) $rating = 5;
        $komentar = trim($_POST['komentar'] ?? '');
        if (empty($komentar)) {
            $_SESSION['error'] = 'Kolom ulasan tidak boleh kosong.';
            header("Location: detail_buku.php?id=" . $id . "#ulasan");
            exit;
        }

        $komentar_esc = mysqli_real_escape_string($conn, $komentar);
        $cek_ada = mysqli_query($conn, "SELECT id FROM ulasan WHERE user_id = '$user_id' AND buku_id = '$id' LIMIT 1");
        if ($cek_ada && mysqli_num_rows($cek_ada) > 0) {
            mysqli_query($conn, "UPDATE ulasan SET rating = '$rating', komentar = '$komentar_esc', created_at = NOW() WHERE user_id = '$user_id' AND buku_id = '$id'");
            $_SESSION['success'] = 'Ulasan Anda berhasil diperbarui!';
        } else {
            mysqli_query($conn, "INSERT INTO ulasan (user_id, buku_id, rating, komentar, created_at) VALUES ('$user_id', '$id', '$rating', '$komentar_esc', NOW())");
            $_SESSION['success'] = 'Terima kasih, ulasan Anda berhasil dikirim!';
        }

        header("Location: detail_buku.php?id=" . $id . "#ulasan");
        exit;
    }

    $q_avg = mysqli_query($conn, "SELECT AVG(rating) AS rata_rata, COUNT(*) AS total FROM ulasan WHERE buku_id = '$id'");
    $row_avg = $q_avg ? mysqli_fetch_assoc($q_avg) : null;
    $total_ulasan = (int)($row_avg['total'] ?? 0);
    $rata_rata = $total_ulasan > 0 ? round((float)$row_avg['rata_rata'], 1) : 0;

    $sudah_beli = false;
    if ($is_customer) {
        $q_beli = mysqli_query($conn, "SELECT dp.id FROM detail_pesanan dp JOIN pesanan p ON dp.pesanan_id = p.id WHERE p.user_id = '$user_id' AND dp.buku_id = '$id' AND p.status_pesanan IN ('selesai', 'dikirim', 'diproses') LIMIT 1");
        if ($q_beli && mysqli_num_rows($q_beli) > 0) {
            $sudah_beli = true;
        }
    }

    $ulasan_saya = null;
    if ($is_customer) {
        $q_my = mysqli_query($conn, "SELECT * FROM ulasan WHERE user_id = '$user_id' AND buku_id = '$id' LIMIT 1");
        if ($q_my && mysqli_num_rows($q_my) > 0) {
            $ulasan_saya = mysqli_fetch_assoc($q_my);
        }
    }

    $daftar_ulasan = [];
    $q_ulasan = mysqli_query($conn, "SELECT u.*, us.nama_lengkap, us.foto_profil FROM ulasan u JOIN users us ON u.user_id = us.id WHERE u.buku_id = '$id' ORDER BY u.id DESC");
    if ($q_ulasan) {
        while ($ru = mysqli_fetch_assoc($q_ulasan)) {
            $daftar_ulasan[] = $ru;
        }
    }

    $buku_terkait = [];
    $kat_id = (int)$buku['kategori_id'];
    $q_terkait = mysqli_query($conn, "SELECT id, judul, penulis, harga, harga_diskon, stok, gambar FROM buku WHERE kategori_id = '$kat_id' AND id != '$id' ORDER BY id DESC LIMIT 4");
    if ($q_terkait) {
        while ($rt = mysqli_fetch_assoc($q_terkait)) {
            $buku_terkait[] = $rt;
        }
    }

    $harga = (float)$buku['harga'];
    $diskon = (float)$buku['harga_diskon'];
    $ada_diskon = $diskon > 0 && $diskon < $harga;
    $harga_final = $ada_diskon ? $diskon : $harga;
    $persen = $ada_diskon ? round((($harga - $diskon) / $harga) * 100) : 0;
    $stok = (int)$buku['stok'];
    $habis = $stok <= 0;
    $gambar = (!empty($buku['gambar']) && file_exists(__DIR__ . '/assets/uploads/buku/' . $buku['gambar'])) ? ('assets/uploads/buku/' . $buku['gambar']) : 'assets/images/banner/banner1.jpg';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($buku['judul']); ?> - PustakaKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include(__DIR__ . '/layouts/navbar.php'); ?>

    <main class="container my-3 my-md-4">
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="index.php" class="text-success text-decoration-none">Beranda</a></li>
                <li class="breadcrumb-item"><a href="katalog.php" class="text-success text-decoration-none">Katalog</a></li>
                <li class="breadcrumb-item"><a href="katalog.php?kategori=<?= (int)$buku['kategori_id']; ?>" class="text-success text-decoration-none"><?= htmlspecialchars($buku['nama_kategori']); ?></a></li>
                <li class="breadcrumb-item active text-truncate" style="max-width: 250px;" aria-current="page"><?= htmlspecialchars($buku['judul']); ?></li>
            </ol>
        </nav>

        <div class="row g-4 mb-5">
            <div class="col-12 col-md-5 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-3 bg-white text-center position-sticky" style="top: 150px;">
                    <div class="detail-book-cover mb-3 position-relative overflow-hidden rounded-3">
                        <?php if ($ada_diskon): ?>
                            <span class="badge bg-danger position-absolute top-0 start-0 m-2 px-3 py-2 rounded-pill fw-bold shadow-sm">-<?= $persen; ?>%</span>
                        <?php endif; ?>
                        <img src="<?= htmlspecialchars($gambar); ?>" alt="<?= htmlspecialchars($buku['judul']); ?>" class="img-fluid rounded-3 shadow-sm" style="max-height: 420px; object-fit: contain;">
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-7 col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 fw-semibold align-self-start mb-2"><?= htmlspecialchars($buku['nama_kategori']); ?></span>
                    <h1 class="h2 fw-bold text-dark mb-2"><?= htmlspecialchars($buku['judul']); ?></h1>
                    <p class="text-muted mb-3 fs-6">Penulis: <strong class="text-dark"><?= htmlspecialchars($buku['penulis']); ?></strong> &bull; Penerbit: <span class="text-dark"><?= htmlspecialchars($buku['penerbit'] ?: '-'); ?></span></p>

                    <div class="d-flex align-items-center gap-3 mb-4 pb-3 border-bottom flex-wrap">
                        <div class="d-flex align-items-center gap-1 text-warning">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="bi <?= ($i <= round($rata_rata)) ? 'bi-star-fill' : 'bi-star'; ?> fs-5"></i>
                            <?php endfor; ?>
                            <span class="fw-bold text-dark ms-2 fs-5"><?= $rata_rata; ?></span>
                            <span class="text-muted small">/ 5.0</span>
                        </div>
                        <span class="text-muted small">&bull;</span>
                        <a href="#ulasan" class="text-success small fw-semibold text-decoration-none"><i class="bi bi-chat-text me-1"></i><?= $total_ulasan; ?> Ulasan Pembeli</a>
                        <span class="text-muted small">&bull;</span>
                        <span class="badge <?= $habis ? 'bg-danger-subtle text-danger' : ($stok <= 10 ? 'bg-warning-subtle text-warning-emphasis' : 'bg-success-subtle text-success'); ?> rounded-pill px-3 py-1">
                            <?= $habis ? 'Stok Habis' : 'Stok Tersedia: ' . $stok; ?>
                        </span>
                    </div>

                    <div class="detail-price-box p-3 rounded-4 bg-light mb-4">
                        <div class="d-flex align-items-baseline gap-3 flex-wrap">
                            <span class="h2 fw-bold text-success mb-0"><?= formatRupiah($harga_final); ?></span>
                            <?php if ($ada_diskon): ?>
                                <del class="text-muted fs-5"><?= formatRupiah($harga); ?></del>
                                <span class="badge bg-danger rounded-pill px-2 py-1 small">Hemat <?= formatRupiah($harga - $diskon); ?></span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-2">Deskripsi & Sinopsis</h6>
                        <div class="text-muted lh-lg" style="white-space: pre-line;"><?= !empty($buku['deskripsi']) ? htmlspecialchars($buku['deskripsi']) : 'Belum ada deskripsi lengkap untuk buku ini.'; ?></div>
                    </div>

                    <div class="mb-4">
                        <h6 class="fw-bold text-dark mb-2">Spesifikasi Buku</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless small mb-0">
                                <tbody>
                                    <tr><td class="text-muted" style="width: 160px;">ISBN</td><td class="fw-semibold text-dark"><?= htmlspecialchars($buku['isbn'] ?: '-'); ?></td></tr>
                                    <tr><td class="text-muted">Tahun Terbit</td><td class="fw-semibold text-dark"><?= htmlspecialchars($buku['tahun_terbit'] ?: '-'); ?></td></tr>
                                    <tr><td class="text-muted">Berat</td><td class="fw-semibold text-dark"><?= (int)$buku['berat_gram']; ?> gram</td></tr>
                                    <tr><td class="text-muted">Kategori</td><td class="fw-semibold text-dark"><?= htmlspecialchars($buku['nama_kategori']); ?></td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="pt-4 border-top">
                        <?php if ($user_role === 'admin'): ?>
                            <div class="alert alert-warning d-flex align-items-center gap-2 rounded-3 small mb-0" role="alert">
                                <i class="bi bi-shield-exclamation fs-5 flex-shrink-0"></i>
                                <div>Anda masuk sebagai <strong>Administrator</strong>. Akun admin tidak dapat berbelanja atau menambahkan produk ke keranjang.</div>
                            </div>
                        <?php elseif ($habis): ?>
                            <button class="btn btn-secondary rounded-pill px-4 py-2 fw-semibold" disabled><i class="bi bi-x-circle me-1"></i> Stok Habis</button>
                        <?php else: ?>
                            <div class="d-flex align-items-center gap-3 flex-wrap">
                                <div class="input-group" style="width: 130px;">
                                    <button class="btn btn-outline-secondary" type="button" onclick="ubahDetailQty(-1)"><i class="bi bi-dash"></i></button>
                                    <input type="number" id="detailQtyInput" class="form-control text-center fw-bold" value="1" min="1" max="<?= $stok; ?>" readonly>
                                    <button class="btn btn-outline-secondary" type="button" onclick="ubahDetailQty(1)"><i class="bi bi-plus"></i></button>
                                </div>
                                <button class="btn btn-success rounded-pill px-4 py-2 fw-semibold d-flex align-items-center gap-2 shadow-sm" onclick="beliDetail(<?= (int)$buku['id']; ?>, this)">
                                    <i class="bi bi-bag-plus fs-5"></i>
                                    <span>Tambah ke Keranjang</span>
                                </button>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <section id="ulasan" class="mb-5">
            <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 pb-3 border-bottom mb-4">
                    <div>
                        <h4 class="fw-bold text-dark mb-1"><i class="bi bi-chat-quote-fill text-success me-2"></i>Ulasan & Penilaian Pembeli</h4>
                        <p class="text-muted small mb-0">Ulasan autentik dari pelanggan yang telah memesan dan menerima buku ini</p>
                    </div>
                    <div class="d-flex align-items-center gap-3 p-2 px-3 rounded-4 bg-light border">
                        <div class="text-center">
                            <div class="h2 fw-bold text-dark mb-0"><?= $rata_rata; ?></div>
                            <div class="text-warning small">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="bi <?= ($i <= round($rata_rata)) ? 'bi-star-fill' : 'bi-star'; ?>"></i>
                                <?php endfor; ?>
                            </div>
                        </div>
                        <div class="border-start ps-3 text-muted small">
                            <div>Total Ulasan</div>
                            <strong class="text-dark fs-6"><?= $total_ulasan; ?></strong> ulasan
                        </div>
                    </div>
                </div>

                <div class="mb-4 p-3 p-md-4 rounded-4 bg-light border">
                    <?php if (!$user_id): ?>
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Ingin memberikan ulasan?</h6>
                                <p class="text-muted small mb-0">Silakan masuk ke akun Anda terlebih dahulu. Ulasan dapat diberikan apabila Anda sudah pernah membeli buku ini.</p>
                            </div>
                            <a href="auth/login.php" class="btn btn-success btn-sm rounded-pill px-3 py-2 fw-semibold">Masuk ke Akun</a>
                        </div>
                    <?php elseif ($user_role === 'admin'): ?>
                        <div class="text-muted small d-flex align-items-center gap-2">
                            <i class="bi bi-info-circle text-primary"></i>
                            <span>Akun administrator hanya dapat melihat ulasan pembeli dan tidak dapat menuliskan ulasan produk.</span>
                        </div>
                    <?php elseif (!$sudah_beli): ?>
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-warning-subtle text-warning d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                                <i class="bi bi-lock-fill fs-5"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-1">Form Ulasan Belum Terbuka</h6>
                                <p class="text-muted small mb-0">Anda hanya dapat memberikan ulasan dan rating apabila Anda sudah pernah membeli buku ini di PustakaKu.</p>
                            </div>
                        </div>
                    <?php else: ?>
                        <div>
                            <h6 class="fw-bold text-dark mb-1"><?= $ulasan_saya ? 'Perbarui Ulasan Anda' : 'Tulis Ulasan untuk Buku Ini'; ?></h6>
                            <p class="text-muted small mb-3">Sebagai pembeli terverifikasi, bagikan pengalaman membaca Anda kepada calon pembeli lain.</p>

                            <form action="detail_buku.php?id=<?= $id; ?>" method="POST">
                                <input type="hidden" name="aksi" value="tambah_ulasan">

                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-secondary">Penilaian Bintang <span class="text-danger">*</span></label>
                                    <div class="d-flex align-items-center gap-3">
                                        <?php $curr_r = $ulasan_saya ? (int)$ulasan_saya['rating'] : 5; ?>
                                        <select name="rating" id="ratingSelect" class="form-select form-select-sm rounded-pill w-auto ps-3 fw-semibold text-warning-emphasis" required>
                                            <option value="5" <?= $curr_r === 5 ? 'selected' : ''; ?>>⭐⭐⭐⭐⭐ (5 - Sangat Puas)</option>
                                            <option value="4" <?= $curr_r === 4 ? 'selected' : ''; ?>>⭐⭐⭐⭐ (4 - Puas)</option>
                                            <option value="3" <?= $curr_r === 3 ? 'selected' : ''; ?>>⭐⭐⭐ (3 - Cukup)</option>
                                            <option value="2" <?= $curr_r === 2 ? 'selected' : ''; ?>>⭐⭐ (2 - Kurang)</option>
                                            <option value="1" <?= $curr_r === 1 ? 'selected' : ''; ?>>⭐ (1 - Kecewa)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="komentar" class="form-label small fw-semibold text-secondary">Ulasan Anda <span class="text-danger">*</span></label>
                                    <textarea name="komentar" id="komentar" class="form-control rounded-3" rows="3" placeholder="Ceritakan kualitas buku, isi bacaan, atau kecepatan pengiriman..." required><?= $ulasan_saya ? htmlspecialchars($ulasan_saya['komentar']) : ''; ?></textarea>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success rounded-pill px-4 py-2 fw-semibold d-flex align-items-center gap-2 shadow-sm">
                                        <i class="bi bi-send-fill"></i>
                                        <span><?= $ulasan_saya ? 'Simpan Perubahan Ulasan' : 'Kirim Ulasan'; ?></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>

                <div>
                    <h6 class="fw-bold text-dark mb-3">Daftar Ulasan (<?= count($daftar_ulasan); ?>)</h6>
                    <?php if (empty($daftar_ulasan)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-chat-square-text fs-1 d-block mb-2 text-muted"></i>
                            <p class="mb-0 small">Belum ada ulasan untuk buku ini. Jadilah yang pertama memberikan ulasan setelah menyelesaikan pembelian!</p>
                        </div>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($daftar_ulasan as $u): ?>
                                <div class="p-3 rounded-4 border bg-white shadow-sm">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-success-subtle text-success fw-bold d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                                <?= strtoupper(substr($u['nama_lengkap'] ?: 'P', 0, 1)); ?>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark small"><?= htmlspecialchars($u['nama_lengkap']); ?></div>
                                                <span class="badge bg-success-subtle text-success small" style="font-size: 0.7rem;"><i class="bi bi-patch-check-fill me-1"></i>Pembeli Terverifikasi</span>
                                            </div>
                                        </div>
                                        <div class="text-end">
                                            <div class="text-warning small">
                                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                                    <i class="bi <?= ($i <= (int)$u['rating']) ? 'bi-star-fill' : 'bi-star'; ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                            <small class="text-muted" style="font-size: 0.72rem;"><?= date('d M Y, H:i', strtotime($u['created_at'])); ?></small>
                                        </div>
                                    </div>
                                    <p class="text-dark small mb-0 lh-base"><?= nl2br(htmlspecialchars($u['komentar'])); ?></p>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>

        <?php if (!empty($buku_terkait)): ?>
            <section class="mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <span class="text-success fw-bold text-uppercase small tracking-wide" style="font-size: 0.75rem;">Koleksi Serupa</span>
                        <h4 class="fw-bold text-dark mb-0">Buku Terkait Lainnya</h4>
                    </div>
                    <a href="katalog.php?kategori=<?= (int)$buku['kategori_id']; ?>" class="text-success small fw-semibold text-decoration-none">Lihat Semua <i class="bi bi-arrow-right"></i></a>
                </div>

                <div class="row row-cols-2 row-cols-md-4 g-3">
                    <?php foreach ($buku_terkait as $bt): ?>
                        <?php
                            $h_bt = (float)$bt['harga'];
                            $d_bt = (float)$bt['harga_diskon'];
                            $ada_d_bt = $d_bt > 0 && $d_bt < $h_bt;
                            $img_bt = (!empty($bt['gambar']) && file_exists(__DIR__ . '/assets/uploads/buku/' . $bt['gambar'])) ? ('assets/uploads/buku/' . $bt['gambar']) : 'assets/images/banner/banner1.jpg';
                        ?>
                        <div class="col">
                            <div class="product-card h-100">
                                <div class="product-img-box">
                                    <a href="detail_buku.php?id=<?= (int)$bt['id']; ?>" class="d-block text-center">
                                        <img src="<?= htmlspecialchars($img_bt); ?>" alt="<?= htmlspecialchars($bt['judul']); ?>" loading="lazy">
                                    </a>
                                </div>
                                <div class="p-3 d-flex flex-column flex-grow-1">
                                    <a href="detail_buku.php?id=<?= (int)$bt['id']; ?>" class="text-decoration-none text-dark">
                                        <h6 class="product-title" title="<?= htmlspecialchars($bt['judul']); ?>"><?= htmlspecialchars($bt['judul']); ?></h6>
                                    </a>
                                    <p class="product-author"><?= htmlspecialchars($bt['penulis']); ?></p>
                                    <div class="mt-auto d-flex align-items-center justify-content-between pt-2 border-top">
                                        <div>
                                            <?php if ($ada_d_bt): ?>
                                                <div class="fw-bold text-success fs-6"><?= formatRupiah($d_bt); ?></div>
                                                <del class="text-muted small" style="font-size: 0.72rem;"><?= formatRupiah($h_bt); ?></del>
                                            <?php else: ?>
                                                <div class="fw-bold text-dark fs-6"><?= formatRupiah($h_bt); ?></div>
                                            <?php endif; ?>
                                        </div>
                                        <a href="detail_buku.php?id=<?= (int)$bt['id']; ?>" class="btn btn-outline-success btn-sm rounded-circle d-flex align-items-center justify-content-center p-2" style="width: 36px; height: 36px;" title="Lihat Detail"><i class="bi bi-eye"></i></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>
    </main>

    <?php include(__DIR__ . '/layouts/footer.php'); ?>

    <script>
        function ubahDetailQty(delta) {
            const el = document.getElementById('detailQtyInput');
            if (!el) return;
            let val = parseInt(el.value, 10) || 1;
            const min = parseInt(el.min, 10) || 1;
            const max = parseInt(el.max, 10) || 999;
            val += delta;
            if (val < min) val = min;
            if (val > max) val = max;
            el.value = val;
        }

        function beliDetail(id, btn) {
            const el = document.getElementById('detailQtyInput');
            const qty = el ? parseInt(el.value, 10) || 1 : 1;
            tambahKeKeranjang(id, btn, qty);
        }
    </script>
</body>
</html>

