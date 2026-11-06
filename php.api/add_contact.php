<?php
include 'condb.php';

$data = json_decode(file_get_contents("php://input"), true);

if (!is_array($data)) {
    echo json_encode([
        "success" => false,
        "message" => "รูปแบบข้อมูลไม่ถูกต้อง"
    ]);
    exit;
}

$fields = ['subject', 'detail', 'fullname', 'email'];
$contact = [];

foreach ($fields as $field) {
    if (!isset($data[$field]) || !is_string($data[$field])) {
        echo json_encode([
            "success" => false,
            "message" => "กรุณากรอกข้อมูลให้ครบถ้วน"
        ]);
        exit;
    }

    $contact[$field] = trim($data[$field]);
    if ($contact[$field] === '') {
        echo json_encode([
            "success" => false,
            "message" => "กรุณากรอกข้อมูลให้ครบถ้วน"
        ]);
        exit;
    }
}

if (!filter_var($contact['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode([
        "success" => false,
        "message" => "รูปแบบอีเมลไม่ถูกต้อง"
    ]);
    exit;
}

try {
    $conn->exec("CREATE TABLE IF NOT EXISTS contacts (
        contact_id INT(11) NOT NULL AUTO_INCREMENT,
        subject VARCHAR(255) NOT NULL,
        detail TEXT NOT NULL,
        fullname VARCHAR(150) NOT NULL,
        email VARCHAR(150) NOT NULL,
        created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (contact_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci");

    $sql = "INSERT INTO contacts (subject, detail, fullname, email)
            VALUES (:subject, :detail, :fullname, :email)";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ':subject' => $contact['subject'],
        ':detail' => $contact['detail'],
        ':fullname' => $contact['fullname'],
        ':email' => $contact['email']
    ]);

    echo json_encode([
        "success" => true,
        "message" => "เพิ่มข้อมูลติดต่อเรียบร้อย"
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => $e->getMessage()
    ]);
}
