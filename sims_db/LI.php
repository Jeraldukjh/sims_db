<?php
$host = "localhost";  // Change this if needed
$user = "root";       // Default XAMPP MySQL user
$pass = "";           // Default is empty in XAMPP
$db = "sims_db";      // Make sure this matches your database

$conn = new mysqli($host, $user, $pass, $db);

// Check for connection error
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Get user input
$student_id = $_POST['student_id'];
$password = $_POST['password'];

// Query the database
$sql = "SELECT * FROM users WHERE student_id = '$student_id' AND password = '$password'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "success";  // ✅ Correct output for JavaScript to detect
} else {
    echo "failed";  // ❌ Not an HTML page
}

$conn->close();
?>
