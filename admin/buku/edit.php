<?php
    require_once __DIR__ . '/../../config/database.php';
    require_once __DIR__ . '/../../services/cloudinary_service.php';

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

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
                    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-pencil-square me-2 text-success"></i>Form 1: Informasi Buku & Foto Utama</h5>
                    <form action="proses_edit.php?aksi=update_buku" method="POST" enctype="multipart/form-data">
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

                            <div class="col-12 col-md-12">
                                <?php 
                                    $prim_img = resolveImageUrl($buku['gambar'] ?? '', 'buku');
                                    if (strpos($prim_img, 'assets/') === 0) {
                                        $prim_img = '../../' . $prim_img;
                                    }
                                ?>
                                <img src="<?= htmlspecialchars($prim_img); ?>" alt="Sampul Utama" class="rounded-2 mb-2 border object-fit-cover shadow-sm" style="width: 100px; height: 130px;" onerror="this.onerror=null;this.src='../../assets/images/placeholder.png';">
                                <input type="file" class="form-control" id="gambar_utama" name="gambar_utama" accept="image/*">
                                <div class="form-text small">Pilih file baru jika ingin mengganti foto utama buku ini. Biarkan kosong jika tidak diubah.</div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-2">
                                <i class="bi bi-save"></i>
                                <span>Simpan Perubahan Buku & Foto Utama</span>
                            </button>
                            <a href="index.php" class="btn btn-secondary">Kembali ke Daftar</a>
                        </div>
                    </form>
                </div>

                <div class="card p-4 shadow-sm border-0 rounded-3">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3 pb-3 border-bottom">
                        <div>
                            <h5 class="fw-bold text-dark mb-1"><i class="bi bi-images me-2 text-primary"></i>Form 2: Kelola Galeri Foto Tambahan (Buku Gambar)</h5>
                            <p class="text-muted small mb-0">Daftar foto tambahan untuk galeri buku ini yang tersimpan pada tabel <code>buku_gambar</code>.</p>
                        </div>
                    </div>

                    <div class="table-responsive rounded-2 mb-4 border">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr class="small text-muted">
                                    <th style="width: 50px;">No</th>
                                    <th style="width: 90px;">Foto</th>
                                    <th>Status Gambar</th>
                                    <th>Tanggal Dibuat</th>
                                    <th style="width: 220px;" class="text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $additional_items = array_filter($galeri, fn($g) => (int)$g['is_primary'] === 0);
                                ?>
                                <?php if (empty($additional_items)): ?>
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-muted small">
                                            <i class="bi bi-images fs-2 d-block mb-1 text-secondary"></i>
                                            Belum ada foto tambahan untuk buku ini. Silakan unggah foto tambahan melalui formulir di bawah.
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php $no = 1; foreach ($additional_items as $item): 
                                        $img_url = resolveImageUrl($item['url_gambar'], 'buku');
                                        if (strpos($img_url, 'assets/') === 0) {
                                            $img_url = '../../' . ltrim($img_url, '/');
                                        }
                                    ?>
                                        <tr>
                                            <td class="fw-semibold text-muted"><?= $no++; ?></td>
                                            <td>
                                                <img src="<?= htmlspecialchars($img_url); ?>" alt="Foto Tambahan" class="rounded-2 border object-fit-cover shadow-sm" style="width: 54px; height: 72px;" onerror="this.onerror=null;this.src='../../assets/images/placeholder.png';">
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle">Foto Tambahan (Galeri)</span>
                                            </td>
                                            <td class="small text-muted">
                                                <?= date('d M Y, H:i', strtotime($item['created_at'])); ?> WIB
                                            </td>
                                            <td class="text-center">
                                                <div class="d-inline-flex gap-2">
                                                    <a href="proses_edit.php?action=set_primary&buku_id=<?= $buku['id']; ?>&gambar_id=<?= $item['id']; ?>" class="btn btn-sm btn-outline-success d-inline-flex align-items-center gap-1" title="Jadikan Sampul Utama">
                                                        <span>Jadikan Utama</span>
                                                    </a>
                                                    <a href="proses_edit.php?action=delete_gambar&buku_id=<?= $buku['id']; ?>&gambar_id=<?= $item['id']; ?>" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-1" onclick="return confirm('Apakah Anda yakin ingin menghapus foto tambahan ini?');" title="Hapus Foto">
                                                        <i class="bi bi-trash"></i>
                                                        <span>Hapus</span>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <div class="p-3 bg-light border rounded-3">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-cloud-arrow-up text-success me-2"></i>Tambah Foto Tambahan Baru</h6>
                        <form action="proses_edit.php?aksi=tambah_gambar_tambahan" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="buku_id" value="<?= $buku['id']; ?>">
                            <div class="row g-2 align-items-start">
                                <div class="col-12 col-md-9">
                                    <input type="file" class="form-control" name="gambar_tambahan[]" multiple accept="image/*" required>
                                    <div class="form-text small">Pilih satu atau beberapa foto tambahan baru untuk ditambahkan ke galeri produk ini.</div>
                                </div>
                                <div class="col-12 col-md-3">
                                    <button type="submit" class="btn btn-success w-100 d-inline-flex align-items-center justify-content-center gap-2">
                                        <i class="bi bi-plus-circle"></i>
                                        <span>Unggah Tambahan</span>
                                    </button>
                                </div>
                            </div>
                        </form>
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