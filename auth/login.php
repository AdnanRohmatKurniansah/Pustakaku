<?php
    session_start();
    require_once __DIR__ . '/../config/helper.php';

    if (isset($_SESSION['user_id']) && isset($_SESSION['role'])) {
        if ($_SESSION['role'] === 'admin') {
            header("Location: ../admin/index.php");
            exit;
        } else {
            header("Location: ../index.php");
            exit;
        }
    }

    $old_email = $_SESSION['old_email'] ?? '';
    unset($_SESSION['old_email']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Masuk - PustakaKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-body d-flex align-items-center justify-content-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5">
                <div class="card card-login p-4 p-sm-5 shadow-sm border-0 rounded-4">
                    <div class="text-center mb-4">
                        <a href="../index.php" class="brand-link mb-2">
                            <span class="brand-icon"><i class="bi bi-book-half"></i></span>
                            <span class="brand-text">
                                <span class="brand-name fs-4">Pustaka<span>Ku</span></span>
                                <span class="brand-caption" style="font-size: 14px;">Buku & Alat Tulis</span>
                            </span>
                        </a>
                        <h5 class="fw-bold text-dark mt-2 mb-1">Masuk ke PustakaKu</h5>
                        <p class="text-muted small mb-0">Masuk untuk mulai berbelanja atau kelola toko</p>
                    </div>

                    <form action="process_login.php" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label small fw-semibold text-secondary">Alamat Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control form-control-custom" id="email" name="email" placeholder="nama@email.com" value="<?= htmlspecialchars($old_email); ?>" required autofocus>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label small fw-semibold text-secondary">Kata Sandi <span class="text-danger">*</span></label>
                            <input type="password" class="form-control form-control-custom" id="password" name="password" placeholder="Masukkan kata sandi Anda" required>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-success btn-success-custom d-flex align-items-center justify-content-center gap-2 py-2 fw-semibold">
                                <i class="bi bi-box-arrow-in-right"></i>
                                <span>Masuk ke Akun</span>
                            </button>
                        </div>
                    </form>

                    <div class="text-center mt-3 pt-2 border-top">
                        <span class="small text-muted">Belum punya akun? </span>
                        <a href="register.php" class="small fw-semibold text-success text-decoration-none">Daftar sekarang</a>
                    </div>
                    <div class="text-center mt-2">
                        <a href="../index.php" class="small text-muted text-decoration-none"><i class="bi bi-arrow-left me-1"></i>Kembali ke Beranda</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/toast.js"></script>
    <?= tampilkan_alert_toast(); ?>
</body>
</html>
