<?php
    require_once __DIR__ . '/../../config/database.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;

    $c_res = mysqli_query($conn, "SELECT COUNT(*) AS total FROM kategori");
    $total_data = $c_res ? (int) mysqli_fetch_assoc($c_res)['total'] : 0;
    $total_pages = max(1, (int) ceil($total_data / $limit));

    $query = mysqli_query($conn, "SELECT * FROM kategori ORDER BY id DESC LIMIT $limit OFFSET $offset");
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
                                    <?php $no = $offset + 1; while ($row = mysqli_fetch_assoc($query)): ?>
                                        <tr>
                                            <td class="fw-semibold text-muted"><?= $no++; ?></td>
                                            <td>
                                                <img src="<?= (!empty($row['gambar']) && file_exists('../../assets/uploads/kategori/' . $row['gambar'])) ? ('../../assets/uploads/kategori/' . htmlspecialchars($row['gambar'])) : '../../assets/images/placeholder.png'; ?>" alt="<?= htmlspecialchars($row['nama_kategori']); ?>" class="rounded-3 object-fit-cover border" style="width: 54px; height: 54px;" onerror="this.onerror=null;this.src='../../assets/images/placeholder.png';">
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

                    <?php if ($total_pages > 1): ?>
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-center gap-3 mt-4 pt-3 border-top">
                            <div class="small text-muted">
                                Menampilkan <?= min($offset + 1, $total_data); ?> - <?= min($offset + $limit, $total_data); ?> dari <?= number_format($total_data, 0, ',', '.'); ?> data
                            </div>
                            <nav aria-label="Navigasi Halaman Kategori">
                                <ul class="pagination pagination-sm mb-0 gap-1">
                                    <li class="page-item <?= ($page <= 1) ? 'disabled' : ''; ?>">
                                        <a class="page-link rounded-3 px-3 py-1 <?= ($page <= 1) ? 'text-muted' : 'text-dark'; ?>" href="index.php?page=<?= max(1, $page - 1); ?>">
                                            <i class="bi bi-chevron-left me-1"></i>Sebelumnya
                                        </a>
                                    </li>
                                    <?php for ($p = 1; $p <= $total_pages; $p++): ?>
                                        <li class="page-item <?= ($p === $page) ? 'active' : ''; ?>">
                                            <a class="page-link rounded-3 px-3 py-1 fw-semibold <?= ($p === $page) ? 'bg-success border-success text-white' : 'text-dark'; ?>" href="index.php?page=<?= $p; ?>">
                                                <?= $p; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    <li class="page-item <?= ($page >= $total_pages) ? 'disabled' : ''; ?>">
                                        <a class="page-link rounded-3 px-3 py-1 <?= ($page >= $total_pages) ? 'text-muted' : 'text-dark'; ?>" href="index.php?page=<?= min($total_pages, $page + 1); ?>">
                                            Berikutnya<i class="bi bi-chevron-right ms-1"></i>
                                        </a>
                                    </li>
                                </ul>
                            </nav>
                        </div>
                    <?php endif; ?>
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
