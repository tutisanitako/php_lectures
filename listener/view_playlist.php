<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['userID']) || $_SESSION['roleID'] != 3) {
    $_SESSION['login_error'] = "You must be logged in as a listener to view playlists.";
    $_SESSION['modal_to_open'] = 'login';
    header("Location: ../index.php");
    exit();
}

$playlistID = isset($_GET['playlist_id']) ? intval($_GET['playlist_id']) : 0;
$playlistName = "Unknown Playlist";
$songsInPlaylist = [];
$canEdit = false;

if ($playlistID > 0) {
    $stmt = $conn->prepare("SELECT PlaylistName, UserID FROM Playlists WHERE PlaylistID = ?");
    $stmt->bind_param("i", $playlistID);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $playlist = $result->fetch_assoc();
        $playlistName = htmlspecialchars($playlist['PlaylistName']);
        if ($playlist['UserID'] == $_SESSION['userID']) {
            $canEdit = true;
        } else {
            $_SESSION['playlist_message'] = "You do not have permission to view this playlist.";
            $_SESSION['playlist_message_type'] = "error";
            header("Location: ../index.php");
            exit();
        }

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
        header("Location: ../index.php");
        exit();
    }
    $stmt->close();
} else {
    $_SESSION['playlist_message'] = "Invalid playlist ID.";
    $_SESSION['playlist_message_type'] = "error";
    header("Location: ../index.php");
    exit();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $playlistName; ?> - Walkman</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <div class="logo"><a href="../index.php" style="text-decoration: none; color: inherit;">Walkman</a></div>
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
        </div>

        <?php
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