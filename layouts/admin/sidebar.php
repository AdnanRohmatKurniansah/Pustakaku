<div class="offcanvas-lg offcanvas-start bg-dark sidebar d-flex flex-column" tabindex="-1" id="sidebarMenu">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon d-flex justify-content-center align-items-center bg-success px-2 py-1 rounded-2">
            <i class="bi bi-book-half fs-5"></i>
        </div>
        <span>
            <a class="text-white text-decoration-none" href="../index.php">PustakaKu</a>
        </span>
        <button type="button" class="btn-close btn-close-white d-lg-none ms-auto" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
    </div>

    <div class="px-3 pt-3 pb-2 text-uppercase fw-bold text-white" style="font-size: 0.72rem; opacity: 0.5; letter-spacing: 0.05rem;">
        Menu Utama
    </div>

    <ul class="nav flex-column mb-auto">
        <li class="nav-item">
            <a class="nav-link" href="admin/index.php" data-menu="dashboard">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="admin/kategori/index.php" data-menu="kategori">
                <i class="bi bi-tags-fill"></i>
                <span>Kategori Buku</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="admin/buku/index.php" data-menu="buku">
                <i class="bi bi-journal-bookmark-fill"></i>
                <span>Data Buku</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="admin/supplier/index.php" data-menu="supplier">
                <i class="bi bi-truck"></i>
                <span>Supplier & Penerbit</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="admin/restok/index.php" data-menu="restok">
                <i class="bi bi-archive"></i>
                <span>Restok Buku</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="admin/pesanan/index.php" data-menu="pesanan">
                <i class="bi bi-cart-check-fill"></i>
                <span>Pesanan Masuk</span>
            </a>
        </li>

        <li class="nav-item">
            <a class="nav-link" href="admin/laporan/index.php" data-menu="laporan">
                <i class="bi bi-file-earmark-bar-graph-fill"></i>
                <span>Laporan Penjualan</span>
            </a>
        </li>
    </ul>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const currentPath = window.location.pathname;
    const sidebarLinks = document.querySelectorAll('#sidebarMenu .nav-link[data-menu]');

    const adminIndex = currentPath.indexOf('/admin/');
    const projectRoot = adminIndex !== -1
        ? currentPath.substring(0, adminIndex)
        : '';

    sidebarLinks.forEach(function (link) {
        const menu = link.dataset.menu;

        const menuPaths = {
            dashboard: '/admin/index.php',
            kategori: '/admin/kategori/index.php',
            buku: '/admin/buku/index.php',
            restok: '/admin/restok/index.php',
            supplier: '/admin/supplier/index.php',
            pesanan: '/admin/pesanan/index.php',
            laporan: '/admin/laporan/index.php'
        };

        if (menuPaths[menu]) {
            link.href = projectRoot + menuPaths[menu];
        }

        if (menu === 'dashboard') {
            if (currentPath === projectRoot + '/admin/' ||
                currentPath === projectRoot + '/admin/index.php') {
                link.classList.add('active');
            }
        } else if (currentPath.includes('/admin/' + menu)) {
            link.classList.add('active');
        }
    });
});
</script>