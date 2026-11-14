<?php
    require_once __DIR__ . '/../../config/database.php';

    session_start();

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../../auth/login.php");
        exit;
    }

    $q_kategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Buku - PustakaKu</title>
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
                        <h4 class="fw-bold text-dark mb-1">Tambah Buku Baru</h4>
                        <p class="text-muted small mb-0">Lengkapi formulir di bawah untuk menambahkan buku ke dalam katalog</p>
                    </div>
                    <div>
                        <a href="index.php" class="btn btn-outline-secondary d-inline-flex align-items-center gap-2">
                            <i class="bi bi-arrow-left"></i>
                            <span>Kembali</span>
                        </a>
                    </div>
                </div>

                <div class="card p-4">
                    <form action="proses_tambah.php" method="POST" enctype="multipart/form-data">
                        <div class="row g-3">
                            <div class="col-12 col-md-8">
                                <label for="judul" class="form-label small fw-semibold text-secondary">Judul Buku <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="judul" name="judul" placeholder="Laskar Pelangi, Bumi Manusia" required autofocus>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="kategori_id" class="form-label small fw-semibold text-secondary">Kategori Buku <span class="text-danger">*</span></label>
                                <select class="form-select" id="kategori_id" name="kategori_id" required>
                                    <option value="" disabled selected>-- Pilih Kategori --</option>
                                    <?php while ($kat = mysqli_fetch_assoc($q_kategori)): ?>
                                        <option value="<?= $kat['id']; ?>"><?= htmlspecialchars($kat['nama_kategori']); ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="penulis" class="form-label small fw-semibold text-secondary">Penulis / Pengarang <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="penulis" name="penulis" placeholder="Andrea Hirata, Pramoedya Ananta Toer" required>
                            </div>

                            <div class="col-12 col-md-6">
                                <label for="penerbit" class="form-label small fw-semibold text-secondary">Penerbit <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="penerbit" name="penerbit" placeholder="Gramedia Pustaka Utama, Mizan" required>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="tahun_terbit" class="form-label small fw-semibold text-secondary">Tahun Terbit <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="tahun_terbit" name="tahun_terbit" placeholder="2024" min="1900" max="<?= date('Y') + 1; ?>">
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="isbn" class="form-label small fw-semibold text-secondary">Nomor ISBN <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="isbn" name="isbn" placeholder="978-602-03-8591-4" required>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="berat_gram" class="form-label small fw-semibold text-secondary">Berat Buku (gram) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="berat_gram" name="berat_gram" value="250" min="1" required>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="harga" class="form-label small fw-semibold text-secondary">Harga Normal (Rp) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="harga" name="harga" placeholder="85000" min="0" required>
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="harga_diskon" class="form-label small fw-semibold text-secondary">Harga Diskon (Rp) <span class="text-muted">(Opsional)</span></label>
                                <input type="number" class="form-control" id="harga_diskon" name="harga_diskon" placeholder="68000" min="0">
                            </div>

                            <div class="col-12 col-md-4">
                                <label for="stok" class="form-label small fw-semibold text-secondary">Jumlah Stok <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="stok" name="stok" placeholder="50" min="0" required>
                            </div>

                            <div class="col-12">
                                <label for="deskripsi" class="form-label small fw-semibold text-secondary">Sinopsis / Deskripsi Buku <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="deskripsi" name="deskripsi" rows="4" placeholder="Tuliskan ringkasan cerita atau sinopsis buku ini..." required></textarea>
                            </div>

                            <div class="col-12">
                                <label for="gambar" class="form-label small fw-semibold text-secondary">Foto Sampul Buku <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="gambar" name="gambar" accept="image/*" required>
                                <div class="form-text small">Format yang didukung: JPG, JPEG, PNG, WEBP. Maksimal 2MB.</div>
                            </div>
                        </div>

                        <div class="d-flex gap-2 mt-4 pt-3 border-top">
                            <button type="submit" class="btn btn-success d-inline-flex align-items-center gap-2">
                                <span>Simpan Buku</span>
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
