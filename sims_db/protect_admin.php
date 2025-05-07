<?php
require_once __DIR__ . '/../sims_db/db.php';
header('Content-Type: application/json');

// Simulate a database of users
$validToken = '3eae07647bb3c6ca1f6e7343afe9a7c4'; // This should be stored securely

// Check if the token is provided in the request header
$headers = apache_request_headers();
$token = $headers['Authorization'] ?? '';

if ($token !== $validToken) {
    echo json_encode([
        "status" => "error",
        "message" => "Unauthorized. Invalid Token."
    ]);
    http_response_code(401);
    exit();
}

// If token is valid, proceed to fetch users
try {
    $stmt = $pdo->prepare("SELECT id, student_id, name, is_admin, approved FROM users");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode([
        "status" => "success",
        "data" => $students
    ]);
} catch (PDOException $e) {
    echo json_encode([
        "status" => "error",
        "message" => "Database error: " . $e->getMessage()
    ]);
}
?>
