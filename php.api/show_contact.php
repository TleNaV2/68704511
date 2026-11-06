<?php
include 'condb.php';

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

    if (isset($_GET['id'])) {
        $stmt = $conn->prepare("SELECT * FROM contacts WHERE contact_id = :id");
        $stmt->execute([':id' => $_GET['id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            echo json_encode(["success" => false, "message" => "ไม่พบข้อมูลติดต่อ"]);
            exit;
        }
        echo json_encode(["success" => true, "data" => $row, "message" => "ดึงข้อมูลติดต่อเรียบร้อย"]);
        exit;
    }

    $stmt = $conn->query("SELECT * FROM contacts ORDER BY contact_id DESC");
    $datas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $datas,
        "message" => "ดึงข้อมูลติดต่อเรียบร้อย"
    ]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
