<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function cart_user_id() {
    return (int) ($_SESSION['user_id'] ?? 0);
}

function cart_get($conn) {
    $uid = cart_user_id();
    if ($uid <= 0) {
        return [];
    }
    $cart = [];
    $res = mysqli_query($conn, "SELECT buku_id, jumlah FROM keranjang WHERE user_id = '$uid'");
    if ($res) {
        while ($row = mysqli_fetch_assoc($res)) {
            $cart[(int) $row['buku_id']] = (int) $row['jumlah'];
        }
    }
    return $cart;
}

function cart_count($conn) {
    $cart = cart_get($conn);
    return (int) array_sum($cart);
}

function cart_store($conn, $id, $qty) {
    $uid = cart_user_id();
    if ($uid <= 0) {
        return;
    }
    $id = (int) $id;
    $qty = (int) $qty;
    if ($id <= 0) {
        return;
    }

    if ($qty <= 0) {
        mysqli_query($conn, "DELETE FROM keranjang WHERE user_id = '$uid' AND buku_id = '$id'");
        return;
    }

    $cek = mysqli_query($conn, "SELECT id FROM keranjang WHERE user_id = '$uid' AND buku_id = '$id'");
    if ($cek && mysqli_num_rows($cek) > 0) {
        mysqli_query($conn, "UPDATE keranjang SET jumlah = '$qty' WHERE user_id = '$uid' AND buku_id = '$id'");
    } else {
        mysqli_query($conn, "INSERT INTO keranjang (user_id, buku_id, jumlah) VALUES ('$uid', '$id', '$qty')");
    }
}

function cart_add($conn, $id, $qty = 1) {
    $uid = cart_user_id();
    if ($uid <= 0) {
        return ['ok' => false, 'login_required' => true, 'message' => 'Silakan masuk ke akun Anda terlebih dahulu untuk belanja.'];
    }

    if (($_SESSION['role'] ?? '') === 'admin') {
        return ['ok' => false, 'message' => 'Akun admin tidak dapat berbelanja atau menambahkan produk ke keranjang.'];
    }

    $id = (int) $id;
    $qty = max(1, (int) $qty);
    if ($id <= 0) {
        return ['ok' => false, 'message' => 'Produk tidak valid.'];
    }

    $res = mysqli_query($conn, "SELECT judul, stok FROM buku WHERE id = '$id'");
    $buku = $res ? mysqli_fetch_assoc($res) : null;
    if (!$buku) {
        return ['ok' => false, 'message' => 'Produk tidak ditemukan.'];
    }

    $stok = (int) $buku['stok'];
    if ($stok <= 0) {
        return ['ok' => false, 'message' => 'Maaf, stok produk ini habis.'];
    }

    $cart = cart_get($conn);
    $baru = ($cart[$id] ?? 0) + $qty;

    if ($baru > $stok) {
        cart_store($conn, $id, $stok);
        return ['ok' => false, 'message' => 'Jumlah di keranjang sudah mencapai stok tersedia (' . $stok . ').'];
    }

    cart_store($conn, $id, $baru);
    return ['ok' => true, 'message' => '"' . $buku['judul'] . '" ditambahkan ke keranjang.'];
}

function cart_set($conn, $id, $qty) {
    $cart = cart_get($conn);
    $id = (int) $id;
    $qty = (int) $qty;
    if ($qty <= 0 || isset($cart[$id])) {
        cart_store($conn, $id, $qty);
    }
}

function cart_remove($conn, $id) {
    cart_store($conn, (int) $id, 0);
}

function cart_clear($conn) {
    $uid = cart_user_id();
    if ($uid > 0) {
        mysqli_query($conn, "DELETE FROM keranjang WHERE user_id = '$uid'");
    }
}

function cart_items($conn) {
    $cart = cart_get($conn);
    if (empty($cart)) {
        return ['items' => [], 'total' => 0.0, 'jumlah' => 0];
    }

    $id_keys = array_values(array_map('intval', array_keys($cart)));
    $id_list = implode(',', $id_keys);

    $res = mysqli_query($conn, "SELECT id, judul, penulis, penerbit, berat_gram, harga, harga_diskon, stok, gambar FROM buku WHERE id IN ($id_list)");

    $items = [];
    $total = 0.0;
    $jumlah = 0;
    $ditemukan = [];

    if ($res) {
        while ($r = mysqli_fetch_assoc($res)) {
            $id = (int) $r['id'];
            $stok = (int) $r['stok'];
            if ($stok <= 0) {
                continue;
            }

            $ditemukan[$id] = true;
            $qty = min((int) $cart[$id], $stok);
            $harga = (float) $r['harga'];
            $diskon = (float) $r['harga_diskon'];
            $ada = $diskon > 0 && $diskon < $harga;
            $satuan = $ada ? $diskon : $harga;

            if ($qty !== (int) $cart[$id]) {
                cart_store($conn, $id, $qty);
            }

            $items[] = [
                'id' => $id,
                'judul' => $r['judul'],
                'penulis' => $r['penulis'],
                'penerbit' => $r['penerbit'],
                'berat' => (int) $r['berat_gram'],
                'gambar' => 'assets/uploads/buku/' . $r['gambar'],
                'harga' => $harga,
                'diskon' => $ada ? $diskon : null,
                'satuan' => $satuan,
                'qty' => $qty,
                'stok' => $stok,
                'subtotal' => $satuan * $qty
            ];
            $total += $satuan * $qty;
            $jumlah += $qty;
        }
    }

    foreach ($cart as $id => $qty) {
        if (!isset($ditemukan[$id])) {
            cart_store($conn, (int) $id, 0);
        }
    }

    return ['items' => $items, 'total' => $total, 'jumlah' => $jumlah];
}

function cart_merge_session($conn, $user_id) {
    return;
}
