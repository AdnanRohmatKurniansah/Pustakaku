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

