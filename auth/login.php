<?php
    session_start();

    if (isset($_SESSION['user_id']) && isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
        header("Location: ../admin/index.php");
        exit;
    }

    $error = $_SESSION['error'] ?? '';
    $old_email = $_SESSION['old_email'] ?? '';
    unset($_SESSION['error'], $_SESSION['old_email']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - PustakaKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body class="login-body d-flex align-items-center justify-content-center py-5">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12 col-sm-10 col-md-8 col-lg-5">
                <div class="card card-login p-4 p-sm-5">
                    <div class="text-center mb-4">
                        <a href="../index.php" class="brand-link mb-2">
                            <span class="brand-icon">
                                <i class="bi bi-book-half"></i>
                            </span>
                            <span class="brand-text">
                                <span class="brand-name fs-4">Pustaka<span>Ku</span></span>
                                <span class="brand-caption" style="font-size: 14px;">Buku & Alat Tulis</span>
                            </span>
                        </a>
                        <p class="text-muted small mb-0">Masuk ke panel kendali toko buku & ATK</p>
                    </div>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center py-2 px-3 small" role="alert">
                            <i class="bi bi-exclamation-triangle-fill me-2 flex-shrink-0"></i>
                            <div><?= htmlspecialchars($error); ?></div>
                            <button type="button" class="btn-close ms-auto p-2 mt-1" style="font-size: 13px;" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="process_login.php" method="POST">
                        <div class="mb-3">
                            <label for="email" class="form-label small fw-semibold text-secondary">Alamat Email</label>
                            <input type="email" class="form-control form-control-custom" id="email" name="email" placeholder="admin@pustakaku.com" value="<?= htmlspecialchars($old_email); ?>" required autofocus>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label small fw-semibold text-secondary">Password</label>
                            <input type="password" class="form-control form-control-custom" id="password" name="password" placeholder="Masukkan password" required>
                        </div>

                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-success btn-success-custom d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-box-arrow-in-right"></i>
                                <span>Masuk ke Dashboard</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>