<?php
session_start();
include '../db_connect.php'; // Connects to the DB from admin sub-directory
include '../log_page_view.php';

// Check if user is logged in and is an Admin (RoleID = 1)
if (!isset($_SESSION['userID']) || $_SESSION['roleID'] != 1) {
    header("Location: ../index.php?error=access_denied");
    exit();
}

include '../db_connect.php'; // Connect to the database

$message = '';
$message_type = ''; // 'success' or 'error'

// Variables for pending confirmations
$confirm_delete_user_id = null;
$confirm_delete_username = null;
$confirm_change_role_user_id = null;
$confirm_change_role_username = null;
$confirm_new_role_id = null;
$confirm_new_role_name = null;


// --- Handle incoming GET requests for confirmation ---

// 1. Handle delete confirmation request
if (isset($_GET['confirm_delete_id'])) {
    $temp_user_id = filter_var($_GET['confirm_delete_id'], FILTER_SANITIZE_NUMBER_INT);

    // Prevent admin from trying to delete themselves via URL manipulation
    if ($temp_user_id == $_SESSION['userID']) {
        $_SESSION['admin_message'] = "You cannot delete your own account!";
        $_SESSION['admin_message_type'] = "error";
        $_SESSION['admin_message_time'] = time(); // Store timestamp
        header("Location: manage_users.php"); // Redirect to self to clear URL
        exit();
    }

    // Fetch username for confirmation message
    $stmt_fetch_user = $conn->prepare("SELECT UserName FROM Users WHERE UserID = ? LIMIT 1");
    $stmt_fetch_user->bind_param("i", $temp_user_id);
    $stmt_fetch_user->execute();
    $stmt_fetch_user->store_result();
    if ($stmt_fetch_user->num_rows > 0) {
        $stmt_fetch_user->bind_result($username_to_delete);
        $stmt_fetch_user->fetch();
        $confirm_delete_user_id = $temp_user_id;
        $confirm_delete_username = $username_to_delete;
    } else {
        $_SESSION['admin_message'] = "User not found for deletion confirmation.";
        $_SESSION['admin_message_type'] = "error";
        $_SESSION['admin_message_time'] = time(); // Store timestamp
        header("Location: manage_users.php"); // Redirect to self to clear URL
        exit();
    }
    $stmt_fetch_user->close();
}

// 2. Handle change role confirmation request
if (isset($_GET['confirm_change_role_id']) && isset($_GET['new_role_id_confirm'])) {
    $temp_user_id = filter_var($_GET['confirm_change_role_id'], FILTER_SANITIZE_NUMBER_INT);
    $temp_new_role_id = filter_var($_GET['new_role_id_confirm'], FILTER_SANITIZE_NUMBER_INT);

    // Prevent admin from trying to change their own role via URL manipulation
    if ($temp_user_id == $_SESSION['userID']) {
        $_SESSION['admin_message'] = "You cannot change your own role!";
        $_SESSION['admin_message_type'] = "error";
        $_SESSION['admin_message_time'] = time(); // Store timestamp
        header("Location: manage_users.php"); // Redirect to self to clear URL
        exit();
    }

    // Fetch username and new role name for confirmation message
    $stmt_fetch_details = $conn->prepare("SELECT u.UserName, r.RoleName FROM Users u JOIN Roles r ON r.RoleID = ? WHERE u.UserID = ? LIMIT 1");
    $stmt_fetch_details->bind_param("ii", $temp_new_role_id, $temp_user_id);
    $stmt_fetch_details->execute();
    $stmt_fetch_details->store_result();
    if ($stmt_fetch_details->num_rows > 0) {
        $stmt_fetch_details->bind_result($username_to_change, $new_role_name);
        $stmt_fetch_details->fetch();
        $confirm_change_role_user_id = $temp_user_id;
        $confirm_change_role_username = $username_to_change;
        $confirm_new_role_id = $temp_new_role_id;
        $confirm_new_role_name = $new_role_name;
    } else {
        $_SESSION['admin_message'] = "User or new role not found for role change confirmation.";
        $_SESSION['admin_message_type'] = "error";
        $_SESSION['admin_message_time'] = time(); // Store timestamp
        header("Location: manage_users.php"); // Redirect to self to clear URL
        exit();
    }
    $stmt_fetch_details->close();
}


// --- Handle Form Submissions (Actual Role Change / Delete) ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Clear any previous session messages before processing new POST actions
    if (isset($_SESSION['admin_message'])) {
        unset($_SESSION['admin_message']);
        unset($_SESSION['admin_message_type']);
        unset($_SESSION['admin_message_time']); // Clear timestamp too
    }

    if (isset($_POST['action'])) {
        $action = $_POST['action'];
        $targetUserID = filter_var($_POST['user_id'], FILTER_SANITIZE_NUMBER_INT);

        // This check acts as a double-safety for direct POSTs (though shouldn't happen with our GET confirmation)
        if ($targetUserID == $_SESSION['userID']) {
            $_SESSION['admin_message'] = "You cannot modify or delete your own account!";
            $_SESSION['admin_message_type'] = "error";
            $_SESSION['admin_message_time'] = time(); // Store timestamp
        } else {
            if ($action == 'change_role' && isset($_POST['new_role_id'])) {
                $newRoleID = filter_var($_POST['new_role_id'], FILTER_SANITIZE_NUMBER_INT);
                $stmt = $conn->prepare("UPDATE Users SET RoleID = ? WHERE UserID = ?");
                $stmt->bind_param("ii", $newRoleID, $targetUserID);
                if ($stmt->execute()) {
                    $_SESSION['admin_message'] = "User role updated successfully!";
                    $_SESSION['admin_message_type'] = "success";
                    $_SESSION['admin_message_time'] = time(); // Store timestamp
                } else {
                    $_SESSION['admin_message'] = "Error updating user role: " . $stmt->error;
                    $_SESSION['admin_message_type'] = "error";
                    $_SESSION['admin_message_time'] = time(); // Store timestamp
                }
                $stmt->close();
            } elseif ($action == 'delete_user') {
                $stmt = $conn->prepare("DELETE FROM Users WHERE UserID = ?");
                $stmt->bind_param("i", $targetUserID);
                if ($stmt->execute()) {
                    $_SESSION['admin_message'] = "User deleted successfully!";
                    $_SESSION['admin_message_type'] = "success";
                    $_SESSION['admin_message_time'] = time(); // Store timestamp
                } else {
                    $_SESSION['admin_message'] = "Error deleting user: " . $stmt->error;
                    $_SESSION['admin_message_type'] = "error";
                    $_SESSION['admin_message_time'] = time(); // Store timestamp
                }
                $stmt->close();
            }
        }
    }
    // Redirect to self to display message and clear POST data
    header("Location: manage_users.php");
    exit();
}

// Check for and display session messages from previous actions
$display_message_html = false;
$seconds_to_show = 2; // The duration to show the message in seconds

if (isset($_SESSION['admin_message']) && isset($_SESSION['admin_message_time'])) {
    $message = $_SESSION['admin_message'];
    $message_type = $_SESSION['admin_message_type'];
    $message_timestamp = $_SESSION['admin_message_time'];

    // If enough time has passed, don't display the message and clear it
    if ((time() - $message_timestamp) >= $seconds_to_show) {
        unset($_SESSION['admin_message']);
        unset($_SESSION['admin_message_type']);
        unset($_SESSION['admin_message_time']);
    } else {
        $display_message_html = true;
    }
}


// --- Fetch Data for Display ---
// Fetch all users and their roles
$users = [];
$sql_users = "SELECT u.UserID, u.FullName, u.UserName, u.Email, u.RoleID, r.RoleName
              FROM Users u
              JOIN Roles r ON u.RoleID = r.RoleID
              ORDER BY u.UserID ASC";
$result_users = $conn->query($sql_users);
if ($result_users && $result_users->num_rows > 0) {
    while ($row = $result_users->fetch_assoc()) {
        $users[] = $row;
    }
}

// Fetch all possible roles for the dropdown
$roles = [];
$sql_roles = "SELECT RoleID, RoleName FROM Roles";
$result_roles = $conn->query($sql_roles);
if ($result_roles && $result_roles->num_rows > 0) {
    while ($row = $result_roles->fetch_assoc()) {
        $roles[] = $row;
    }
}

$conn->close(); // Close the database connection
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin Dashboard</title>
    <link rel="stylesheet" href="style.css">
    <?php if ($display_message_html): ?>
        <meta http-equiv="refresh" content="<?php echo $seconds_to_show; ?>;URL=manage_users.php">
    <?php endif; ?>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-link">&larr; Back to Dashboard</a>
        <h1>Manage Users</h1>

        <?php if ($display_message_html): // Only display if message is not timed out ?>
            <div class="message <?php echo $message_type; ?>-message fade-out">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($confirm_delete_user_id !== null): // Show delete confirmation box ?>
            <div class="confirmation-box delete-confirm">
                <h2>Confirm Deletion</h2>
                <p>Are you absolutely sure you want to delete the user <strong><?php echo htmlspecialchars($confirm_delete_username); ?></strong> (ID: <?php echo htmlspecialchars($confirm_delete_user_id); ?>)? This action cannot be undone.</p>
                <div class="confirmation-actions">
                    <form action="manage_users.php" method="POST">
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($confirm_delete_user_id); ?>">
                        <input type="hidden" name="action" value="delete_user">
                        <button type="submit" class="btn btn-confirm">Yes, Delete Permanently</button>
                    </form>
                    <a href="manage_users.php" class="btn btn-cancel">No, Cancel</a>
                </div>
            </div>
        <?php elseif ($confirm_change_role_user_id !== null): // Show change role confirmation box ?>
            <div class="confirmation-box change-role-confirm">
                <h2>Confirm Role Change</h2>
                <p>Are you sure you want to change the role of user <strong><?php echo htmlspecialchars($confirm_change_role_username); ?></strong> (ID: <?php echo htmlspecialchars($confirm_change_role_user_id); ?>) to <strong><?php echo htmlspecialchars($confirm_new_role_name); ?></strong>?</p>
                <div class="confirmation-actions">
                    <form action="manage_users.php" method="POST">
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($confirm_change_role_user_id); ?>">
                        <input type="hidden" name="action" value="change_role">
                        <input type="hidden" name="new_role_id" value="<?php echo htmlspecialchars($confirm_new_role_id); ?>">
                        <button type="submit" class="btn btn-primary">Yes, Change Role</button>
                    </form>
                    <a href="manage_users.php" class="btn btn-cancel">No, Cancel</a>
                </div>
            </div>
        <?php else: // Show user table (default view) ?>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Full Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="6">No users found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['UserID']); ?></td>
                                <td><?php echo htmlspecialchars($user['FullName']); ?></td>
                                <td><?php echo htmlspecialchars($user['UserName']); ?></td>
                                <td><?php echo htmlspecialchars($user['Email']); ?></td>
                                <td><?php echo htmlspecialchars($user['RoleName']); ?></td>
                                <td>
                                    <?php if ($user['UserID'] != $_SESSION['userID']): // Prevent actions on self ?>
                                        <form action="manage_users.php" method="GET" class="action-form">
                                            <input type="hidden" name="confirm_change_role_id" value="<?php echo $user['UserID']; ?>">
                                            <select name="new_role_id_confirm">
                                                <?php foreach ($roles as $role): ?>
                                                    <option value="<?php echo $role['RoleID']; ?>"
                                                        <?php echo ($role['RoleID'] == $user['RoleID']) ? 'selected' : ''; ?>>
                                                        <?php echo htmlspecialchars($role['RoleName']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="change-role-button-trigger">Change Role</button>
                                        </form>
                                        <form action="manage_users.php" method="GET" class="action-form">
                                            <input type="hidden" name="confirm_delete_id" value="<?php echo $user['UserID']; ?>">
                                            <button type="submit" class="delete-button-trigger">Delete</button>
                                        </form>
                                    <?php else: ?>
                                        <span style="color: #b2bec3; font-style: italic;">(Your account)</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>