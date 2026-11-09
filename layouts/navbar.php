<?php
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }   
    $halaman_ini = basename($_SERVER['PHP_SELF']);
    $kat_dipilih = array_map('intval', (array)($_GET['kategori'] ?? []));
    $promo_aktif = ($_GET['promo'] ?? '') === '1';
    
    require_once __DIR__ . '/../cart/proses_keranjang.php';
    $cart_count = isset($conn) ? cart_count($conn) : 0;
    if (!function_exists('nav_aktif')) {
    function nav_aktif(bool $kondisi): string { return $kondisi ? ' active' : ''; }
}
?>

<div class="announcement-bar bg-success text-white py-2">
    <div class="container d-flex justify-content-center justify-content-md-between align-items-center">
        <span class="small text-center"><i class="bi bi-megaphone-fill me-2"></i>Promo Buku & ATK: Diskon hingga 50% dan gratis ongkir!</span>
        <a href="#footer" class="d-none d-md-inline text-white text-decoration-none small"><i class="bi bi-question-circle me-1"></i>Bantuan</a>
    </div>
</div>

<header class="bg-white sticky-top border-bottom shadow-sm">
    <div class="container py-3">
        <div class="header-main">
            <a href="index.php" class="brand-link">
                <span class="brand-icon"><i class="bi bi-book-half"></i></span>
                <span class="brand-text">
                    <span class="brand-name">Pustaka<span>Ku</span></span>
                    <span class="brand-caption">Buku & Alat Tulis</span>
                </span>
            </a>
            <form action="#katalog" method="GET" id="searchHeaderForm" class="header-search">
                <div class="input-group">
                    <input type="search" name="q" id="searchHeaderInput" class="form-control" placeholder="Cari judul buku, penulis, penerbit, atau ATK..." aria-label="Cari produk">
                    <button class="btn btn-success" type="submit" aria-label="Cari produk"><i class="bi bi-search"></i></button>
                </div>
            </form>

            <div class="header-actions">
                <a href="keranjang.php" class="cart-icon-button" title="Keranjang Belanja" aria-label="Keranjang belanja">
                    <i class="bi bi-bag-check"></i>
                    <?php if ($cart_count > 0): ?>
                        <span class="cart-count"><?= (int) $cart_count; ?></span>
                    <?php endif; ?>
                </a>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <div class="dropdown">
                        <button class="account-button" id="accountDropdownBtn" type="button" aria-expanded="false" aria-label="Menu akun">
                            <span class="avatar-circle"><?= strtoupper(substr($_SESSION['nama_lengkap'] ?? 'U', 0, 1)); ?></span>
                            <i class="bi bi-chevron-down small"></i>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end shadow border-0 rounded-4 p-2" id="accountDropdownMenu" aria-labelledby="accountDropdownBtn">
                            <li class="px-3 py-2 border-bottom">
                                <div class="fw-bold small"><?= htmlspecialchars($_SESSION['nama_lengkap'] ?? ''); ?></div>
                                <div class="text-muted small"><?= htmlspecialchars($_SESSION['email'] ?? ''); ?></div>
                            </li>

                            <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                                <li><a class="dropdown-item py-2" href="admin/index.php"><i class="bi bi-speedometer2 me-2 text-success"></i>Dashboard Admin</a></li>
                            <?php endif; ?>

                           

                            <?php if (($_SESSION['role'] ?? '') === 'pelanggan'): ?>
                                <li><a class="dropdown-item py-2" href="#pesanan"><i class="bi bi-box-seam me-2 text-success"></i>Pesanan Saya</a></li>
                            <?php endif; ?>

                            <li><hr class="dropdown-divider"></li>

                            <li><a class="dropdown-item text-danger/admin/index.php py-2" href="auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="auth/login.php" class="login-button"><i class="bi bi-person"></i><span>Masuk</span></a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <nav class="border-top">
        <div class="container">
            <div class="header-nav">
                <a href="index.php" class="subnav-link<?= nav_aktif($halaman_ini === 'index.php'); ?>">Beranda</a>
                <a href="katalog.php" class="subnav-link<?= nav_aktif($halaman_ini === 'katalog.php' && !$kat_dipilih && !$promo_aktif); ?>">Semua Produk</a>
                <a href="katalog.php?kategori=1" class="subnav-link<?= nav_aktif($kat_dipilih === [1]); ?>">Buku Fiksi</a>
                <a href="katalog.php?kategori=2" class="subnav-link<?= nav_aktif($kat_dipilih === [2]); ?>">Buku Non-Fiksi</a>
                <a href="katalog.php?kategori=3" class="subnav-link<?= nav_aktif($kat_dipilih === [3]); ?>">Buku Pelajaran</a>
                <a href="katalog.php?kategori=4" class="subnav-link<?= nav_aktif($kat_dipilih === [4]); ?>">Buku Anak</a>
                <a href="katalog.php?kategori=5" class="subnav-link<?= nav_aktif($kat_dipilih === [5]); ?>">Pengembangan Diri</a>
                <a href="katalog.php?kategori=6" class="subnav-link<?= nav_aktif($kat_dipilih === [6]); ?>">Alat Tulis & ATK</a>
                <a href="katalog.php?promo=1" class="subnav-link<?= nav_aktif($promo_aktif); ?>">Promo & Diskon</a>
            </div>
        </div>
    </nav>
</header>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/cart.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const btn = document.getElementById('accountDropdownBtn');
        const menu = document.getElementById('accountDropdownMenu');
        if (btn && menu) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                menu.classList.toggle('show');
                btn.setAttribute('aria-expanded', menu.classList.contains('show') ? 'true' : 'false');
            });
            document.addEventListener('click', function(e) {
                if (!btn.contains(e.target) && !menu.contains(e.target)) {
                    menu.classList.remove('show');
                    btn.setAttribute('aria-expanded', 'false');
                }
            });
        }
    });
</script>