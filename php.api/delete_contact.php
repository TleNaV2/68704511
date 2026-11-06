<?php
include 'condb.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['contact_id'])) {
    echo json_encode(["success" => false, "message" => "ไม่พบรหัสที่ต้องการลบ"]);
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM contacts WHERE contact_id = :id");
    $stmt->execute([':id' => $data['contact_id']]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(["success" => false, "message" => "ไม่พบข้อมูลติดต่อที่ต้องการลบ"]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "ลบข้อมูลติดต่อเรียบร้อย"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
