<?php
include '../db_connect.php'; // Connects to the DB from creator sub-directory
include '../log_page_view.php';
session_start();

// Check if user is logged in and is a Creator (RoleID = 2)
if (!isset($_SESSION['userID']) || $_SESSION['roleID'] != 2) {
    // Redirect to the login page
    header("Location: ../index.php?error=access_denied");
    exit();
}

$userID = $_SESSION['userID']; // The logged-in creator's ID
$username = htmlspecialchars($_SESSION['username']); // The logged-in creator's username

// Function to get albums by the current creator
function getAlbumsByCreator($conn, $creatorID) {
    $albums = [];
    $stmt = $conn->prepare("SELECT AlbumID, AlbumName, ReleaseDate, Genre FROM Albums WHERE ArtistID = ? ORDER BY ReleaseDate DESC");
    if ($stmt) {
        $stmt->bind_param("i", $creatorID);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $albums[] = $row;
        }
        $stmt->close();
    }
    return $albums;
}

// Function to get songs by the current creator's albums
function getSongsByCreatorAlbums($conn, $creatorID) {
    $songs = [];
    $stmt = $conn->prepare("
        SELECT s.SongID, s.SongName, s.Duration, al.AlbumName
        FROM Songs s
        JOIN Albums al ON s.AlbumID = al.AlbumID
        WHERE al.ArtistID = ?
        ORDER BY al.AlbumName ASC, s.SongName ASC
    ");
    if ($stmt) {
        $stmt->bind_param("i", $creatorID);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $songs[] = $row;
        }
        $stmt->close();
    }
    return $songs;
}

$creatorAlbums = getAlbumsByCreator($conn, $userID);
$creatorSongs = getSongsByCreatorAlbums($conn, $userID);

$conn->close(); // Close the database connection
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Creator Dashboard - SoundWave</title>
    <style>
        /* Re-use the existing CSS variables and general styles from admin/listener dashboards */
        :root {
            --primary-color: #6c5ce7; /* Purple */
            --secondary-color: #a29bfe; /* Lighter Purple */
            --accent-color: #fd79a8; /* Pink */
            --dark-bg: #2d3436; /* Dark Grey */
            --darker-bg: #1e2124; /* Even Darker Grey */
            --text-white: #ffffff;
            --text-gray: #b2bec3;
            --card-bg: #36393f; /* Card Background */
            --hover-bg: #40434a; /* Hover Background */
            --error-red: #ff6b6b;
            --success-green: #2ecc71;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--darker-bg) 0%, var(--dark-bg) 100%);
            color: var(--text-white);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: flex-start; /* Align to top */
            padding: 40px 20px;
            box-sizing: border-box;
        }

        .container {
            max-width: 1000px; /* Slightly wider for content */
            width: 100%;
            background-color: var(--card-bg);
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
            position: relative;
        }

        .home-link {
            position: absolute;
            top: 20px;
            left: 20px;
            color: var(--secondary-color);
            text-decoration: none;
            font-weight: 600;
            padding: 8px 15px;
            border-radius: 20px;
            background-color: var(--darker-bg);
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .home-link:hover {
            color: var(--text-white);
            background-color: var(--primary-color);
            transform: translateX(-5px);
            box-shadow: 0 2px 10px rgba(108, 92, 231, 0.4);
        }

        h1 {
            font-size: 3rem;
            text-align: center;
            margin-bottom: 30px;
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            padding-top: 20px;
        }

        p {
            color: var(--text-gray);
            text-align: center;
            margin-bottom: 40px;
            font-size: 1.15rem;
            line-height: 1.6;
        }

        h2 {
            font-size: 1.8rem;
            color: var(--secondary-color);
            margin-top: 40px;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid rgba(162, 155, 254, 0.2);
            position: relative;
            padding-left: 40px;
            text-align: left; /* Align h2 to the left */
        }

        h2::before {
            font-size: 1.5rem;
            margin-right: 10px;
            color: var(--accent-color);
            position: absolute;
            left: 0;
            top: 50%;
            transform: translateY(-50%);
        }
        h2.albums::before { content: '💿'; }
        h2.songs::before { content: '🎵'; }


        .section-content {
            background-color: var(--darker-bg);
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            border: 1px solid rgba(108, 92, 231, 0.2);
        }

        .add-button-container {
            text-align: right;
            margin-bottom: 20px;
        }

        .add-btn {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: var(--text-white);
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            box-shadow: 0 4px 10px rgba(108, 92, 231, 0.4);
        }

        .add-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(108, 92, 231, 0.6);
            opacity: 0.9;
        }

        /* Table styles for displaying albums/songs */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            color: var(--text-gray);
        }

        th, td {
            padding: 12px 15px;
            text-align: left;
            border-bottom: 1px solid rgba(178, 190, 195, 0.1);
        }

        th {
            background-color: var(--primary-color);
            color: var(--text-white);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.9rem;
            letter-spacing: 0.05em;
        }

        tr:nth-child(even) {
            background-color: var(--darker-bg); /* Slightly different background for even rows */
        }

        tr:hover {
            background-color: var(--hover-bg);
            color: var(--text-white);
        }

        .action-buttons a, .action-buttons button {
            display: inline-block;
            padding: 8px 15px;
            margin-right: 8px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s ease;
            font-size: 0.9rem;
        }

        .action-buttons .edit-btn {
            background-color: #2ecc71; /* Green */
            color: var(--text-white);
        }
        .action-buttons .edit-btn:hover {
            background-color: #27ae60;
            transform: translateY(-1px);
        }

        .action-buttons .delete-btn {
            background-color: #e74c3c; /* Red */
            color: var(--text-white);
            border: none; /* Make delete button a button */
            cursor: pointer;
        }
        .action-buttons .delete-btn:hover {
            background-color: #c0392b;
            transform: translateY(-1px);
        }

        .empty-message {
            color: var(--text-gray);
            text-align: center;
            padding: 20px;
            font-style: italic;
        }
        .empty-message a {
            color: var(--primary-color);
            text-decoration: none;
            font-weight: bold;
        }
        .empty-message a:hover {
            text-decoration: underline;
        }

        /* Logout and Modal styles (re-use from listener/admin) */
        .logout-container {
            text-align: center;
            margin-top: 40px;
        }

        .logout-btn {
            background: linear-gradient(45deg, var(--accent-color), #ffafbe);
            color: var(--text-white);
            padding: 15px 30px;
            border: none;
            border-radius: 30px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(253, 121, 168, 0.4);
            text-decoration: none;
            display: inline-block;
        }

        .logout-btn:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 20px rgba(253, 121, 168, 0.6);
            opacity: 0.9;
        }

        .custom-modal {
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(5px);
            display: flex;
            justify-content: center;
            align-items: center;
            visibility: hidden;
            opacity: 0;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .custom-modal.show {
            visibility: visible;
            opacity: 1;
        }

        .custom-modal-content {
            background-color: var(--card-bg);
            margin: auto;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.6);
            width: 90%;
            max-width: 400px;
            text-align: center;
            transform: translateY(-50px);
            opacity: 0;
            transition: transform 0.3s ease, opacity 0.3s ease;
            position: relative;
        }

        .custom-modal.show .custom-modal-content {
            transform: translateY(0);
            opacity: 1;
        }

        .custom-modal-content h3 {
            color: var(--primary-color);
            font-size: 1.8rem;
            margin-bottom: 20px;
        }

        .custom-modal-content p {
            color: var(--text-gray);
            font-size: 1.1rem;
            margin-bottom: 30px;
        }

        .modal-buttons {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        .modal-btn {
            padding: 12px 25px;
            border: none;
            border-radius: 25px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .modal-btn.confirm {
            background: linear-gradient(45deg, var(--accent-color), #ffafbe);
            color: var(--text-white);
            box-shadow: 0 4px 10px rgba(253, 121, 168, 0.4);
        }

        .modal-btn.confirm:hover {
            transform: translateY(-2px) scale(1.02);
            box-shadow: 0 6px 15px rgba(253, 121, 168, 0.6);
        }

        .modal-btn.cancel {
            background-color: var(--darker-bg);
            color: var(--text-gray);
            border: 2px solid var(--primary-color);
        }

        .modal-btn.cancel:hover {
            background-color: var(--primary-color);
            color: var(--text-white);
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(108, 92, 231, 0.4);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            body {
                padding: 20px 10px;
            }
            .container {
                padding: 30px 20px;
            }
            .home-link {
                top: 15px;
                left: 15px;
                padding: 6px 12px;
                font-size: 0.9rem;
            }
            h1 {
                font-size: 2.5rem;
                padding-top: 10px;
            }
            h2 {
                font-size: 1.5rem;
                margin-top: 30px;
                padding-left: 30px;
            }
            h2::before {
                font-size: 1.2rem;
            }
            table, thead, tbody, th, td, tr {
                display: block; /* Stack table elements on small screens */
            }
            thead tr {
                position: absolute;
                top: -9999px;
                left: -9999px;
            }
            tr {
                border: 1px solid rgba(178, 190, 195, 0.2);
                margin-bottom: 15px;
                border-radius: 8px;
            }
            td {
                border: none;
                position: relative;
                padding-left: 50%; /* Space for pseudo-element label */
                text-align: right;
            }
            td::before {
                content: attr(data-label); /* Use data-label for content */
                position: absolute;
                left: 15px;
                width: calc(50% - 30px);
                padding-right: 10px;
                white-space: nowrap;
                text-align: left;
                font-weight: 600;
                color: var(--secondary-color);
            }
            .action-buttons {
                display: flex;
                justify-content: flex-end;
                gap: 5px;
                padding-top: 10px;
                border-top: 1px dashed rgba(178, 190, 195, 0.1);
                margin-top: 10px;
            }
            .action-buttons a, .action-buttons button {
                margin-right: 0; /* Remove right margin when stacked */
            }
            .logout-btn {
                padding: 12px 25px;
                font-size: 1rem;
            }
            .modal-buttons {
                flex-direction: column;
                gap: 15px;
            }
            .modal-btn {
                width: 100%;
            }
        }
        @media (max-width: 480px) {
            .container {
                padding: 30px 15px;
            }
            h1 {
                font-size: 2rem;
            }
            p {
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="../index.php" class="home-link">&larr; Back to Home</a>

        <h1>Creator Dashboard - Welcome, <?php echo $username; ?>!</h1>
        <p>Manage your uploaded albums and songs here. You can add new content, or edit and delete existing ones.</p>

        <h2 class="albums">Your Albums</h2>
        <div class="section-content">
            <div class="add-button-container">
                <a href="add_album.php" class="add-btn">Add New Album</a>
            </div>
            <?php if (empty($creatorAlbums)): ?>
                <p class="empty-message">You haven't uploaded any albums yet. Click "Add New Album" to get started!</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Album Name</th>
                            <th>Release Date</th>
                            <th>Genre</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($creatorAlbums as $album): ?>
                            <tr>
                                <td data-label="Album Name"><?php echo htmlspecialchars($album['AlbumName']); ?></td>
                                <td data-label="Release Date"><?php echo htmlspecialchars($album['ReleaseDate']); ?></td>
                                <td data-label="Genre"><?php echo htmlspecialchars($album['Genre']); ?></td>
                                <td data-label="Actions" class="action-buttons">
                                    <a href="edit_album.php?id=<?php echo $album['AlbumID']; ?>" class="edit-btn">Edit</a>
                                    <button type="button" class="delete-btn" onclick="showDeleteConfirmation('album', <?php echo $album['AlbumID']; ?>, '<?php echo addslashes(htmlspecialchars($album['AlbumName'])); ?>')">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <h2 class="songs">Your Songs</h2>
        <div class="section-content">
            <div class="add-button-container">
                <a href="add_song.php" class="add-btn">Add New Song</a>
            </div>
            <?php if (empty($creatorSongs)): ?>
                <p class="empty-message">You haven't uploaded any songs yet. Add an album first, then "Add New Song" to it!</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Song Name</th>
                            <th>Album</th>
                            <th>Duration</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($creatorSongs as $song): ?>
                            <tr>
                                <td data-label="Song Name"><?php echo htmlspecialchars($song['SongName']); ?></td>
                                <td data-label="Album"><?php echo htmlspecialchars($song['AlbumName']); ?></td>
                                <td data-label="Duration"><?php echo htmlspecialchars($song['Duration']); ?></td>
                                <td data-label="Actions" class="action-buttons">
                                    <a href="edit_song.php?id=<?php echo $song['SongID']; ?>" class="edit-btn">Edit</a>
                                    <button type="button" class="delete-btn" onclick="showDeleteConfirmation('song', <?php echo $song['SongID']; ?>, '<?php echo addslashes(htmlspecialchars($song['SongName'])); ?>')">Delete</button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <div class="logout-container">
            <button type="button" class="logout-btn" onclick="showLogoutConfirmation()">Logout</button>
        </div>
    </div>

    <div id="logoutConfirmationModal" class="custom-modal">
        <div class="custom-modal-content">
            <h3>Confirm Logout</h3>
            <p>Are you sure you want to log out of your creator session?</p>
            <div class="modal-buttons">
                <button type="button" class="modal-btn confirm" onclick="proceedLogout()">Yes, Log Out</button>
                <button type="button" class="modal-btn cancel" onclick="hideLogoutConfirmation()">Cancel</button>
            </div>
        </div>
    </div>

    <div id="deleteConfirmationModal" class="custom-modal">
        <div class="custom-modal-content">
            <h3 id="deleteModalTitle">Confirm Deletion</h3>
            <p id="deleteModalMessage">Are you sure you want to delete this item? This action cannot be undone.</p>
            <div class="modal-buttons">
                <button type="button" class="modal-btn confirm" id="confirmDeleteBtn">Yes, Delete</button>
                <button type="button" class="modal-btn cancel" onclick="hideDeleteConfirmation()">Cancel</button>
            </div>
        </div>
    </div>

    <script>
        // --- Logout Modal Functions (Reused) ---
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

        // --- Delete Confirmation Modal Functions (New) ---
        let currentDeleteItemType = '';
        let currentDeleteItemID = null;

        function showDeleteConfirmation(type, id, name) {
            currentDeleteItemType = type;
            currentDeleteItemID = id;

            const modal = document.getElementById('deleteConfirmationModal');
            const title = document.getElementById('deleteModalTitle');
            const message = document.getElementById('deleteModalMessage');
            const confirmBtn = document.getElementById('confirmDeleteBtn');

            title.textContent = `Delete ${type === 'album' ? 'Album' : 'Song'}`;
            message.textContent = `Are you sure you want to delete "${name}"? This will permanently remove it and all associated data. This action cannot be undone.`;

            // Set the action for the confirm button
            confirmBtn.onclick = proceedDelete;

            modal.classList.add('show');
        }

        function hideDeleteConfirmation() {
            const modal = document.getElementById('deleteConfirmationModal');
            modal.classList.remove('show');
            setTimeout(() => {
                modal.style.display = 'none';
            }, 300);
        }

        function proceedDelete() {
            // This is where you would typically make an AJAX call or redirect to a PHP script
            // to handle the actual deletion in the database.

            if (currentDeleteItemType === 'album') {
                window.location.href = `delete_album.php?id=${currentDeleteItemID}`;
            } else if (currentDeleteItemType === 'song') {
                window.location.href = `delete_song.php?id=${currentDeleteItemID}`;
            }
            hideDeleteConfirmation(); // Hide modal after initiating deletion
        }

        document.getElementById('deleteConfirmationModal').addEventListener('click', function(event) {
            if (event.target === this) {
                hideDeleteConfirmation();
            }
        });
    </script>
</body>
</html>