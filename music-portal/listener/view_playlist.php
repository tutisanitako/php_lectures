<?php
session_start();
include '../db_connect.php'; // Adjust path based on your folder structure

// Check if user is logged in and is a listener
if (!isset($_SESSION['userID']) || $_SESSION['roleID'] != 3) {
    $_SESSION['login_error'] = "You must be logged in as a listener to view playlists.";
    $_SESSION['modal_to_open'] = 'login';
    header("Location: ../index.php"); // Redirect to home/login
    exit();
}

$playlistID = isset($_GET['playlist_id']) ? intval($_GET['playlist_id']) : 0;
$playlistName = "Unknown Playlist";
$songsInPlaylist = [];
$canEdit = false; // Flag to check if current user owns this playlist

if ($playlistID > 0) {
    // Fetch playlist details
    $stmt = $conn->prepare("SELECT PlaylistName, UserID FROM Playlists WHERE PlaylistID = ?");
    $stmt->bind_param("i", $playlistID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $playlist = $result->fetch_assoc();
        $playlistName = htmlspecialchars($playlist['PlaylistName']);
        // Check if the logged-in user owns this playlist
        if ($playlist['UserID'] == $_SESSION['userID']) {
            $canEdit = true;
        } else {
            $_SESSION['playlist_message'] = "You do not have permission to view this playlist.";
            $_SESSION['playlist_message_type'] = "error";
            header("Location: ../index.php");
            exit();
        }

        // Fetch songs in the playlist
        $stmt_songs = $conn->prepare("
            SELECT ps.PlaylistSongID, s.SongID, s.SongName, al.AlbumName, ar.ArtistName
            FROM PlaylistSongs ps
            JOIN Songs s ON ps.SongID = s.SongID
            JOIN Albums al ON s.AlbumID = al.AlbumID
            JOIN Artists ar ON al.ArtistID = ar.ArtistID
            WHERE ps.PlaylistID = ?
            ORDER BY ps.AddedAt ASC
        ");
        $stmt_songs->bind_param("i", $playlistID);
        $stmt_songs->execute();
        $result_songs = $stmt_songs->get_result();
        while ($row = $result_songs->fetch_assoc()) {
            $songsInPlaylist[] = $row;
        }
        $stmt_songs->close();

    } else {
        $_SESSION['playlist_message'] = "Playlist not found.";
        $_SESSION['playlist_message_type'] = "error";
        header("Location: ../index.php"); // Redirect if playlist not found
        exit();
    }
    $stmt->close();
} else {
    $_SESSION['playlist_message'] = "Invalid playlist ID.";
    $_SESSION['playlist_message_type'] = "error";
    header("Location: ../index.php"); // Redirect if no ID
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $playlistName; ?> - SoundWave</title>
    <style>
        /* Re-use most of your index.php CSS */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary-color: #6c5ce7;
            --secondary-color: #a29bfe;
            --accent-color: #fd79a8;
            --dark-bg: #2d3436;
            --darker-bg: #1e2124;
            --text-white: #ffffff;
            --text-gray: #b2bec3;
            --card-bg: #36393f;
            --hover-bg: #40434a;
            --error-red: #ff6b6b;
            --success-green: #2ecc71;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--darker-bg) 0%, var(--dark-bg) 100%);
            color: var(--text-white);
            min-height: 100vh;
        }

        /* Header (copy from index.php or create a shared header file) */
        .header {
            background: rgba(45, 52, 54, 0.95);
            backdrop-filter: blur(10px);
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            border-bottom: 1px solid rgba(108, 92, 231, 0.3);
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: bold;
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .search-container {
            flex: 1;
            max-width: 400px;
            margin: 0 2rem;
            position: relative;
        }

        .search-bar {
            width: 100%;
            padding: 0.8rem 1rem;
            border: none;
            border-radius: 25px;
            background: var(--card-bg);
            color: var(--text-white);
            font-size: 1rem;
            outline: none;
            transition: all 0.3s ease;
        }

        .search-bar:focus {
            box-shadow: 0 0 0 2px var(--primary-color);
        }

        .auth-buttons, .user-nav {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .btn {
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            text-align: center;
            transition: all 0.3s ease;
            font-size: 0.9rem;
        }

        .btn-primary {
            background: linear-gradient(45deg, var(--primary-color), var(--secondary-color));
            color: white;
        }

        .btn-secondary {
            background: transparent;
            color: var(--text-white);
            border: 2px solid var(--primary-color);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(108, 92, 231, 0.4);
        }

        .welcome-message {
            color: var(--text-white);
            font-size: 1rem;
            margin-right: 15px;
            white-space: nowrap;
        }
        .user-nav .btn {
            padding: 0.6rem 1.2rem;
            font-size: 0.85rem;
        }

        /* Main Content specific for this page */
        .main-content {
            margin-top: 100px; /* To clear fixed header */
            padding: 2rem;
            max-width: 900px;
            margin-left: auto;
            margin-right: auto;
        }

        .playlist-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            border-bottom: 1px solid rgba(253, 121, 168, 0.3);
            padding-bottom: 1rem;
        }

        .playlist-title {
            font-size: 2.5rem;
            color: var(--accent-color);
            background: linear-gradient(45deg, var(--accent-color), #ffafbe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .song-list {
            list-style: none;
            padding: 0;
        }

        .song-item {
            background: var(--card-bg);
            border-radius: 10px;
            padding: 1rem 1.5rem;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: background 0.3s ease;
        }

        .song-item:hover {
            background: var(--hover-bg);
        }

        .song-info {
            flex-grow: 1;
        }

        .song-info h3 {
            font-size: 1.1rem;
            margin-bottom: 0.3rem;
            color: var(--text-white);
        }

        .song-info p {
            font-size: 0.9rem;
            color: var(--text-gray);
        }

        .song-actions {
            display: flex;
            gap: 1rem;
            align-items: center;
        }

        .song-actions .btn {
            padding: 0.5rem 1rem;
            font-size: 0.8rem;
        }
        
        .empty-playlist-message {
            color: var(--text-gray);
            text-align: center;
            padding: 3rem;
            font-size: 1.1rem;
            border: 2px dashed var(--text-gray);
            border-radius: 15px;
            margin-top: 2rem;
        }

        /* Message Styles (copied for consistency) */
        .message {
            padding: 10px 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            font-size: 0.9rem;
            text-align: center;
        }
        .error-message {
            background-color: rgba(255, 107, 107, 0.2);
            color: var(--error-red);
            border: 1px solid var(--error-red);
        }
        .success-message {
            background-color: rgba(46, 204, 113, 0.2);
            color: var(--success-green);
            border: 1px solid var(--success-green);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .nav-container {
                flex-wrap: wrap;
                justify-content: center;
                gap: 1rem;
            }
            .search-container {
                order: 3;
                flex-basis: 100%;
                margin: 0;
            }
            .main-content {
                padding: 1rem;
            }
            .playlist-title {
                font-size: 2rem;
            }
            .song-item {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.5rem;
            }
            .song-actions {
                width: 100%;
                justify-content: flex-end;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <div class="logo"><a href="../index.php" style="text-decoration: none; color: inherit;">SoundWave</a></div>
            <div class="search-container">
                <input type="text" class="search-bar" placeholder="Search for songs, artists, albums..." id="searchInput">
            </div>
            <div class="user-nav">
                <span class="welcome-message">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="../index.php" class="btn btn-secondary">Home</a>
                <a href="my_profile.php" class="btn btn-primary">My Profile</a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <div class="playlist-header">
            <h1 class="playlist-title"><?php echo $playlistName; ?></h1>
            <?php if ($canEdit): ?>
                <?php endif; ?>
        </div>

        <?php
        // Display song action messages (add/remove)
        if (isset($_SESSION['song_action_message'])) {
            $messageClass = ($_SESSION['song_action_message_type'] == 'success') ? 'success-message' : 'error-message';
            echo '<div class="message ' . $messageClass . '">' . htmlspecialchars($_SESSION['song_action_message']) . '</div>';
            unset($_SESSION['song_action_message']);
            unset($_SESSION['song_action_message_type']);
        }
        ?>

        <?php if (empty($songsInPlaylist)): ?>
            <p class="empty-playlist-message">This playlist is empty. Go to the <a href="../index.php" style="color: var(--primary-color); text-decoration: none; font-weight: bold;">home page</a> to add some songs!</p>
        <?php else: ?>
            <ul class="song-list">
                <?php foreach ($songsInPlaylist as $song): ?>
                    <li class="song-item">
                        <div class="song-info">
                            <h3><?php echo htmlspecialchars($song['SongName']); ?></h3>
                            <p><?php echo htmlspecialchars($song['ArtistName']); ?> - <?php echo htmlspecialchars($song['AlbumName']); ?></p>
                        </div>
                        <div class="song-actions">
                            <form action="remove_song_from_playlist.php" method="POST" style="display:inline;">
                                <input type="hidden" name="playlist_song_id" value="<?php echo htmlspecialchars($song['PlaylistSongID']); ?>">
                                <input type="hidden" name="playlist_id" value="<?php echo htmlspecialchars($playlistID); ?>">
                                <button type="submit" class="btn btn-primary">Remove</button>
                            </form>
                        </div>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </main>
</body>
</html>