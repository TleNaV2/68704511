<?php

include 'condb.php';

header("Content-Type: application/json; charset=utf-8");

function respond($data, $statusCode = 200)
{
    http_response_code($statusCode);
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

function postField($name)
{
    return isset($_POST[$name]) && is_string($_POST[$name])
        ? trim($_POST[$name])
        : '';
}

function saveUploadedImage()
{
    if (!isset($_FILES['image'])) {
        return null;
    }

    $file = $_FILES['image'];
    if (!is_array($file) || !isset($file['error'], $file['tmp_name'], $file['size'])) {
        respond(["error" => "ข้อมูลไฟล์รูปภาพไม่ถูกต้อง"], 400);
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        respond(["error" => "อัปโหลดรูปภาพไม่สำเร็จ"], 400);
    }
    if (!is_string($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        respond(["error" => "ไฟล์รูปภาพไม่ถูกต้อง"], 400);
    }
    if ((int)$file['size'] < 1 || (int)$file['size'] > 5 * 1024 * 1024) {
        respond(["error" => "รูปภาพต้องมีขนาดไม่เกิน 5 MB"], 400);
    }

    $imageInfo = @getimagesize($file['tmp_name']);
    $extensions = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp'
    ];
    if ($imageInfo === false || !isset($extensions[$imageInfo['mime'] ?? ''])) {
        respond(["error" => "รองรับเฉพาะไฟล์รูปภาพ JPEG, PNG, GIF หรือ WebP"], 400);
    }

    $targetDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
    if (!is_dir($targetDir) && !mkdir($targetDir, 0755, true) && !is_dir($targetDir)) {
        respond(["error" => "ไม่สามารถสร้างโฟลเดอร์เก็บรูปภาพได้"], 500);
    }

    try {
        $filename = bin2hex(random_bytes(16)) . '.' . $extensions[$imageInfo['mime']];
    } catch (Throwable $error) {
        respond(["error" => "ไม่สามารถสร้างชื่อไฟล์รูปภาพได้"], 500);
    }

    if (!move_uploaded_file($file['tmp_name'], $targetDir . $filename)) {
        respond(["error" => "ไม่สามารถบันทึกรูปภาพได้"], 500);
    }

    return $filename;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';

try {
    $conn->exec("CREATE TABLE IF NOT EXISTS assets (
        asset_id INT(11) NOT NULL AUTO_INCREMENT,
        asset_code VARCHAR(50) NOT NULL,
        asset_name VARCHAR(255) NOT NULL,
        purchase_date DATE DEFAULT NULL,
        price DECIMAL(10,2) DEFAULT NULL,
        image VARCHAR(255) DEFAULT NULL,
        PRIMARY KEY (asset_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    if ($method === 'GET') {
        $stmt = $conn->query(
            "SELECT asset_id, asset_code, asset_name, purchase_date, price, image
             FROM assets ORDER BY asset_id ASC"
        );
        $assets = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
        foreach ($assets as &$asset) {
            $storedImage = is_string($asset['image']) ? basename($asset['image']) : '';
            if ($storedImage !== '' && !is_file($uploadDir . $storedImage)) {
                $matchingImage = $asset['asset_name'] . '.jpg';
                if (basename($matchingImage) === $matchingImage && is_file($uploadDir . $matchingImage)) {
                    $asset['image'] = $matchingImage;
                }
            }
        }
        unset($asset);
        respond(["success" => true, "data" => $assets]);
    }

    if ($method !== 'POST') {
        respond(["error" => "ไม่รองรับคำขอนี้"], 405);
    }

    $action = postField('action');
    $assetId = postField('asset_id');

    if ($action === 'delete') {
        if (!ctype_digit($assetId) || (int)$assetId < 1) {
            respond(["error" => "รหัสข้อมูลไม่ถูกต้อง"], 400);
        }

        $stmt = $conn->prepare("DELETE FROM assets WHERE asset_id = :asset_id");
        $stmt->execute([':asset_id' => $assetId]);
        if ($stmt->rowCount() === 0) {
            respond(["error" => "ไม่พบข้อมูลอุปกรณ์"], 404);
        }

        respond(["message" => "ลบข้อมูลอุปกรณ์สำเร็จ"]);
    }

    if ($action !== 'add' && $action !== 'update') {
        respond(["error" => "Action ไม่ถูกต้อง"], 400);
    }

    $assetCode = postField('asset_code');
    $assetName = postField('asset_name');
    $purchaseDate = postField('purchase_date');
    $price = postField('price');

    if ($assetCode === '' || strlen($assetCode) > 50) {
        respond(["error" => "กรุณากรอกรหัสครุภัณฑ์ไม่เกิน 50 ตัวอักษร"], 400);
    }
    if ($assetName === '' || (function_exists('mb_strlen') && mb_strlen($assetName, 'UTF-8') > 255)) {
        respond(["error" => "กรุณากรอกชื่อครุภัณฑ์ไม่เกิน 255 ตัวอักษร"], 400);
    }
    if ($purchaseDate !== '') {
        $dateParts = explode('-', $purchaseDate);
        if (
            count($dateParts) !== 3 ||
            !ctype_digit($dateParts[0]) ||
            !ctype_digit($dateParts[1]) ||
            !ctype_digit($dateParts[2]) ||
            !checkdate((int)$dateParts[1], (int)$dateParts[2], (int)$dateParts[0])
        ) {
            respond(["error" => "วันที่ซื้อไม่ถูกต้อง"], 400);
        }
    }
    if ($price !== '' && (!is_numeric($price) || (float)$price < 0 || (float)$price > 99999999.99)) {
        respond(["error" => "ราคาต้องเป็นตัวเลขตั้งแต่ 0 ถึง 99,999,999.99"], 400);
    }

    if ($action === 'update') {
        if (!ctype_digit($assetId) || (int)$assetId < 1) {
            respond(["error" => "รหัสข้อมูลไม่ถูกต้อง"], 400);
        }

        $check = $conn->prepare("SELECT asset_id FROM assets WHERE asset_id = :asset_id");
        $check->execute([':asset_id' => $assetId]);
        if (!$check->fetchColumn()) {
            respond(["error" => "ไม่พบข้อมูลอุปกรณ์"], 404);
        }
    }

    $image = saveUploadedImage();
    $values = [
        ':asset_code' => $assetCode,
        ':asset_name' => $assetName,
        ':purchase_date' => $purchaseDate === '' ? null : $purchaseDate,
        ':price' => $price === '' ? null : $price
    ];

    if ($action === 'add') {
        $values[':image'] = $image;
        $stmt = $conn->prepare(
            "INSERT INTO assets (asset_code, asset_name, purchase_date, price, image)
             VALUES (:asset_code, :asset_name, :purchase_date, :price, :image)"
        );
        $stmt->execute($values);
        respond(["message" => "เพิ่มข้อมูลอุปกรณ์สำเร็จ"]);
    }

    $values[':asset_id'] = $assetId;
    $imageUpdate = '';
    if ($image !== null) {
        $values[':image'] = $image;
        $imageUpdate = ', image = :image';
    }
    $stmt = $conn->prepare(
        "UPDATE assets
         SET asset_code = :asset_code, asset_name = :asset_name,
             purchase_date = :purchase_date, price = :price$imageUpdate
         WHERE asset_id = :asset_id"
    );
    $stmt->execute($values);

    respond(["message" => "แก้ไขข้อมูลอุปกรณ์สำเร็จ"]);
} catch (PDOException $e) {
    respond(["error" => "เกิดข้อผิดพลาดในการจัดการข้อมูลอุปกรณ์"], 500);
}
?>
