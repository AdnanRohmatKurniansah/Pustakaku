<?php
    require_once dirname(__DIR__) . '/config/database.php';
    require_once dirname(__DIR__) . '/services/rajaongkir_service.php';

    header('Content-Type: application/json');

    $action = $_GET['action'] ?? '';

    if ($action === 'provinces') {
        $provinces = rajaongkir_get_provinces();
        echo json_encode(['ok' => true, 'data' => $provinces]);
        exit();
    }

    if ($action === 'cities') {
        $province_id = isset($_GET['province_id']) ? (int) $_GET['province_id'] : null;
        $cities = rajaongkir_get_cities($province_id);
        echo json_encode(['ok' => true, 'data' => $cities]);
        exit();
    }

    if ($action === 'cost') {
        $city_id = isset($_GET['city_id']) ? (int) $_GET['city_id'] : 0;
        $weight = isset($_GET['weight']) ? (int) $_GET['weight'] : 1000;
        $courier = isset($_GET['courier']) ? trim($_GET['courier']) : 'jne';

        if ($city_id <= 0) {
            echo json_encode(['ok' => false, 'message' => 'Pilih kota tujuan terlebih dahulu.']);
            exit();
        }

        $services = rajaongkir_calculate_cost($city_id, $weight, $courier);
        echo json_encode(['ok' => true, 'data' => $services]);
        exit();
    }

    echo json_encode(['ok' => false, 'message' => 'Aksi tidak valid.']);
?>

