<?php
session_start();
$_SESSION = []; // Clear all session variables
session_unset(); // Unset session data
session_destroy(); // Destroy session
header("Location: Home.php");
exit();
?>
