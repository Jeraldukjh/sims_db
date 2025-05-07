<?php
require_once __DIR__ . '/../sims_db/db.php';
header('Content-Type: application/json');

// Kunin ang method (GET, POST, DELETE, PUT)
$method = $_SERVER['REQUEST_METHOD'];

// Kunin ang headers (Authorization para sa API Key)
$headers = apache_request_headers();
$apiKey = $headers['Authorization'] ?? '';

// Check kung login request
if ($method == 'POST' && $_SERVER['REQUEST_URI'] == '/api/admin_login.php') {
    loginAdmin();
    exit();
}

// Check kung valid ang API key
if ($apiKey !== 'jeraldfern09') {
    echo json_encode(["status" => "error", "message" => "Unauthorized. Invalid API Key."]);
    http_response_code(401);
    exit();
}

// Kunin ang request parameters
$request = explode('/', trim($_SERVER['PATH_INFO'] ?? '', '/'));
$id = isset($request[0]) ? $request[0] : null; // Get user ID if exists

switch ($method) {
    case 'GET':
        if ($id) {
            getUser($id);
        } else {
            getUsers();
        }
        break;

    case 'POST':
        createUser();
        break;

    case 'DELETE':
        deleteUser($id);
        break;

    case 'PUT':
        updateUser($id);
        break;

    default:
        echo json_encode(["status" => "error", "message" => "Method Not Allowed"]);
        http_response_code(405);
        break;
}

function loginAdmin() {
    // Get input for login
    $data = json_decode(file_get_contents('php://input'), true);
    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    // Example Admin Credentials
    $adminUsername = '2311600083';
    $adminPassword = 'jeraldfern09';

    // Validate login
    if ($username === $adminUsername && $password === $adminPassword) {
        echo json_encode([
            "status" => "success",
            "token" => "jeraldfern09"
        ]);
    } else {
        echo json_encode([
            "status" => "error",
            "message" => "Invalid username or password."
        ]);
        http_response_code(401);
    }
}

// Function to get all users
function getUsers() {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT id, student_id, name, is_admin, approved FROM users");
        $stmt->execute();
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["status" => "success", "data" => $students]);
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
}

// Function to get a specific user by ID
function getUser($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("SELECT id, student_id, name, is_admin, approved FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $student = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($student) {
            echo json_encode(["status" => "success", "data" => $student]);
        } else {
            echo json_encode(["status" => "error", "message" => "User not found"]);
            http_response_code(404);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
}

// Function to create a new user
function createUser() {
    global $pdo;
    $inputData = json_decode(file_get_contents('php://input'), true);

    if (isset($inputData['student_id'], $inputData['name'], $inputData['is_admin'], $inputData['approved'])) {
        try {
            $stmt = $pdo->prepare("INSERT INTO users (student_id, name, is_admin, approved) VALUES (:student_id, :name, :is_admin, :approved)");
            $stmt->bindParam(':student_id', $inputData['student_id']);
            $stmt->bindParam(':name', $inputData['name']);
            $stmt->bindParam(':is_admin', $inputData['is_admin']);
            $stmt->bindParam(':approved', $inputData['approved']);
            $stmt->execute();
            echo json_encode(["status" => "success", "message" => "User created successfully"]);
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        http_response_code(400);
    }
}

// Function to delete a user
function deleteUser($id) {
    global $pdo;
    try {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            echo json_encode(["status" => "success", "message" => "User deleted successfully"]);
        } else {
            echo json_encode(["status" => "error", "message" => "User not found"]);
            http_response_code(404);
        }
    } catch (PDOException $e) {
        echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
    }
}

// Function to update a user
function updateUser($id) {
    global $pdo;
    $inputData = json_decode(file_get_contents('php://input'), true);

    if (isset($inputData['student_id'], $inputData['name'], $inputData['is_admin'], $inputData['approved'])) {
        try {
            $stmt = $pdo->prepare("UPDATE users SET student_id = :student_id, name = :name, is_admin = :is_admin, approved = :approved WHERE id = :id");
            $stmt->bindParam(':student_id', $inputData['student_id']);
            $stmt->bindParam(':name', $inputData['name']);
            $stmt->bindParam(':is_admin', $inputData['is_admin']);
            $stmt->bindParam(':approved', $inputData['approved']);
            $stmt->bindParam(':id', $id);
            $stmt->execute();

            if ($stmt->rowCount() > 0) {
                echo json_encode(["status" => "success", "message" => "User updated successfully"]);
            } else {
                echo json_encode(["status" => "error", "message" => "User not found or no changes made"]);
                http_response_code(404);
            }
        } catch (PDOException $e) {
            echo json_encode(["status" => "error", "message" => "Database error: " . $e->getMessage()]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Missing required fields"]);
        http_response_code(400);
    }
}
?>
