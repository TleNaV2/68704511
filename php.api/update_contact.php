<?php
include 'condb.php';

$data = json_decode(file_get_contents("php://input"), true);

if (
    !isset($data['contact_id']) ||
    !isset($data['subject']) ||
    !isset($data['detail']) ||
    !isset($data['fullname']) ||
    !isset($data['email'])
) {
    echo json_encode(["success" => false, "message" => "ข้อมูลไม่ครบ"]);
    exit;
}

try {
    $sql = "UPDATE contacts
            SET subject = :subject, detail = :detail, fullname = :fullname, email = :email
            WHERE contact_id = :id";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':id'       => $data['contact_id'],
        ':subject'  => $data['subject'],
        ':detail'   => $data['detail'],
        ':fullname' => $data['fullname'],
        ':email'    => $data['email']
    ]);

    echo json_encode(["success" => true, "message" => "แก้ไขข้อมูลติดต่อเรียบร้อย"]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
