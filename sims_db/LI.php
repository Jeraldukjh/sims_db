<?php
$host = "localhost"; 
$user = "root";      
$pass = "";         
$db = "sims_db";      

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$student_id = $_POST['student_id'];
$password = $_POST['password'];

$sql = "SELECT * FROM users WHERE student_id = '$student_id' AND password = '$password'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo "success"; 
} else {
    echo "failed"; 
}

$conn->close();
?>
