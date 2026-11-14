<?php
session_start();
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/cart/proses_keranjang.php';

if (!isset($_SESSION['user_id'])) {
    $_SESSION['error'] = "Silakan masuk terlebih dahulu untuk mengakses akun Anda.";
    $_SESSION['redirect_after_login'] = 'akun.php';
    header("Location: auth/login.php");
    exit;
}

$user_id = (int) $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'update_profile') {
        $nama_lengkap = trim($_POST['nama_lengkap'] ?? '');
        $no_telepon = trim($_POST['no_telepon'] ?? '');
        $alamat_lengkap = trim($_POST['alamat_lengkap'] ?? '');

        if (empty($nama_lengkap)) {
            $_SESSION['error'] = "Nama lengkap tidak boleh kosong!";
            header("Location: akun.php");
            exit;
        }

        $foto_baru = null;
        if (isset($_FILES['foto_profil']) && $_FILES['foto_profil']['error'] === UPLOAD_ERR_OK) {
            $file_tmp = $_FILES['foto_profil']['tmp_name'];
            $file_name = $_FILES['foto_profil']['name'];
            $file_size = $_FILES['foto_profil']['size'];
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($file_ext, $allowed_ext)) {
                $_SESSION['error'] = "Format foto hanya mendukung JPG, JPEG, PNG, atau WEBP!";
                header("Location: akun.php");
                exit;
            }

            if ($file_size > 2 * 1024 * 1024) {
                $_SESSION['error'] = "Ukuran file foto maksimal 2MB!";
                header("Location: akun.php");
                exit;
            }

            $unique_name = 'user_' . $user_id . '_' . time() . '.' . $file_ext;
            $upload_dir = __DIR__ . '/assets/uploads/user/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $target_file = $upload_dir . $unique_name;
            if (move_uploaded_file($file_tmp, $target_file)) {
                $foto_baru = $unique_name;

                $q_old = mysqli_query($conn, "SELECT foto_profil FROM users WHERE id = '$user_id'");
                $old_row = $q_old ? mysqli_fetch_assoc($q_old) : null;
                if ($old_row && !empty($old_row['foto_profil']) && file_exists($upload_dir . $old_row['foto_profil'])) {
                    @unlink($upload_dir . $old_row['foto_profil']);
                }
            }
        }

        $nama_esc = mysqli_real_escape_string($conn, $nama_lengkap);
        $telp_esc = mysqli_real_escape_string($conn, $no_telepon);
        $alamat_esc = mysqli_real_escape_string($conn, $alamat_lengkap);

        if ($foto_baru !== null) {
            $foto_esc = mysqli_real_escape_string($conn, $foto_baru);
            mysqli_query($conn, "UPDATE users SET nama_lengkap = '$nama_esc', no_telepon = '$telp_esc', alamat_lengkap = '$alamat_esc', foto_profil = '$foto_esc', updated_at = NOW() WHERE id = '$user_id'");
        } else {
            mysqli_query($conn, "UPDATE users SET nama_lengkap = '$nama_esc', no_telepon = '$telp_esc', alamat_lengkap = '$alamat_esc', updated_at = NOW() WHERE id = '$user_id'");
        }

        $_SESSION['nama_lengkap'] = $nama_lengkap;
        $_SESSION['success'] = "Data profil berhasil diperbarui!";
        header("Location: akun.php");
        exit;
    }

    if ($aksi === 'update_password') {
        $password_lama = $_POST['password_lama'] ?? '';
        $password_baru = $_POST['password_baru'] ?? '';
        $konfirmasi = $_POST['konfirmasi_password'] ?? '';

        if (empty($password_lama) || empty($password_baru) || empty($konfirmasi)) {
            $_SESSION['error'] = "Semua kolom kata sandi wajib diisi!";
            header("Location: akun.php#keamanan");
            exit;
        }

        if (strlen($password_baru) < 6) {
            $_SESSION['error'] = "Kata sandi baru minimal 6 karakter!";
            header("Location: akun.php#keamanan");
            exit;
        }

        if ($password_baru !== $konfirmasi) {
            $_SESSION['error'] = "Konfirmasi kata sandi baru tidak cocok!";
            header("Location: akun.php#keamanan");
            exit;
        }

        $q_pass = mysqli_query($conn, "SELECT password FROM users WHERE id = '$user_id' LIMIT 1");
        $user_db = $q_pass ? mysqli_fetch_assoc($q_pass) : null;

        if ($user_db && (password_verify($password_lama, $user_db['password']) || md5($password_lama) === $user_db['password'])) {
            $hashed_new = password_hash($password_baru, PASSWORD_BCRYPT);
            $hashed_esc = mysqli_real_escape_string($conn, $hashed_new);
            mysqli_query($conn, "UPDATE users SET password = '$hashed_esc', updated_at = NOW() WHERE id = '$user_id'");
            $_SESSION['success'] = "Kata sandi berhasil diubah!";
            header("Location: akun.php#keamanan");
            exit;
        } else {
            $_SESSION['error'] = "Kata sandi saat ini tidak sesuai!";
            header("Location: akun.php#keamanan");
            exit;
        }
    }
}

$user = null;
$q_user = mysqli_query($conn, "SELECT id, nama_lengkap, email, role, no_telepon, alamat_lengkap, foto_profil, created_at FROM users WHERE id = '$user_id' LIMIT 1");
$user = $q_user ? mysqli_fetch_assoc($q_user) : null;

if (!$user) {
    session_destroy();
    header("Location: auth/login.php");
    exit;
}

$cart_qty = cart_count($conn);
$has_photo = !empty($user['foto_profil']) && file_exists(__DIR__ . '/assets/uploads/user/' . $user['foto_profil']);
$initials = strtoupper(substr($user['nama_lengkap'] ?? 'U', 0, 1));
$joined_date = !empty($user['created_at']) ? date('d M Y', strtotime($user['created_at'])) : '-';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Saya - PustakaKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-light">
    <?php include(__DIR__ . '/layouts/navbar.php'); ?>

    <main class="container py-4 my-md-2">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="index.php" class="text-success text-decoration-none">Beranda</a></li>
                <li class="breadcrumb-item active" aria-current="page">Akun Saya</li>
            </ol>
        </nav>

        <div class="row g-4">
            <div class="col-12 col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 p-4 text-center mb-4">
                    <div class="position-relative d-inline-block mx-auto mb-3">
                        <?php if ($has_photo): ?>
                            <img src="assets/uploads/user/<?= htmlspecialchars($user['foto_profil']); ?>" alt="<?= htmlspecialchars($user['nama_lengkap']); ?>" class="rounded-circle object-fit-cover shadow-sm border border-3 border-success-subtle" style="width: 100px; height: 100px;">
                        <?php else: ?>
                            <div class="rounded-circle bg-success text-white fw-bold d-flex align-items-center justify-content-center shadow-sm border border-3 border-success-subtle" style="width: 100px; height: 100px; font-size: 2.2rem;"><?= $initials; ?></div>
                        <?php endif; ?>
                    </div>

                    <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($user['nama_lengkap']); ?></h5>
                    <p class="text-muted small mb-2"><?= htmlspecialchars($user['email']); ?></p>

                    <div>
                        <span class="badge <?= ($user['role'] === 'admin') ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-success-subtle text-success border border-success-subtle'; ?> rounded-pill px-3 py-1 fw-semibold small">
                            <i class="bi <?= ($user['role'] === 'admin') ? 'bi-shield-check' : 'bi-person-check'; ?> me-1"></i><?= ucfirst(htmlspecialchars($user['role'])); ?>
                        </span>
                    </div>

                    <hr class="my-3 text-muted">

                    <div class="text-start small text-muted">
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="bi bi-calendar3 me-2 text-success"></i>Bergabung:</span>
                            <span class="fw-semibold text-dark"><?= $joined_date; ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span><i class="bi bi-telephone me-2 text-success"></i>Telepon:</span>
                            <span class="fw-semibold text-dark"><?= !empty($user['no_telepon']) ? htmlspecialchars($user['no_telepon']) : '-'; ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span><i class="bi bi-bag-check me-2 text-success"></i>Keranjang:</span>
                            <span class="badge bg-success rounded-pill px-2 py-1"><?= $cart_qty; ?> item</span>
                        </div>
                    </div>

                    <hr class="my-3 text-muted">

                    <div class="d-grid gap-2">
                        <a href="keranjang.php" class="btn btn-outline-success rounded-pill btn-sm py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-bag-fill"></i><span>Lihat Keranjang Belanja</span>
                        </a>
                        <?php if ($user['role'] === 'admin'): ?>
                            <a href="admin/index.php" class="btn btn-success rounded-pill btn-sm py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                                <i class="bi bi-speedometer2"></i><span>Panel Administrator</span>
                            </a>
                        <?php endif; ?>
                        <a href="auth/logout.php" class="btn btn-outline-danger rounded-pill btn-sm py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-box-arrow-right"></i><span>Keluar dari Akun</span>
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 mb-4">
                    <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-4">
                        <div>
                            <h5 class="fw-bold text-dark mb-1"><i class="bi bi-person-lines-fill text-success me-2"></i>Perbarui Data Profil</h5>
                            <p class="text-muted small mb-0">Kelola informasi pribadi, kontak, dan alamat pengiriman Anda</p>
                        </div>
                    </div>

                    <form action="akun.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="aksi" value="update_profile">

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="nama_lengkap" class="form-label small fw-semibold text-secondary">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control rounded-3" id="nama_lengkap" name="nama_lengkap" value="<?= htmlspecialchars($user['nama_lengkap']); ?>" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="email" class="form-label small fw-semibold text-secondary">Alamat Email</label>
                                <input type="email" class="form-control rounded-3 bg-light" id="email" value="<?= htmlspecialchars($user['email']); ?>" disabled readonly>
                                <div class="form-text small" style="font-size: 11px;">Alamat email digunakan sebagai identitas akun dan tidak dapat diubah.</div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-12 col-md-6">
                                <label for="no_telepon" class="form-label small fw-semibold text-secondary">Nomor Telepon / WhatsApp</label>
                                <input type="tel" class="form-control rounded-3" id="no_telepon" name="no_telepon" placeholder="Contoh: 08123456789" value="<?= htmlspecialchars($user['no_telepon'] ?? ''); ?>">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="foto_profil" class="form-label small fw-semibold text-secondary">Ganti Foto Profil</label>
                                <input type="file" class="form-control rounded-3" id="foto_profil" name="foto_profil" accept="image/jpeg,image/png,image/webp">
                                <div class="form-text small" style="font-size: 11px;">Format: JPG, PNG, atau WEBP. Maksimal 2MB.</div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="alamat_lengkap" class="form-label small fw-semibold text-secondary">Alamat Lengkap Pengiriman</label>
                            <textarea class="form-control rounded-3" id="alamat_lengkap" name="alamat_lengkap" rows="3" placeholder="Tuliskan jalan, nomor rumah, RT/RW, kelurahan, kecamatan, kota/kabupaten, dan kode pos"><?= htmlspecialchars($user['alamat_lengkap'] ?? ''); ?></textarea>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-success rounded-pill px-4 py-2 fw-semibold d-flex align-items-center gap-2 shadow-sm">
                                <i class="bi bi-save"></i>
                                <span>Simpan Perubahan Profil</span>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5" id="keamanan">
                    <div class="d-flex align-items-center justify-content-between pb-3 border-bottom mb-4">
                        <div>
                            <h5 class="fw-bold text-dark mb-1"><i class="bi bi-shield-lock-fill text-success me-2"></i>Ganti Kata Sandi</h5>
                            <p class="text-muted small mb-0">Pastikan gunakan kata sandi yang aman untuk melindungi akun Anda</p>
                        </div>
                    </div>

                    <form action="akun.php" method="POST">
                        <input type="hidden" name="aksi" value="update_password">

                        <div class="mb-3">
                            <label for="password_lama" class="form-label small fw-semibold text-secondary">Kata Sandi Saat Ini <span class="text-danger">*</span></label>
                            <input type="password" class="form-control rounded-3" id="password_lama" name="password_lama" placeholder="Masukkan kata sandi lama Anda" required>
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12 col-md-6">
                                <label for="password_baru" class="form-label small fw-semibold text-secondary">Kata Sandi Baru <span class="text-danger">*</span></label>
                                <input type="password" class="form-control rounded-3" id="password_baru" name="password_baru" placeholder="Minimal 6 karakter" required minlength="6">
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="konfirmasi_password" class="form-label small fw-semibold text-secondary">Ulangi Kata Sandi Baru <span class="text-danger">*</span></label>
                                <input type="password" class="form-control rounded-3" id="konfirmasi_password" name="konfirmasi_password" placeholder="Ulangi kata sandi baru" required minlength="6">
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button type="submit" class="btn btn-outline-success rounded-pill px-4 py-2 fw-semibold d-flex align-items-center gap-2">
                                <i class="bi bi-key-fill"></i>
                                <span>Perbarui Kata Sandi</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include(__DIR__ . '/layouts/footer.php'); ?>
</body>
</html>

