<?php
session_start();
include '../../db_connect.php';
include '../../log_page_view.php';

if (!isset($_SESSION['userID']) || $_SESSION['roleID'] != 2) {
    header("Location: ../index.php?error=access_denied");
    exit();
}

$creatorID = $_SESSION['userID'];
$songID = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($songID === 0) {
    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'manage_album.php') !== false) {
        header("Location: " . $_SERVER['HTTP_REFERER'] . "&error=no_song_id");
    } else {
        header("Location: ../dashboard.php?error=no_song_id");
    }
    exit();
}

$songDetails = null;
$stmt = $conn->prepare("
    SELECT cs.CreatorSongID, cs.CreatorSongName, cs.CreatorAlbumID, cs.ReleaseYear, cs.FilePath, cs.IsPublic, ca.CreatorAlbumName
    FROM CreatorSongs cs
    JOIN CreatorAlbums ca ON cs.CreatorAlbumID = ca.CreatorAlbumID
    WHERE cs.CreatorSongID = ? AND ca.CreatorID = ?
");
$stmt->bind_param("ii", $songID, $creatorID);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $songDetails = $result->fetch_assoc();
}
$stmt->close();

if (!$songDetails) {
    $_SESSION['creator_message'] = "Song not found or you don't have permission to manage it.";
    $_SESSION['creator_message_type'] = "error";
    if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'manage_album.php') !== false) {
        header("Location: " . $_SERVER['HTTP_REFERER']);
    } else {
        header("Location: ../dashboard.php");
    }
    exit();
}

$creatorAlbums = [];
$albumsStmt = $conn->prepare("SELECT CreatorAlbumID, CreatorAlbumName FROM CreatorAlbums WHERE CreatorID = ? ORDER BY CreatorAlbumName ASC");
$albumsStmt->bind_param("i", $creatorID);
$albumsStmt->execute();
$albumsResult = $albumsStmt->get_result();
if ($albumsResult && $albumsResult->num_rows > 0) {
    while ($row = $albumsResult->fetch_assoc()) {
        $creatorAlbums[] = $row;
    }
}
$albumsStmt->close();

$message = '';
$messageType = '';
if (isset($_SESSION['creator_message'])) {
    $message = $_SESSION['creator_message'];
    $messageType = $_SESSION['creator_message_type'];
    unset($_SESSION['creator_message']);
    unset($_SESSION['creator_message_type']);
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Song: <?php echo htmlspecialchars($songDetails['CreatorSongName']); ?> - SoundWave</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <?php
            $backLink = '../dashboard.php';
            if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'manage_album.php') !== false) {
                $backLink = $_SERVER['HTTP_REFERER'];
            } else if (!empty($songDetails['CreatorAlbumID'])) {
                $backLink = '../manage_albums/manage_album.php?id=' . htmlspecialchars($songDetails['CreatorAlbumID']);
            }
            ?>
            <a href="<?php echo $backLink; ?>" class="btn btn-secondary">&larr; Back</a>
            <h1 class="logo" style="font-size: 1.5rem; text-align: center; flex-grow: 1; margin-left: 20px;">Manage Song: "<?php echo htmlspecialchars($songDetails['CreatorSongName']); ?>"</h1>
            <div class="user-nav">
                </div>
        </div>
    </header>

    <div class="main-content">
        <?php if ($message): ?>
            <div class="message <?php echo $messageType; ?>-message">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <section class="section">
            <h2 class="section-title">Edit Song Details</h2>
            <form action="process_song.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="song_id" value="<?php echo htmlspecialchars($songDetails['CreatorSongID']); ?>">
                <input type="hidden" name="current_file_path" value="<?php echo htmlspecialchars($songDetails['FilePath']); ?>">
                <input type="hidden" name="current_album_id" value="<?php echo htmlspecialchars($songDetails['CreatorAlbumID']); ?>">

                <div class="form-group">
                    <label for="editSongName">Song Name</label>
                    <input type="text" id="editSongName" name="song_name" value="<?php echo htmlspecialchars($songDetails['CreatorSongName']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="editSongAlbum">Album</label>
                    <select id="editSongAlbum" name="album_id" required>
                        <?php if (empty($creatorAlbums)): ?>
                            <option value="">No albums available. Please create an album first.</option>
                        <?php else: ?>
                            <?php foreach ($creatorAlbums as $album): ?>
                                <option value="<?php echo htmlspecialchars($album['CreatorAlbumID']); ?>"
                                    <?php echo ($album['CreatorAlbumID'] == $songDetails['CreatorAlbumID']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($album['CreatorAlbumName']); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="editSongReleaseYear">Release Year</label>
                    <input type="number" id="editSongReleaseYear" name="release_year" min="1900" max="<?php echo date('Y'); ?>" value="<?php echo htmlspecialchars($songDetails['ReleaseYear']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="editAudioFile">Change Audio File (Optional)</label>
                    <input type="file" id="editAudioFile" name="audio_file" accept=".mp3,.wav,.ogg">
                    <?php if (!empty($songDetails['FilePath'])): ?>
                        <div class="current-file-info" style="color: var(--text-gray); font-size: 0.9rem; margin-top: 5px;">
                            Current file: <?php echo basename($songDetails['FilePath']); ?>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <input type="checkbox" id="editIsPublicSong" name="is_public" value="1" <?php echo $songDetails['IsPublic'] ? 'checked' : ''; ?>>
                    <label for="editIsPublicSong">Publish Song? (Make it visible to listeners)</label>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">Update Song</button>
            </form>
        </section>

        <section class="section">
            <h2 class="section-title">Danger Zone</h2>
            <form action="process_song.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this song? This action cannot be undone.');">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="song_id" value="<?php echo htmlspecialchars($songDetails['CreatorSongID']); ?>">
                <input type="hidden" name="album_id" value="<?php echo htmlspecialchars($songDetails['CreatorAlbumID']); ?>">
                <button type="submit" class="btn btn-danger" style="width: 100%;">Delete Song</button>
            </form>
        </section>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const messageDiv = document.querySelector('.message');
            if (messageDiv) {
                setTimeout(() => {
                    messageDiv.style.opacity = '0';
                    messageDiv.style.transition = 'opacity 1s ease-out';
                    setTimeout(() => messageDiv.remove(), 1000);
                }, 5000);
            }
        });
    </script>
</body>
</html>