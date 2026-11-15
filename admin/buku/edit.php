<?php
    require_once __DIR__ . '/../../config/database.php';

    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $query = mysqli_query($conn, "SELECT * FROM buku WHERE id = '$id' LIMIT 1");

    if (!$query || mysqli_num_rows($query) === 0) {
        $_SESSION['error'] = "Data buku tidak ditemukan";
        header("Location: index.php");
        exit;
    }

    $buku = mysqli_fetch_assoc($query);
    $q_kategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ubah Data Buku - PustakaKu</title>
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
                        <h4 class="fw-bold text-dark mb-1">Ubah Data Buku</h4>
                        <p class="text-muted small mb-0">Perbarui informasi buku dalam katalog PustakaKu</p>
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
                        <input type="hidden" name="id" value="<?= $buku['id']; ?>">

                        <div class="row g-3">
                            <div class="col-12 col-md-8">
                                <label for="judul" class="form-label small fw-semibold text-secondary">Judul Buku <span class="text-danger">*</span></label>
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
                                <label class="form-label small fw-semibold text-secondary d-block">Sampul Buku Saat Ini</label>
                                <img src="<?= (!empty($buku['gambar']) && file_exists('../../assets/uploads/buku/' . $buku['gambar'])) ? ('../../assets/uploads/buku/' . htmlspecialchars($buku['gambar'])) : '../../assets/images/placeholder.png'; ?>" alt="Sampul" class="rounded-2 border mb-2 object-fit-cover" style="width: 70px; height: 95px;" onerror="this.onerror=null;this.src='../../assets/images/placeholder.png';">

                                <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*">
                                <div class="form-text small">Biarkan kosong jika tidak ingin mengubah foto sampul buku.</div>
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
