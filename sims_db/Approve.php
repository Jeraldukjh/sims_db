<?php
require_once 'db.php';
session_start(); // Ensure the session is started

$error = '';
$success = '';

// Handle approval
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['approve'])) {
    $student_id = trim($_POST["student_id"]);

    try {
        // Check if the student exists in the users table
        $stmt = $pdo->prepare("SELECT id FROM users WHERE student_id = :student_id");
        $stmt->bindValue(':student_id', $student_id, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            // Approve the student by setting the approved column to 1
            $stmt = $pdo->prepare("UPDATE users SET approved = 1 WHERE student_id = :student_id");
            $stmt->bindValue(':student_id', $student_id, PDO::PARAM_STR);
            if ($stmt->execute()) {
                $success = "Student approved successfully!";
            } else {
                throw new Exception("Error: Could not approve the student.");
            }
        } else {
            throw new Exception("Error: Student ID not found.");
        }
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}

// Fetch all students from the users table
$stmt = $pdo->prepare("SELECT id, name, student_id, email, approved FROM users");
$stmt->execute();
$students = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Approve Students</title>
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
        .input-container {
            position: relative;
            margin-bottom: 20px;
        }
        .input-container input {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
            font-size: 16px;
            transition: border-color 0.3s;
        }
        .input-container label {
            position: absolute;
            left: 10px;
            top: 10px;
            font-size: 16px;
            color: #aaa;
            transition: 0.2s ease all;
        }
        .input-container input:focus {
            border-color: #1abc9c;
            outline: none;
        }
        .input-container input:focus + label,
        .input-container input:not(:placeholder-shown) + label {
            top: -10px;
            left: 10px;
            font-size: 12px;
            color: #1abc9c;
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
        /* Zoom + shadow on sidebar hover */
.sidebar a {
    transform: scale(1);
    transition: transform 0.3s ease, background 0.3s ease;
}
.sidebar a:hover {
    transform: scale(1.05);
}

/* Fade & slide-in animation on content container */
@keyframes fadeSlide {
    0% {
        opacity: 0;
        transform: translateY(20px);
    }
    100% {
        opacity: 1;
        transform: translateY(0);
    }
}
.container {
    animation: fadeSlide 0.6s ease-out forwards;
}

/* Input label float animation */
.input-container input:focus + label,
.input-container input:not(:placeholder-shown) + label {
    top: -8px;
    left: 5px;
    font-size: 12px;
    color: #1abc9c;
    background: white;
    padding: 0 5px;
}

/* Table row hover zoom + shadow */
tbody tr {
    transition: transform 0.2s ease, background 0.3s ease;
}
tbody tr:hover {
    transform: scale(1.01);
    background-color: #d1f2eb;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.08);
}

/* Button subtle pop effect */
button {
    transition: background 0.3s ease, transform 0.2s ease;
}
button:hover {
    transform: scale(1.05);
}

/* Logout button animation */
.logout {
    transition: background 0.3s ease, transform 0.2s ease;
}
.logout:hover {
    transform: scale(1.05);
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
            <h2>Approve Students</h2>

            <?php if (!empty($error)) : ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <?php if (!empty($success)) : ?>
                <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>

            <form method="POST" class="mb-4">
                <div class="input-container">
                    <input type="text" name="student_id" id="student_id" placeholder=" " required>
                    <label for="student_id">Student ID</label>
                </div>
                <button type="submit" name="approve" class="btn btn-primary">Approve Student</button>
            </form>

            <h3>Student List</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Student ID</th>
                        <th>Email</th>
                        <th>Approved</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($students as $student): ?>
                    <tr>
                        <td><?= htmlspecialchars($student['id']); ?></td>
                        <td><?= htmlspecialchars($student['name']); ?></td>
                        <td><?= htmlspecialchars($student['student_id']); ?></td>
                        <td><?= htmlspecialchars($student['email']); ?></td>
                        <td><?= $student['approved'] ? 'Yes' : 'No'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>