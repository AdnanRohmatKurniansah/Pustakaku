<?php
    include('../../config/database.php');

    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    $success = $_SESSION['success'] ?? '';
    $error = $_SESSION['error'] ?? '';
    unset($_SESSION['success'], $_SESSION['error']);

    $query = mysqli_query($conn, "SELECT * FROM kategori ORDER BY id DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori Buku - PustakaKu</title>
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
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4 pb-4 border-bottom">
                    <div>
                        <h4 class="fw-bold text-dark mb-1">Manajemen Kategori Buku</h4>
                        <p class="text-muted small mb-0">Kelola kelompok kategori berbagai buku</p>
                    </div>
                    <div>
                        <a href="tambah.php" class="btn btn-success d-inline-flex align-items-center gap-2">
                            <i class="bi bi-plus-circle"></i>
                            <span>Tambah Kategori</span>
                        </a>
                    </div>
                </div>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center" role="alert">
                        <i class="bi bi-check-circle-fill me-2 flex-shrink-0"></i>
                        <div><?= htmlspecialchars($success); ?></div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2 flex-shrink-0"></i>
                        <div><?= htmlspecialchars($error); ?></div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>

                <div class="card p-4">
                    <div class="table-responsive rounded-2">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr class="small text-muted">
                                    <th style="width: 60px;">No</th>
                                    <th style="width: 100px;">Gambar</th>
                                    <th>Nama Kategori</th>
                                    <th>Deskripsi</th>
                                    <th>Dibuat</th>
                                    <th>Diupdate</th>
                                    <th style="width: 100px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($query && mysqli_num_rows($query) > 0): ?>
                                    <?php $no = 1; while ($row = mysqli_fetch_assoc($query)): ?>
                                        <tr>
                                            <td class="fw-semibold text-muted"><?= $no++; ?></td>
                                            <td>
                                                <?php if (!empty($row['gambar']) && file_exists('../../assets/uploads/kategori/' . $row['gambar'])): ?>
                                                    <img src="../../assets/uploads/kategori/<?= htmlspecialchars($row['gambar']); ?>" alt="<?= htmlspecialchars($row['nama_kategori']); ?>" class="rounded-3 object-fit-cover border" style="width: 54px; height: 54px;">
                                                <?php else: ?>
                                                    <div class="bg-light rounded-3 d-flex align-items-center justify-content-center text-muted border" style="width: 54px; height: 54px;">
                                                        <i class="bi bi-image fs-5"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark"><?= htmlspecialchars($row['nama_kategori']); ?></div>
                                            </td>
                                            <td>
                                                <span class="text-secondary small"><?= htmlspecialchars($row['deskripsi'] ?: '-'); ?></span>
                                            </td>
                                            <td>
                                                <span class="text-secondary small"><?= date('d M Y H:i', strtotime($row['created_at'])); ?></span>
                                            </td>
                                            <td>
                                                <span class="text-secondary small"><?= date('d M Y H:i', strtotime($row['updated_at'])); ?></span>
                                            </td>
                                            <td class="text-center">
                                                <div class="d-inline-flex gap-2">
                                                    <a href="edit.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-primary" title="Ubah">
                                                        <i class="bi bi-pencil"></i>
                                                    </a>
                                                    <a href="hapus.php?id=<?= $row['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus kategori ini?')" title="Hapus">
                                                        <i class="bi bi-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-5 text-muted">
                                            <span style="font-size: 15px;">Belum ada data kategori buku yang tersedia.</span>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
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
