<?php
session_start(); // Start the session
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIMS - Features</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: url('https://upload.wikimedia.org/wikipedia/commons/thumb/a/a4/Tokyo_Metropolitan_Aoi_High_School.jpg/1280px-Tokyo_Metropolitan_Aoi_High_School.jpg') no-repeat center center fixed;
            background-size: cover;
            color: white;
        }
        .header {
            background: rgba(0, 0, 0, 0.8); /* Darker background for better contrast */
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
        .features {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 80vh;
            background: rgba(0, 0, 0, 0.7); /* Darker background for better contrast */
            padding: 40px;
            text-align: justify;
        }
        .features h1 {
            font-size: 42px;
            margin-bottom: 20px;
            font-weight: bold;
            text-align: center;
        }
        .features p {
            font-size: 18px;
            max-width: 800px;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .feature-list {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 20px;
        }
        .feature-card {
            background: rgba(255, 255, 255, 0.2); /* Slightly more opaque for better visibility */
            padding: 20px;
            width: 300px;
            border-radius: 12px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
            text-align: center;
            transition: all 0.3s;
        }
        .feature-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.4);
        }
        .feature-card i {
            font-size: 40px;
            margin-bottom: 10px;
            color: #ffcc00;
        }
        .feature-card h3 {
            font-size: 20px;
            margin-bottom: 10px;
        }
        .feature-card p {
            font-size: 16px;
            text-align: justify;
        }
        .footer {
            background: rgba(0, 0, 0, 0.8); /* Darker footer for better contrast */
            color: white;
            padding: 10px;
            font-size: 16px;
            position: absolute;
            bottom: 0;
            width: 100%;
            text-align: center;
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
    <div class="features">
        <h1>🎯 Objectives</h1>
        <p>IT students often need to manage various resources, such as hardware components, software licenses, and study materials. Without an effective inventory management system, students can struggle to keep track of what they have, what they need, and when to reorder supplies. This can lead to wasted time and increased costs. The Student Inventory Management System (SIMS) aims to provide a structured solution for managing these resources efficiently.</p>
        <div class="feature-list">
            <div class="feature-card">
                <i class="fas fa-compass"></i>
                <h3>User-Friendly</h3>
                <p>To create an intuitive platform for easy navigation and management of inventory.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-bell"></i>
                <h3>Automated Alerts</h3>
                <p>Notify students when the items need to be returned.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-clock"></i>
                <h3>Real-Time Inventory Tracking</h3>
                <p>To allow students to monitor their resources and supplies in real-time.</p>
            </div>
            <div class="feature-card">
                <i class="fas fa-dollar-sign"></i>
                <h3>Reduce Wasted Time and Costs</h3>
                <p>Streamline inventory management, minimizing the time spent searching for or managing hardware components and reducing unnecessary expenses.</p>
            </div>
        </div>
    </div>
    <div class="footer">
        © 2025 SIMS. All rights reserved.
    </div>
</body>
</html>