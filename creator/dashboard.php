<?php
session_start();
include '../db_connect.php';
include '../log_page_view.php';

if (!isset($_SESSION['userID']) || $_SESSION['roleID'] != 2) {
    header("Location: ../index.php?error=access_denied");
    exit();
}

$creatorID = $_SESSION['userID'];

function getCreatorAlbums($conn, $creatorID) {
    $albums = [];
    $stmt = $conn->prepare("SELECT CreatorAlbumID, CreatorAlbumName, ReleaseYear, IsPublic FROM CreatorAlbums WHERE CreatorID = ? ORDER BY CreatedAt DESC");
    $stmt->bind_param("i", $creatorID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $albums[] = $row;
        }
    }
    $stmt->close();
    return $albums;
}

function getCreatorSongs($conn, $creatorID) {
    $songs = [];
    $stmt = $conn->prepare("
        SELECT cs.CreatorSongID, cs.CreatorSongName, ca.CreatorAlbumName, cs.ReleaseYear, cs.IsPublic
        FROM CreatorSongs cs
        JOIN CreatorAlbums ca ON cs.CreatorAlbumID = ca.CreatorAlbumID
        WHERE ca.CreatorID = ?
        ORDER BY cs.CreatedAt DESC
    ");
    $stmt->bind_param("i", $creatorID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $songs[] = $row;
        }
    }
    $stmt->close();
    return $songs;
}

$creatorAlbums = getCreatorAlbums($conn, $creatorID);
$creatorSongs = getCreatorSongs($conn, $creatorID);

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
    <title>Creator Dashboard - SoundWave</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <a href="../index.php" class="logo-link">
                <div class="logo">SoundWave</div>
            </a>
            <div class="search-container">
                </div>
            <div class="user-nav">
                <span class="welcome-message">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                <a href="../logout.php" class="btn btn-secondary">Logout</a>
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
            <h2 class="section-title">💿 Your Albums</h2>
            <div class="cards-grid">
                <?php if (!empty($creatorAlbums)): ?>
                    <?php foreach ($creatorAlbums as $album): ?>
                        <div class="card clickable"> <div class="card-image">💿</div>
                            <div class="card-title"><?php echo htmlspecialchars($album['CreatorAlbumName']); ?></div>
                            <div class="card-subtitle">
                                <?php echo htmlspecialchars($album['ReleaseYear']); ?>
                                (<?php echo $album['IsPublic'] ? 'Public' : 'Draft'; ?>)
                            </div>
                            <div class="item-actions">
                                <a href="manage_albums/manage_album.php?id=<?php echo $album['CreatorAlbumID']; ?>" class="btn btn-primary btn-sm">Manage</a>
                                <a href="manage_albums/process_album.php?action=delete&album_id=<?php echo $album['CreatorAlbumID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this album and all its songs? This action cannot be undone.');">Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div class="playlist-add-card" onclick="showModal('createAlbumModal')">
                    <span class="icon">+</span>
                    <span class="text">Create New Album</span>
                </div>
            </div>
            <?php if (empty($creatorAlbums)): ?>
                <p class="empty-message">You haven't created any albums yet. Start by adding one!</p>
            <?php endif; ?>
        </section>

        <section class="section">
            <h2 class="section-title">🎵 Your Songs</h2>
            <div class="cards-grid">
                <?php if (!empty($creatorSongs)): ?>
                    <?php foreach ($creatorSongs as $song): ?>
                        <div class="card clickable"> <div class="card-image">🎵</div>
                            <div class="card-title"><?php echo htmlspecialchars($song['CreatorSongName']); ?></div>
                            <div class="card-subtitle">
                                <?php echo htmlspecialchars($song['CreatorAlbumName']); ?> &bull; <?php echo htmlspecialchars($song['ReleaseYear']); ?>
                                (<?php echo $song['IsPublic'] ? 'Public' : 'Draft'; ?>)
                            </div>
                            <div class="item-actions">
                                <a href="manage_songs/manage_song.php?id=<?php echo $song['CreatorSongID']; ?>" class="btn btn-primary btn-sm">Manage</a>
                                <a href="manage_songs/process_song.php?action=delete&song_id=<?php echo $song['CreatorSongID']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this song? This action cannot be undone.');">Delete</a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
                <div class="playlist-add-card" onclick="showModal('createSongModal')">
                    <span class="icon">+</span>
                    <span class="text">Upload New Song</span>
                </div>
            </div>
            <?php if (empty($creatorSongs)): ?>
                <p class="empty-message">You haven't uploaded any songs yet. Add your first song!</p>
            <?php endif; ?>
        </section>
    </div>

    <div id="createAlbumModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideModal('createAlbumModal')">&times;</span>
            <h2 class="form-title">Create New Album</h2>
            <form action="manage_albums/process_album.php" method="POST">
                <input type="hidden" name="action" value="create">
                <div class="form-group">
                    <label for="albumName">Album Name</label>
                    <input type="text" id="albumName" name="album_name" required>
                </div>
                <div class="form-group">
                    <label for="releaseYear">Release Year</label>
                    <input type="number" id="releaseYear" name="release_year" min="1900" max="<?php echo date('Y'); ?>" value="<?php echo date('Y'); ?>" required>
                </div>
                <div class="form-group">
                    <input type="checkbox" id="isPublicAlbum" name="is_public" value="1" checked>
                    <label for="isPublicAlbum">Publish Album? (Make it visible to listeners)</label>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Create Album</button>
            </form>
        </div>
    </div>

    <div id="createSongModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideModal('createSongModal')">&times;</span>
            <h2 class="form-title">Upload New Song</h2>
            <form action="manage_songs/process_song.php" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="create">
                <div class="form-group">
                    <label for="songName">Song Name</label>
                    <input type="text" id="songName" name="song_name" required>
                </div>
                <div class="form-group">
                    <label for="songAlbum">Select Album</label>
                    <select id="songAlbum" name="album_id" required>
                        <option value="">-- Select an Album --</option>
                        <?php
                        include '../db_connect.php';
                        $modalAlbums = getCreatorAlbums($conn, $creatorID);
                        $conn->close();
                        foreach ($modalAlbums as $album):
                            echo '<option value="' . htmlspecialchars($album['CreatorAlbumID']) . '">' . htmlspecialchars($album['CreatorAlbumName']) . '</option>';
                        endforeach;
                        ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="songReleaseYear">Release Year</label>
                    <input type="number" id="songReleaseYear" name="release_year" min="1900" max="<?php echo date('Y'); ?>" value="<?php echo date('Y'); ?>" required>
                </div>
                <div class="form-group">
                    <label for="audioFile">Audio File (MP3, WAV, etc.)</label>
                    <input type="file" id="audioFile" name="audio_file" accept=".mp3,.wav,.ogg" required>
                </div>
                <div class="form-group">
                    <input type="checkbox" id="isPublicSong" name="is_public" value="1" checked>
                    <label for="isPublicSong">Publish Song? (Make it visible to listeners)</label>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Upload Song</button>
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