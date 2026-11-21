<?php
    require_once dirname(__DIR__) . '/config/database.php';
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION = [];

    session_unset();

    session_destroy();

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $_SESSION['success'] = "Anda berhasil keluar dari akun.";
    header("Location: login.php");
    exit;
