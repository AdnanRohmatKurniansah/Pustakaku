<?php
    $current_uri = $_SERVER['REQUEST_URI'];
?>
<div class="offcanvas-lg offcanvas-start bg-dark sidebar d-flex flex-column" tabindex="-1" id="sidebarMenu">
    <div class="sidebar-brand">
        <div class="sidebar-brand-icon d-flex justify-content-center align-items-center bg-success px-2 py-1 rounded-2">
            <i class="bi bi-book-half fs-5"></i>
        </div>
        <span>PustakaKu</span>
        <button type="button" class="btn-close btn-close-white d-lg-none ms-auto" data-bs-dismiss="offcanvas" data-bs-target="#sidebarMenu" aria-label="Close"></button>
    </div>
    <div class="px-3 pt-3 pb-2 text-uppercase fw-bold text-white" style="font-size: 0.72rem; opacity: 0.5; letter-spacing: 0.05rem;">Menu Utama</div>
    <ul class="nav flex-column mb-auto">
        <li class="nav-item">
            <a class="nav-link <?= (strpos($current_uri, '/admin/index.php') !== false || substr($current_uri, -7) === '/admin/') ? 'active' : ''; ?>" href="/Pustakaku/admin/index.php">
                <i class="bi bi-grid-1x2-fill"></i>
                <span>Dashboard</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= strpos($current_uri, '/admin/kategori') !== false ? 'active' : ''; ?>" href="/Pustakaku/admin/kategori/index.php">
                <i class="bi bi-tags-fill"></i>
                <span>Kategori Buku</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= strpos($current_uri, '/admin/buku') !== false ? 'active' : ''; ?>" href="/Pustakaku/admin/buku/index.php">
                <i class="bi bi-journal-bookmark-fill"></i>
                <span>Data Buku</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= strpos($current_uri, '/admin/supplier') !== false ? 'active' : ''; ?>" href="#">
                <i class="bi bi-truck"></i>
                <span>Supplier & Penerbit</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= strpos($current_uri, '/admin/restok') !== false ? 'active' : ''; ?>" href="#">
                <i class="bi bi-archive"></i>
                <span>Restok Buku</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= strpos($current_uri, '/admin/pesanan') !== false ? 'active' : ''; ?>" href="#">
                <i class="bi bi-cart-check-fill"></i>
                <span>Pesanan Masuk</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= strpos($current_uri, '/admin/laporan') !== false ? 'active' : ''; ?>" href="#">
                <i class="bi bi-file-earmark-bar-graph-fill"></i>
                <span>Laporan Penjualan</span>
            </a>
        </li>
    </ul>
</div>