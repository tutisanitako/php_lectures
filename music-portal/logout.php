<?php
session_start(); // Start the session

// Unset all of the session variables
$_SESSION = array();

// Destroy the session.
session_destroy();

// Redirect to the homepage or login page
header("Location: index.php"); // This assumes index.php is in the same (parent) directory
exit();
?>