<?php

if (!isset($conn)) { 
    include 'db_connect.php';
}

$page_url = $_SERVER['REQUEST_URI'];

$visitor_ip = $_SERVER['REMOTE_ADDR'];

if (isset($conn) && $conn) {
    $stmt = $conn->prepare("INSERT INTO PageViews (PageURL, VisitorIP) VALUES (?, ?)");
    $stmt->bind_param("ss", $page_url, $visitor_ip);
    $stmt->execute();
    $stmt->close();
}
?>