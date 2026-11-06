<?php
include 'condb.php';

header("Content-Type: application/json; charset=utf-8");

$data = json_decode(file_get_contents("php://input"), true);

$username = is_array($data) && isset($data['username']) && is_string($data['username'])
    ? trim($data['username'])
    : '';
$password = is_array($data) && isset($data['password']) && is_string($data['password'])
    ? $data['password']
    : '';

if ($username === '' || $password === '') {
    echo json_encode([
        "success" => false,
        "message" => "กรอกข้อมูลไม่ครบ"
    ]);
    exit;
}

try {
    $stmt = $conn->prepare(
        "SELECT customer_id, firstName, lastName, username, password
         FROM customers WHERE username = :username"
    );
    $stmt->execute([":username" => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode([
            "success" => false,
            "message" => "ไม่พบผู้ใช้งาน"
        ]);
        exit;
    }

    $storedPassword = (string)$user['password'];
    $passwordMatches = password_verify($password, $storedPassword);

    if (!$passwordMatches && hash_equals($storedPassword, $password)) {
        $newHash = password_hash($password, PASSWORD_DEFAULT);
        $update = $conn->prepare(
            "UPDATE customers SET password = :password WHERE customer_id = :customer_id"
        );
        $update->execute([
            ":password" => $newHash,
            ":customer_id" => $user['customer_id']
        ]);
        $passwordMatches = true;
    }

    if (!$passwordMatches) {
        echo json_encode([
            "success" => false,
            "message" => "รหัสผ่านไม่ถูกต้อง"
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "message" => "Login สำเร็จ",
        "user" => [
            "id" => $user['customer_id'],
            "name" => $user['firstName'] . " " . $user['lastName'],
            "username" => $user['username']
        ]
    ], JSON_UNESCAPED_UNICODE);
} catch (PDOException $e) {
    echo json_encode([
        "success" => false,
        "message" => "เกิดข้อผิดพลาดในการเข้าสู่ระบบ"
    ]);
}