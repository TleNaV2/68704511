<?php
include 'condb.php';

$data = json_decode(file_get_contents("php://input"), true);

if (
    !isset($data['emp_id']) ||
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
        ':id'        => $data['emp_id'],
        ':firstName' => $data['firstName'],
        ':lastName'  => $data['lastName'],
        ':phone'     => $data['phone'],
        ':username'  => $data['username']
    ];

    $sql = "UPDATE employee
            SET firstName = :firstName, lastName = :lastName, phone = :phone,
                username = :username";

    if (!empty($data['password'])) {
        $sql .= ", password = :password";
        $params[':password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }

    $sql .= " WHERE emp_id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    echo json_encode(["success" => true, "message" => "แก้ไขข้อมูลพนักงานเรียบร้อย"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
