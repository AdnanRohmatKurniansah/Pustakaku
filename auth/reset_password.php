<?php
    require_once dirname(__DIR__) . '/config/database.php';
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $token = trim($_GET['token'] ?? '');
    $token_valid = false;
    $user_name = '';

    if (!empty($token)) {
        $token_esc = mysqli_real_escape_string($conn, $token);
        $q_token = mysqli_query($conn, "SELECT id, nama_lengkap, email FROM users WHERE reset_token = '$token_esc' AND reset_token_expires >= NOW() LIMIT 1");
        if ($q_token && mysqli_num_rows($q_token) > 0) {
            $token_valid = true;
            $user_row = mysqli_fetch_assoc($q_token);
            $user_name = $user_row['nama_lengkap'];
        }
    }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atur Ulang Kata Sandi - PustakaKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="icon" href="../assets/images/favicon.png" type="image/png">
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
                        <h5 class="fw-bold text-dark mt-2 mb-1">Kata Sandi Baru</h5>
                        <?php if ($token_valid): ?>
                            <p class="text-muted small mb-0">Halo, <strong><?= htmlspecialchars($user_name); ?></strong>. Masukkan kata sandi baru untuk akun Anda.</p>
                        <?php else: ?>
                            <p class="text-danger small mb-0">Tautan pemulihan tidak valid atau sudah kedaluwarsa.</p>
                        <?php endif; ?>
                    </div>

                    <?php if ($token_valid): ?>
                        <form action="proses_reset_password.php" method="POST">
                            <input type="hidden" name="token" value="<?= htmlspecialchars($token); ?>">

                            <div class="mb-3">
                                <label for="password_baru" class="form-label small fw-semibold text-secondary">Kata Sandi Baru <span class="text-danger">*</span></label>
                                <input type="password" class="form-control form-control-custom" id="password_baru" name="password_baru" placeholder="Minimal 6 karakter" required minlength="6" autofocus>
                            </div>

                            <div class="mb-4">
                                <label for="konfirmasi_password" class="form-label small fw-semibold text-secondary">Ulangi Kata Sandi Baru <span class="text-danger">*</span></label>
                                <input type="password" class="form-control form-control-custom" id="konfirmasi_password" name="konfirmasi_password" placeholder="Ulangi kata sandi baru" required minlength="6">
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-success btn-success-custom d-flex align-items-center justify-content-center gap-2 py-2 fw-semibold">
                                    <i class="bi bi-shield-lock-fill"></i>
                                    <span>Simpan Kata Sandi Baru</span>
                                </button>
                            </div>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-danger py-2 small rounded-3 text-center mb-4">
                            Tautan reset ini sudah tidak berlaku atau salah. Silakan lakukan permintaan ulang melalui halaman lupa kata sandi.
                        </div>
                        <div class="d-grid gap-2">
                            <a href="lupa_password.php" class="btn btn-success btn-success-custom py-2 fw-semibold text-center">Minta Tautan Baru</a>
                            <a href="login.php" class="btn btn-outline-secondary py-2 fw-semibold text-center">Kembali ke Halaman Masuk</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/toast.js"></script>
    <?= tampilkan_alert_toast(); ?>
</body>
</html>

