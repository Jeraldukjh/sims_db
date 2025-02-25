<?php
session_start();
require_once __DIR__ . '/../sims_db/db.php'; // Ensure $pdo is used

// Debugging Mode (only enable errors on localhost)
if ($_SERVER['SERVER_NAME'] === 'localhost') { 
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

$loginError = "";

// Initialize variables
$admin_id = '';
$password = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $admin_id = isset($_POST['admin_id']) ? htmlspecialchars(strip_tags(trim($_POST['admin_id']))) : '';
    $password = isset($_POST['password']) ? htmlspecialchars(strip_tags(trim($_POST['password']))) : '';

    try {
        // Fixed Query: Separate placeholders for student_id and email
        $stmt = $pdo->prepare("SELECT id, student_id, name, password, is_admin 
                               FROM users 
                               WHERE student_id = :student_id OR email = :email");
        $stmt->bindValue(':student_id', $admin_id, PDO::PARAM_STR);
        $stmt->bindValue(':email', $admin_id, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            if (password_verify($password, $user['password'])) {
                if ($user['is_admin'] == 1) { 
                    $_SESSION['admin_id'] = $user['id'];
                    $_SESSION['username'] = $user['name'];
                    $_SESSION['is_admin'] = $user['is_admin']; 

                    // Redirect
                    $redirectUrl = isset($_GET['redirect']) ? $_GET['redirect'] : 'manage_request.php';
                    header("Location: $redirectUrl");
                    exit();
                } else {
                    $loginError = "⚠️ You do not have admin access.";
                }
            } else {
                $loginError = "❌ Incorrect password. Please try again.";
            }
        } else {
            $loginError = "⚠️ No account found with this Admin ID.";
        }
    } catch (PDOException $e) {
        die("Database Error: " . $e->getMessage());
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - SIMS</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            color: #fff;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background: url('https://upload.wikimedia.org/wikipedia/commons/thumb/a/a4/Tokyo_Metropolitan_Aoi_High_School.jpg/1280px-Tokyo_Metropolitan_Aoi_High_School.jpg') no-repeat center center fixed;
            background-size: cover;
        }
        .container {
            background: rgba(0, 0, 0, 0.7);
            border-radius: 15px;
            padding: 30px;
            text-align: center;
        }
        .error-message {
            color: #ff4c4c;
            font-size: 14px;
            margin-bottom: 10px;
        }
        input, button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: none;
            border-radius: 5px;
        }
        button {
            background: #3498db;
            color: white;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover {
            background: #2980b9;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Admin Login to SIMS</h2>
        <form method="POST">
            <?php if (!empty($loginError)) { echo "<p class='error-message'>$loginError</p>"; } ?>
            <label for="admin_id">Admin ID</label>
            <input type="text" name="admin_id" placeholder="Enter your Admin ID" required>
            <label for="password">Password</label>
            <input type="password" name="password" placeholder="Enter password" required>
            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>