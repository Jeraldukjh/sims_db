<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] != 1) {
    header("Location: home.php");
    exit();
}

require_once __DIR__ . '/db.php';

if (!isset($pdo)) {
    die("Database connection failed.");
}

// Fetch pending borrow requests
$stmt = $pdo->prepare("SELECT br.request_id, br.student_id, br.product_id, br.due_date, br.status, 
                              p.product_name, u.name AS student_name 
                       FROM borrow_requests br 
                       JOIN products p ON br.product_id = p.product_id 
                       JOIN users u ON br.student_id = u.student_id 
                       WHERE br.status = 'pending'");
$stmt->execute();
$pendingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle request approval or rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['request_id'])) {
    $requestId = intval($_POST['request_id']); // Ensure request_id is an integer
    $response = [];

    try {
        $pdo->beginTransaction(); // Start a transaction

        if (isset($_POST['approve'])) {
            $stmt = $pdo->prepare("UPDATE borrow_requests SET status = 'approved' WHERE request_id = ?");
            $stmt->execute([$requestId]);

            $stmt = $pdo->prepare("UPDATE products 
                                   SET stock = CASE WHEN stock > 0 THEN stock - 1 ELSE 0 END 
                                   WHERE product_id = (SELECT product_id FROM borrow_requests WHERE request_id = ?)");
            $stmt->execute([$requestId]);

            $pdo->commit();
            $response['message'] = 'Request approved successfully!';
        } elseif (isset($_POST['reject'])) {
            $stmt = $pdo->prepare("UPDATE borrow_requests SET status = 'rejected' WHERE request_id = ?");
            $stmt->execute([$requestId]);

            $pdo->commit();
            $response['message'] = 'Request rejected successfully!';
        }

    } catch (PDOException $e) {
        $pdo->rollBack();
        $response['message'] = 'Database error: ' . addslashes($e->getMessage());
    }

    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode($response);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Borrow Requests - SIMS</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #ecf0f1;
            color: #2c3e50;
            margin: 0;
            padding: 20px;
        }

        h2 {
            text-align: center;
            font-weight: 600;
            color: #1abc9c;
        }

        .container {
            width: 90%;
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            border-radius: 10px;
            overflow: hidden;
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

        @media (max-width: 768px) {
            table, th, td {
                font-size: 14px;
            }
            button {
                padding: 6px 10px;
                font-size: 14px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Pending Borrow Requests</h2>
        
        <form action="logout.php" method="POST" style="display: inline;">
            <button type="submit" class="logout">Logout</button>
        </form>

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
                            <td><?php echo $request['request_id']; ?></td>
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
    </div>

    <script>
        $(document).ready(function() {
            $('.approve-request, .reject-request').click(function() {
                var requestId = $(this).data('id');
                var action = $(this).hasClass('approve-request') ? 'approve' : 'reject';

                $.post("manage_requests.php", { [action]: true, request_id: requestId }, function(response) {
    alert(response.message);
    location.reload();
}, "json").fail(function(jqXHR, textStatus, errorThrown) {
    console.error("Error details:", textStatus, errorThrown);
    console.error("Response:", jqXHR.responseText);
    alert('An error occurred while processing your request. Check console for details.');
});
            });
        });
    </script>
</body>
</html> 