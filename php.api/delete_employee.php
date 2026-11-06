<?php
include 'condb.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['emp_id'])) {
    echo json_encode(["success" => false, "message" => "ไม่พบรหัสที่ต้องการลบ"]);
    exit;
}

try {
    $stmt = $conn->prepare("DELETE FROM employee WHERE emp_id = :id");
    $stmt->execute([':id' => $data['emp_id']]);

    if ($stmt->rowCount() === 0) {
        echo json_encode(["success" => false, "message" => "ไม่พบข้อมูลพนักงานที่ต้องการลบ"]);
        exit;
    }

    echo json_encode(["success" => true, "message" => "ลบข้อมูลพนักงานเรียบร้อย"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
