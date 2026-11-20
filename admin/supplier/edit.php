<?php
    require_once __DIR__ . '/../../config/database.php';

    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $query = mysqli_query($conn, "SELECT * FROM supplier WHERE id = '$id' LIMIT 1");

    if (!$query || mysqli_num_rows($query) === 0) {
        $_SESSION['error'] = "Data supplier tidak ditemukan";
        header("Location: index.php");
        exit;
    }
    $supplier = mysqli_fetch_assoc($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Supplier - PustakaKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
    <link rel="icon" href="../../assets/images/favicon.png" type="image/png">
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
                        <h4 class="fw-bold text-dark mb-1">Tambah Supplier Baru</h4>
                        <p class="text-muted small mb-0">Lengkapi formulir di bawah untuk menambahkan supplier buku</p>
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
                        <input type="hidden" name="id" value="<?= $supplier['id']; ?>">
                        <div class="mb-3">
                            <label for="nama_supplier" class="form-label small fw-semibold text-secondary">Nama Supplier <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_supplier" name="nama_supplier" value="<?= htmlspecialchars($supplier['nama_supplier']); ?>" required autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="telepon" class="form-label small fw-semibold text-secondary">Nomor Telepon <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="telepon" name="telepon" value="<?= htmlspecialchars($supplier['telepon']); ?>" required >
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label small fw-semibold text-secondary">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" value="<?= htmlspecialchars($supplier['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label small fw-semibold text-secondary">Alamat <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3"required><?= htmlspecialchars($supplier['alamat'] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>
                        <div class="d-flex gap-2">
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
