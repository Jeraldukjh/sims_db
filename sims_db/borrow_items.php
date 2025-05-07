<?php
// Display errors for debugging
if (isset($_GET['debug']) && $_GET['debug'] == '1') {
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

require 'db.php'; // Ensure this file correctly initializes $pdo

// Get session data
$studentId = $_SESSION['student_id'];
$username = $_SESSION['username'] ?? 'User  ';
$isAdmin = $_SESSION['is_admin'] ?? false;

if (empty($studentId)) {
    die("Error: Session data missing. Please log in again.");
}

// Fetch available products with categories
$stmt = $pdo->query("SELECT product_id, product_name, stock, category, image_url FROM products WHERE stock > 0");
$availableProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Handle category filter (Using prepared statement for security)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['category'])) {
    $category = trim($_POST['category']);
    
    $stmt = $pdo->prepare("SELECT * FROM products WHERE LOWER(TRIM(category)) = LOWER(TRIM(?))");
    $stmt->execute([$category]);
    
    $filteredProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($filteredProducts);
    exit();
}

// Handle borrow request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['borrow_item'])) {
    $productId = $_POST['product_id'];
    
    // Set due date to 1 day from now
    $dueDate = date('Y-m-d', strtotime('+1 day'));

    // Check stock availability
    $stmt = $pdo->prepare("SELECT stock FROM products WHERE product_id = ?");
    $stmt->execute([$productId]);
    $stock = $stmt->fetchColumn();

    if ($stock > 0) {   
        $stmt = $pdo->prepare("INSERT INTO borrow_requests (student_id, product_id, due_date, status) VALUES (?, ?, ?, 'pending')");
        $stmt->execute([$studentId, $productId, $dueDate]);

        $stmt = $pdo->prepare("UPDATE products SET stock = stock - 1 WHERE product_id = ?");
        $stmt->execute([$productId]);

        // Generate a unique barcode
        $barcode = uniqid('barcode_');
        $message = "Your borrow request for item ID $productId is pending admin approval. Barcode: $barcode";
        $stmt = $pdo->prepare("INSERT INTO notifications (student_id, message) VALUES (?, ?)");
        $stmt->execute([$studentId, $message]);

        echo json_encode(["status" => "success", "message" => "Borrow request sent successfully! Barcode: $barcode"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Item is out of stock!"]);
    }
    exit();
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
                    .container {
                padding: 20px;
            }

            .item-grid {
                display: flex;
                flex-wrap: wrap;
                justify-content: space-around;
            }

            .item-box {
                border: 1px solid #ccc;
                border-radius: 8px;
                margin: 10px;
                padding: 15px;
                width: calc(33% - 20px); /* Adjust width for 3 boxes in a row */
                box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
                text-align: center;
            }

            .item-box img {
                max-width: 100%;
                height: auto;
                border-radius: 8px 8px 0 0; 
            }

            .button {
                background-color: #4CAF50;
                color: white;
                border: none;
                padding: 10px;
                cursor: pointer;
                border-radius: 5px;
            }

            .button:hover {
                background-color: #45a049;
            }
        .item-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            margin: 10px;
            width: calc(30% - 20px); /* Adjust width for 3 items per row */
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
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
        @keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.item-box {
    border: 1px solid #ccc;
    border-radius: 8px;
    margin: 10px;
    padding: 15px;
    width: calc(33% - 20px);
    box-shadow: 2px 2px 10px rgba(0, 0, 0, 0.1);
    text-align: center;
    background-color: #fff;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    animation: fadeInUp 0.5s ease forwards;
    opacity: 0;
}

.item-box:hover {
    transform: scale(1.05);
    box-shadow: 4px 4px 20px rgba(0, 0, 0, 0.15);
    z-index: 1;
}



.item-box:nth-child(1) { animation-delay: 0s; }
.item-box:nth-child(2) { animation-delay: 0.1s; }
.item-box:nth-child(3) { animation-delay: 0.2s; }
.item-box:nth-child(4) { animation-delay: 0.3s; }
.item-box:nth-child(5) { animation-delay: 0.4s; }

 
    </style>
</head>

<body>
    <button class="toggle-btn" id="toggle-btn">☰</button>
    <nav class="sidebar" id="sidebar">
        <h2>SIMS</h2>
        <ul>
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="borrow_items.php" class="active">Borrow Items</a></li>
            <li><a href="return_items.php">Return Requests</a></li>
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
                    </tr>
                </thead>
                <div class="item-grid" id="item-grid">
    <?php foreach ($availableProducts as $product): ?>
    <div class="item-box" data-category="<?php echo htmlspecialchars($product['category'] ?? ''); ?>">
        <img src="<?php echo htmlspecialchars($product['image_url'] ?? ''); ?>" alt="<?php echo htmlspecialchars($product['product_name'] ?? ''); ?>">
        <h4><?php echo htmlspecialchars($product['product_name'] ?? ''); ?></h4>
        <p><strong>Category:</strong> <?php echo htmlspecialchars($product['category'] ?? ''); ?></p>
        <p><strong>Stock:</strong> <?php echo htmlspecialchars($product['stock'] ?? ''); ?></p>
        <button class="borrow-request" data-id="<?php echo htmlspecialchars($product['product_id'] ?? ''); ?>">Request Borrow</button>
    </div>
    <?php endforeach; ?>
</div>
                </tbody>
            </table>
        </section>
    </main>

    <script>
        $(document).ready(function() {
            $('#search').on('keyup', function() {
    let value = $(this).val().toLowerCase();
    $('.item-box').filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
    });
});

$('#filter-category').on('change', function() {
    let category = $(this).val().toLowerCase();
    $('.item-box').each(function() {
        let boxCategory = $(this).data('category').toLowerCase().trim();
        $(this).toggle(category === "all" || boxCategory.includes(category.replace("-", " ")));
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
                    url: 'borrow_items.php',
                    data: { borrow_item: true, product_id: productId },
                    success: function(response) {
                        var result = JSON.parse(response);
                        alert(result.message);
                    },
                    error: function() {
                        alert('An error occurred while processing your request.');
                    }
                });
            });
        });
    </script>
</body>
</html>
