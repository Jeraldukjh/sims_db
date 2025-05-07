<?php
session_start();
require_once __DIR__ . '/../sims_db/db.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Read JSON raw input
    $input = json_decode(file_get_contents('php://input'), true);

    $student_id = htmlspecialchars(strip_tags(trim($input['student_id'] ?? '')));
    $password = htmlspecialchars(strip_tags(trim($input['password'] ?? '')));

    try {
        $stmt = $pdo->prepare("SELECT id, student_id, name, password, is_admin, approved FROM users WHERE student_id = :student_id");
        $stmt->bindValue(':student_id', $student_id, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if ($user['approved'] == 0) {
                echo json_encode([
                    "status" => "error",
                    "message" => "Your account is not approved yet. Please contact admin."
                ]);
            } else {
                if (password_verify($password, $user['password'])) {
                    $_SESSION['student_id'] = $user['student_id'];
                    $_SESSION['username'] = $user['name'];
                    $_SESSION['is_admin'] = $user['is_admin'];

                    // Generate random token
                    $token = bin2hex(random_bytes(16)); // 32-character random token

                    // OPTIONAL: Save token to database if you want to track sessions

                    echo json_encode([
                        "status" => "success",
                        "message" => "Login successful",
                        "token" => $token,
                        "user" => [
                            "student_id" => $user['student_id'],
                            "name" => $user['name'],
                            "is_admin" => $user['is_admin']
                        ]
                    ]);
                } else {
                    echo json_encode([
                        "status" => "error",
                        "message" => "Incorrect password."
                    ]);
                }
            }
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "No account found with this Student ID."
            ]);
        }
    } catch (PDOException $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Database error: " . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        "status" => "error",
        "message" => "Only POST method is allowed."
    ]);
}
?>
