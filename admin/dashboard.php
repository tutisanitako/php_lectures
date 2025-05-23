<?php
include '../db_connect.php';
include '../log_page_view.php';
session_start();

if (!isset($_SESSION['userID']) || $_SESSION['roleID'] != 1) {
    header("Location: ../index.php?error=access_denied");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SoundWave</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Welcome, Admin <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
        <p>This is your exclusive admin dashboard. Use the links below to manage the platform.</p>

        <div class="nav-links">
            <a href="manage_users.php">Manage Users</a>
            <a href="manage_artists.php">Manage Artists</a>
            <a href="manage_songs_albums.php">Manage Songs & Albums</a>
            <a href="analytics.php">Site Analytics</a>
        </div>

        <div class="logout-container">
            <button type="button" class="logout-btn" onclick="showLogoutConfirmation()">Logout</button>
        </div>
    </div>

    <div id="logoutConfirmationModal" class="custom-modal">
        <div class="custom-modal-content">
            <h3>Confirm Logout</h3>
            <p>Are you sure you want to log out of your admin session?</p>
            <div class="modal-buttons">
                <button type="button" class="modal-btn confirm" onclick="proceedLogout()">Yes, Log Out</button>
                <button type="button" class="modal-btn cancel" onclick="hideLogoutConfirmation()">Cancel</button>
            </div>
        </div>
    </div>
    <script>
        function showLogoutConfirmation() {
            const modal = document.getElementById('logoutConfirmationModal');
            modal.classList.add('show');
        }

        function hideLogoutConfirmation() {
            const modal = document.getElementById('logoutConfirmationModal');
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        function proceedLogout() {
            window.location.href = "../logout.php";
        }

        document.getElementById('logoutConfirmationModal').addEventListener('click', function(event) {
            if (event.target === this) { 
                hideLogoutConfirmation();
            }
        });

    </script>
</body>
</html>