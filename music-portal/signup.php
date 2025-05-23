<?php
session_start(); // Start session at the very beginning

include 'db_connect.php'; // Include your database connection file

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $roleID = 3; // Default role for new signups is 'Listener'

    // Clear any previous signup error or login error messages
    if (isset($_SESSION['signup_error'])) {
        unset($_SESSION['signup_error']);
    }
    if (isset($_SESSION['login_error'])) {
        unset($_SESSION['login_error']);
    }
    if (isset($_SESSION['signup_success_message'])) {
        unset($_SESSION['signup_success_message']);
    }

    // Check if username or email already exists
    $checkStmt = $conn->prepare("SELECT UserID FROM Users WHERE UserName = ? OR Email = ? LIMIT 1");
    $checkStmt->bind_param("ss", $username, $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $_SESSION['signup_error'] = "Username or Email already exists. Please choose another.";
        $_SESSION['modal_to_open'] = 'signup'; // Indicate that signup modal should open
        header("Location: index.php"); // Redirect without GET param
        exit();
    }
    $checkStmt->close();

    // Insert new user
    $stmt = $conn->prepare("INSERT INTO Users (FullName, UserName, Email, Password, RoleID) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $fullname, $username, $email, $password, $roleID);

    if ($stmt->execute()) {
        $newUserID = $stmt->insert_id;

        // Automatically log in the user
        $_SESSION['userID'] = $newUserID;
        $_SESSION['username'] = $username;
        $_SESSION['roleID'] = $roleID;

        // Clear the modal_to_open session variable as signup was successful
        if (isset($_SESSION['modal_to_open'])) {
            unset($_SESSION['modal_to_open']);
        }

        // Redirect directly to the listener dashboard
        header("Location: index.php");
        exit();

    } else {
        $_SESSION['signup_error'] = "Error creating account. Please try again.";
        $_SESSION['modal_to_open'] = 'signup'; // Indicate that signup modal should open
        header("Location: index.php"); // Redirect without GET param
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>