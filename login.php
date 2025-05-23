<?php
session_start();

include 'db_connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    unset($_SESSION['login_error']);
    unset($_SESSION['signup_success_message']);
    unset($_SESSION['modal_to_open']);

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
        $stmt->bind_result($userID, $storedPassword, $roleID);
        $stmt->fetch();

        if ($password === $storedPassword) {
            $_SESSION['userID'] = $userID;
            $_SESSION['username'] = $username;
            $_SESSION['roleID'] = $roleID;

            if ($roleID == 1) {
                header("Location: admin/dashboard.php");
            } elseif ($roleID == 2) {
                header("Location: creator/dashboard.php");
            } elseif ($roleID == 3) {
                header("Location: index.php");
            } else {
                $_SESSION['login_error'] = "Your account has an unrecognized role. Please contact support.";
                $_SESSION['modal_to_open'] = 'login';
                header("Location: index.php");
            }
            exit();
        } else {
            $_SESSION['login_error'] = "Incorrect username or password.";
            $_SESSION['modal_to_open'] = 'login';
            header("Location: index.php");
            exit();
        }
    } else {
        $_SESSION['login_error'] = "Incorrect username or password.";
        $_SESSION['modal_to_open'] = 'login';
        header("Location: index.php");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: index.php");
    exit();
}
?>