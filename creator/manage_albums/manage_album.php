<?php
session_start();
include '../../db_connect.php';
include '../../log_page_view.php';

if (!isset($_SESSION['userID']) || $_SESSION['roleID'] != 2) {
    header("Location: ../index.php?error=access_denied");
    exit();
}

$creatorID = $_SESSION['userID'];
$albumID = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($albumID === 0) {
    $_SESSION['creator_message'] = "No album ID provided.";
    $_SESSION['creator_message_type'] = "error";
    header("Location: ../dashboard.php");
    exit();
}

$albumDetails = null;
$stmt = $conn->prepare("SELECT CreatorAlbumID, CreatorAlbumName, ReleaseYear, IsPublic FROM CreatorAlbums WHERE CreatorAlbumID = ? AND CreatorID = ?");
$stmt->bind_param("ii", $albumID, $creatorID);
$stmt->execute();
$result = $stmt->get_result();
if ($result && $result->num_rows > 0) {
    $albumDetails = $result->fetch_assoc();
}
$stmt->close();

if (!$albumDetails) {
    $_SESSION['creator_message'] = "Album not found or you don't have permission to manage it.";
    $_SESSION['creator_message_type'] = "error";
    header("Location: ../dashboard.php");
    exit();
}

$songsInAlbum = [];
$songStmt = $conn->prepare("SELECT CreatorSongID, CreatorSongName, ReleaseYear, IsPublic FROM CreatorSongs WHERE CreatorAlbumID = ? ORDER BY CreatorSongName ASC");
$songStmt->bind_param("i", $albumID);
$songStmt->execute();
$songResult = $songStmt->get_result();
if ($songResult && $songResult->num_rows > 0) {
    while($row = $songResult->fetch_assoc()) {
        $songsInAlbum[] = $row;
    }
}
$songStmt->close();

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
    <title>Manage Album: <?php echo htmlspecialchars($albumDetails['CreatorAlbumName']); ?> - SoundWave</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <a href="../dashboard.php" class="btn btn-secondary">&larr; Back to Dashboard</a>
            <h1 class="logo" style="font-size: 1.5rem; text-align: center; flex-grow: 1; margin-left: 20px;"><?php echo htmlspecialchars($albumDetails['CreatorAlbumName']); ?></h1>
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
            <h2 class="section-title">Edit Album Details</h2>
            <form action="process_album.php" method="POST">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="album_id" value="<?php echo htmlspecialchars($albumDetails['CreatorAlbumID']); ?>">

                <div class="form-group">
                    <label for="editAlbumName">Album Name</label>
                    <input type="text" id="editAlbumName" name="album_name" value="<?php echo htmlspecialchars($albumDetails['CreatorAlbumName']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="editReleaseYear">Release Year</label>
                    <input type="number" id="editReleaseYear" name="release_year" min="1900" max="<?php echo date('Y'); ?>" value="<?php echo htmlspecialchars($albumDetails['ReleaseYear']); ?>" required>
                </div>
                <div class="form-group">
                    <input type="checkbox" id="editIsPublicAlbum" name="is_public" value="1" <?php echo $albumDetails['IsPublic'] ? 'checked' : ''; ?>>
                    <label for="editIsPublicAlbum">Publish Album? (Make it visible to listeners)</label>
                </div>
                <button type="submit" class="btn btn-primary">Update Album</button>
            </form>
        </section>

        <section class="section">
            <h2 class="section-title">Songs in this Album</h2>
            <div class="cards-grid">
                <?php if (empty($songsInAlbum)): ?>
                    <p class="empty-message">No songs in this album yet. Add one below!</p>
                <?php else: ?>
                    <?php foreach ($songsInAlbum as $song): ?>
                        <div class="card clickable">
                            <div class="card-image">🎵</div> <div class="card-title"><?php echo htmlspecialchars($song['CreatorSongName']); ?></div>
                            <div class="card-subtitle">
                                <?php echo htmlspecialchars($song['ReleaseYear']); ?>
                                (<?php echo $song['IsPublic'] ? 'Public' : 'Draft'; ?>)
                            </div>
                            <div class="item-actions">
                                <a href="../manage_songs/manage_song.php?id=<?php echo $song['CreatorSongID']; ?>" class="btn btn-sm btn-secondary">Edit</a>
                                <a href="../manage_songs/process_song.php?action=delete&song_id=<?php echo $song['CreatorSongID']; ?>&album_id=<?php echo $albumDetails['CreatorAlbumID']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure you want to delete this song? This action cannot be undone.');">Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div class="playlist-add-card" onclick="openCreateSongModalForAlbum(<?php echo htmlspecialchars($albumDetails['CreatorAlbumID']); ?>)">
                    <span class="icon">+</span>
                    <span class="text">Add New Song to this Album</span>
                </div>
            </div>
        </section>
    </div>

    <div id="createSongModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideModal('createSongModal')">&times;</span>
            <h2 class="form-title">Upload New Song to Album</h2>
            <form action="../manage_songs/process_song.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create">
                <input type="hidden" name="album_id" id="songAlbumIDForModal" value="<?php echo htmlspecialchars($albumDetails['CreatorAlbumID']); ?>">
                <div class="form-group">
                    <label for="modalSongName">Song Name</label>
                    <input type="text" id="modalSongName" name="song_name" required>
                </div>
                <div class="form-group">
                    <label for="modalSongReleaseYear">Release Year</label>
                    <input type="number" id="modalSongReleaseYear" name="release_year" min="1900" max="<?php echo date('Y'); ?>" value="<?php echo date('Y'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="modalAudioFile">Audio File (MP3, WAV, OGG)</label>
                    <input type="file" id="modalAudioFile" name="audio_file" accept=".mp3,.wav,.ogg" required>
                </div>
                <div class="form-group">
                    <input type="checkbox" id="modalIsPublicSong" name="is_public" value="1" checked>
                    <label for="modalIsPublicSong">Publish Song? (Make it visible to listeners)</label>
                </div>
                <button type="submit" class="btn btn-primary">Upload Song</button>
            </form>
        </div>
    </div>

    <script>
        function showModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
            document.body.classList.add('modal-open');
        }

        function hideModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.body.classList.remove('modal-open');
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                hideModal(event.target.id);
            }
        }

        function openCreateSongModalForAlbum(albumID) {
            document.getElementById('songAlbumIDForModal').value = albumID;
            document.getElementById('modalSongName').value = '';
            document.getElementById('modalAudioFile').value = '';
            document.getElementById('modalSongReleaseYear').value = new Date().getFullYear();
            document.getElementById('modalIsPublicSong').checked = true;
            showModal('createSongModal');
        }

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