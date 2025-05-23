<?php
include 'db_connect.php';
include 'log_page_view.php';
session_start();

// Check if ArtistID is provided in the URL
if (!isset($_GET['artist_id']) || !is_numeric($_GET['artist_id'])) {
    header("Location: index.php?error=artist_not_found");
    exit();
}

$artistID = $_GET['artist_id'];

$artist = null;
$albums = [];
$songs = [];

// Fetch Artist Details
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
    // Artist not found, redirect back
    header("Location: index.php?error=artist_not_found");
    exit();
}

// Fetch Albums by this Artist
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

// Fetch Songs by this Artist (limited to 8 for brevity, you can adjust)
// This query might need to join with Albums table to get songs directly associated with artist via albums
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

// Determine which modal to display based on session variable (for add to playlist)
$displayAddSongToPlaylistModal = false;
if (isset($_SESSION['modal_to_open'])) {
    if ($_SESSION['modal_to_open'] === 'addSongToPlaylist') {
        $displayAddSongToPlaylistModal = true;
    }
    unset($_SESSION['modal_to_open']);
}

// Re-fetch listener playlists for the 'add song to playlist' modal if a listener is logged in
$listenerPlaylistsForModal = [];
if (isset($_SESSION['userID']) && $_SESSION['roleID'] == 3) {
    // Need to re-establish connection as it was closed earlier, or pass it from the main include
    include 'db_connect.php'; // Re-open connection for this specific fetch
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
    $conn->close(); // Close again
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($artist['ArtistName']); ?> - SoundWave</title>
    <link rel="stylesheet" href="style.css"> <style>
        /* Specific styles for Artist Profile page */
        .artist-header {
            background-color: var(--card-bg);
            padding: 4rem 2rem;
            text-align: center;
            border-radius: 20px;
            margin-bottom: 3rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
        }

        .artist-header h1 {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 2px 5px rgba(0, 0, 0, 0.3);
        }

        .artist-header p {
            color: var(--text-gray);
            font-size: 1.1rem;
            line-height: 1.6;
            max-width: 700px;
            margin: 0 auto 1.5rem auto;
        }

        .artist-meta {
            font-size: 0.95rem;
            color: var(--text-gray);
            margin-bottom: 1rem;
        }
        .artist-meta strong {
            color: var(--text-white);
        }

        /* Adjustments for existing card styles for this page */
        .cards-grid {
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); /* Slightly smaller cards */
        }
        .card {
            padding: 1.2rem;
            text-align: center;
        }
        .card-image {
            height: 120px; /* Smaller image height */
            font-size: 1.8rem;
        }
        .card-title {
            font-size: 1.1rem;
        }
        .card-subtitle {
            font-size: 0.85rem;
        }

        /* Specific styles for songs on artist profile */
        .song-card .card-image {
            background: linear-gradient(45deg, var(--secondary-color), var(--primary-color));
        }

        /* Specific styles for albums on artist profile */
        .album-card .card-image {
            background: linear-gradient(45deg, #8e44ad, #9b59b6); /* Different gradient for albums */
        }

        /* General section titles */
        .section-title {
            text-align: center;
            margin-top: 3rem;
            margin-bottom: 2rem;
            font-size: 2.5rem;
            color: var(--text-white);
        }
        .section-title span {
            background: linear-gradient(45deg, var(--primary-color), var(--accent-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* No content message */
        .no-content-message {
            text-align: center;
            color: var(--text-gray);
            font-style: italic;
            padding: 2rem;
            background-color: var(--darker-bg);
            border-radius: 10px;
            margin-bottom: 2rem;
        }

        @media (max-width: 768px) {
            .artist-header {
                padding: 3rem 1.5rem;
            }
            .artist-header h1 {
                font-size: 2.8rem;
            }
            .artist-header p {
                font-size: 1rem;
            }
            .section-title {
                font-size: 2rem;
            }
        }

        @media (max-width: 480px) {
            .artist-header {
                padding: 2rem 1rem;
            }
            .artist-header h1 {
                font-size: 2.2rem;
            }
            .artist-header p {
                font-size: 0.9rem;
            }
            .cards-grid {
                grid-template-columns: 1fr;
            }
            .card {
                padding: 1rem;
            }
            .card-image {
                height: 100px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <a href="index.php" class="logo-link"><div class="logo">SoundWave</div></a>
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
                        <a href="admin/dashboard.php" class="btn btn-primary">Admin Dashboard</a>
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
                            <p class="no-playlists-message">You have no playlists yet. <br>Please <span style="color: var(--primary-color); cursor: pointer; text-decoration: underline;" onclick="hideModal('addSongToPlaylistModal'); window.location.href = 'index.php?modal_to_open=createPlaylist';">create one</span> first!</p>
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
        let currentSongID = null; // To store the song ID when the modal opens

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

        // Handle the case where the user has no playlists and clicks "create one" within the add song modal
        // This will redirect to index.php and open the createPlaylist modal there
        document.querySelector('.no-playlists-message span').onclick = function() {
            hideModal('addSongToPlaylistModal');
            window.location.href = 'index.php?modal_to_open=createPlaylist';
        };

        // PHP-driven modal display for 'add song to playlist' if there was an error
        <?php if ($displayAddSongToPlaylistModal): ?>
            // This re-opens the add song modal on the artist profile page if there was an error
            // (You'd need to pass the songID and songName back via session for this to be fully functional)
            // For now, it just opens the empty modal. A more robust solution would involve AJAX or more session data.
            showModal('addSongToPlaylistModal');
        <?php endif; ?>

        // Re-add event listener for search bar (if you have dynamic search functionality)
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