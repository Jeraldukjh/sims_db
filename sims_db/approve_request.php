<?php
session_start();
require_once __DIR__ . '/db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['request_id'])) {
    $requestId = $_POST['request_id'];

    try {
        $query = "UPDATE borrow_requests SET status = 'approved' WHERE request_id = ?";
        $stmt = $pdo->prepare($query);
        $stmt->execute([$requestId]);

        if ($stmt->rowCount() > 0) {
            echo "Request ID $requestId has been approved!";
        } else {
            echo "Failed to approve request.";
        }
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    echo "Invalid request.";
}
?>
