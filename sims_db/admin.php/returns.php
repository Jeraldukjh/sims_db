<?php
include "../db.php"; // Include database connection

$query = "
    SELECT br.request_id, br.student_id, s.name AS student_name, p.product_name, br.status 
    FROM borrow_requests br
    LEFT JOIN students s ON br.student_id = s.student_id
    LEFT JOIN products p ON br.product_id = p.id
    WHERE br.status = 'pending'
";

$stmt = $pdo->prepare($query);
$stmt->execute();
$requests = $stmt->fetchAll();

if (count($requests) > 0) {
    foreach ($requests as $row) {
        echo "<tr>
                <td>{$row['request_id']}</td>
                <td>{$row['student_id']}</td>
                <td>{$row['student_name']}</td>
                <td>{$row['product_name']}</td>
                <td>{$row['status']}</td>
                <td>
                    <button onclick=\"updateStatus({$row['request_id']}, 'approve')\">Approve</button>
                    <button onclick=\"updateStatus({$row['request_id']}, 'reject')\">Reject</button>
                </td>
              </tr>";
    }
} else {
    echo "<tr><td colspan='6'>No pending requests</td></tr>";
}
?>
