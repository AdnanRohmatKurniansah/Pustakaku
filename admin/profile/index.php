<?php
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../services/cloudinary_service.php';

    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    $userId = $_SESSION['user_id'];
    $query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$userId' LIMIT 1");
    $user = mysqli_fetch_assoc($query);

    $foto_src = resolveImageUrl($user['foto_profil'] ?? '', 'user');
    if (strpos($foto_src, 'http://') !== 0 && strpos($foto_src, 'https://') !== 0) {
        $foto_src = '../../' . ltrim($foto_src, '/');
    }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Profile - PustakaKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="icon" href="../../assets/images/favicon.png" type="image/png">
</head>
<body>
    <div class="d-flex">
        <?php include('../../layouts/admin/sidebar.php'); ?>

        <div class="main-wrapper">
            <?php include('../../layouts/admin/navbar.php'); ?>

            <main class="p-3 p-md-4">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-4 border-bottom">
                    <div>
                        <h4 class="fw-bold text-dark mb-1">Pengaturan Profil & Keamanan</h4>
                        <p class="text-muted small mb-0">Kelola informasi identitas dan kata sandi akun administrator</p>
                    </div>
                    <div>
                        <a href="../index.php" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                            <i class="bi bi-arrow-left"></i>
                            <span>Kembali ke Dashboard</span>
                        </a>
                    </div>
                </div>

                <div class="row g-4">
                    <div class="col-12 col-lg-7">
                        <div class="card p-4 shadow-sm border-0 rounded-3">
                            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-person-badge me-2 text-success"></i>Informasi Akun</h5>
                            <form action="proses_edit.php" method="POST" enctype="multipart/form-data">
                                <input type="hidden" name="aksi" value="update_profile">
                                <div class="row g-3">
                                    <div class="col-12 col-md-6">
                                        <label for="nama_lengkap" class="form-label small fw-semibold text-secondary">Nama Lengkap <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?= htmlspecialchars($user['nama_lengkap']); ?>" required autofocus>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="email" class="form-label small fw-semibold text-secondary">Email</label>
                                        <input type="email" class="form-control bg-light" disabled id="email" value="<?= htmlspecialchars($user['email']); ?>">
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label for="no_telepon" class="form-label small fw-semibold text-secondary">No Telepon</label>
                                        <input type="text" class="form-control" id="no_telepon" name="no_telepon" value="<?= htmlspecialchars($user['no_telepon'] ?? ''); ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-semibold text-secondary d-block">Foto Profil</label>
                                        <div class="d-flex align-items-center gap-3">
                                            <img src="<?= htmlspecialchars($foto_src); ?>" alt="Preview" class="rounded-circle border object-fit-cover shadow-sm" style="width: 70px; height: 70px;" onerror="this.onerror=null;this.src='../../assets/images/placeholder.png';">
                                            <div class="flex-grow-1">
                                                <input type="file" class="form-control" id="foto_profil" name="foto_profil" accept="image/*">
                                                <div class="form-text small">Mendukung format JPG, PNG, WEBP. Otomatis terintegrasi Cloudinary atau lokal.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 mt-4 pt-3 border-top">
                                    <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-2">
                                        <i class="bi bi-save"></i>
                                        <span>Simpan Profil</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="col-12 col-lg-5">
                        <div class="card p-4 shadow-sm border-0 rounded-3">
                            <h5 class="fw-bold text-dark mb-3"><i class="bi bi-shield-lock me-2 text-warning"></i>Ubah Kata Sandi</h5>
                            <form action="proses_edit.php" method="POST">
                                <input type="hidden" name="aksi" value="update_password">
                                <div class="mb-3">
                                    <label for="password_lama" class="form-label small fw-semibold text-secondary">Kata Sandi Saat Ini <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password_lama" name="password_lama" placeholder="Masukkan kata sandi saat ini" required>
                                </div>
                                <div class="mb-3">
                                    <label for="password_baru" class="form-label small fw-semibold text-secondary">Kata Sandi Baru <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="password_baru" name="password_baru" placeholder="Minimal 6 karakter" required minlength="6">
                                </div>
                                <div class="mb-4">
                                    <label for="konfirmasi_password" class="form-label small fw-semibold text-secondary">Konfirmasi Kata Sandi Baru <span class="text-danger">*</span></label>
                                    <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" placeholder="Ulangi kata sandi baru" required minlength="6">
                                </div>
                                <div class="d-flex gap-2 pt-2 border-top">
                                    <button type="submit" class="btn btn-warning text-dark fw-semibold d-inline-flex align-items-center gap-2">
                                        <i class="bi bi-key-fill"></i>
                                        <span>Perbarui Kata Sandi</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </main>

            <?php include('../../layouts/admin/footer.php'); ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../assets/js/toast.js"></script>
    <?= tampilkan_alert_toast(); ?>
</body>
</html>
