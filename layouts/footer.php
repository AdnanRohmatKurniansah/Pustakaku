<?php
    $f_kategori = [];
    if (isset($conn) && $conn) {
        $res_fk = mysqli_query($conn, "SELECT id, nama_kategori FROM kategori ORDER BY id ASC");
        if ($res_fk) {
            while ($row = mysqli_fetch_assoc($res_fk)) {
                $f_kategori[] = $row;
            }
        }
    }
    $half_kat = ceil(count($f_kategori) / 2);
    $footer_col1 = array_slice($f_kategori, 0, $half_kat);
    $footer_col2 = array_slice($f_kategori, $half_kat);
?>

<footer id="footer" class="footer-wrap pt-5 pb-4 mt-5">
    <div class="container">
        <div class="row g-4 pb-4 border-bottom border-success-subtle">
            <div class="col-12 col-lg-5">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div class="rounded-3 bg-success text-white d-flex align-items-center justify-content-center p-2 shadow-sm" style="width: 38px; height: 38px;"><i class="bi bi-book-half fs-5"></i></div>
                    <span class="fs-4 fw-bold text-dark">Pustaka<span class="text-success">Ku</span></span>
                </div>
                <p class="text-muted small mb-0 lh-base" style="max-width: 440px;">PustakaKu adalah platform toko buku & ATK online terpercaya di Indonesia dengan ribuan judul buku pilihan & peralatan tulis kantor berkualitas sejak 2024.</p>
            </div>

            <div class="col-6 col-sm-3 col-lg-2">
                <h6 class="footer-title">Kategori Buku</h6>
                <ul class="list-unstyled mb-0">
                    <?php foreach ($footer_col1 as $k): ?>
                        <li><a href="#terlaris" class="footer-link"><?= htmlspecialchars($k['nama_kategori']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="col-6 col-sm-3 col-lg-2">
                <h6 class="footer-title">Kategori Pilihan</h6>
                <ul class="list-unstyled mb-0">
                    <?php foreach ($footer_col2 as $k): ?>
                        <li><a href="#atk" class="footer-link"><?= htmlspecialchars($k['nama_kategori']); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <div class="col-12 col-sm-6 col-lg-3">
                <h6 class="footer-title">Kontak Kami</h6>
                <div class="text-muted small mb-2 d-flex align-items-start gap-2"><i class="bi bi-geo-alt-fill text-success flex-shrink-0 mt-1"></i><span>Jl. Kaliurang KM 9, Sleman, Daerah Istimewa Yogyakarta 55581</span></div>
                <div class="text-muted small mb-2 d-flex align-items-center gap-2"><i class="bi bi-whatsapp text-success flex-shrink-0"></i><span>+62 812-3456-7890</span></div>
                <div class="text-muted small mb-2 d-flex align-items-center gap-2"><i class="bi bi-envelope-fill text-success flex-shrink-0"></i><span>halo@pustakaku.com</span></div>
                <div class="text-muted small d-flex align-items-center gap-2"><i class="bi bi-clock-fill text-success flex-shrink-0"></i><span>Buka Setiap Hari: 08.00 - 21.00 WIB</span></div>
            </div>
        </div>

        <div class="pt-3 d-flex flex-column flex-md-row align-items-center justify-content-between gap-2 text-muted" style="font-size: 13px;">
            <div>&copy; <?= date('Y'); ?> <strong>PustakaKu Indonesia</strong> - Hak Cipta Dilindungi Undang-Undang.</div>
            <div class="d-flex gap-3">
                <a href="#footer" class="text-muted text-decoration-none">Syarat & Ketentuan</a>
                <span>•</span>
                <a href="#footer" class="text-muted text-decoration-none">Kebijakan Privasi</a>
                <span>•</span>
                <a href="#footer" class="text-muted text-decoration-none">Pusat Bantuan</a>
            </div>
        </div>
    </div>
</footer>
<script src="assets/js/cart.js"></script>


