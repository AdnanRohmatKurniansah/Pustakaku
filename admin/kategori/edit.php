<?php
    require_once __DIR__ . '/../../config/database.php';

    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $query = mysqli_query($conn, "SELECT * FROM kategori WHERE id = '$id' LIMIT 1");

    if (!$query || mysqli_num_rows($query) === 0) {
        $_SESSION['error'] = "Data kategori tidak ditemukan";
        header("Location: index.php");
        exit;
    }

    $kategori = mysqli_fetch_assoc($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Kategori - PustakaKu</title>
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
                        <h4 class="fw-bold text-dark mb-1">Ubah Data Kategori</h4>
                        <p class="text-muted small mb-0">Perbarui informasi kategori buku PustakaKu</p>
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
                        <input type="hidden" name="id" value="<?= $kategori['id']; ?>">

                        <div class="mb-3">
                            <label for="nama_kategori" class="form-label small fw-semibold text-secondary">Nama Kategori <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_kategori" name="nama_kategori" value="<?= htmlspecialchars($kategori['nama_kategori']); ?>" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi" class="form-label small fw-semibold text-secondary">Deskripsi <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" required autofocus><?= htmlspecialchars($kategori['deskripsi'] ?? ''); ?></textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label small fw-semibold text-secondary d-block">Gambar Saat Ini</label>
                            <img src="<?= (!empty($kategori['gambar']) && file_exists('../../assets/uploads/kategori/' . $kategori['gambar'])) ? ('../../assets/uploads/kategori/' . htmlspecialchars($kategori['gambar'])) : '../../assets/images/placeholder.png'; ?>" alt="Preview" class="rounded-3 border mb-2 object-fit-cover" style="width: 80px; height: 80px;" onerror="this.onerror=null;this.src='../../assets/images/placeholder.png';">

                            <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*">
                            <div class="form-text small">Biarkan kosong jika tidak ingin mengubah gambar kategori.</div>
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
