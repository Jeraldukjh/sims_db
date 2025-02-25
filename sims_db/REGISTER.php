<?php
require_once 'db.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST["name"]);
    $student_id = trim($_POST["student_id"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);
    $role = isset($_POST["role"]) ? $_POST["role"] : 'user'; // Default role is 'user'

    try {
        // ✅ Validate Inputs
        if (!preg_match("/^[a-zA-Z ]*$/", $name)) {
            throw new Exception("Error: Name should only contain letters and spaces.");
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Error: Invalid email format.");
        }
        if (!preg_match("/^[0-9]{8,10}$/", $student_id)) {
            throw new Exception("Error: Student ID must be 8-10 digits.");
        }
        if ($password !== $confirm_password) {
            throw new Exception("Error: Passwords do not match.");
        }

        // ✅ Hash the password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // ✅ Check if email or student ID already exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = :email OR student_id = :student_id");
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':student_id', $student_id, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            throw new Exception("Error: Email or Student ID already exists.");
        }

        // ✅ Ensure only Admins can create Admin accounts
        if ($role === 'admin') {
            if (!isset($_SESSION['user_role']) || $_SESSION['user_role'] !== 'admin') {
                throw new Exception("Error: Only an Admin can create another Admin account.");
            }
        }

        // ✅ Insert user into database
        $stmt = $pdo->prepare("INSERT INTO users (name, student_id, email, password, role) VALUES (:name, :student_id, :email, :password, :role)");
        $stmt->bindValue(':name', $name, PDO::PARAM_STR);
        $stmt->bindValue(':student_id', $student_id, PDO::PARAM_STR);
        $stmt->bindValue(':email', $email, PDO::PARAM_STR);
        $stmt->bindValue(':password', $hashed_password, PDO::PARAM_STR);
        $stmt->bindValue(':role', $role, PDO::PARAM_STR);

        if ($stmt->execute()) {
            echo "<script>alert('Registration successful! Redirecting to login...'); window.location.href='login.php';</script>";
            exit();
        } else {
            throw new Exception("Error: Could not register. Please try again.");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - SIMS</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        body {
            font-family: Arial, sans-serif;
            background: url('https://upload.wikimedia.org/wikipedia/commons/thumb/a/a4/Tokyo_Metropolitan_Aoi_High_School.jpg/1280px-Tokyo_Metropolitan_Aoi_High_School.jpg') no-repeat center center fixed;
            background-size: cover;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
        }
        .container {
            background: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 30px;
            width: 400px;
            border-radius: 10px;
            text-align: center;
        }
        .form-control {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
        .btn-custom {
            background: #3498db;
            color: white;
            width: 100%;
        }
        .btn-custom:hover {
            background: #2980b9;
        }
        .login-link a {
            color: #f1c40f;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Create an Account</h2>

        <!-- Success & Error Message -->
        <?php if (!empty($error)) : ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form id="registerForm" method="POST">
            <div class="mb-3">
                <label class="form-label">Full Name</label>
                <input type="text" class="form-control" name="name" placeholder="Enter your full name" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Student ID</label>
                <input type="text" class="form-control" name="student_id" placeholder="Enter Student ID" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" name="email" placeholder="Enter your email" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password" placeholder="Enter password" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="confirm_password" name="confirm_password" placeholder="Confirm your password" required>
            </div>

            <!-- Role Selection (Only visible if logged in as Admin) -->
            <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') : ?>
                <div class="mb-3">
                    <label class="form-label">Role</label>
                    <select class="form-control" name="role">
                        <option value="user">User</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
            <?php endif; ?>

            <button type="submit" class="btn btn-custom">Register</button>
        </form>

        <div class="login-link mt-3">
            <p>Already have an account? <a href="login.php">Login here</a></p>
        </div>
    </div>

    <script>
        document.getElementById("registerForm").addEventListener("submit", function(event) {
            let password = document.getElementById("password").value.trim();
            let confirmPassword = document.getElementById("confirm_password").value.trim();
            if (password !== confirmPassword) {
                alert("Passwords do not match.");
                event.preventDefault();
            }
        });
    </script>
</body>
</html>
