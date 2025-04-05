<?php
session_start();
require_once __DIR__ . '/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized access!");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (!isset($_POST['request_id'])) {
        die("Request ID missing.");
    }

    $request_id = $_POST['request_id'];

    try {
        $stmt = $pdo->prepare("UPDATE borrow_requests SET status = 'rejected' WHERE request_id = :request_id");
        $stmt->bindParam(':request_id', $request_id, PDO::PARAM_INT);

        if ($stmt->execute()) {
            echo "Request rejected successfully.";
        } else {
            echo "Failed to reject request.";
        }
    } catch (PDOException $e) {
        echo "Error: " . htmlspecialchars($e->getMessage());
    }
} else {
    echo "Invalid request.";
}
?>
