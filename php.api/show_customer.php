<?php
include 'condb.php';

try {
    if (isset($_GET['id'])) {
        $stmt = $conn->prepare("SELECT customer_id, firstName, lastName, phone, username FROM customers WHERE customer_id = :id");
        $stmt->execute([':id' => $_GET['id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            echo json_encode(["success" => false, "message" => "ไม่พบข้อมูลลูกค้า"]);
            exit;
        }
        echo json_encode(["success" => true, "data" => $row, "message" => "ดึงข้อมูลลูกค้าเรียบร้อย"]);
        exit;
    }

    $stmt = $conn->query("SELECT customer_id, firstName, lastName, phone, username FROM customers ORDER BY customer_id ASC");
    $datas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $datas,
        "message" => "ดึงข้อมูลลูกค้าเรียบร้อย"
    ]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
