<?php
    require_once __DIR__ . '/../config/database.php';
    require_once __DIR__ . '/../config/helper.php';
    require_once __DIR__ . '/../vendor/autoload.php';

    use Dompdf\Dompdf;
    use Dompdf\Options;

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
        header("Location: ../auth/login.php");
        exit;
    }

    $tanggal_mulai = trim($_GET['tanggal_mulai'] ?? date('Y-m-01'));
    $tanggal_selesai = trim($_GET['tanggal_selesai'] ?? date('Y-m-d'));
    $status_filter = trim($_GET['status'] ?? 'berhasil');

    $mulai_esc = mysqli_real_escape_string($conn, $tanggal_mulai);
    $selesai_esc = mysqli_real_escape_string($conn, $tanggal_selesai);

    $where_clauses = ["DATE(p.created_at) >= '$mulai_esc'", "DATE(p.created_at) <= '$selesai_esc'"];

    if ($status_filter === 'berhasil') {
        $where_clauses[] = "p.status_pesanan IN ('diproses', 'dikirim', 'selesai')";
    } elseif ($status_filter === 'selesai') {
        $where_clauses[] = "p.status_pesanan = 'selesai'";
    } elseif ($status_filter === 'menunggu') {
        $where_clauses[] = "p.status_pesanan = 'menunggu_pembayaran'";
    } elseif ($status_filter === 'dibatalkan') {
        $where_clauses[] = "p.status_pesanan = 'dibatalkan'";
    }

    $where_sql = implode(' AND ', $where_clauses);

    $q_rekap = mysqli_query($conn, "SELECT COUNT(p.id) AS total_transaksi, COALESCE(SUM(p.total_bayar), 0) AS total_omzet, COALESCE(SUM(p.total_buku), 0) AS total_buku_omzet, COALESCE(SUM(p.ongkos_kirim), 0) AS total_ongkir FROM pesanan p WHERE $where_sql");
    $rekap = mysqli_fetch_assoc($q_rekap);

    $total_transaksi = (int) ($rekap['total_transaksi'] ?? 0);
    $total_omzet = (float) ($rekap['total_omzet'] ?? 0);
    $rata_rata_order = $total_transaksi > 0 ? ($total_omzet / $total_transaksi) : 0;

    $q_buku_terjual = mysqli_query($conn, "SELECT COALESCE(SUM(dp.jumlah), 0) AS terjual FROM detail_pesanan dp JOIN pesanan p ON dp.pesanan_id = p.id WHERE $where_sql");
    $total_buku_terjual = (int) (mysqli_fetch_assoc($q_buku_terjual)['terjual'] ?? 0);

    $q_list = mysqli_query($conn, "SELECT p.*, u.nama_lengkap AS nama_pelanggan, (SELECT SUM(dp.jumlah) FROM detail_pesanan dp WHERE dp.pesanan_id = p.id) AS jumlah_eksemplar FROM pesanan p LEFT JOIN users u ON p.user_id = u.id WHERE $where_sql ORDER BY p.created_at DESC");

    $status_label_map = [
        'semua' => 'Semua Status',
        'berhasil' => 'Transaksi Sukses (Diproses, Dikirim, Selesai)',
        'selesai' => 'Selesai Diterima',
        'menunggu' => 'Menunggu Pembayaran',
        'dibatalkan' => 'Dibatalkan / Kadaluwarsa'
    ];
    $label_status = $status_label_map[$status_filter] ?? ucfirst($status_filter);

    $nama_admin = $_SESSION['nama_lengkap'] ?? 'Administrator';
    $waktu_cetak = date('d F Y, H:i') . ' WIB';
    $periode_teks = date('d M Y', strtotime($tanggal_mulai)) . ' s/d ' . date('d M Y', strtotime($tanggal_selesai));

    $html = '<!DOCTYPE html>
    <html lang="id">
    <head>
        <meta charset="UTF-8">
        <title>Laporan Penjualan PustakaKu</title>
        <style>
            @page {
                margin: 28px 30px;
            }
            body {
                font-family: Helvetica, Arial, sans-serif;
                font-size: 11px;
                color: #212529;
                line-height: 1.4;
            }
            .header-table {
                width: 100%;
                border-bottom: 2px solid #198754;
                padding-bottom: 12px;
                margin-bottom: 15px;
            }
            .brand-title {
                font-size: 20px;
                font-weight: bold;
                color: #198754;
                margin: 0;
            }
            .report-title {
                font-size: 14px;
                font-weight: bold;
                color: #333333;
                margin: 4px 0 0 0;
            }
            .meta-text {
                font-size: 10px;
                color: #6c757d;
            }
            .kpi-table {
                width: 100%;
                margin-bottom: 16px;
                border-spacing: 6px;
            }
            .kpi-box {
                background-color: #f8f9fa;
                border: 1px solid #e9ecef;
                border-radius: 6px;
                padding: 10px 12px;
                text-align: left;
            }
            .kpi-label {
                font-size: 9px;
                color: #6c757d;
                text-transform: uppercase;
                font-weight: bold;
            }
            .kpi-val {
                font-size: 15px;
                font-weight: bold;
                color: #333333;
                margin-top: 4px;
            }
            table.data-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 8px;
            }
            table.data-table th {
                background-color: #198754;
                color: #ffffff;
                font-weight: bold;
                font-size: 10px;
                padding: 7px 6px;
                text-align: left;
                border: 1px solid #198754;
            }
            table.data-table td {
                padding: 6px 6px;
                border: 1px solid #dee2e6;
                font-size: 10px;
            }
            table.data-table tr:nth-child(even) {
                background-color: #fbfcfc;
            }
            .badge {
                display: inline-block;
                padding: 2px 6px;
                border-radius: 4px;
                font-size: 8.5px;
                font-weight: bold;
            }
            .badge-selesai { background-color: #d1e7dd; color: #0f5132; }
            .badge-diproses { background-color: #cff4fc; color: #055160; }
            .badge-dikirim { background-color: #cfe2ff; color: #084298; }
            .badge-menunggu { background-color: #fff3cd; color: #664d03; }
            .badge-batal { background-color: #f8d7da; color: #842029; }
            .signature-table {
                width: 100%;
                margin-top: 25px;
            }
            .signature-box {
                width: 200px;
                text-align: center;
                float: right;
            }
        </style>
    </head>
    <body>
        <table class="header-table">
            <tr>
                <td style="width: 65%;">
                    <h1 class="brand-title">PustakaKu</h1>
                    <div class="report-title">Laporan Rekapitulasi Penjualan & Transaksi</div>
                    <div class="meta-text" style="margin-top: 3px;">Periode: <strong>' . htmlspecialchars($periode_teks) . '</strong> &bull; Status: <strong>' . htmlspecialchars($label_status) . '</strong></div>
                </td>
                <td style="width: 35%; text-align: right; vertical-align: bottom;">
                    <div class="meta-text">Waktu Cetak: ' . htmlspecialchars($waktu_cetak) . '</div>
                    <div class="meta-text">Dicetak Oleh: ' . htmlspecialchars($nama_admin) . '</div>
                </td>
            </tr>
        </table>

        <table class="kpi-table">
            <tr>
                <td class="kpi-box" style="width: 25%;">
                    <div class="kpi-label">Total Omzet Penjualan</div>
                    <div class="kpi-val">' . formatRupiah($total_omzet) . '</div>
                </td>
                <td class="kpi-box" style="width: 25%;">
                    <div class="kpi-label">Total Transaksi</div>
                    <div class="kpi-val">' . number_format($total_transaksi, 0, ',', '.') . ' Pesanan</div>
                </td>
                <td class="kpi-box" style="width: 25%;">
                    <div class="kpi-label">Total Buku Terjual</div>
                    <div class="kpi-val">' . number_format($total_buku_terjual, 0, ',', '.') . ' Eksemplar</div>
                </td>
                <td class="kpi-box" style="width: 25%;">
                    <div class="kpi-label">Rata-rata Order</div>
                    <div class="kpi-val">' . formatRupiah($rata_rata_order) . '</div>
                </td>
            </tr>
        </table>

        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 25px; text-align: center;">No</th>
                    <th style="width: 120px;">No. Pesanan</th>
                    <th style="width: 80px;">Tanggal</th>
                    <th>Nama Pelanggan</th>
                    <th style="width: 45px; text-align: center;">Item</th>
                    <th style="width: 90px; text-align: right;">Total Bayar</th>
                    <th style="width: 95px;">Metode</th>
                    <th style="width: 80px; text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>';

    if (mysqli_num_rows($q_list) === 0) {
        $html .= '<tr><td colspan="8" style="text-align: center; padding: 25px; color: #6c757d;">Tidak ada data transaksi yang ditemukan pada periode dan filter ini.</td></tr>';
    } else {
        $no = 1;
        while ($row = mysqli_fetch_assoc($q_list)) {
            $st = $row['status_pesanan'];
            $st_class = 'badge-menunggu';
            if ($st === 'selesai') $st_class = 'badge-selesai';
            elseif ($st === 'diproses') $st_class = 'badge-diproses';
            elseif ($st === 'dikirim') $st_class = 'badge-dikirim';
            elseif ($st === 'dibatalkan') $st_class = 'badge-batal';

            $html .= '<tr>
                <td style="text-align: center;">' . $no++ . '</td>
                <td style="font-family: monospace; font-weight: bold;">' . htmlspecialchars($row['nomor_pesanan']) . '</td>
                <td>' . date('d/m/Y H:i', strtotime($row['created_at'])) . '</td>
                <td>' . htmlspecialchars($row['nama_pelanggan'] ?? 'Pelanggan') . '</td>
                <td style="text-align: center;">' . (int)$row['jumlah_eksemplar'] . '</td>
                <td style="text-align: right; font-weight: bold;">' . formatRupiah($row['total_bayar']) . '</td>
                <td>' . htmlspecialchars($row['metode_pembayaran']) . '</td>
                <td style="text-align: center;"><span class="badge ' . $st_class . '">' . htmlspecialchars(ucfirst(str_replace('_', ' ', $st))) . '</span></td>
            </tr>';
        }
    }

    $html .= '</tbody>
        </table>

        <table class="signature-table">
            <tr>
                <td style="width: 65%;"></td>
                <td style="width: 35%; text-align: center;">
                    <div style="font-size: 10px; color: #6c757d; margin-bottom: 45px;">
                        Yogyakarta, ' . date('d F Y') . '<br>
                        Penanggung Jawab Operasional
                    </div>
                    <div style="font-weight: bold; text-decoration: underline;">' . htmlspecialchars($nama_admin) . '</div>
                    <div style="font-size: 9px; color: #6c757d;">Administrator PustakaKu</div>
                </td>
            </tr>
        </table>
    </body>
    </html>';

    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isRemoteEnabled', true);
    $options->set('defaultFont', 'Helvetica');

    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();

    $filename = "Laporan_Penjualan_PustakaKu_{$tanggal_mulai}_sd_{$tanggal_selesai}.pdf";
    $dompdf->stream($filename, ["Attachment" => true]);
    exit();

