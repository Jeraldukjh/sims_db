<?php
require_once 'db.php';
session_start(); // Ensure the session is started

$error = '';
$success = '';

// Fetch all borrow requests
$stmt = $pdo->prepare("SELECT borrow_requests.request_id, borrow_requests.student_id, borrow_requests.product_id, borrow_requests.due_date, borrow_requests.status, borrow_requests.request_date, users.name FROM borrow_requests JOIN users ON borrow_requests.student_id = users.student_id");
$stmt->execute();
$borrowedItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Borrowed Items</title>
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
            margin-left: 270px;
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
    </style>
</head>
<body>
    <div class="sidebar">
        <h2>Admin Menu</h2>
        <a href="Manage_request.php">Dashboard</a>
        <a href="CRUD.php">Manage Products</a>
        <a href="Approve.php">Students</a>
        <a href="Records.php">Records</a>
        <form action="logout.php" method="POST" style="display: inline;">
            <button type="submit" class="logout">Logout</button>
        </form>
    </div>
    <div class="content">
        <div class="container">
            <h2>Borrowed Items</h2>

            <table>
                <thead>
                    <tr>
                        <th>Request ID</th>
                        <th>Student Name</th>
                        <th>Student ID</th>
                        <th>Product ID</th>
                        <th>Due Date</th>
                        <th>Status</th>
                        <th>Request Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($borrowedItems as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['request_id']); ?></td>
                        <td><?= htmlspecialchars($item['name']); ?></td>
                        <td><?= htmlspecialchars($item['student_id']); ?></td>
                        <td><?= htmlspecialchars($item['product_id']); ?></td>
                        <td><?= htmlspecialchars($item['due_date']); ?></td>
                        <td><?= htmlspecialchars($item['status']); ?></td>
                        <td><?= htmlspecialchars($item['request_date']); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
