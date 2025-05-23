<?php
include '../db_connect.php';
include '../log_page_view.php';
session_start();

// Check if user is logged in and is an Artist (RoleID = 2)
if (!isset($_SESSION['userID']) || $_SESSION['roleID'] != 2) {
    header("Location: ../index.php?error=access_denied");
    exit();
}

$artistID = null;
// Get the ArtistID linked to the logged-in UserID
$stmt = $conn->prepare("SELECT ArtistID FROM Artists WHERE UserID = ?");
$stmt->bind_param("i", $_SESSION['userID']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $artistData = $result->fetch_assoc();
    $artistID = $artistData['ArtistID'];
} else {
    // No artist profile found for this user, something is wrong.
    header("Location: ../logout.php?error=no_artist_profile_found");
    exit();
}
$stmt->close();

$albums = [];
// Fetch all albums for the logged-in artist
$stmt = $conn->prepare("SELECT AlbumID, AlbumName, ReleaseYear FROM Albums WHERE ArtistID = ? ORDER BY ReleaseYear DESC, AlbumName ASC");
$stmt->bind_param("i", $artistID);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $albums[] = $row;
}
$stmt->close();

$conn->close();

// Check for messages (success/error from actions like add/edit/delete)
$message = '';
$messageType = '';
if (isset($_SESSION['album_message'])) {
    $message = $_SESSION['album_message'];
    $messageType = $_SESSION['album_message_type'];
    unset($_SESSION['album_message']);
    unset($_SESSION['album_message_type']);
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Albums - SoundWave Artist</title>
    <link rel="stylesheet" href="../style.css"> <style>
        /* Specific styles for forms and tables on management pages */
        .management-container {
            max-width: 900px;
            margin: 120px auto 40px auto; /* Adjust margin-top for fixed header */
            background-color: var(--card-bg);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        .management-title {
            font-size: 2.5rem;
            text-align: center;
            margin-bottom: 30px;
            background: linear-gradient(45deg, var(--artist-color), #feca57);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        .action-buttons {
            display: flex;
            justify-content: flex-end; /* Align to right */
            margin-bottom: 25px;
            gap: 15px;
        }

        .action-btn {
            background: linear-gradient(45deg, var(--artist-color), #feca57);
            color: var(--darker-bg); /* Darker text for light buttons */
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            font-size: 0.95rem;
            box-shadow: 0 4px 10px rgba(254, 205, 78, 0.3);
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 15px rgba(254, 205, 78, 0.5);
            opacity: 0.9;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
            background-color: var(--darker-bg);
            border-radius: 10px;
            overflow: hidden; /* Ensures rounded corners */
        }

        .data-table th, .data-table td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .data-table th {
            background-color: var(--primary-color);
            color: var(--text-white);
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.9rem;
        }

        .data-table tr:last-child td {
            border-bottom: none;
        }

        .data-table tbody tr:hover {
            background-color: var(--hover-bg);
        }

        .data-table .actions {
            display: flex;
            gap: 8px;
            justify-content: center; /* Center action buttons */
        }

        .data-table .action-icon-btn {
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            color: var(--text-gray);
            transition: color 0.2s ease, transform 0.2s ease;
            padding: 5px; /* Add padding for easier clicking */
        }

        .data-table .action-icon-btn.edit:hover {
            color: var(--artist-color);
            transform: scale(1.2);
        }
        .data-table .action-icon-btn.delete:hover {
            color: var(--error-red);
            transform: scale(1.2);
        }

        /* Form styling (for add/edit modals) */
        .modal .form-group label {
            color: var(--text-white);
        }
        .modal .form-group input, .modal .form-group select {
            background: var(--darker-bg);
            border: 1px solid rgba(255, 234, 167, 0.3); /* Artist themed border */
            color: var(--text-white);
            padding: 0.8rem;
            border-radius: 8px;
            width: 100%;
            font-size: 1rem;
        }
        .modal .form-group input:focus, .modal .form-group select:focus {
            outline: none;
            border-color: var(--artist-color);
            box-shadow: 0 0 0 2px rgba(255, 234, 167, 0.2);
        }
        .modal .btn-primary {
            background: linear-gradient(45deg, var(--artist-color), #feca57);
            color: var(--darker-bg);
        }
        .modal .btn-primary:hover {
            box-shadow: 0 5px 15px rgba(254, 205, 78, 0.4);
        }

        /* Message styling (success/error) */
        .message {
            padding: 10px 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            font-size: 0.95rem;
            text-align: center;
            opacity: 0; /* Start hidden */
            transform: translateY(-10px);
            animation: fadeInMessage 0.5s forwards;
        }

        @keyframes fadeInMessage {
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .success-message {
            background-color: rgba(46, 204, 113, 0.2);
            color: var(--success-green);
            border: 1px solid var(--success-green);
        }

        .error-message {
            background-color: rgba(255, 107, 107, 0.2);
            color: var(--error-red);
            border: 1px solid var(--error-red);
        }

        .no-records {
            text-align: center;
            color: var(--text-gray);
            padding: 30px;
            background-color: var(--darker-bg);
            border-radius: 10px;
            margin-top: 20px;
        }

        /* Re-using modal styles from index.php, ensure path is correct */
        /* If `style.css` contains all modal styles, remove duplicated modal CSS from here */
    </style>
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <a href="../index.php" class="logo">SoundWave</a>
            <div class="search-container">
                <input type="text" class="search-bar" placeholder="Search for songs, artists, albums..." id="searchInput">
            </div>
            <div class="user-nav">
                <span class="welcome-message">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="dashboard.php" class="btn btn-primary">Artist Dashboard</a>
            </div>
        </div>
    </header>

    <main class="management-container">
        <h1 class="management-title">Manage Your Albums</h1>

        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="action-buttons">
            <button class="action-btn" onclick="openAddAlbumModal()">+ Add New Album</button>
        </div>

        <?php if (!empty($albums)): ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Album Name</th>
                        <th>Release Year</th>
                        <th style="width: 120px; text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($albums as $album): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($album['AlbumName']); ?></td>
                            <td><?php echo htmlspecialchars($album['ReleaseYear']); ?></td>
                            <td class="actions">
                                <button class="action-icon-btn edit" onclick="openEditAlbumModal(<?php echo $album['AlbumID']; ?>, '<?php echo addslashes($album['AlbumName']); ?>', <?php echo $album['ReleaseYear']; ?>)">✏️</button>
                                <button class="action-icon-btn delete" onclick="showDeleteConfirmation(<?php echo $album['AlbumID']; ?>, '<?php echo addslashes($album['AlbumName']); ?>')">🗑️</button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-records">You haven't added any albums yet. Click "Add New Album" to get started!</p>
        <?php endif; ?>
    </main>

    <div id="addAlbumModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideModal('addAlbumModal')">&times;</span>
            <h2 class="form-title">Add New Album</h2>
            <form action="process_album.php" method="POST">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="artist_id" value="<?php echo htmlspecialchars($artistID); ?>">
                <div class="form-group">
                    <label for="newAlbumName">Album Name</label>
                    <input type="text" id="newAlbumName" name="album_name" required>
                </div>
                <div class="form-group">
                    <label for="newReleaseYear">Release Year</label>
                    <input type="number" id="newReleaseYear" name="release_year" min="1900" max="<?php echo date('Y'); ?>" value="<?php echo date('Y'); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Add Album</button>
            </form>
        </div>
    </div>

    <div id="editAlbumModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideModal('editAlbumModal')">&times;</span>
            <h2 class="form-title">Edit Album</h2>
            <form action="process_album.php" method="POST">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="album_id" id="editAlbumID">
                <input type="hidden" name="artist_id" value="<?php echo htmlspecialchars($artistID); ?>">
                <div class="form-group">
                    <label for="editAlbumName">Album Name</label>
                    <input type="text" id="editAlbumName" name="album_name" required>
                </div>
                <div class="form-group">
                    <label for="editReleaseYear">Release Year</label>
                    <input type="number" id="editReleaseYear" name="release_year" min="1900" max="<?php echo date('Y'); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Save Changes</button>
            </form>
        </div>
    </div>

    <div id="deleteConfirmationModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideModal('deleteConfirmationModal')">&times;</span>
            <h2 class="form-title" style="color: var(--error-red);">Confirm Deletion</h2>
            <p>Are you sure you want to delete the album "<strong id="albumNameToDelete"></strong>"?</p>
            <p style="font-size: 0.9em; color: var(--text-gray);">This will also delete all songs associated with this album!</p>
            <div class="modal-buttons">
                <form action="process_album.php" method="POST" style="display: inline;">
                    <input type="hidden" name="action" value="delete">
                    <input type="hidden" name="album_id" id="deleteAlbumID">
                    <button type="submit" class="btn modal-btn confirm">Yes, Delete It</button>
                </form>
                <button type="button" class="btn modal-btn cancel" onclick="hideModal('deleteConfirmationModal')">Cancel</button>
            </div>
        </div>
    </div>


    <script>
        // Generic modal functions (can be reused if you have a common JS file)
        function showModal(modalId) {
            document.getElementById(modalId).style.display = 'flex'; // Use flex for centering
            // Add a small delay for animation if using transitions on display property
        }

        function hideModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Close modal if user clicks outside of the content
        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        // Album specific functions
        function openAddAlbumModal() {
            // Reset form fields if needed
            document.getElementById('newAlbumName').value = '';
            document.getElementById('newReleaseYear').value = new Date().getFullYear();
            showModal('addAlbumModal');
        }

        function openEditAlbumModal(albumID, albumName, releaseYear) {
            document.getElementById('editAlbumID').value = albumID;
            document.getElementById('editAlbumName').value = albumName;
            document.getElementById('editReleaseYear').value = releaseYear;
            showModal('editAlbumModal');
        }

        function showDeleteConfirmation(albumID, albumName) {
            document.getElementById('deleteAlbumID').value = albumID;
            document.getElementById('albumNameToDelete').textContent = albumName;
            showModal('deleteConfirmationModal');
        }

        // Add search functionality (re-add from index.php if not in a global script)
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        const query = encodeURIComponent(this.value);
                        window.location.href = `../search_results.php?query=${query}`; // Adjust path if search_results.php is not in root
                    }
                });
            }
        });
    </script>
</body>
</html>