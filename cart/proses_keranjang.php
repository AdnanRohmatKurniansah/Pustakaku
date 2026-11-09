<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function cart_user_id(): int {
    return (int) ($_SESSION['user_id'] ?? 0);
}

/** Isi keranjang: [id_buku => jumlah] */
function cart_get($conn): array {
    $uid = cart_user_id();
    if ($uid <= 0) {
        return $_SESSION['cart'] ?? [];
    }
    $cart = [];
    $stmt = mysqli_prepare($conn, "SELECT buku_id, jumlah FROM keranjang WHERE user_id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $uid);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        while ($res && ($r = mysqli_fetch_assoc($res))) {
            $cart[(int) $r['buku_id']] = (int) $r['jumlah'];
        }
    }
    return $cart;
}

function cart_count($conn): int {
    return (int) array_sum(cart_get($conn));
}

/** Simpan jumlah satu buku. Jumlah <= 0 berarti hapus. */
function cart_store($conn, int $id, int $qty): void {
    $uid = cart_user_id();

    if ($uid <= 0) {
        $cart = $_SESSION['cart'] ?? [];
        if ($qty <= 0) unset($cart[$id]); else $cart[$id] = $qty;
        $_SESSION['cart'] = $cart;
        return;
    }

    if ($qty <= 0) {
        $stmt = mysqli_prepare($conn, "DELETE FROM keranjang WHERE user_id = ? AND buku_id = ?");
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, 'ii', $uid, $id);
            mysqli_stmt_execute($stmt);
        }
        return;
    }

    $stmt = mysqli_prepare($conn,
        "INSERT INTO keranjang (user_id, buku_id, jumlah) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE jumlah = VALUES(jumlah)");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'iii', $uid, $id, $qty);
        mysqli_stmt_execute($stmt);
    }
}

/** Tambah buku ke keranjang. Mengembalikan ['ok' => bool, 'message' => string]. */
function cart_add($conn, int $id, int $qty = 1): array {
    $qty = max(1, $qty);
    if ($id <= 0) {
        return ['ok' => false, 'message' => 'Produk tidak valid.'];
    }

    $stmt = mysqli_prepare($conn, "SELECT judul, stok FROM buku WHERE id = ?");
    if (!$stmt) {
        return ['ok' => false, 'message' => 'Terjadi kesalahan pada server.'];
    }
    mysqli_stmt_bind_param($stmt, 'i', $id);
    mysqli_stmt_execute($stmt);
    $res = mysqli_stmt_get_result($stmt);
    $buku = $res ? mysqli_fetch_assoc($res) : null;

    if (!$buku) {
        return ['ok' => false, 'message' => 'Produk tidak ditemukan.'];
    }

    $stok = (int) $buku['stok'];
    if ($stok <= 0) {
        return ['ok' => false, 'message' => 'Maaf, stok produk ini habis.'];
    }

    $cart  = cart_get($conn);
    $baru  = ($cart[$id] ?? 0) + $qty;

    if ($baru > $stok) {
        cart_store($conn, $id, $stok);
        return ['ok' => false, 'message' => 'Jumlah di keranjang sudah mencapai stok tersedia (' . $stok . ').'];
    }

    cart_store($conn, $id, $baru);
    return ['ok' => true, 'message' => '"' . $buku['judul'] . '" ditambahkan ke keranjang.'];
}

/** Ubah jumlah (hanya untuk buku yang sudah ada di keranjang). */
function cart_set($conn, int $id, int $qty): void {
    $cart = cart_get($conn);
    if ($qty <= 0 || isset($cart[$id])) {
        cart_store($conn, $id, $qty);
    }
}

function cart_remove($conn, int $id): void {
    cart_store($conn, $id, 0);
}

function cart_clear($conn): void {
    $uid = cart_user_id();
    if ($uid <= 0) {
        $_SESSION['cart'] = [];
        return;
    }
    $stmt = mysqli_prepare($conn, "DELETE FROM keranjang WHERE user_id = ?");
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, 'i', $uid);
        mysqli_stmt_execute($stmt);
    }
}

/**
 * Detail isi keranjang. Harga dihitung dari database dan jumlah
 * disesuaikan dengan stok terkini (item yang stoknya habis dihapus).
 */
function cart_items($conn): array {
    $cart = cart_get($conn);
    if (!$cart) {
        return ['items' => [], 'total' => 0.0, 'jumlah' => 0];
    }

    $ids = implode(',', array_map('intval', array_keys($cart)));
    $res = mysqli_query($conn, "SELECT id, judul, penulis, penerbit, berat_gram, harga, harga_diskon, stok, gambar FROM buku WHERE id IN ($ids)");

    $items = [];
    $total = 0.0;
    $jumlah = 0;
    $ditemukan = [];

    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) {
            $id   = (int) $r['id'];
            $stok = (int) $r['stok'];
            if ($stok <= 0) continue;

            $ditemukan[$id] = true;
            $qty    = min((int) $cart[$id], $stok);
            $harga  = (float) $r['harga'];
            $diskon = (float) $r['harga_diskon'];
            $ada    = $diskon > 0 && $diskon < $harga;
            $satuan = $ada ? $diskon : $harga;

            if ($qty !== (int) $cart[$id]) {
                cart_store($conn, $id, $qty);
            }

            $items[] = [
                'id'       => $id,
                'judul'    => $r['judul'],
                'penulis'  => $r['penulis'],
                'penerbit' => $r['penerbit'],
                'berat'    => (int) $r['berat_gram'],
                'gambar'   => 'assets/uploads/buku/' . $r['gambar'],
                'harga'    => $harga,
                'diskon'   => $ada ? $diskon : null,
                'satuan'   => $satuan,
                'qty'      => $qty,
                'stok'     => $stok,
                'subtotal' => $satuan * $qty,
            ];
            $total  += $satuan * $qty;
            $jumlah += $qty;
        }
    }

    // Buang item yang bukunya sudah dihapus atau stoknya habis
    foreach ($cart as $id => $qty) {
        if (!isset($ditemukan[$id])) cart_store($conn, (int) $id, 0);
    }

    return ['items' => $items, 'total' => $total, 'jumlah' => $jumlah];
}

/**
 * Panggil SEKALI setelah login berhasil: memindahkan keranjang tamu
 * (session) ke database dan menjumlahkannya dengan isi yang sudah ada.
 */
function cart_merge_session($conn, int $user_id): void {
    $tamu = $_SESSION['cart'] ?? [];
    if ($user_id <= 0 || !$tamu) return;

    $stmt = mysqli_prepare($conn,
        "INSERT INTO keranjang (user_id, buku_id, jumlah) VALUES (?, ?, ?)
         ON DUPLICATE KEY UPDATE jumlah = jumlah + VALUES(jumlah)");
    if ($stmt) {
        foreach ($tamu as $buku_id => $qty) {
            $b = (int) $buku_id;
            $q = (int) $qty;
            if ($b <= 0 || $q <= 0) continue;
            mysqli_stmt_bind_param($stmt, 'iii', $user_id, $b, $q);
            mysqli_stmt_execute($stmt);
        }
    }
    $_SESSION['cart'] = [];
}