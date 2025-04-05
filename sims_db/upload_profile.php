<?php
// Start the session
session_start();

// Check if the user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: home.php");
    exit();
}

// Include database connection
require 'db.php';

$studentId = $_SESSION['student_id'];

// Check if the form is submitted
if (isset($_POST['upload'])) {
    // Check if a file is uploaded
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        // Get the file details
        $fileName = $_FILES['profile_picture']['name'];
        $fileTmpName = $_FILES['profile_picture']['tmp_name'];
        $fileSize = $_FILES['profile_picture']['size'];
        $fileType = $_FILES['profile_picture']['type'];
        
        // Validate file type (image types only)
        $validExtensions = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($fileType, $validExtensions)) {
            // Set the upload directory
            $uploadDir = 'uploads/';
            
            // Check if the uploads directory exists, if not, create it
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true); // Create the directory with appropriate permissions
            }

            $newFileName = uniqid() . '_' . basename($fileName);
            $uploadFilePath = $uploadDir . $newFileName;

            // Move the uploaded file to the uploads directory
            if (move_uploaded_file($fileTmpName, $uploadFilePath)) {
                // Update the profile picture in the database
                $stmt = $pdo->prepare("UPDATE users SET profile_pic = ? WHERE student_id = ?");
                $stmt->execute([$uploadFilePath, $studentId]);

                // Redirect to the dashboard with success message
                header("Location: dashboard.php");
                exit();
            } else {
                echo "Error uploading the file.";
            }
        } else {
            echo "Invalid file type. Only images are allowed.";
        }
    } else {
        echo "No file uploaded or there was an error.";
    }
}
?>
