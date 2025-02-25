<?php
// Enable error reporting for debugging (Only in localhost)
if ($_SERVER['SERVER_NAME'] === 'localhost') { 
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Start session
session_start();
if (!isset($_SESSION['student_id'])) {
    header("Location: home.php");
    exit();
}

require 'db.php'; // Make sure this file correctly initializes $pdo (PDO) and $conn (MySQLi)

// Get session data
$studentId = $_SESSION['student_id'];
$username = $_SESSION['username'] ?? 'User';
$isAdmin = $_SESSION['is_admin'] ?? false;

if (empty($studentId)) {
    die("Error: Session data missing. Please log in again.");
}

// Handle category filter (Using prepared statement for security)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category'])) {
    $category = trim($_POST['category']); // Siguraduhin walang extra spaces

    $stmt = $pdo->prepare("SELECT * FROM products WHERE LOWER(TRIM(category)) = LOWER(TRIM(?))");
    $stmt->execute([$category]);

    $filteredProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($filteredProducts);

    exit();
}

// Fetch available products with categories
$stmt = $pdo->query("SELECT product_id, product_name, stock, category FROM products WHERE stock > 0");
$availableProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle borrow request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow_item'])) {
    $productId = $_POST['product_id'];
    $dueDate = date('Y-m-d', strtotime('+7 days'));

    // Check stock availability
    $stmt = $pdo->prepare("SELECT stock FROM products WHERE product_id = ?");
    $stmt->execute([$productId]);
    $stock = $stmt->fetchColumn();

    if ($stock > 0) {
        // Insert borrow request
        $stmt = $pdo->prepare("INSERT INTO borrow_requests (student_id, product_id, due_date, status) VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$studentId, $productId, $dueDate]);

        // Send notification
        $message = "Your borrow request for item ID $productId is pending admin approval.";
        $stmt = $pdo->prepare("INSERT INTO notifications (student_id, message) VALUES (?, ?)");
        $stmt->execute([$studentId, $message]);

        echo json_encode(["status" => "success", "message" => "Borrow request sent successfully!"]);
        exit();
    } else {
        echo json_encode(["status" => "error", "message" => "Item is out of stock!"]);
        exit();
    }
}
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Borrow Items - SIMS</title>
    <link rel="stylesheet" href="styles.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f4f7f6;
            color: #333;
            margin: 0;
            display: flex;
        }
        .sidebar {
            width: 250px;
            background-color: #2c3e50;
            color: white;
            padding: 20px;
            position: fixed;
            height: 100vh;
            top: 0;
            left: 0;
            transition: transform 0.3s ease-in-out;
        }
        .sidebar.collapsed {
            transform: translateX(-100%);
        }
        .sidebar h2 {
            text-align: center;
        }
        .sidebar ul {
            list-style: none;
            padding: 0;
        }
        .sidebar ul li a {
            color: white;
            text-decoration: none;
            display: block;
            padding: 10px;
            border-radius: 4px;
            transition: background 0.3s;
        }
        .sidebar ul li a:hover {
            background-color: #1abc9c;
        }
        .toggle-btn {
            position: fixed;
            top: 20px;
            left: 20px;
            background: #1abc9c;
            color: white;
            border: none;
            padding: 10px;
            cursor: pointer;
            border-radius: 5px;
            transition: background 0.3s;
            z-index: 1000;
        }
        .toggle-btn:hover {
            background: #2471a3;
        }
        .content {
            margin-left: 250px;
            padding: 30px;
            flex-grow: 1;
            transition: margin-left 0.3s ease-in-out;
        }
        .content.collapsed {
            margin-left: 0;
        }
        .dashboard-header {
            background: #1abc9c;
            color: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }
        .inventory-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        th, td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background: #1abc9c;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        button {
            background: #1abc9c;
            color: white;
            border: none;
            padding: 8px 12px;
            cursor: pointer;
            border-radius: 5px;
            transition: background 0.3s;
        }
        button:hover {
            background: #2471a3;
        }
        .search-bar {
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .search-bar input {
            width: 70%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
            margin-right: 10px;
        }
        .search-bar select {
            width: 25%;
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
    </style>
</head>

<body>
    <button class="toggle-btn" id="toggle-btn">☰</button>
    <nav class="sidebar" id="sidebar">
        <h2>SIMS</h2>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="borrow_items.php" class="active">Borrow Items</a></li>
            <li><a href="return_requests.php">Return Requests</a></li>
            <li><a href="products.php">Inventory</a></li>
            <li><a href="home.php">Logout</a></li>
        </ul>
    </nav>

    <main class="content" id="content">
        <div class="dashboard-header">
            <h2>Borrow Items</h2>
        </div>
        
        <section class="inventory-box">
            <h3>Available Items</h3>
            <div class="search-bar">
                <input type="text" id="search" placeholder="Search for items...">
                <select id="filter-category">
                    <option value="all">All Categories</option>
                    <option value="input-devices">Input Devices</option>
                    <option value="output-devices">Output Devices</option>
                    <option value="storage-devices">Storage Devices</option>
                </select>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Category</th> <!-- Dito na lalabas ang category -->
                        <th>Stock</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="item-table">
                    <?php foreach ($availableProducts as $product): ?>
                    <tr data-category="<?php echo htmlspecialchars($product['category'] ?? ''); ?>">
                        <td><?php echo htmlspecialchars($product['product_name'] ?? ''); ?></td>
                        <td><?php echo htmlspecialchars($product['category'] ?? ''); ?></td> <!-- Nilagay ang Category -->
                        <td><?php echo htmlspecialchars($product['stock'] ?? ''); ?></td>
                        <td>
                            <button class="borrow-request" data-id="<?php echo htmlspecialchars($product['product_id'] ?? ''); ?>">
                                Request Borrow
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>

    <script>
        $(document).ready(function() {
            $('#search').on('keyup', function() {
                let value = $(this).val().toLowerCase();
                $('#item-table tr').filter(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
                });
            });

            $('#filter-category').on('change', function() {
                let category = $(this).val();
                $('#item-table tr').each(function() {
                    if (category === "all" || $(this).data('category') === category) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            });

            $('#toggle-btn').click(function() {
                $('#sidebar').toggleClass('collapsed');
                $('#content').toggleClass('collapsed');
            });

            $('.borrow-request').click(function() {
                var productId = $(this).data('id');

                $.ajax({
                    type: 'POST',
                    url: 'borrow_items.php', // URL ng PHP file na nagha-handle ng borrow request
                    data: { borrow_item: true, product_id: productId },
                    success: function(response) {
                        var result = JSON.parse(response);
                        alert(result.message);
                    },
                    error: function() {
                        alert('An error occurred while processing your request.');
                    }
                });a
            });
        });
    </script>
</body>
</html>