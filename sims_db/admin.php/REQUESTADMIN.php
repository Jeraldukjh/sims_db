<?php
session_start();
require 'db.php';

// Ensure admin access
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit();
}

// Approve borrow request
if (isset($_POST['approve_borrow'])) {
    $requestId = $_POST['request_id'];

    // Get borrow request details
    $stmt = $pdo->prepare("SELECT student_id, product_id, due_date FROM borrow_requests WHERE request_id = ?");
    $stmt->execute([$requestId]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($request) {
        // Add to borrowed_products table
        $stmt = $pdo->prepare("INSERT INTO borrowed_products (student_id, product_id, due_date) VALUES (?, ?, ?)");
        $stmt->execute([$request['student_id'], $request['product_id'], $request['due_date']]);

        // Decrease stock
        $stmt = $pdo->prepare("UPDATE products SET stock = stock - 1 WHERE product_id = ?");
        $stmt->execute([$request['product_id']]);

        // Notify student
        $message = "Your borrow request has been approved.";
        $stmt = $pdo->prepare("INSERT INTO notifications (student_id, message) VALUES (?, ?)");
        $stmt->execute([$request['student_id'], $message]);

        // Delete request
        $stmt = $pdo->prepare("DELETE FROM borrow_requests WHERE request_id = ?");
        $stmt->execute([$requestId]);
    }
}

// Approve return request
if (isset($_POST['approve_return'])) {
    $requestId = $_POST['request_id'];

    // Get return request details
    $stmt = $pdo->prepare("SELECT student_id, product_id FROM return_requests WHERE request_id = ?");
    $stmt->execute([$requestId]);
    $request = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($request) {
        // Remove from borrowed_products
        $stmt = $pdo->prepare("DELETE FROM borrowed_products WHERE student_id = ? AND product_id = ?");
        $stmt->execute([$request['student_id'], $request['product_id']]);

        // Increase stock
        $stmt = $pdo->prepare("UPDATE products SET stock = stock + 1 WHERE product_id = ?");
        $stmt->execute([$request['product_id']]);

        // Notify student
        $message = "Your return request has been approved.";
        $stmt = $pdo->prepare("INSERT INTO notifications (student_id, message) VALUES (?, ?)");
        $stmt->execute([$request['student_id'], $message]);

        // Delete request
        $stmt = $pdo->prepare("DELETE FROM return_requests WHERE request_id = ?");
        $stmt->execute([$requestId]);
    }
}

// Fetch borrow requests
$stmt = $pdo->query("SELECT * FROM borrow_requests WHERE status = 'pending'");
$borrowRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch return requests
$stmt = $pdo->query("SELECT * FROM return_requests WHERE status = 'pending'");
$returnRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - SIMS</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <div class="dashboard-container">
        <nav class="sidebar">
            <h2>Admin Dashboard</h2>
            <ul>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="admin_logout.php">Logout</a></li>
            </ul>
        </nav>
        
        <main class="content">
            <h2>Admin Actions</h2>
            
            <!-- Borrow Requests Section -->
            <section class="borrow-requests">
                <h3>Pending Borrow Requests</h3>
                <?php if (!empty($borrowRequests)) { ?>
                    <ul>
                        <?php foreach ($borrowRequests as $request) { ?>
                            <li>
                                Borrow Request ID: <?php echo htmlspecialchars($request['request_id']); ?>,
                                Student ID: <?php echo htmlspecialchars($request['student_id']); ?>,
                                Product ID: <?php echo htmlspecialchars($request['product_id']); ?>,
                                Due Date: <?php echo htmlspecialchars($request['due_date']); ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                                    <button type="submit" name="approve_borrow">Approve</button>
                                </form>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <p>No pending borrow requests.</p>
                <?php } ?>
            </section>

            <!-- Return Requests Section -->
            <section class="return-requests">
                <h3>Pending Return Requests</h3>
                <?php if (!empty($returnRequests)) { ?>
                    <ul>
                        <?php foreach ($returnRequests as $request) { ?>
                            <li>
                                Return Request ID: <?php echo htmlspecialchars($request['request_id']); ?>,
                                Student ID: <?php echo htmlspecialchars($request['student_id']); ?>,
                                Product ID: <?php echo htmlspecialchars($request['product_id']); ?>
                                <form method="POST" style="display:inline;">
                                    <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                                    <button type="submit" name="approve_return">Approve</button>
                                </form>
                            </li>
                        <?php } ?>
                    </ul>
                <?php } else { ?>
                    <p>No pending return requests.</p>
                <?php } ?>
            </section>
        </main>
    </div>
</body>
</html>
