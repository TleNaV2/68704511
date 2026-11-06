<?php
include 'condb.php';

$data = json_decode(file_get_contents("php://input"), true);

if (
    !isset($data['customer_id']) ||
    !isset($data['firstName']) ||
    !isset($data['lastName']) ||
    !isset($data['phone']) ||
    !isset($data['username'])
) {
    echo json_encode(["success" => false, "message" => "ข้อมูลไม่ครบ"]);
    exit;
}

try {
    $params = [
        ':id'        => $data['customer_id'],
        ':firstName' => $data['firstName'],
        ':lastName'  => $data['lastName'],
        ':phone'     => $data['phone'],
        ':username'  => $data['username']
    ];

    // เปลี่ยนรหัสผ่านก็ต่อเมื่อกรอกรหัสผ่านใหม่มา
    if (!empty($data['password'])) {
        $sql = "UPDATE customers
                SET firstName = :firstName, lastName = :lastName, phone = :phone,
                    username = :username, password = :password
                WHERE customer_id = :id";
        $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    } else {
        $sql = "UPDATE customers
                SET firstName = :firstName, lastName = :lastName, phone = :phone,
                    username = :username
                WHERE customer_id = :id";
    }

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    echo json_encode(["success" => true, "message" => "แก้ไขข้อมูลลูกค้าเรียบร้อย"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
