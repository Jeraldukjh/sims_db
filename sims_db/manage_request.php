<?php
session_start();
require_once __DIR__ . '/db.php';

// Ensure the user is logged in as admin
if (!isset($_SESSION['is_admin'])) {
    header("Location: Home.php");
    exit();
}

if (!isset($pdo)) {
    die("Database connection failed.");
}

try {
    // Fetch pending borrow requests
    $stmt = $pdo->prepare("
    SELECT br.request_id, br.due_date, COALESCE(u.name, 'Unknown') AS student_name, p.product_name 
    FROM borrow_requests br
    LEFT JOIN users u ON br.student_id = u.id
    LEFT JOIN products p ON br.product_id = p.product_id
    WHERE br.status = 'pending'
");

    $stmt->execute();
    $pendingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch pending return requests
    $stmt = $pdo->prepare("
        SELECT rr.request_id, u.name AS student_name, p.product_name 
        FROM return_requests rr
        JOIN users u ON rr.student_id = u.id  -- FIXED: Changed u.student_id to u.id
        JOIN products p ON rr.product_id = p.product_id
        WHERE rr.status = 'pending'
    ");
    $stmt->execute();
    $pendingReturns = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error fetching data: " . htmlspecialchars($e->getMessage()));
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SIMS</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #ecf0f1;
            color: #2c3e50;
            margin: 0;
            display: flex;
        }
        h2 {
            text-align: center;
            font-weight: 600;
            color: #1abc9c;
        }
        .sidebar {
            width: 250px;
            background-color: #34495e;
            padding: 20px;
            color: white;
            height: 100vh;
            position: fixed;
        }
        .sidebar h2 {
            text-align: center;
            margin-bottom: 20px;
        }
        .sidebar a {
            display: block;
            color: white;
            padding: 10px;
            text-decoration: none;
            border-radius: 5px;
            margin: 5px 0;
            transition: background 0.3s;
        }
        .sidebar a:hover {
            background-color: #1abc9c;
        }
        .content {
            margin-left: 270px; /* Space for sidebar */
            padding: 20px;
            width: calc(100% - 270px);
        }
        .container {
            background: white;
            padding: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #1abc9c;
            color: white;
            font-weight: 600;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        tr:hover {
            background-color: #d1f2eb;
        }
        button {
            background: #1abc9c;
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 5px;
            transition: background 0.3s ease-in-out;
            font-weight: 600;
        }
        button:hover {
            background: #16a085;
        }
        .reject {
            background: #e74c3c;
        }
        .reject:hover {
            background: #c0392b;
        }
        .logout {
            background: #e74c3c;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            float: right;
            margin-bottom: 20px;
        }
        .logout:hover {
            background: #c0392b;
        }
        .button-group {
            display: flex;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Menu</h2>
        <a href="manage_request.php">Dashboard</a>
        <a href="CRUD.php">Manage Products</a>
        <a href="Approve.php">Students</a>
        <form action="logout.php" method="POST" style="display: inline;">
            <button type="submit" class="logout">Logout</button>
        </form>
    </div>

    <div class="content">
        <div class="container">
            <h2>Admin Dashboard</h2>

            <!-- Pending Borrow Requests -->
            <h3>Pending Borrow Requests</h3>
            <table>
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Student Name</th>
                        <th>Product Name</th>
                        <th>Due Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pendingRequests)): ?>
                        <?php foreach ($pendingRequests as $request): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($request['request_id']); ?></td>
                                <td><?php echo htmlspecialchars($request['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($request['product_name']); ?></td>
                                <td><?php echo htmlspecialchars($request['due_date']); ?></td>
                                <td>
                                    <div class="button-group">
                                        <button class="approve-request" data-id="<?php echo $request['request_id']; ?>">Approve</button>
                                        <button class="reject reject-request" data-id="<?php echo $request['request_id']; ?>">Reject</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="5" style="text-align:center;">No pending requests.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <!-- Pending Return Requests -->
            <h3>Pending Return Requests</h3>
            <table>
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Student Name</th>
                        <th>Product Name</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($pendingReturns)): ?>
                        <?php foreach ($pendingReturns as $return): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($return['request_id']); ?></td>
                                <td><?php echo htmlspecialchars($return['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($return['product_name']); ?></td>
                                <td>
                                    <div class="button-group">
                                        <button class="approve-return" data-id="<?php echo $return['request_id']; ?>">Approve</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="text-align:center;">No pending return requests.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $(".approve-request").click(function () {
                let requestId = $(this).data("id");

                if (!confirm("Are you sure you want to approve this borrow request?")) return;

                $.post("approve_request.php", { request_id: requestId }, function (response) {
                    alert(response);
                    location.reload();
                }).fail(function () {
                    alert("Error approving request.");
                });
            });

            $(".reject-request").click(function () {
                let requestId = $(this).data("id");

                if (!confirm("Are you sure you want to reject this borrow request?")) return;

                $.post("reject_request.php", { request_id: requestId }, function (response) {
                    alert(response);
                    location.reload();
                }).fail(function () {
                    alert("Error rejecting request.");
                });
            });

            $(".approve-return").click(function () {
                let requestId = $(this).data("id");

                if (!confirm("Are you sure you want to approve this return request?")) return;

                $.post("approve_return.php", { request_id: requestId }, function (response) {
                    alert(response);
                    location.reload();
                }).fail(function () {
                    alert("Error approving return.");
                });
            });
        });
    </script>
</body>
</html>