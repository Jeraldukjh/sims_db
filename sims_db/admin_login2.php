<?php
require_once __DIR__ . '/../sims_db/db.php';
header('Content-Type: application/json');

// Admin login route (POST request)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    loginAdmin();
}

function loginAdmin() {
    // Get login data from the request body
    $data = json_decode(file_get_contents('php://input'), true);
    $studentId = $data['student_id'] ?? '';  // Get student_id from the request body
    $password = $data['password'] ?? '';     // Get password from the request body

    // Query the database to check if the student_id exists
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT id, student_id, password, is_admin FROM users WHERE student_id = :student_id AND is_admin = 1");
        $stmt->bindParam(':student_id', $studentId);
        $stmt->execute();
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($admin) {
            // Verify the password (use password_verify for hashed passwords)
            if (password_verify($password, $admin['password'])) {
                // If valid, create a token
                $token = generateToken($studentId);
                echo json_encode([
                    "status" => "success",
                    "token" => $token // Return the token
                ]);
            } else {
                echo json_encode([
                    "status" => "error",
                    "message" => "Invalid password."
                ]);
                http_response_code(401); // Unauthorized
            }
        } else {
            echo json_encode([
                "status" => "error",
                "message" => "Admin with the provided student ID not found."
            ]);
            http_response_code(401); // Unauthorized
        }
    } catch (PDOException $e) {
        echo json_encode([
            "status" => "error",
            "message" => "Database error: " . $e->getMessage()
        ]);
        http_response_code(500); // Internal Server Error
    }
}

// Token generation function (You can modify this for a more complex token)
function generateToken($studentId) {
    // For simplicity, we are using a random string as a token (Use a proper JWT in production)
    $token = md5($studentId . time() . rand(1, 10000));
    return $token; // Return the generated token
}
?>
