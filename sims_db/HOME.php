<?php
session_start(); // Start the session
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMS - Home</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: url('https://upload.wikimedia.org/wikipedia/commons/thumb/a/a4/Tokyo_Metropolitan_Aoi_High_School.jpg/1280px-Tokyo_Metropolitan_Aoi_High_School.jpg') no-repeat center center fixed;
            background-size: cover;
            text-align: center;
            color: white;
        }
        .header {
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 20px;
            font-size: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 50px;
        }
        .header a {
            color: white;
            text-decoration: none;
            margin: 0 15px;
            font-size: 18px;
        }
        .hero {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 80vh;
            background: rgba(0, 0, 0, 0.5);
            padding: 20px;
        }
        .hero h1 {
            font-size: 52px;
            margin-bottom: 10px;
            font-weight: bold;
        }
        .hero p {
            font-size: 22px;
            max-width: 800px;
            margin-bottom: 20px;
        }
        .cta-button {
            background: #ffcc00;
            color: black;
            padding: 15px 30px;
            font-size: 22px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
        }
        .cta-button:hover {
            background: #e6b800;
        }
        .footer {
            background: rgba(0, 0, 0, 0.7);
            color: white;
            padding: 10px;
            font-size: 16px;
            position: absolute;
            bottom: 0;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="header">
        <div class="logo"><strong>SIMS</strong></div>
        <div>
            <a href="Home.php">Home</a>
            <a href="featured.php">Features</a>
            <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true): ?>
                <a href="admin.php">Admin</a>
            <?php else: ?>
                <a href="admin_login.php">Admin Login</a> <!-- Link para sa admin login -->
            <?php endif; ?>
            <a href="login.php">Log In</a>
            <a href="REGISTER.php" class="cta-button">Sign Up</a>
        </div>
    </div>

    <div class="hero">
        <h1>🔧 Manage Your Inventory Seamlessly</h1>
        <p>Track, borrow, and return IT hardware components efficiently with our smart inventory system.</p>
        <?php if (isset($_SESSION['student_id'])) { ?>
            <a href="REGISTER.php" class="cta-button">Go to Dashboard</a> <!-- Show dashboard link if logged in -->
        <?php } else { ?>
            <a href="REGISTER.php" class="cta-button">Get Started</a> <!-- Show sign-up link if not logged in -->
        <?php } ?>
    </div>

    <div class="footer">
        &copy; 2025 SIMS. All rights reserved.
    </div>
</body>
</html>