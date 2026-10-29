<?php
    include('../config/database.php');

    session_start();
    
    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../auth/login.php");
        exit;
    }

    $q_pesanan = mysqli_query($conn, "SELECT COUNT(*) AS total FROM pesanan");
    $row_pesanan = mysqli_fetch_assoc($q_pesanan);
    $total_pesanan = $row_pesanan['total'] ?? 0;

    $q_buku = mysqli_query($conn, "SELECT COUNT(*) AS total FROM buku");
    $row_buku = mysqli_fetch_assoc($q_buku);
    $total_buku = $row_buku['total'] ?? 0;

    $q_kategori = mysqli_query($conn, "SELECT COUNT(*) AS total FROM kategori");
    $row_kategori = mysqli_fetch_assoc($q_kategori);
    $total_kategori = $row_kategori['total'] ?? 0;

    $q_pengguna = mysqli_query($conn, "SELECT COUNT(*) AS total FROM users WHERE role = 'pelanggan'");
    $row_pengguna = mysqli_fetch_assoc($q_pengguna);
    $total_pengguna = $row_pengguna['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - PustakaKu</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="d-flex">
        <?php 
            include('../layouts/admin/sidebar.php');
        ?>

        <div class="main-wrapper">
            <?php 
                include('../layouts/admin/navbar.php');
            ?>

            <main class="p-3 p-md-4">
                <div class="admin-banner mb-4">
                    <div class="row align-items-center">
                        <div class="col-lg-8">
                            <span class="badge bg-white bg-opacity-25 text-white px-3 py-2 rounded-pill mb-2"><i class="bi bi-grid-1x2-fill me-1"></i> Dashboard Pengelolaan</span>
                            <h2 class="fw-bold mb-2">Selamat Datang, <?= htmlspecialchars($_SESSION['nama_lengkap']); ?>!</h2>
                            <p class="mb-0 opacity-75">Pantau ringkasan penjualan buku, katalog buku, dan status transaksi toko PustakaKu dalam satu tempat.</p>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card-stat">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <div class="text-muted small fw-semibold">Total Pesanan</div>
                                    <h4 class="fw-bold mb-0 mt-1"><?= number_format($total_pesanan, 0, ',', '.'); ?></h4>
                                </div>
                                <div class="stat-icon bg-success-subtle text-success">
                                    <i class="bi bi-bag-check-fill"></i>
                                </div>
                            </div>
                            <div class="small text-muted">
                                <span>Seluruh riwayat pesanan</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card-stat">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <div class="text-muted small fw-semibold">Total Kategori</div>
                                    <h4 class="fw-bold mb-0 mt-1"><?= number_format($total_kategori, 0, ',', '.'); ?></h4>
                                </div>
                                <div class="stat-icon bg-primary-subtle text-primary">
                                    <i class="bi bi-tags-fill"></i>
                                </div>
                            </div>
                            <div class="small text-muted">
                                <span>Seluruh kategori buku</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card-stat">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <div class="text-muted small fw-semibold">Total Buku</div>
                                    <h4 class="fw-bold mb-0 mt-1"><?= number_format($total_buku, 0, ',', '.'); ?></h4>
                                </div>
                                <div class="stat-icon bg-info-subtle text-info">
                                    <i class="bi bi-journal-album"></i>
                                </div>
                            </div>
                            <div class="small text-muted">
                                <span>Seluruh data buku yg ada</span>
                            </div>
                        </div>
                    </div>

                    <div class="col-12 col-sm-6 col-xl-3">
                        <div class="card-stat">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <div>
                                    <div class="text-muted small fw-semibold">Pengguna</div>
                                    <h4 class="fw-bold mb-0 mt-1 text-secondary"><?= $total_pengguna; ?></h4>
                                </div>
                                <div class="stat-icon bg-secondary-subtle text-secondary">
                                    <i class="bi bi-person-fill"></i>
                                </div>
                            </div>
                            <div class="small text-muted">
                                <span>Pengguna aktif</span>
                            </div>
                        </div>
                    </div>
                </div>
            </main>

            <?php
                include('../layouts/admin/footer.php');
            ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
