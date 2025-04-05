<?php
include "../db.php";

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id"], $_POST["action"])) {
    $id = $_POST["id"];
    $action = $_POST["action"];
    
    if ($action === "approve" || $action === "reject") {
        $status = $action === "approve" ? "approved" : "rejected";
        
        $stmt = $pdo->prepare("UPDATE borrow_requests SET status = ? WHERE request_id = ?");
        if ($stmt->execute([$status, $id])) {
            echo "Request $status successfully!";
        } else {
            echo "Failed to update request.";
        }
    } else {
        echo "Invalid action.";
    }
} else {
    echo "Invalid request.";
}
?>
