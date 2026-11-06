<?php
include 'condb.php';

try {
    if (isset($_GET['id'])) {
        $stmt = $conn->prepare("SELECT emp_id, firstName, lastName, phone, username FROM employee WHERE emp_id = :id");
        $stmt->execute([':id' => $_GET['id']]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            echo json_encode(["success" => false, "message" => "ไม่พบข้อมูลพนักงาน"]);
            exit;
        }
        echo json_encode(["success" => true, "data" => $row, "message" => "ดึงข้อมูลพนักงานเรียบร้อย"]);
        exit;
    }

    $stmt = $conn->query("SELECT emp_id, firstName, lastName, phone, username FROM employee ORDER BY emp_id ASC");
    $datas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        "success" => true,
        "data" => $datas,
        "message" => "ดึงข้อมูลพนักงานเรียบร้อย"
    ]);
} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => $e->getMessage()]);
}
