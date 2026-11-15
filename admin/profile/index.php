<?php
    require_once __DIR__ . '/../../config/database.php';

    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    $userId = $_SESSION['user_id'];
    $query = mysqli_query($conn, "SELECT * FROM users WHERE id = '$userId' LIMIT 1");
    $user = mysqli_fetch_assoc($query);
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
</head>
<body>
    <div class="d-flex">
        <?php 
            include('../../layouts/admin/sidebar.php');
        ?>

        <div class="main-wrapper">
            <?php 
                include('../../layouts/admin/navbar.php');
            ?>

            <main class="p-3 p-md-4">
                <div class="d-flex justify-content-between align-items-center mb-4 pb-4 border-bottom">
                    <div>
                        <h4 class="fw-bold text-dark mb-1">Ubah Informasi Akun</h4>
                        <p class="text-muted small mb-0">Perbarui data diri anda dengan benar</p>
                    </div>
                    <div>
                        <a href="index.php" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                            <i class="bi bi-arrow-left"></i>
                            <span>Kembali</span>
                        </a>
                    </div>
                </div>

                <div class="card p-4">
                    <form action="proses_edit.php" method="POST" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-12 col-md-6">
                                <label for="nama_lengkap" class="form-label small fw-semibold text-secondary">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" value="<?= htmlspecialchars($user['nama_lengkap']); ?>" required autofocus>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="email" class="form-label small fw-semibold text-secondary">Email</label>
                                <input type="email" class="form-control" disabled id="email" name="email" value="<?= htmlspecialchars($user['email']); ?>" required autofocus>
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="no_telepon" class="form-label small fw-semibold text-secondary">No Telepon </label>
                                <input type="text" class="form-control" id="no_telepon" name="no_telepon" value="<?= htmlspecialchars($user['no_telepon']); ?>" autofocus>
                            </div>
                             <div class="col-12 col-md-6">
                                <label for="alamat_lengkap" class="form-label small fw-semibold text-secondary">Alamat Lengkap</label>
                                <input type="text" class="form-control" id="alamat_lengkap" name="alamat_lengkap" value="<?= htmlspecialchars($user['alamat_lengkap']); ?>" autofocus>
                            </div>
                            <div class="col-md-12">
                                <div class="mb-4">
                                    <label class="form-label small fw-semibold text-secondary d-block">Profile Saat Ini</label>
                                    <img src="<?= (!empty($user['foto_profil']) && file_exists('../../assets/uploads/user/' . $user['foto_profil'])) ? ('../../assets/uploads/user/' . htmlspecialchars($user['foto_profil'])) : '../../assets/images/placeholder.png'; ?>" alt="Preview" class="rounded-3 border mb-2 object-fit-cover" style="width: 80px; height: 80px;" onerror="this.onerror=null;this.src='../../assets/images/placeholder.png';">

                                    <input type="file" class="form-control" id="foto_profil" name="foto_profil" accept="image/*">
                                    <div class="form-text small">Biarkan kosong jika tidak ingin mengubah profile anda.</div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-2">
                                <span>Simpan Perubahan</span>
                            </button>
                            <a href="index.php" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>
            </main>

            <?php
                include('../../layouts/admin/footer.php');
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
