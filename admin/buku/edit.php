<?php
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../services/cloudinary_service.php';

    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id <= 0) {
        $_SESSION['error'] = "ID buku tidak valid!";
        header("Location: index.php");
        exit;
    }

    $query = mysqli_query($conn, "SELECT * FROM buku WHERE id = '$id' LIMIT 1");
    if (!$query || mysqli_num_rows($query) === 0) {
        $_SESSION['error'] = "Data buku tidak ditemukan!";
        header("Location: index.php");
        exit;
    }

    $buku = mysqli_fetch_assoc($query);
    $q_kategori = mysqli_query($conn, "SELECT id, nama_kategori FROM kategori ORDER BY nama_kategori ASC");

    $q_galeri = mysqli_query($conn, "SELECT * FROM buku_gambar WHERE buku_id = '$id' ORDER BY is_primary DESC, urutan ASC, id ASC");
    $galeri = [];
    while ($g = mysqli_fetch_assoc($q_galeri)) {
        $galeri[] = $g;
    }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Buku & Galeri - PustakaKu</title>
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
                        <h4 class="fw-bold text-dark mb-1">Edit Produk & Galeri Foto</h4>
                        <p class="text-muted small mb-0">Perbarui spesifikasi produk dan kelola koleksi foto (Primary / Tambahan)</p>
                    </div>
                    <div>
                        <a href="index.php" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                            <i class="bi bi-arrow-left"></i>
                            <span>Kembali</span>
                        </a>
                    </div>
                </div>

                <div class="card p-4 shadow-sm border-0 rounded-3 mb-4">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-pencil-square me-2 text-success"></i>Informasi Buku</h5>
                    <form action="proses_edit.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="id" value="<?= $buku['id']; ?>">

                        <div class="row g-3">
                            <div class="col-12 col-md-8">
                                <label for="judul" class="form-label small fw-semibold text-secondary">Judul Buku / Nama Produk <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="judul" name="judul" value="<?= htmlspecialchars($buku['judul']); ?>" required autofocus>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="kategori_id" class="form-label small fw-semibold text-secondary">Kategori Buku <span class="text-danger">*</span></label>
                                <select class="form-select" id="kategori_id" name="kategori_id" required>
                                    <option value="" disabled>-- Pilih Kategori --</option>
                                    <?php while ($kat = mysqli_fetch_assoc($q_kategori)): ?>
                                        <option value="<?= $kat['id']; ?>" <?= $buku['kategori_id'] == $kat['id'] ? 'selected' : ''; ?>><?= htmlspecialchars($kat['nama_kategori']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="penulis" class="form-label small fw-semibold text-secondary">Penulis / Pengarang <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="penulis" name="penulis" value="<?= htmlspecialchars($buku['penulis']); ?>" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="penerbit" class="form-label small fw-semibold text-secondary">Penerbit <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="penerbit" name="penerbit" value="<?= htmlspecialchars($buku['penerbit']); ?>" required>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="tahun_terbit" class="form-label small fw-semibold text-secondary">Tahun Terbit <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="tahun_terbit" name="tahun_terbit" value="<?= htmlspecialchars($buku['tahun_terbit'] ?? ''); ?>" min="1900" max="<?= date('Y') + 1; ?>">
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="isbn" class="form-label small fw-semibold text-secondary">Nomor ISBN <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="isbn" name="isbn" required value="<?= htmlspecialchars($buku['isbn'] ?? ''); ?>">
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="berat_gram" class="form-label small fw-semibold text-secondary">Berat Buku (gram) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="berat_gram" name="berat_gram" value="<?= $buku['berat_gram']; ?>" min="1" required>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="harga" class="form-label small fw-semibold text-secondary">Harga Normal (Rp) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="harga" name="harga" value="<?= intval($buku['harga']); ?>" min="0" required>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="harga_diskon" class="form-label small fw-semibold text-secondary">Harga Diskon (Rp) <span class="text-muted">(Opsional)</span></label>
                                <input type="number" class="form-control" id="harga_diskon" name="harga_diskon" value="<?= !empty($buku['harga_diskon']) ? intval($buku['harga_diskon']) : ''; ?>" min="0">
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="stok" class="form-label small fw-semibold text-secondary">Jumlah Stok <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="stok" name="stok" value="<?= $buku['stok']; ?>" min="0" required>
                            </div>

                            <div class="col-12">
                                <label for="deskripsi" class="form-label small fw-semibold text-secondary">Sinopsis / Deskripsi Buku <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" required><?= htmlspecialchars($buku['deskripsi'] ?? ''); ?></textarea>
                            </div>

                            <div class="col-12">
                                <label for="gambar_baru" class="form-label small fw-semibold text-secondary">Tambah Foto Galeri Baru</label>
                                <input type="file" class="form-control" id="gambar_baru" name="gambar_baru[]" multiple accept="image/*">
                                <div class="form-text small">Pilih satu atau lebih file baru untuk ditambahkan ke galeri produk ini.</div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-2">
                                <i class="bi bi-save"></i>
                                <span>Simpan Perubahan</span>
                            </button>
                            <a href="index.php" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                </div>

                <div class="card p-4 shadow-sm border-0 rounded-3">
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-images me-2 text-primary"></i>Galeri Foto Produk Saat Ini</h5>
                    <?php if (empty($galeri)): ?>
                        <p class="text-muted small">Belum ada foto galeri terdaftar untuk buku ini.</p>
                    <?php else: ?>
                        <div class="row g-3">
                            <?php foreach ($galeri as $item): 
                                $img_url = resolveImageUrl($item['url_gambar'], 'buku');
                                if (strpos($img_url, 'http://') !== 0 && strpos($img_url, 'https://') !== 0) {
                                    $img_url = '../../' . ltrim($img_url, '/');
                                }
                            ?>
                                <div class="col-6 col-sm-4 col-md-3 col-lg-2">
                                    <div class="card h-100 border text-center p-2 rounded-3 position-relative">
                                        <?php if ($item['is_primary'] == 1): ?>
                                            <span class="badge bg-success position-absolute top-0 start-50 translate-middle mt-2 shadow-sm">Utama (Primary)</span>
                                        <?php endif; ?>
                                        <div class="ratio ratio-1x1 mb-2 mt-2">
                                            <img src="<?= htmlspecialchars($img_url); ?>" alt="Galeri" class="img-fluid rounded-2 object-fit-cover border" onerror="this.onerror=null;this.src='../../assets/images/placeholder.png';">
                                        </div>
                                        <div class="d-flex flex-column gap-1">
                                            <?php if ($item['is_primary'] == 0): ?>
                                                <a href="proses_edit.php?action=set_primary&buku_id=<?= $buku['id']; ?>&gambar_id=<?= $item['id']; ?>" class="btn btn-xs btn-outline-success py-1" style="font-size:0.75rem;">Jadikan Utama</a>
                                                <a href="proses_edit.php?action=delete_gambar&buku_id=<?= $buku['id']; ?>&gambar_id=<?= $item['id']; ?>" class="btn btn-xs btn-outline-danger py-1" style="font-size:0.75rem;" onclick="return confirm('Hapus foto ini dari galeri?');">Hapus</a>
                                            <?php else: ?>
                                                <span class="small text-muted py-1" style="font-size:0.75rem;">Foto Sampul Utama</span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
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
