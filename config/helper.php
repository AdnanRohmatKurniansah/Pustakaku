<?php
    if (!function_exists('formatRupiah')) {
        function formatRupiah($angka) {
            return 'Rp ' . number_format((float)$angka, 0, ',', '.');
        }
    }

    if (!function_exists('nav_aktif')) {
        function nav_aktif($kondisi) {
            return $kondisi ? ' active' : '';
        }
    }

    if (!function_exists('tampilkan_alert_toast')) {
        function tampilkan_alert_toast() {
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            $sukses = $_SESSION['success'] ?? '';
            $error = $_SESSION['error'] ?? '';
            unset($_SESSION['success'], $_SESSION['error']);

            $html = '<div id="cartToastWrap" class="cart-toast-wrap" style="position:fixed!important;top:20px!important;right:20px!important;z-index:9999!important;display:flex!important;flex-direction:column!important;gap:8px!important;max-width:350px!important;width:auto!important;pointer-events:none!important;"></div>';
            if (!empty($sukses)) {
                $html .= '<script>document.addEventListener("DOMContentLoaded", function(){ if (window.tampilkanToast) window.tampilkanToast(' . json_encode($sukses) . ', true); else if (window.tampilkanCartToast) window.tampilkanCartToast(' . json_encode($sukses) . ', true); });</script>';
            }
            if (!empty($error)) {
                $html .= '<script>document.addEventListener("DOMContentLoaded", function(){ if (window.tampilkanToast) window.tampilkanToast(' . json_encode($error) . ', false); else if (window.tampilkanCartToast) window.tampilkanCartToast(' . json_encode($error) . ', false); });</script>';
            }
            return $html;
        }
    }

    if (!function_exists('status_pesanan_badge')) {
        function status_pesanan_badge($status) {
            $map = [
                'menunggu_pembayaran' => ['badge bg-warning-subtle text-warning border border-warning-subtle', 'bi bi-clock-history', 'Menunggu Pembayaran'],
                'menunggu_verifikasi' => ['badge bg-info-subtle text-info border border-info-subtle', 'bi bi-patch-check', 'Menunggu Verifikasi'],
                'diproses' => ['badge bg-primary-subtle text-primary border border-primary-subtle', 'bi bi-box-seam', 'Diproses'],
                'dikirim' => ['badge bg-secondary-subtle text-dark border border-secondary-subtle', 'bi bi-truck', 'Dikirim'],
                'selesai' => ['badge bg-success-subtle text-success border border-success-subtle', 'bi bi-check-circle-fill', 'Selesai'],
                'dibatalkan' => ['badge bg-danger-subtle text-danger border border-danger-subtle', 'bi bi-x-circle', 'Dibatalkan']
            ];
            $item = $map[$status] ?? ['badge bg-light text-secondary border', 'bi bi-info-circle', ucfirst(str_replace('_', ' ', $status))];
            return '<span class="' . $item[0] . ' rounded-pill px-3 py-2 fw-semibold"><i class="' . $item[1] . ' me-1"></i>' . $item[2] . '</span>';
        }
    }

