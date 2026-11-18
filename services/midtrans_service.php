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

    function midtrans_check_transaction_status($order_id) {
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

        if ($http_code === 200 && $response) {
            $data = json_decode($response, true);
            return $data['transaction_status'] ?? null;
        }

        return null;
    }
?>