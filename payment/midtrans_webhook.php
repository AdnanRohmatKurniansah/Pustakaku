<?php
    require_once dirname(__DIR__) . '/config/database.php';
    require_once dirname(__DIR__) . '/services/midtrans_service.php';

    header('Content-Type: application/json');

    $json_str = file_get_contents('php://input');
    if (empty($json_str)) {
        echo json_encode(['ok' => false, 'message' => 'No payload']);
        exit();
    }

    $data = json_decode($json_str, true);
    if (!$data || empty($data['order_id'])) {
        echo json_encode(['ok' => false, 'message' => 'Invalid JSON']);
        exit();
    }

    $order_id = mysqli_real_escape_string($conn, $data['order_id']);
    $transaction_status = $data['transaction_status'] ?? '';
    $payment_type = mysqli_real_escape_string($conn, $data['payment_type'] ?? '');
    $fraud_status = $data['fraud_status'] ?? '';
    $transaction_id = mysqli_real_escape_string($conn, $data['transaction_id'] ?? '');

    $q_order = mysqli_query($conn, "SELECT id, status_pesanan FROM pesanan WHERE nomor_pesanan = '$order_id' LIMIT 1");
    if (!$q_order || mysqli_num_rows($q_order) === 0) {
        http_response_code(404);
        echo json_encode(['ok' => false, 'message' => 'Order not found']);
        exit();
    }

    $order = mysqli_fetch_assoc($q_order);
    $current_status = $order['status_pesanan'];
    $new_status = $current_status;

    if ($transaction_status == 'capture') {
        if ($fraud_status == 'challenge') {
            $new_status = 'menunggu_verifikasi';
        } else if ($fraud_status == 'accept') {
            $new_status = 'diproses';
        }
    } else if ($transaction_status == 'settlement') {
        $new_status = 'diproses';
    } else if ($transaction_status == 'pending') {
        $new_status = 'menunggu_pembayaran';
    } else if ($transaction_status == 'deny' || $transaction_status == 'expire' || $transaction_status == 'cancel') {
        $new_status = 'dibatalkan';
    }

    $new_status_esc = mysqli_real_escape_string($conn, $new_status);
    $clean_metode = format_nama_metode_pembayaran($data['payment_type'] ?? '', $data['bank'] ?? '', $data['va_numbers'] ?? []);
    $clean_metode_esc = mysqli_real_escape_string($conn, $clean_metode);
    mysqli_query($conn, "UPDATE pesanan SET status_pesanan = '$new_status_esc', metode_pembayaran = '$clean_metode_esc', midtrans_transaction_id = '$transaction_id', midtrans_payment_type = '$payment_type', updated_at = NOW() WHERE nomor_pesanan = '$order_id'");

    echo json_encode(['ok' => true, 'new_status' => $new_status]);
?>

