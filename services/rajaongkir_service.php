<?php
    require_once dirname(__DIR__) . '/config/database.php';
    require_once __DIR__ . '/rajaongkir_data.php';

    function rajaongkir_api_call($endpoint, $params = [], $method = 'GET') {
        static $is_down = null;
        if ($is_down === true) {
            return null;
        }

        if (session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['rajaongkir_down_until'])) {
            if (time() < (int)$_SESSION['rajaongkir_down_until']) {
                $is_down = true;
                return null;
            }
        }

        $api_key = $_ENV['RAJAONGKIR_API_KEY'] ?? '';
        if (empty($api_key)) {
            return null;
        }

        $url = 'https://api.rajaongkir.com/starter/' . ltrim($endpoint, '/');
        if ($method === 'GET' && !empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $curl = curl_init();
        $options = [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 1,
            CURLOPT_CONNECTTIMEOUT => 1,
            CURLOPT_HTTPHEADER => [
                "key: " . $api_key,
                "content-type: application/x-www-form-urlencoded"
            ]
        ];

        if ($method === 'POST') {
            $options[CURLOPT_POST] = true;
            $options[CURLOPT_POSTFIELDS] = http_build_query($params);
        }

        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);
        $err = curl_error($curl);
        $http_code = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($err || !$response || $http_code >= 400) {
            $is_down = true;
            if (session_status() === PHP_SESSION_ACTIVE) {
                $_SESSION['rajaongkir_down_until'] = time() + 300;
            }
            return null;
        }

        $decoded = json_decode($response, true);
        if (isset($decoded['rajaongkir']['results']) && !empty($decoded['rajaongkir']['results'])) {
            $is_down = false;
            if (session_status() === PHP_SESSION_ACTIVE) {
                unset($_SESSION['rajaongkir_down_until']);
            }
            return $decoded['rajaongkir']['results'];
        }

        $is_down = true;
        if (session_status() === PHP_SESSION_ACTIVE) {
            $_SESSION['rajaongkir_down_until'] = time() + 300;
        }
        return null;
    }

    function rajaongkir_get_provinces() {
        $results = rajaongkir_api_call('province');
        if ($results && is_array($results) && count($results) > 0) {
            return $results;
        }

        return get_master_provinces();
    }

    function rajaongkir_get_cities($province_id = null) {
        $params = $province_id ? ['province' => $province_id] : [];
        $results = rajaongkir_api_call('city', $params);
        if ($results && is_array($results) && count($results) > 0) {
            return $results;
        }

        return get_master_cities($province_id);
    }

    function rajaongkir_calculate_cost($destination_city_id, $weight_gram, $courier = 'jne') {
        $origin_city_id = $_ENV['RAJAONGKIR_ORIGIN_CITY_ID'] ?? '501';
        $weight = max(100, (int)$weight_gram);
        $courier = strtolower($courier);

        $results = rajaongkir_api_call('cost', [
            'origin' => $origin_city_id,
            'destination' => $destination_city_id,
            'weight' => $weight,
            'courier' => $courier
        ], 'POST');

        if ($results && is_array($results) && !empty($results[0]['costs'])) {
            $services = [];
            $courier_label = strtoupper($courier);
            foreach ($results[0]['costs'] as $c) {
                $cost_val = $c['cost'][0]['value'] ?? 0;
                $etd = $c['cost'][0]['etd'] ?? '-';
                $srv_name = $courier_label . ' - ' . $c['service'];
                if (!empty($c['description'])) {
                    $srv_name .= ' (' . $c['description'] . ')';
                }
                $services[] = [
                    'service' => $c['service'],
                    'service_name' => $srv_name,
                    'description' => $c['description'] ?? '',
                    'cost' => (int) $cost_val,
                    'etd' => $etd . (strpos($etd, 'HARI') === false && strpos($etd, 'hari') === false ? ' hari' : '')
                ];
            }
            return $services;
        }

        $berat_kg = ceil($weight / 1000);
        if ($berat_kg < 1) $berat_kg = 1;

        $base_rates = [
            'jne' => [
                ['service' => 'REG', 'service_name' => 'JNE - Reguler', 'description' => 'Layanan Reguler', 'cost' => $berat_kg * 12000, 'etd' => '2-3 hari'],
                ['service' => 'YES', 'service_name' => 'JNE - YES (Kilat)', 'description' => 'Yakin Esok Sampai', 'cost' => $berat_kg * 22000, 'etd' => '1 hari'],
                ['service' => 'OKE', 'service_name' => 'JNE - OKE (Hemat)', 'description' => 'Ongkos Kirim Ekonomis', 'cost' => $berat_kg * 9000, 'etd' => '3-5 hari']
            ],
            'pos' => [
                ['service' => 'Pos Reguler', 'service_name' => 'POS - Kilat Khusus', 'description' => 'Pos Kilat Khusus', 'cost' => $berat_kg * 11000, 'etd' => '2-4 hari'],
                ['service' => 'Pos Next Day', 'service_name' => 'POS - Next Day', 'description' => 'Pos Express', 'cost' => $berat_kg * 20000, 'etd' => '1 hari']
            ],
            'tiki' => [
                ['service' => 'REG', 'service_name' => 'TIKI - Reguler', 'description' => 'Regular Service', 'cost' => $berat_kg * 12500, 'etd' => '2-3 hari'],
                ['service' => 'ONS', 'service_name' => 'TIKI - Over Night', 'description' => 'Over Night Service', 'cost' => $berat_kg * 23000, 'etd' => '1 hari'],
                ['service' => 'ECO', 'service_name' => 'TIKI - Ekonomi', 'description' => 'Economy Service', 'cost' => $berat_kg * 9500, 'etd' => '3-5 hari']
            ]
        ];

        return $base_rates[$courier] ?? $base_rates['jne'];
    }
?>