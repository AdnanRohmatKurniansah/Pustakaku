<nav class="navbar top-navbar">
    <div class="container-fluid px-0">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-light d-lg-none border" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu">
                <i class="bi bi-list fs-5"></i>
            </button>
            <h5 class="mb-0 fw-bold text-dark d-none d-sm-block">Dashboard Administrator</h5>
        </div>

        <div class="d-flex align-items-center gap-3 ms-auto">
            <div class="dropdown">
                <button class="btn btn-light border dropdown-toggle d-flex align-items-center gap-2 py-1 px-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="avatar-circle">
                        <?= strtoupper(substr($_SESSION['nama_lengkap'], 0, 1)); ?>
                    </div>
                    <div class="text-start d-none d-md-block pe-1">
                        <div class="fw-semibold small text-dark mb-0"><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></div>
                        <div class="text-muted" style="font-size: 0.75rem;"><?= $_SESSION['role']; ?></div>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border mt-2">
                    <li>
                        <div class="px-3 py-2 border-bottom">
                            <div class="fw-bold small"><?= htmlspecialchars($_SESSION['nama_lengkap']); ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($_SESSION['email']); ?></div>
                        </div>
                    </li>
                    <li><a class="dropdown-item py-2 text-danger" href="/projek_web/auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a></li>
                </ul>
            </div>
        </div>
    </div>
</nav>