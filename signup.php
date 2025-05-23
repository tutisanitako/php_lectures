<?php
session_start();

include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $password = $_POST['password'];

    $roleID = 3;

    if (isset($_SESSION['signup_error'])) {
        unset($_SESSION['signup_error']);
    }
    if (isset($_SESSION['login_error'])) {
        unset($_SESSION['login_error']);
    }
    if (isset($_SESSION['signup_success_message'])) {
        unset($_SESSION['signup_success_message']);
    }

    $checkStmt = $conn->prepare("SELECT UserID FROM Users WHERE UserName = ? OR Email = ? LIMIT 1");
    $checkStmt->bind_param("ss", $username, $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        $_SESSION['signup_error'] = "Username or Email already exists. Please choose another.";
        $_SESSION['modal_to_open'] = 'signup';
        header("Location: index.php");
        exit();
    }
    $checkStmt->close();

    $stmt = $conn->prepare("INSERT INTO Users (FullName, UserName, Email, Password, RoleID) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssi", $fullname, $username, $email, $password, $roleID);

    if ($stmt->execute()) {
        $newUserID = $stmt->insert_id;

        $_SESSION['userID'] = $newUserID;
        $_SESSION['username'] = $username;
        $_SESSION['roleID'] = $roleID;

        if (isset($_SESSION['modal_to_open'])) {
            unset($_SESSION['modal_to_open']);
        }

        header("Location: index.php");
        exit();

    } else {
        $_SESSION['signup_error'] = "Error creating account. Please try again.";
        $_SESSION['modal_to_open'] = 'signup';
        header("Location: index.php");
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>