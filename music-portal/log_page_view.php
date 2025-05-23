<?php
// This file will be included at the top of every page you want to track.

// Ensure db_connect.php is included only once globally if possible,
// or ensure this file includes it.
if (!isset($conn)) { // Check if $conn is already available (e.g., from an earlier include)
    include 'db_connect.php'; // Adjust path if necessary
}

// Get the current page URL
$page_url = $_SERVER['REQUEST_URI'];

// Get the visitor's IP address
// This is a basic way to get IP; for more robust solutions, consider proxies etc.
$visitor_ip = $_SERVER['REMOTE_ADDR'];

// Insert the page view into the database
if (isset($conn) && $conn) { // Ensure connection is established
    $stmt = $conn->prepare("INSERT INTO PageViews (PageURL, VisitorIP) VALUES (?, ?)");
    $stmt->bind_param("ss", $page_url, $visitor_ip);
    $stmt->execute();
    $stmt->close();
    // Do NOT close $conn here, as other parts of the page might need it.
    // The connection should be closed at the very end of the main script.
}
?>