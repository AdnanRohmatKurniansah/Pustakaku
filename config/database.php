<?php
    $hostname = "localhost";
    $username = "root";
    $password = "";
    $database = "pustakaku";

    $conn = mysqli_connect($hostname, $username, $password, $database);

    if (!$conn) {
        die("Koneksi gagal: " . mysqli_connect_error());
    }

    require_once __DIR__ . '/helper.php';
?>