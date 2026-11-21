<?php
    require_once dirname(__DIR__) . '/config/database.php';

    function uploadFileCloudinary($file_input, $folder = 'buku') {
        $cloud_name = $_ENV['CLOUDINARY_CLOUD_NAME'] ?? '';
        $api_key = $_ENV['CLOUDINARY_API_KEY'] ?? '';
        $api_secret = $_ENV['CLOUDINARY_API_SECRET'] ?? '';

        $tmp_file = $file_input['tmp_name'] ?? '';
        $original_name = $file_input['name'] ?? '';

        if (empty($tmp_file) || !is_uploaded_file($tmp_file)) {
            return ['ok' => false, 'message' => 'Berkas tidak valid.'];
        }

        $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $allowed_ext)) {
            return ['ok' => false, 'message' => 'Format berkas hanya JPG, PNG, atau WEBP.'];
        }

        if (empty($cloud_name) || empty($api_key) || empty($api_secret) || !class_exists('Cloudinary\Configuration\Configuration')) {
            return ['ok' => false, 'message' => 'Kredensial Cloudinary belum dikonfigurasi di file .env.'];
        }

        try {
            \Cloudinary\Configuration\Configuration::instance([
                'cloud' => [
                    'cloud_name' => $cloud_name,
                    'api_key' => $api_key,
                    'api_secret' => $api_secret
                ],
                'url' => ['secure' => true]
            ]);

            $upload = new \Cloudinary\Api\Upload\UploadApi();
            $result = $upload->upload($tmp_file, [
                'folder' => 'pustakaku/' . $folder,
                'resource_type' => 'image'
            ]);

            return [
                'ok' => true,
                'url' => $result['secure_url'],
                'public_id' => $result['public_id']
            ];
        } catch (\Exception $e) {
            return ['ok' => false, 'message' => 'Gagal mengunggah ke Cloudinary: ' . $e->getMessage()];
        }
    }

    function resolveImageUrl($image_value, $folder = 'buku') {
        if (!empty($image_value) && (strpos($image_value, 'http://') === 0 || strpos($image_value, 'https://') === 0)) {
            return $image_value;
        }

        $local_path = dirname(__DIR__) . '/assets/uploads/' . $folder . '/' . $image_value;
        if (!empty($image_value) && file_exists($local_path)) {
            return 'assets/uploads/' . $folder . '/' . $image_value;
        }

        return 'assets/images/placeholder.png';
    }
?>