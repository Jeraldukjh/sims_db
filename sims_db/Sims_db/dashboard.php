<?php
// Enable error reporting for debugging (Only in localhost)
if ($_SERVER['SERVER_NAME'] === 'localhost') { 
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Start the session
session_start();

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: home.php");
    exit();
}

// Include database connection
require 'db.php';

// Retrieve session data
$studentId = $_SESSION['student_id'];
$username = $_SESSION['username'] ?? 'User';
$isAdmin = $_SESSION['is_admin'] ?? false; // Ensure this exists

// Debugging: Check if session variables exist
if (empty($studentId)) {
    die("Error: Session data missing. Please log in again.");
}

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
        $message = "Your borrow request for " . $productId . " is pending admin approval.";
        $stmt = $pdo->prepare("INSERT INTO notifications (student_id, message) VALUES (?, ?)");
        $stmt->execute([$studentId, $message]);

        echo "<script>alert('Borrow request sent successfully!'); window.location.href='dashboard.php';</script>";
        exit();
    } else {
        echo "<script>alert('Item is out of stock!');</script>";
    }
}

// Handle return request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['return_item'])) {
    $productId = $_POST['return_product_id'];

    $stmt = $pdo->prepare("INSERT INTO return_requests (student_id, product_id, status) VALUES (?, ?, 'pending')");
    $stmt->execute([$studentId, $productId]);

    $message = "Your return request for " . $productId . " is pending admin approval.";
    $stmt = $pdo->prepare("INSERT INTO notifications (student_id, message) VALUES (?, ?)");
    $stmt->execute([$studentId, $message]);

    echo "<script>alert('Return request sent successfully!'); window.location.href='dashboard.php';</script>";
    exit();
}

// Handle borrow request approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['approve_borrow']) || isset($_POST['reject_borrow'])) {
        $requestId = $_POST['request_id'];
        $action = isset($_POST['approve_borrow']) ? 'approved' : 'rejected';

        // Update the borrow request status
        $stmt = $pdo->prepare("UPDATE borrow_requests SET status = ? WHERE request_id = ?");
        $stmt->execute([$action, $requestId]);

        // Notify the user about the decision
        $stmt = $pdo->prepare("SELECT student_id, product_id FROM borrow_requests WHERE request_id = ?");
        $stmt->execute([$requestId]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);

        $message = "Your borrow request for product ID " . $request['product_id'] . " has been " . $action . " by the admin.";
        $stmt = $pdo->prepare("INSERT INTO notifications (student_id, message) VALUES (?, ?)");
        $stmt->execute([$request['student_id'], $message]);

        echo "<script>alert('Request " . ucfirst($action) . "!'); window.location.href='dashboard.php';</script>";
        exit();
    }
}

// Fetch borrowed products
$stmt = $pdo->prepare("SELECT p.product_id, p.product_name, DATE_FORMAT(bp.due_date, '%b %d, %Y') AS due_date 
                        FROM borrowed_products bp
                        JOIN products p ON bp.product_id = p.product_id 
                        WHERE bp.student_id = ?");
$stmt->execute([$studentId]);
$borrowedProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch available products
$stmt = $pdo->query("SELECT product_id, product_name, stock FROM products WHERE stock > 0");
$availableProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Fetch pending borrow requests (if admin)
$pendingRequests = [];
if ($isAdmin) {
    $stmt = $pdo->query("SELECT br.request_id, u.name AS username, p.product_name, br.due_date, br.status 
                        FROM borrow_requests br 
                        JOIN users u ON br.student_id = u.student_id
                        JOIN products p ON br.product_id = p.product_id 
                        WHERE br.status = 'pending'");
    $pendingRequests = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fetch notifications
$stmt = $pdo->prepare("SELECT message FROM notifications WHERE student_id = ? ORDER BY created_at DESC LIMIT 5");
$stmt->execute([$studentId]);
$notifications = $stmt->fetchAll(PDO::FETCH_COLUMN);

// Fetch user profile picture
$stmt = $pdo->prepare("SELECT profile_pic FROM users WHERE student_id = ?");
$stmt->execute([$studentId]);
$profilePic = $stmt->fetchColumn();

// Kung walang profile pic, gamitin ang default image
if (!$profilePic) {
    $profilePic = 'uploads/default.png'; // Siguraduhin may default na image sa 'uploads/' folder
    echo "Profile pic path: " . $profilePic;
}

?>





<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - SIMS</title>
    <link rel="stylesheet" href="styles.css">
    <script>
document.getElementById('profile-upload').addEventListener('change', function(event) {
    let reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('profileImage').src = e.target.result;
    }
    reader.readAsDataURL(event.target.files[0]);
});
</script>

    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script>
document.getElementById('profile-upload').addEventListener('change', function(event) {
    let reader = new FileReader();
    reader.onload = function(e) {
        document.getElementById('profileImage').src = e.target.result;
    }
    reader.readAsDataURL(event.target.files[0]);
});
</script>
<script src="JS.js"></script> 
<style>
    /* Hide the default file input */
    #profile-upload {
        display: none;
    }

    /* Custom styling for the "Choose File" button */
    .custom-file-upload {
        display: inline-block;
        background-color: #007bff;
        color: white;
        padding: 10px 15px;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        text-align: center;
        transition: background 0.3s;
    }

    .custom-file-upload:hover {
        background-color: #0056b3;
    }

    /* Style for the upload button */
    .upload-button {
        background-color: #28a745;
        border: none;
        color: white;
        padding: 10px 20px;
        font-size: 14px;
        border-radius: 5px;
        cursor: pointer;
        margin-top: 10px;
        transition: background 0.3s;
    }

    .upload-button:hover {
        background-color: #218838;
    }

    /* Style for profile image */
    .profile-pic {
        display: block;
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        margin-bottom: 10px;
        border: 3px solid #007bff;
    }

    .upload-button:hover {
        background-color: #218838;
    }
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
            background: #16a085;
        }
        .content {
            margin-left: 250px;
            padding: 30px;
            flex-grow: 1;
            transition: margin-left 0.3s ease-in-out;
        }
        .collapsed + .content {
            margin-left: 0;
        }
        .dashboard-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: #1abc9c;
            color: white;
            border-radius: 8px;
        }
        .notification-box, .inventory-box {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
            position: relative;
        }
        .notification-box h3, .inventory-box h3 {
            margin-bottom: 15px;
            color: #2c3e50;
        }
        .notification-box ul, .inventory-box ul {
            list-style: none;
            padding: 0;
        }
        .notification-box ul li, .inventory-box ul li {
            background: #ecf0f1;
            padding: 10px;
            border-radius: 4px;
            margin-bottom: 10px;
            transition: background 0.3s;
        }
        .notification-box ul li:hover, .inventory-box ul li:hover {
            background: #d5dbdb;
        }
        .notification-header, .inventory-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        .notification-header i, .inventory-header i {
            font-size: 24px;
            cursor: pointer;
        }
        .notification-actions, .inventory-actions {
            display: flex;
            justify-content: space-around;
            margin-top: 15px;
        }
        .notification-actions button, .inventory-actions button {
            background: #1abc9c;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
            transition: background 0.3s;
        }
        .notification-actions button:hover, .inventory-actions button:hover {
            background: #16a085;
        }
    </style>
</head>
<body>
    <button class="toggle-btn" id="toggle-btn">☰</button>
    <nav class="sidebar" id="sidebar">
        <h2>SIMS</h2>
        <nav class="sidebar" id="sidebar">
    <h2>SIMS</h2>

    <div class="profile-section">
    <form action="upload_profile.php" method="POST" enctype="multipart/form-data">
        <!-- Display Profile Image -->
        <img src="<?php echo htmlspecialchars($profilePic); ?>" alt="Profile Picture" class="profile-pic" id="profileImage">

        <!-- Custom File Upload -->
        <label for="profile-upload" class="custom-file-upload">Choose File</label>
        <input type="file" id="profile-upload" name="profile_picture" accept="image/*">

        <!-- Upload Button -->
        <button type="submit" name="upload" class="upload-button">Upload</button>
    </form>
    <h3 class="profile-name"><?php echo htmlspecialchars($username); ?></h3>
</div>

    <ul>
        <li><a href="dashboard.php">Dashboard</a></li>
        <li><a href="borrow_items.php">Borrow Items</a></li>
        <li><a href="#">Return Requests</a></li>
        <li><a href="#">Inventory</a></li>
        <li><a href="home.php">Logout</a></li>
    </ul>
</nav>

        <ul>
            <li><a href="dashboard.php".php>Dashboard</a></li>
            <li><a href="borrow_items.php">Borrow Items</a></li>
            <li><a href="#">Return Requests</a></li>
            <li><a href="#">Inventory</a></li>
            <li><a href="home.php">Logout</a></li>
        </ul>
    </nav>
    <main class="content" id="content">
        <div class="dashboard-header">
            <h2>Welcome, <?php echo htmlspecialchars($username); ?>!</h2>
        </div>
        
        <section class="notification-box">
            <div class="notification-header">
                <h3>Notifications</h3>
                <i class="fas fa-bell"></i>
            </div>
            <ul>
                <?php if (!empty($notifications)) { ?>
                    <?php foreach ($notifications as $note) { ?>
                        <li><?php echo htmlspecialchars($note); ?></li>
                    <?php } ?>
                <?php } else { ?>
                    <li>No new notifications.</li>
                <?php } ?>
            </ul>
            
        </section>
        
    </main>
    <script>
        document.getElementById('toggle-btn').addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('collapsed');
            document.getElementById('content').classList.toggle('collapsed');
        });
    </script>
</body>
</html>
