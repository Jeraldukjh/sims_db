<?php
include "db.php";  // Ensure the correct path

$sql = "SELECT br.request_id, br.student_id, s.name, p.product_name, br.status 
        FROM borrow_requests br
        JOIN students s ON br.student_id = s.student_id
        JOIN products p ON br.product_id = p.id
        WHERE br.status = 'pending'";

$stmt = $pdo->query($sql);
$borrowRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!empty($borrowRequests)) {
    foreach ($borrowRequests as $request) {
        echo "<tr>
                <td>{$request['request_id']}</td>
                <td>{$request['student_id']} - {$request['name']}</td>
                <td>{$request['product_name']}</td>
                <td>{$request['status']}</td>
                <td><button onclick='updateStatus({$request['request_id']}, \"approve\")'>Approve</button></td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='5'>No pending requests.</td></tr>";
}
?>
