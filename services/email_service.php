<?php
    require_once dirname(__DIR__) . '/config/database.php';

    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;

    function kirim_email_reset_password($email_tujuan, $nama_lengkap, $token) {
        $smtp_host = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
        $smtp_port = (int) ($_ENV['SMTP_PORT'] ?? 587);
        $smtp_user = $_ENV['SMTP_USER'] ?? '';
        $smtp_pass = $_ENV['SMTP_PASS'] ?? '';
        $app_url = rtrim($_ENV['APP_URL'] ?? 'http://localhost/projek_web', '/');

        $reset_link = $app_url . '/auth/reset_password.php?token=' . urlencode($token);
        $icon_path = dirname(__DIR__) . '/assets/images/brand-icon.png';

        if (empty($smtp_user) || empty($smtp_pass) || !class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return [
                'ok' => true,
                'is_mock' => true,
                'reset_link' => $reset_link
            ];
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $smtp_user;
            $mail->Password = $smtp_pass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $smtp_port;

            $mail->setFrom($smtp_user, 'PustakaKu');
            $mail->addAddress($email_tujuan, $nama_lengkap);

            $img_tag = '<div style="width:40px;height:40px;background-color:#198754;border-radius:10px;color:#ffffff;font-size:20px;font-weight:bold;line-height:40px;text-align:center;">P</div>';
            if (file_exists($icon_path)) {
                $mail->addEmbeddedImage($icon_path, 'brand_icon', 'brand-icon.png');
                $img_tag = '<img src="cid:brand_icon" width="40" height="40" alt="PustakaKu" style="display:block;border-radius:10px;border:none;">';
            }

            $mail->isHTML(true);
            $mail->Subject = 'Pengaturan Ulang Kata Sandi - PustakaKu';
            $mail->Body = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Reset Password - PustakaKu</title>
            </head>
            <body style="margin:0;padding:0;background-color:#f4f6f8;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;">
                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#f4f6f8;padding:30px 10px;">
                    <tr>
                        <td align="center">
                            <table width="600" border="0" cellspacing="0" cellpadding="0" style="max-width:600px;background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 15px rgba(0,0,0,0.05);border:1px solid #e9ecef;">
                                <tr>
                                    <td style="padding:24px 32px;background-color:#ffffff;border-bottom:1px solid #e9ecef;text-align:left;">
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td>
                                                    <table border="0" cellspacing="0" cellpadding="0">
                                                        <tr>
                                                            <td style="width:40px;height:40px;vertical-align:middle;padding:0;">
                                                                ' . $img_tag . '
                                                            </td>
                                                            <td style="padding-left:12px;vertical-align:middle;">
                                                                <div style="font-size:22px;font-weight:800;color:#212529;letter-spacing:-0.5px;line-height:1.1;">Pustaka<span style="color:#198754;">Ku</span></div>
                                                                <div style="font-size:11px;color:#6c757d;margin-top:2px;letter-spacing:0.5px;">Buku &amp; Alat Tulis</div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:36px 32px;">
                                        <h2 style="margin:0 0 16px 0;font-size:20px;font-weight:700;color:#212529;">Permintaan Atur Ulang Kata Sandi</h2>
                                        <p style="margin:0 0 14px 0;font-size:15px;line-height:1.6;color:#495057;">Halo <strong>' . htmlspecialchars($nama_lengkap) . '</strong>,</p>
                                        <p style="margin:0 0 24px 0;font-size:14px;line-height:1.6;color:#495057;">Kami menerima permintaan untuk mengatur ulang kata sandi akun PustakaKu Anda. Klik tombol di bawah ini untuk melanjutkan:</p>
                                        
                                        <div style="text-align:center;margin:30px 0;">
                                            <a href="' . $reset_link . '" style="background-color:#198754;color:#ffffff;text-decoration:none;padding:14px 32px;border-radius:50px;font-weight:700;font-size:15px;display:inline-block;box-shadow:0 3px 8px rgba(25,135,84,0.3);">Atur Ulang Kata Sandi</a>
                                        </div>

                                        <p style="margin:0 0 10px 0;font-size:13px;line-height:1.5;color:#6c757d;">Tautan ini hanya berlaku selama <strong>1 jam</strong>. Jika Anda tidak pernah meminta pengaturan ulang kata sandi, abaikan email ini dengan aman.</p>
                                        
                                        <hr style="border:none;border-top:1px solid #e9ecef;margin:28px 0;">
                                        <p style="margin:0;font-size:12px;color:#adb5bd;line-height:1.5;">Jika tombol tidak dapat diklik, salin tautan berikut ke peramban Anda:<br><a href="' . $reset_link . '" style="color:#198754;word-break:break-all;">' . $reset_link . '</a></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:20px 32px;background-color:#f8f9fa;border-top:1px solid #e9ecef;text-align:center;">
                                        <p style="margin:0;font-size:12px;color:#6c757d;">&copy; ' . date('Y') . ' PustakaKu. Hak cipta dilindungi undang-undang.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
            </html>';

            $mail->send();
            return ['ok' => true, 'is_mock' => false, 'reset_link' => $reset_link];
        } catch (Exception $e) {
            return ['ok' => false, 'message' => $mail->ErrorInfo, 'reset_link' => $reset_link];
        }
    }

    function kirim_email_konfirmasi_pesanan($pesanan_id, $conn) {
        $pesanan_id = (int)$pesanan_id;
        $q_pesanan = mysqli_query($conn, "SELECT p.*, u.nama_lengkap, u.email FROM pesanan p JOIN users u ON p.user_id = u.id WHERE p.id = '$pesanan_id' LIMIT 1");
        $pesanan = $q_pesanan ? mysqli_fetch_assoc($q_pesanan) : null;
        if (!$pesanan || empty($pesanan['email'])) {
            return false;
        }

        $q_items = mysqli_query($conn, "SELECT dp.*, b.judul, b.penulis FROM detail_pesanan dp JOIN buku b ON dp.buku_id = b.id WHERE dp.pesanan_id = '$pesanan_id'");
        $items = [];
        if ($q_items) {
            while ($row = mysqli_fetch_assoc($q_items)) {
                $items[] = $row;
            }
        }

        $app_url = rtrim($_ENV['APP_URL'] ?? 'http://localhost/projek_web', '/');
        $pesanan_url = $app_url . '/pesanan_sukses.php?id=' . $pesanan_id;
        $smtp_host = $_ENV['SMTP_HOST'] ?? 'smtp.gmail.com';
        $smtp_port = (int) ($_ENV['SMTP_PORT'] ?? 587);
        $smtp_user = $_ENV['SMTP_USER'] ?? '';
        $smtp_pass = $_ENV['SMTP_PASS'] ?? '';
        $icon_path = dirname(__DIR__) . '/assets/images/brand-icon.png';

        if (empty($smtp_user) || empty($smtp_pass) || !class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return false;
        }

        try {
            $mail = new PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = $smtp_host;
            $mail->SMTPAuth = true;
            $mail->Username = $smtp_user;
            $mail->Password = $smtp_pass;
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = $smtp_port;

            $mail->setFrom($smtp_user, 'PustakaKu');
            $mail->addAddress($pesanan['email'], $pesanan['nama_lengkap']);

            $img_tag = '<div style="width:40px;height:40px;background-color:#198754;border-radius:10px;color:#ffffff;font-size:20px;font-weight:bold;line-height:40px;text-align:center;">P</div>';
            if (file_exists($icon_path)) {
                $mail->addEmbeddedImage($icon_path, 'brand_icon_order', 'brand-icon.png');
                $img_tag = '<img src="cid:brand_icon_order" width="40" height="40" alt="PustakaKu" style="display:block;border-radius:10px;border:none;">';
            }

            $rows_html = '';
            foreach ($items as $it) {
                $rows_html .= '
                <tr>
                    <td style="padding:12px 10px;border-bottom:1px solid #f1f3f5;font-size:14px;color:#212529;">
                        <strong>' . htmlspecialchars($it['judul']) . '</strong><br>
                        <span style="font-size:12px;color:#6c757d;">' . htmlspecialchars($it['penulis']) . ' &times; ' . (int)$it['jumlah'] . '</span>
                    </td>
                    <td align="right" style="padding:12px 10px;border-bottom:1px solid #f1f3f5;font-size:14px;font-weight:600;color:#198754;">
                        Rp ' . number_format($it['subtotal'], 0, ',', '.') . '
                    </td>
                </tr>';
            }

            $email_html = '
            <!DOCTYPE html>
            <html>
            <head>
                <meta charset="UTF-8">
                <title>Konfirmasi Pesanan - PustakaKu</title>
            </head>
            <body style="margin:0;padding:0;background-color:#f4f6f8;font-family:-apple-system,BlinkMacSystemFont,\'Segoe UI\',Roboto,Helvetica,Arial,sans-serif;">
                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#f4f6f8;padding:30px 10px;">
                    <tr>
                        <td align="center">
                            <table width="600" border="0" cellspacing="0" cellpadding="0" style="max-width:600px;background-color:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 15px rgba(0,0,0,0.05);border:1px solid #e9ecef;">
                                <tr>
                                    <td style="padding:24px 32px;background-color:#ffffff;border-bottom:1px solid #e9ecef;">
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td>
                                                    <table border="0" cellspacing="0" cellpadding="0">
                                                        <tr>
                                                            <td style="width:40px;height:40px;vertical-align:middle;padding:0;">
                                                                ' . $img_tag . '
                                                            </td>
                                                            <td style="padding-left:12px;vertical-align:middle;">
                                                                <div style="font-size:22px;font-weight:800;color:#212529;letter-spacing:-0.5px;line-height:1.1;">Pustaka<span style="color:#198754;">Ku</span></div>
                                                                <div style="font-size:11px;color:#6c757d;margin-top:2px;letter-spacing:0.5px;">Buku &amp; Alat Tulis</div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td align="right" style="vertical-align:middle;">
                                                    <span style="display:inline-block;padding:6px 14px;background-color:#e8f5e9;color:#198754;border-radius:20px;font-size:12px;font-weight:700;border:1px solid #c8e6c9;text-transform:capitalize;">' . htmlspecialchars($pesanan['status_pesanan']) . '</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:32px;">
                                        <p style="margin:0 0 10px 0;font-size:15px;color:#495057;">Halo <strong>' . htmlspecialchars($pesanan['nama_lengkap']) . '</strong>,</p>
                                        <p style="margin:0 0 20px 0;font-size:14px;color:#495057;line-height:1.6;">Terima kasih atas pesanan Anda di <strong>PustakaKu</strong>! Pesanan Anda telah tercatat dalam sistem kami dengan rincian sebagai berikut:</p>

                                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color:#f8f9fa;border-radius:8px;padding:16px;margin-bottom:24px;border:1px solid #e9ecef;">
                                            <tr>
                                                <td style="font-size:13px;color:#6c757d;padding:4px 0;">Nomor Pesanan:</td>
                                                <td align="right" style="font-size:13px;font-weight:700;color:#212529;padding:4px 0;font-family:monospace;">' . htmlspecialchars($pesanan['nomor_pesanan']) . '</td>
                                            </tr>
                                            <tr>
                                                <td style="font-size:13px;color:#6c757d;padding:4px 0;">Tanggal Pemesanan:</td>
                                                <td align="right" style="font-size:13px;color:#212529;padding:4px 0;">' . date('d M Y, H:i', strtotime($pesanan['created_at'])) . ' WIB</td>
                                            </tr>
                                            <tr>
                                                <td style="font-size:13px;color:#6c757d;padding:4px 0;">Layanan Pengiriman:</td>
                                                <td align="right" style="font-size:13px;color:#212529;padding:4px 0;">' . htmlspecialchars($pesanan['kurir_service']) . '</td>
                                            </tr>
                                        </table>

                                        <h3 style="margin:0 0 12px 0;font-size:16px;font-weight:700;color:#212529;">Item Pembelian</h3>
                                        <table width="100%" border="0" cellspacing="0" cellpadding="0" style="margin-bottom:20px;">
                                            ' . $rows_html . '
                                            <tr>
                                                <td style="padding:10px 10px 4px 10px;font-size:13px;color:#6c757d;">Ongkos Kirim</td>
                                                <td align="right" style="padding:10px 10px 4px 10px;font-size:13px;color:#212529;">Rp ' . number_format($pesanan['ongkos_kirim'], 0, ',', '.') . '</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:6px 10px;font-size:15px;font-weight:800;color:#212529;">Total Pembayaran</td>
                                                <td align="right" style="padding:6px 10px;font-size:17px;font-weight:800;color:#198754;">Rp ' . number_format($pesanan['total_bayar'], 0, ',', '.') . '</td>
                                            </tr>
                                        </table>

                                        <div style="background-color:#f8f9fa;border-left:4px solid #198754;padding:12px 16px;border-radius:4px;margin-bottom:28px;">
                                            <div style="font-size:12px;font-weight:700;color:#495057;text-transform:uppercase;margin-bottom:4px;">Alamat Pengiriman:</div>
                                            <div style="font-size:13px;color:#212529;line-height:1.5;">' . nl2br(htmlspecialchars($pesanan['alamat_pengiriman'])) . '</div>
                                        </div>

                                        <div style="text-align:center;margin:30px 0 10px 0;">
                                            <a href="' . $pesanan_url . '" style="background-color:#198754;color:#ffffff;text-decoration:none;padding:14px 34px;border-radius:50px;font-weight:700;font-size:15px;display:inline-block;box-shadow:0 3px 8px rgba(25,135,84,0.3);">Lihat Status Pesanan</a>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:20px 32px;background-color:#f8f9fa;border-top:1px solid #e9ecef;text-align:center;">
                                        <p style="margin:0;font-size:12px;color:#6c757d;">Jika ada pertanyaan seputar pesanan Anda, silakan hubungi tim layanan pelanggan kami.</p>
                                        <p style="margin:6px 0 0 0;font-size:11px;color:#adb5bd;">&copy; ' . date('Y') . ' PustakaKu. All rights reserved.</p>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </body>
            </html>';

            $mail->isHTML(true);
            $mail->Subject = 'Konfirmasi Pesanan #' . $pesanan['nomor_pesanan'] . ' - PustakaKu';
            $mail->Body = $email_html;

            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
?>

