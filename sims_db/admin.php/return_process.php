<?php
include "db.php";

if (isset($_POST['id']) && isset($_POST['action'])) {
    $id = $_POST['id'];
    $action = $_POST['action'];

    if ($action == "approve") {
        // Update return request status
        $stmt = $pdo->prepare("UPDATE return_requests SET status = 'approved' WHERE request_id = ?");
        $stmt->execute([$id]);

        echo "Return Approved!";
    }
}
?>
