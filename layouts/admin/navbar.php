<?php
    $admin_pos = strpos($_SERVER['SCRIPT_NAME'], '/admin/');
    $sub_path = $admin_pos !== false ? substr($_SERVER['SCRIPT_NAME'], $admin_pos + 7) : '';
    $admin_root_prefix = str_repeat('../', substr_count($sub_path, '/') + 1);
    $admin_asset_prefix = $admin_root_prefix . 'assets/';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($conn) || !$conn) {
        require_once __DIR__ . '/../../config/database.php';
    }
    require_once __DIR__ . '/../../services/cloudinary_service.php';

    $admin_nav_user = null;
    if (isset($_SESSION['user_id']) && isset($conn) && $conn) {
        $admin_nav_id = (int) $_SESSION['user_id'];
        $q_admin_nav = mysqli_query($conn, "SELECT nama_lengkap, email, role, foto_profil FROM users WHERE id = '$admin_nav_id' LIMIT 1");
        if ($q_admin_nav) {
            $admin_nav_user = mysqli_fetch_assoc($q_admin_nav);
        }
    }

    $admin_nama = $admin_nav_user['nama_lengkap'] ?? ($_SESSION['nama_lengkap'] ?? '');
    $admin_email = $admin_nav_user['email'] ?? ($_SESSION['email'] ?? '');
    $admin_role = $admin_nav_user['role'] ?? ($_SESSION['role'] ?? '');
    $admin_foto_raw = trim((string) ($admin_nav_user['foto_profil'] ?? ''));
    $admin_has_photo = $admin_foto_raw !== '' && resolveImageUrl($admin_foto_raw, 'user') !== 'assets/images/placeholder.png';
    $admin_foto_url = $admin_has_photo ? resolveImageUrl($admin_foto_raw, 'user') : '';
    $admin_inisial = strtoupper(substr($admin_nama !== '' ? $admin_nama : 'A', 0, 1));
?>
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
                    <?php if ($admin_has_photo): ?>
                        <img src="<?= htmlspecialchars($admin_foto_url, ENT_QUOTES, 'UTF-8'); ?>" alt="<?= htmlspecialchars($admin_nama, ENT_QUOTES, 'UTF-8'); ?>" class="avatar-circle avatar-photo" onerror="this.outerHTML='<span class=\'avatar-circle\'>' + this.alt.charAt(0).toUpperCase() + '</span>';">
                    <?php else: ?>
                        <div class="avatar-circle">
                            <?= htmlspecialchars($admin_inisial, ENT_QUOTES, 'UTF-8'); ?>
                        </div>
                    <?php endif; ?>
                    <div class="text-start d-none d-md-block pe-1">
                        <div class="fw-semibold small text-dark mb-0"><?= htmlspecialchars($admin_nama, ENT_QUOTES, 'UTF-8'); ?></div>
                        <div class="text-muted" style="font-size: 0.75rem;"><?= htmlspecialchars($admin_role, ENT_QUOTES, 'UTF-8'); ?></div>
                    </div>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border mt-2">
                    <li>
                        <div class="px-3 py-2 border-bottom">
                            <div class="fw-bold small"><?= htmlspecialchars($admin_nama, ENT_QUOTES, 'UTF-8'); ?></div>
                            <div class="text-muted small"><?= htmlspecialchars($admin_email, ENT_QUOTES, 'UTF-8'); ?></div>
                        </div>
                    </li>
                    <li>
                        <a class="dropdown-item py-2" href="<?= $admin_root_prefix; ?>admin/profile/index.php"><i class="bi bi-person-circle me-2"></i>Profile</a>
                        <a class="dropdown-item py-2 text-danger" href="<?= $admin_root_prefix; ?>auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Keluar</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</nav>

<script src="<?= $admin_asset_prefix; ?>js/toast.js"></script>
<?= tampilkan_alert_toast(); ?>
