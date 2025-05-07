<?php
include "db.php";

if (isset($_POST['id']) && isset($_POST['action'])) {
    $id = $_POST['id'];
    $action = $_POST['action'];

    if ($action == "approve") {
        $sql = "UPDATE borrow_requests SET status = 'approved' WHERE id = $id";
    } elseif ($action == "reject") {
        $sql = "UPDATE borrow_requests SET status = 'rejected' WHERE id = $id";
    }

    if ($conn->query($sql)) {
        echo "Request updated successfully!";
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
