<?php
include '../db_connect.php';
include '../log_page_view.php';
session_start();

if (!isset($_GET['artist_id']) || !is_numeric($_GET['artist_id'])) {
    header("Location: ../index.php?error=artist_not_found");
    exit();
}

$artistID = $_GET['artist_id'];

$artist = null;
$albums = [];
$songs = [];

$stmt = $conn->prepare("SELECT ArtistID, ArtistName, CompanyID FROM Artists WHERE ArtistID = ?");
if ($stmt) {
    $stmt->bind_param("i", $artistID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows > 0) {
        $artist = $result->fetch_assoc();
    }
    $stmt->close();
}

if (!$artist) {
    header("Location: ../index.php?error=artist_not_found");
    exit();
}

$stmt = $conn->prepare("SELECT AlbumID, AlbumName, ReleaseYear FROM Albums WHERE ArtistID = ? ORDER BY ReleaseYear DESC");
if ($stmt) {
    $stmt->bind_param("i", $artistID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $albums[] = $row;
    }
    $stmt->close();
}

$stmt = $conn->prepare("
    SELECT s.SongID, s.SongName, al.AlbumName, al.ReleaseYear
    FROM Songs s
    JOIN Albums al ON s.AlbumID = al.AlbumID
    WHERE al.ArtistID = ?
    ORDER BY s.SongName ASC
    LIMIT 8
");
if ($stmt) {
    $stmt->bind_param("i", $artistID);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $songs[] = $row;
    }
    $stmt->close();
}

$conn->close();

$displayAddSongToPlaylistModal = false;
if (isset($_SESSION['modal_to_open'])) {
    if ($_SESSION['modal_to_open'] === 'addSongToPlaylist') {
        $displayAddSongToPlaylistModal = true;
    }
    unset($_SESSION['modal_to_open']);
}

$listenerPlaylistsForModal = [];
if (isset($_SESSION['userID']) && $_SESSION['roleID'] == 3) {
    include '../db_connect.php';
    $stmt = $conn->prepare("SELECT PlaylistID, PlaylistName FROM Playlists WHERE UserID = ? ORDER BY CreatedAt DESC");
    if ($stmt) {
        $stmt->bind_param("i", $_SESSION['userID']);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $listenerPlaylistsForModal[] = $row;
        }
        $stmt->close();
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($artist['ArtistName']); ?> - Walkman</title>
    <link rel="stylesheet" href="style.css">

</head>
<body>
    <header class="header">
        <div class="nav-container">
            <a href="../index.php" class="logo-link"><div class="logo">Walkman</div></a>
            <div class="search-container">
                <input type="text" class="search-bar" placeholder="Search for songs, artists, albums..." id="searchInput">
            </div>
            <?php if (isset($_SESSION['userID'])): ?>
                <div class="user-nav">
                    <span class="welcome-message">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <?php if ($_SESSION['roleID'] == 3): ?>
                        <a href="#my-playlists" class="btn btn-secondary">My Playlists</a>
                        <a href="listener/my_profile.php" class="btn btn-primary">My Profile</a>
                    <?php elseif ($_SESSION['roleID'] == 1): ?>
                        <a href="../admin/dashboard.php" class="btn btn-primary">Admin Dashboard</a>
                    <?php endif; ?>
                </div>
            <?php else: ?>
                <div class="auth-buttons">
                    <button type="button" class="btn btn-secondary" onclick="showModal('loginModal')">Login</button>
                    <button type="button" class="btn btn-primary" onclick="showModal('signupModal')">Sign Up</button>
                </div>
            <?php endif; ?>
        </div>
    </header>

    <main class="main-content">
        <section class="artist-header">
            <h1><?php echo htmlspecialchars($artist['ArtistName']); ?></h1>
            <?php if (!empty($artist['Bio'])): ?>
                <p><?php echo nl2br(htmlspecialchars($artist['Bio'])); ?></p>
            <?php else: ?>
                <p>No biography available for this artist yet.</p>
            <?php endif; ?>
            <?php if (!empty($artist['CompanyName'])): ?>
                <p class="artist-meta">Managed by: <strong><?php echo htmlspecialchars($artist['CompanyName']); ?></strong></p>
            <?php else: ?>
                <p class="artist-meta">Status: <strong>Independent</strong></p>
            <?php endif; ?>
        </section>

        <section class="section">
            <h2 class="section-title"><span>🎶</span> Songs by <?php echo htmlspecialchars($artist['ArtistName']); ?></h2>
            <div class="cards-grid">
                <?php if (!empty($songs)): ?>
                    <?php foreach ($songs as $song): ?>
                        <div class="card song-card">
                            <div class="card-image">🎵</div>
                            <div class="card-title"><?php echo htmlspecialchars($song['SongName']); ?></div>
                            <div class="card-subtitle"><?php echo htmlspecialchars($song['AlbumName'] ?: 'Single'); ?> • <?php echo htmlspecialchars($song['ReleaseYear']); ?></div>
                            <?php if (isset($_SESSION['userID']) && $_SESSION['roleID'] == 3): ?>
                                <button class="add-to-playlist-btn" onclick="openAddSongModal(<?php echo $song['SongID']; ?>, '<?php echo addslashes($song['SongName']); ?>')">+</button>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-content-message">No songs found for this artist.</p>
                <?php endif; ?>
            </div>
        </section>

        <section class="section">
            <h2 class="section-title"><span>💿</span> Albums by <?php echo htmlspecialchars($artist['ArtistName']); ?></h2>
            <div class="cards-grid">
                <?php if (!empty($albums)): ?>
                    <?php foreach ($albums as $album): ?>
                        <div class="card album-card">
                            <div class="card-image">💿</div>
                            <div class="card-title"><?php echo htmlspecialchars($album['AlbumName']); ?></div>
                            <div class="card-subtitle">Released: <?php echo htmlspecialchars($album['ReleaseYear']); ?></div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-content-message">No albums found for this artist.</p>
                <?php endif; ?>
            </div>
        </section>
    </main>

    <div id="addSongToPlaylistModal" class="modal" style="display: <?php echo $displayAddSongToPlaylistModal ? 'block' : 'none'; ?>;">
        <div class="modal-content">
            <span class="close" onclick="hideModal('addSongToPlaylistModal')">&times;</span>
            <h2 class="form-title">Add "<span id="songTitleForModal"></span>" to Playlist</h2>
            <form action="listener/add_song_to_playlist.php" method="POST">
                <input type="hidden" name="song_id" id="addSongModalSongID">
                <div class="form-group">
                    <label>Select a Playlist:</label>
                    <div class="playlist-selection" id="playlistSelectionRadios">
                        <?php if (empty($listenerPlaylistsForModal)): ?>
                            <p class="no-playlists-message">You have no playlists yet. <br>Please <span style="color: var(--primary-color); cursor: pointer; text-decoration: underline;" onclick="hideModal('addSongToPlaylistModal'); window.location.href = '../index.php?modal_to_open=createPlaylist';">create one</span> first!</p>
                        <?php else: ?>
                            <?php foreach ($listenerPlaylistsForModal as $playlist): ?>
                                <label class="playlist-option">
                                    <input type="radio" name="playlist_id" value="<?php echo htmlspecialchars($playlist['PlaylistID']); ?>" required>
                                    <?php echo htmlspecialchars($playlist['PlaylistName']); ?>
                                </label>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (!empty($listenerPlaylistsForModal)): ?>
                    <button type="submit" class="btn btn-primary" style="width: 100%;">Add Song</button>
                <?php endif; ?>
            </form>
        </div>
    </div>
    <div id="loginModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideModal('loginModal')">&times;</span>
            <h2 class="form-title">Welcome Back</h2>
            <form action="login.php" method="POST">
                <div class="form-group">
                    <label for="loginUsername">Username</label>
                    <input type="text" id="loginUsername" name="username" required>
                </div>
                <div class="form-group">
                    <label for="loginPassword">Password</label>
                    <input type="password" id="loginPassword" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Login</button>
            </form>
        </div>
    </div>

    <div id="signupModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideModal('signupModal')">&times;</span>
            <h2 class="form-title">Join SoundWave</h2>
            <form action="signup.php" method="POST">
                <div class="form-group">
                    <label for="signupFullName">Full Name</label>
                    <input type="text" id="signupFullName" name="fullname" required>
                </div>
                <div class="form-group">
                    <label for="signupUsername">Username</label>
                    <input type="text" id="signupUsername" name="username" required>
                </div>
                <div class="form-group">
                    <label for="signupEmail">Email</label>
                    <input type="email" id="signupEmail" name="email" required>
                </div>
                <div class="form-group">
                    <label for="signupPassword">Password</label>
                    <input type="password" id="signupPassword" name="password" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Sign Up as Listener</button>
            </form>
        </div>
    </div>

    <div id="createPlaylistModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="hideModal('createPlaylistModal')">&times;</span>
            <h2 class="form-title">Create New Playlist</h2>
            <form action="create_playlist.php" method="POST">
                <div class="form-group">
                    <label for="playlistName">Playlist Name</label>
                    <input type="text" id="playlistName" name="playlist_name" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Create Playlist</button>
            </form>
        </div>
    </div>


    <script>
        let currentSongID = null;

        function showModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function hideModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('modal')) {
                event.target.style.display = 'none';
            }
        }

        function openAddSongModal(songID, songName) {
            currentSongID = songID;
            document.getElementById('addSongModalSongID').value = songID;
            document.getElementById('songTitleForModal').textContent = songName;

            const radioButtons = document.querySelectorAll('#playlistSelectionRadios input[type="radio"]');
            radioButtons.forEach(radio => radio.checked = false);

            showModal('addSongToPlaylistModal');
        }

        document.querySelector('.no-playlists-message span').onclick = function() {
            hideModal('addSongToPlaylistModal');
            window.location.href = '../index.php?modal_to_open=createPlaylist';
        };

        <?php if ($displayAddSongToPlaylistModal): ?>
            showModal('addSongToPlaylistModal');
        <?php endif; ?>

        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('searchInput');
            if (searchInput) {
                searchInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        const query = encodeURIComponent(this.value);
                        window.location.href = `search_results.php?query=${query}`;
                    }
                });
            }
        });

    </script>
</body>
</html>