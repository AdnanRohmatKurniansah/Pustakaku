<?php
    require_once dirname(__DIR__) . '/config/database.php';

    function midtrans_create_snap_token($order_id, $gross_amount, $customer_details = [], $item_details = []) {
        $server_key = $_ENV['MIDTRANS_SERVER_KEY'] ?? '';
        $is_production = filter_var($_ENV['MIDTRANS_IS_PRODUCTION'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (empty($server_key) || !class_exists('Midtrans\Snap')) {
            return 'MOCK-SNAP-' . strtoupper(md5($order_id . time()));
        }

        \Midtrans\Config::$serverKey = $server_key;
        \Midtrans\Config::$isProduction = $is_production;
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        $params = [
            'transaction_details' => [
                'order_id' => $order_id,
                'gross_amount' => (int) $gross_amount
            ],
            'customer_details' => [
                'first_name' => $customer_details['nama'] ?? 'Pelanggan',
                'email' => $customer_details['email'] ?? 'pelanggan@pustakaku.com',
                'phone' => $customer_details['telepon'] ?? '08123456789'
            ]
        ];

        if (!empty($item_details)) {
            $params['item_details'] = $item_details;
        }

        $app_url = rtrim($_ENV['APP_URL'] ?? 'http://localhost/projek_web', '/');
        $params['callbacks'] = [
            'finish' => $app_url . '/pesanan_sukses.php'
        ];

        try {
            return \Midtrans\Snap::getSnapToken($params);
        } catch (\Exception $e) {
            return 'MOCK-SNAP-' . strtoupper(md5($order_id . time()));
        }
    }

    function format_nama_metode_pembayaran($payment_type, $bank = '', $va_numbers = []) {
        $pt = strtolower(trim((string)$payment_type));
        $b = strtolower(trim((string)$bank));
        if (empty($b) && !empty($va_numbers) && is_array($va_numbers) && !empty($va_numbers[0]['bank'])) {
            $b = strtolower(trim((string)$va_numbers[0]['bank']));
        }
        if ($pt === 'qris') return 'QRIS';
        if ($pt === 'gopay') return 'GoPay';
        if ($pt === 'shopeepay') return 'ShopeePay';
        if ($pt === 'bank_transfer') {
            if ($b === 'bca') return 'Transfer Bank BCA';
            if ($b === 'bni') return 'Transfer Bank BNI';
            if ($b === 'bri') return 'Transfer Bank BRI';
            if ($b === 'permata') return 'Transfer Bank Permata';
            if (!empty($b)) return 'Transfer Bank ' . strtoupper($b);
            return 'Transfer Bank';
        }
        if ($pt === 'echannel') return 'Transfer Bank Mandiri';
        if ($pt === 'cstore') return 'Minimarket (Indomaret / Alfamart)';
        if ($pt === 'credit_card') return 'Kartu Kredit / Debit';
        if (!empty($pt)) {
            return ucwords(str_replace('_', ' ', $pt));
        }
        return 'Transfer Bank / QRIS';
    }

    function midtrans_get_transaction_data($order_id) {
        $server_key = $_ENV['MIDTRANS_SERVER_KEY'] ?? '';
        $is_production = filter_var($_ENV['MIDTRANS_IS_PRODUCTION'] ?? false, FILTER_VALIDATE_BOOLEAN);

        if (empty($server_key)) {
            return null;
        }

        $base_url = $is_production ? 'https://api.midtrans.com/v2/' : 'https://api.sandbox.midtrans.com/v2/';
        $url = $base_url . urlencode($order_id) . '/status';

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 3,
            CURLOPT_HTTPHEADER => [
                'Accept: application/json',
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode($server_key . ':')
            ]
        ]);

        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if (($http_code === 200 || $http_code === 404) && $response) {
            return json_decode($response, true);
        }

        return null;
    }

    function midtrans_check_transaction_status($order_id) {
        $data = midtrans_get_transaction_data($order_id);
        return $data['transaction_status'] ?? null;
    }

    function midtrans_sinkronisasi_pesanan($conn, $pesanan_input) {
        if (is_array($pesanan_input)) {
            $pesanan = $pesanan_input;
            $pesanan_id = (int)($pesanan['id'] ?? 0);
        } else {
            $pesanan_id = (int)$pesanan_input;
            $q = mysqli_query($conn, "SELECT * FROM pesanan WHERE id = '$pesanan_id' LIMIT 1");
            $pesanan = $q ? mysqli_fetch_assoc($q) : null;
        }

        if (!$pesanan) {
            return null;
        }

        if ($pesanan['status_pesanan'] !== 'menunggu_pembayaran') {
            return $pesanan;
        }

        $nomor_pesanan = $pesanan['nomor_pesanan'];
        $nomor_pesanan_esc = mysqli_real_escape_string($conn, $nomor_pesanan);
        $api_data = midtrans_get_transaction_data($nomor_pesanan);

        if ($api_data && is_array($api_data)) {
            $api_status = strtolower($api_data['transaction_status'] ?? '');
            $api_code = (string)($api_data['status_code'] ?? '');
            $api_metode = format_nama_metode_pembayaran($api_data['payment_type'] ?? '', $api_data['bank'] ?? '', $api_data['va_numbers'] ?? []);
            $api_metode_esc = mysqli_real_escape_string($conn, $api_metode);

            if (in_array($api_status, ['settlement', 'capture', 'success'], true)) {
                mysqli_query($conn, "UPDATE pesanan SET status_pesanan = 'diproses', metode_pembayaran = '$api_metode_esc', updated_at = NOW() WHERE id = '$pesanan_id'");
                $pesanan['status_pesanan'] = 'diproses';
                $pesanan['metode_pembayaran'] = $api_metode;
                return $pesanan;
            }

            if (in_array($api_status, ['expire', 'cancel', 'deny'], true)) {
                mysqli_query($conn, "UPDATE pesanan SET status_pesanan = 'dibatalkan', updated_at = NOW() WHERE id = '$pesanan_id'");
                $pesanan['status_pesanan'] = 'dibatalkan';
                $q_rest = mysqli_query($conn, "SELECT buku_id, jumlah FROM detail_pesanan WHERE pesanan_id = '$pesanan_id'");
                if ($q_rest) {
                    while ($r_rest = mysqli_fetch_assoc($q_rest)) {
                        $b_id = (int) $r_rest['buku_id'];
                        $b_qty = (int) $r_rest['jumlah'];
                        mysqli_query($conn, "UPDATE buku SET stok = stok + $b_qty, updated_at = NOW() WHERE id = '$b_id'");
                    }
                }
                return $pesanan;
            }

            if (!empty($api_data['payment_type'])) {
                mysqli_query($conn, "UPDATE pesanan SET metode_pembayaran = '$api_metode_esc', updated_at = NOW() WHERE id = '$pesanan_id'");
                $pesanan['metode_pembayaran'] = $api_metode;
            }

            if ($api_code === '404') {
                $created_time = strtotime($pesanan['created_at']);
                if ((time() - $created_time) > 1800) {
                    mysqli_query($conn, "UPDATE pesanan SET status_pesanan = 'dibatalkan', updated_at = NOW() WHERE id = '$pesanan_id'");
                    $pesanan['status_pesanan'] = 'dibatalkan';
                    $q_rest = mysqli_query($conn, "SELECT buku_id, jumlah FROM detail_pesanan WHERE pesanan_id = '$pesanan_id'");
                    if ($q_rest) {
                        while ($r_rest = mysqli_fetch_assoc($q_rest)) {
                            $b_id = (int) $r_rest['buku_id'];
                            $b_qty = (int) $r_rest['jumlah'];
                            mysqli_query($conn, "UPDATE buku SET stok = stok + $b_qty, updated_at = NOW() WHERE id = '$b_id'");
                        }
                    }
                    return $pesanan;
                }
            }
        }

        $created_time = strtotime($pesanan['created_at']);
        if ((time() - $created_time) > 86400) {
            mysqli_query($conn, "UPDATE pesanan SET status_pesanan = 'dibatalkan', updated_at = NOW() WHERE id = '$pesanan_id'");
            $pesanan['status_pesanan'] = 'dibatalkan';
            $q_rest = mysqli_query($conn, "SELECT buku_id, jumlah FROM detail_pesanan WHERE pesanan_id = '$pesanan_id'");
            if ($q_rest) {
                while ($r_rest = mysqli_fetch_assoc($q_rest)) {
                    $b_id = (int) $r_rest['buku_id'];
                    $b_qty = (int) $r_rest['jumlah'];
                    mysqli_query($conn, "UPDATE buku SET stok = stok + $b_qty, updated_at = NOW() WHERE id = '$b_id'");
                }
            }
        }

        return $pesanan;
    }
?>