<?php
date_default_timezone_set('Asia/Jakarta');
if (ob_get_level() === 0) {
    ob_start();
}
require_once __DIR__ . '/database.php';
