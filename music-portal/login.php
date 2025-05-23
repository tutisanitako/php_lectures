<?php
session_start(); // Start session at the very beginning

include 'db_connect.php'; // Include your database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password']; // This will be compared directly to the stored password

    // Clear any previous login/signup messages (important for redirect logic below)
    unset($_SESSION['login_error']);
    unset($_SESSION['signup_success_message']);
    unset($_SESSION['modal_to_open']); // Clear this proactively

    // Prepare a SQL statement to prevent SQL injection
    // Fetch Password and RoleID
    $stmt = $conn->prepare("SELECT UserID, Password, RoleID FROM Users WHERE UserName = ? LIMIT 1");
    if ($stmt === false) {
        error_log("Failed to prepare statement in login.php: " . $conn->error);
        $_SESSION['login_error'] = "A database error occurred. Please try again later.";
        $_SESSION['modal_to_open'] = 'login';
        header("Location: index.php");
        exit();
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($userID, $storedPassword, $roleID); // Renamed to $storedPassword
        $stmt->fetch();

        // --- CRITICAL CHANGE: Comparing plain text password directly ---
        if ($password === $storedPassword) { // Direct comparison of plain text passwords
            $_SESSION['userID'] = $userID;
            $_SESSION['username'] = $username;
            $_SESSION['roleID'] = $roleID;

            // Redirect based on role
            if ($roleID == 1) { // Admin
                header("Location: admin/dashboard.php");
            } elseif ($roleID == 2) { // Artist
                header("Location: creator/dashboard.php");
            } elseif ($roleID == 3) { // Listener
                header("Location: index.php");
            } else {
                // Should not happen if roles are well-defined
                $_SESSION['login_error'] = "Your account has an unrecognized role. Please contact support.";
                $_SESSION['modal_to_open'] = 'login';
                header("Location: index.php");
            }
            exit();
        } else {
            // Invalid password
            $_SESSION['login_error'] = "Incorrect username or password.";
            $_SESSION['modal_to_open'] = 'login';
            header("Location: index.php");
            exit();
        }
    } else {
        // User not found
        $_SESSION['login_error'] = "Incorrect username or password.";
        $_SESSION['modal_to_open'] = 'login';
        header("Location: index.php");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    // If someone tries to access login.php directly without POST
    header("Location: index.php");
    exit();
}
?>