<?php
    session_start();
    $_SESSION = [];

    session_unset();

    session_destroy();

    session_start();

    $_SESSION['success'] = "Anda berhasil keluar dari akun.";
    header("Location: login.php");
    exit;
