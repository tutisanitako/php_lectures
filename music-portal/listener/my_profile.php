<?php
session_start();

// Check if user is logged in and is a Listener (RoleID = 3)
if (!isset($_SESSION['userID']) || $_SESSION['roleID'] != 3) {
    header("Location: ../index.php?error=access_denied");
    exit();
}

include '../db_connect.php'; // Connect to the database
include '../log_page_view.php'; // Log this page view

// Fetch user details for the profile page
$userID = $_SESSION['userID'];
$userDetails = null;
$stmt_user = $conn->prepare("SELECT UserID, Username, FullName, Email, CreatedAt FROM Users WHERE UserID = ? LIMIT 1");
$stmt_user->bind_param("i", $userID);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
if ($result_user && $result_user->num_rows > 0) {
    $userDetails = $result_user->fetch_assoc();
}
$stmt_user->close();


// Function to get listening history for the current user
function getListeningHistory($conn, $userID) {
    $history = [];
    $stmt = $conn->prepare("
        SELECT s.SongName, a.ArtistName, al.AlbumName, lh.ListenedAt
        FROM ListeningHistory lh
        JOIN Songs s ON lh.SongID = s.SongID
        JOIN Albums al ON s.AlbumID = al.AlbumID
        JOIN Artists a ON al.ArtistID = a.ArtistID
        WHERE lh.UserID = ?
        ORDER BY lh.ListenedAt DESC
        LIMIT 10
    ");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
    }
    $stmt->close();
    return $history;
}

// Fetch data for the listener's profile
$listeningHistory = getListeningHistory($conn, $userID);

$conn->close(); // Close the database connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - SoundWave</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <a href="../index.php" class="home-link">&larr; Back to Home</a>

        <h1>My Profile</h1>

        <?php if ($userDetails): ?>
            <h2 class="profile">Account Details</h2>
            <div class="section-content profile-details">
                <p><strong>Username:</strong> <span><?php echo htmlspecialchars($userDetails['Username']); ?></span></p>
                <p><strong>Full Name:</strong> <span><?php echo htmlspecialchars($userDetails['FullName']); ?></span></p>
                <p><strong>Email:</strong> <span><?php echo htmlspecialchars($userDetails['Email']); ?></span></p>
                <p><strong>Member Since:</strong> <span><?php echo date("F j, Y", strtotime(htmlspecialchars($userDetails['CreatedAt']))); ?></span></p>
            </div>
        <?php else: ?>
            <p class="empty-message">Could not load user details.</p>
        <?php endif; ?>

        <h2 class="history">Recent Listening History</h2>
        <div class="section-content">
            <?php if (empty($listeningHistory)): ?>
                <p class="empty-message">You haven't listened to any songs yet. Start exploring music on the <a href="../index.php">Home page</a>!</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($listeningHistory as $item): ?>
                        <li>
                            <div class="song-info">
                                <strong><?php echo htmlspecialchars($item['SongName']); ?></strong>
                                <span><?php echo htmlspecialchars($item['ArtistName']); ?> &bull; <?php echo htmlspecialchars($item['AlbumName']); ?></span>
                            </div>
                            <div class="song-meta">
                                Listened at: <?php echo htmlspecialchars($item['ListenedAt']); ?>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>

        <div class="logout-container">
            <button type="button" class="logout-btn" onclick="showLogoutConfirmation()">Logout</button>
        </div>
    </div>

    <div id="logoutConfirmationModal" class="custom-modal">
        <div class="custom-modal-content">
            <h3>Confirm Logout</h3>
            <p>Are you sure you want to log out of your account?</p>
            <div class="modal-buttons">
                <button type="button" class="modal-btn confirm" onclick="proceedLogout()">Yes, Log Out</button>
                <button type="button" class="modal-btn cancel" onclick="hideLogoutConfirmation()">Cancel</button>
            </div>
        </div>
    </div>
    <script>
        function showLogoutConfirmation() {
            const modal = document.getElementById('logoutConfirmationModal');
            modal.classList.add('show'); // Add 'show' class to trigger fade-in and slide-down
        }

        function hideLogoutConfirmation() {
            const modal = document.getElementById('logoutConfirmationModal');
            modal.classList.remove('show'); // Remove 'show' class to trigger fade-out and slide-up
            // Optional: Add a small delay before setting display: none if you want the animation to complete
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300); // Matches CSS transition duration
        }

        function proceedLogout() {
            window.location.href = "../logout.php"; // Redirect to your logout script
        }

        // Close modal if user clicks outside of the content (but within the modal overlay)
        document.getElementById('logoutConfirmationModal').addEventListener('click', function(event) {
            if (event.target === this) { // If the click was on the modal background itself
                hideLogoutConfirmation();
            }
        });
    </script>
</body>
</html>