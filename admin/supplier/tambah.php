<?php
    include('../../config/database.php');

    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    $error = $_SESSION['error'] ?? '';
    unset($_SESSION['error']);
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

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2 flex-shrink-0"></i>
                        <div><?= htmlspecialchars($error); ?></div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card p-4">
                    <form action="proses_tambah.php" method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="nama_supplier" class="form-label small fw-semibold text-secondary">Nama Supplier <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_supplier" name="nama_supplier" placeholder="Contoh: SiDu, Gramed, dll." required autofocus>
                        </div>
                        <div class="mb-3">
                            <label for="telepon" class="form-label small fw-semibold text-secondary">Nomor Telepon <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="telepon" name="telepon" placeholder="Contoh: 081234567890" pattern="[0-9]{10,13}" required >
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label small fw-semibold text-secondary">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="Contoh: sidu@hmail.com" required>
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label small fw-semibold text-secondary">Alamat <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3" placeholder="Tuliskan Alamat dari supplier..." required></textarea>
                        </div>
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-2">
                                <span>Simpan Supplier</span>
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
