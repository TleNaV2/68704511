<?php
include 'condb.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['customer_id'])) {
    echo json_encode(["success" => false, "message" => "ไม่พบรหัสที่ต้องการลบ"]);
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM customers WHERE customer_id = :id");
    $stmt->execute([':id' => $data['customer_id']]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(["success" => false, "message" => "ไม่พบข้อมูลลูกค้าที่ต้องการลบ"]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "ลบข้อมูลลูกค้าเรียบร้อย"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
