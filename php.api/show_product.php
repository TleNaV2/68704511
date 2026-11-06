<?php

  // เชื่อมต่อฐานข้อมูล
include 'condb.php';

header("Content-Type: application/json; charset=utf-8");

try {
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'GET') {
        http_response_code(405);
        echo json_encode(["success" => false, "message" => "Invalid request method"]);
        exit;
    }

    $stmt = $conn->query("SELECT * FROM products ORDER BY product_id ASC");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $uploadDir = __DIR__ . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
    $imageAliases = [
        'tshirt.jpg' => 'product-shirt.jpg',
        'jeans.jpg' => 'product-jeans.jpg',
        'sneakers.jpg' => 'product-sneakers.jpg'
    ];

    foreach ($products as &$product) {
        $image = isset($product['image']) ? basename((string)$product['image']) : '';
        if ($image !== '' && !is_file($uploadDir . $image) && isset($imageAliases[$image])) {
            $replacement = $imageAliases[$image];
            if (is_file($uploadDir . $replacement)) {
                $image = $replacement;
            }
        }
        $product['image'] = $image !== '' && is_file($uploadDir . $image) ? $image : null;
    }
    unset($product);

    echo json_encode(["success" => true, "data" => $products], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "message" => "Database error"]);
}
?>
