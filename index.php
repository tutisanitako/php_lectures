<?php
include 'db_connect.php';
include 'log_page_view.php';
session_start();

function getTrendingSongs($conn) {
    $songs = [];
    $sql = "SELECT s.SongID, s.SongName, a.ArtistName, al.ReleaseYear
            FROM ListeningHistory lh
            JOIN Songs s ON lh.SongID = s.SongID
            JOIN Albums al ON s.AlbumID = al.AlbumID
            JOIN Artists a ON al.ArtistID = a.ArtistID
            GROUP BY s.SongID
            ORDER BY COUNT(lh.HistoryID) DESC
            LIMIT 4";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $songs[] = $row;
        }
    }
    return $songs;
}

function getPopularArtists($conn) {
    $artists = [];
    $sql = "SELECT ar.ArtistID, ar.ArtistName, c.CompanyName
            FROM Artists ar
            LEFT JOIN Company c ON ar.CompanyID = c.CompanyID
            LIMIT 4";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $artists[] = $row;
        }
    }
    return $artists;
}

function getPopularAlbums($conn) {
    $albums = [];
    $sql = "SELECT al.AlbumID, al.AlbumName, ar.ArtistName, al.ReleaseYear
            FROM Albums al
            JOIN Artists ar ON al.ArtistID = ar.ArtistID
            LIMIT 4";
    $result = $conn->query($sql);
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $albums[] = $row;
        }
    }
    return $albums;
}

function getUserPlaylists($conn, $userID) {
    $playlists = [];
    $stmt = $conn->prepare("SELECT PlaylistID, PlaylistName FROM Playlists WHERE UserID = ? ORDER BY CreatedAt DESC");
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $playlists[] = $row;
        }
    }
    $stmt->close();
    return $playlists;
}

$trendingSongs = getTrendingSongs($conn);
$popularArtists = getPopularArtists($conn);
$popularAlbums = getPopularAlbums($conn);

$userPlaylists = [];
$listenerPlaylistsForModal = [];
if (isset($_SESSION['userID']) && $_SESSION['roleID'] == 3) {
    $userPlaylists = getUserPlaylists($conn, $_SESSION['userID']);
    $listenerPlaylistsForModal = $userPlaylists;
}

$conn->close();

$displayLoginModal = false;
$displaySignupModal = false;
$displayCreatePlaylistModal = false;
$displayAddSongToPlaylistModal = false;

if (isset($_SESSION['modal_to_open'])) {
    if ($_SESSION['modal_to_open'] === 'login') {
        $displayLoginModal = true;
    } elseif ($_SESSION['modal_to_open'] === 'signup') {
        $displaySignupModal = true;
    } elseif ($_SESSION['modal_to_open'] === 'createPlaylist') {
        $displayCreatePlaylistModal = true;
    } elseif ($_SESSION['modal_to_open'] === 'addSongToPlaylist') {
        $displayAddSongToPlaylistModal = true;
    }
    unset($_SESSION['modal_to_open']);
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Walkman - Music Portal</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <a href="index.php" class="logo-link"><div class="logo">WalkMan</div></a>
            
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
                    <?php elseif ($_SESSION['roleID'] == 2): ?>
                        <a href="creator/dashboard.php" class="btn btn-primary">Creator Dashboard</a>
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
        <section class="hero-section">
            <h1 class="hero-title">Discover Amazing Music</h1>
            <p class="hero-subtitle">Explore millions of songs, discover new artists, and create your perfect playlists</p>
            <a href="#trending" class="btn btn-primary">Explore Now</a>
        </section>

        <?php if (isset($_SESSION['userID']) && $_SESSION['roleID'] == 3): ?>
            <section class="section" id="my-playlists">
                <h2 class="section-title">🎶 My Playlists</h2>
                <?php
                if (isset($_SESSION['playlist_message'])) {
                    $messageClass = ($_SESSION['playlist_message_type'] == 'success') ? 'success-message' : 'error-message';
                    echo '<div class="message ' . $messageClass . '">' . htmlspecialchars($_SESSION['playlist_message']) . '</div>';
                    unset($_SESSION['playlist_message']);
                    unset($_SESSION['playlist_message_type']);
                }
                if (isset($_SESSION['song_action_message'])) {
                    $messageClass = ($_SESSION['song_action_message_type'] == 'success') ? 'success-message' : 'error-message';
                    echo '<div class="message ' . $messageClass . '">' . htmlspecialchars($_SESSION['song_action_message']) . '</div>';
                    unset($_SESSION['song_action_message']);
                    unset($_SESSION['song_action_message_type']);
                }
                ?>
                <div class="cards-grid" id="userPlaylists">
                    <?php foreach ($userPlaylists as $playlist): ?>
                        <a href="listener/view_playlist.php?playlist_id=<?php echo htmlspecialchars($playlist['PlaylistID']); ?>" class="playlist-card card">
                            <div class="card-image">▶️</div> <div class="card-title"><?php echo htmlspecialchars($playlist['PlaylistName']); ?></div>
                            <div class="card-subtitle">Playlist</div>
                        </a>
                    <?php endforeach; ?>
                    <div class="playlist-add-card card" onclick="showModal('createPlaylistModal')">
                        <span class="icon">+</span>
                        <span class="text">Create New Playlist</span>
                    </div>
                </div>
            </section>
        <?php endif; ?>

        <section class="section" id="trending">
            <h2 class="section-title">🔥 Trending Songs</h2>
            <div class="cards-grid" id="trendingSongs">
                <?php foreach ($trendingSongs as $song): ?>
                    <div class="card">
                        <div class="card-image">🎵</div>
                        <div class="card-title"><?php echo htmlspecialchars($song['SongName']); ?></div>
                        <div class="card-subtitle"><?php echo htmlspecialchars($song['ArtistName']); ?> • <?php echo htmlspecialchars($song['ReleaseYear']); ?></div>
                        <?php if (isset($_SESSION['userID']) && $_SESSION['roleID'] == 3): ?>
                            <button class="add-to-playlist-btn" onclick="openAddSongModal(<?php echo $song['SongID']; ?>, '<?php echo addslashes($song['SongName']); ?>')">+</button>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section">
            <h2 class="section-title">⭐ Popular Artists</h2>
            <div class="cards-grid" id="popularArtists">
                <?php foreach ($popularArtists as $artist): ?>
                    <a href="home/artist_profile.php?artist_id=<?php echo htmlspecialchars($artist['ArtistID']); ?>" class="card clickable artist-card"> <div class="card-image">🎤</div>
                        <div class="card-title"><?php echo htmlspecialchars($artist['ArtistName']); ?></div>
                        <div class="card-subtitle"><?php echo htmlspecialchars($artist['CompanyName'] ?: 'Independent'); ?></div>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="section">
            <h2 class="section-title">💿 Popular Albums</h2>
            <div class="cards-grid" id="popularAlbums">
                <?php foreach ($popularAlbums as $album): ?>
                    <div class="card">
                        <div class="card-image">💿</div>
                        <div class="card-title"><?php echo htmlspecialchars($album['AlbumName']); ?></div>
                        <div class="card-subtitle"><?php echo htmlspecialchars($album['ArtistName']); ?> • <?php echo htmlspecialchars($album['ReleaseYear']); ?></div>
                        </div>
                <?php endforeach; ?>
            </div>
        </section>
    </main>

    <div id="loginModal" class="modal" style="display: <?php echo $displayLoginModal ? 'block' : 'none'; ?>;">
        <div class="modal-content">
            <span class="close" onclick="hideModal('loginModal')">&times;</span>
            <h2 class="form-title">Welcome Back</h2>
            <?php
            if (isset($_SESSION['login_error'])) {
                echo '<div class="message error-message">' . htmlspecialchars($_SESSION['login_error']) . '</div>';
                unset($_SESSION['login_error']);
            }
            if (isset($_SESSION['signup_success_message'])) {
                echo '<div class="message success-message">' . htmlspecialchars($_SESSION['signup_success_message']) . '</div>';
                unset($_SESSION['signup_success_message']);
            }
            ?>
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

    <div id="signupModal" class="modal" style="display: <?php echo $displaySignupModal ? 'block' : 'none'; ?>;">
        <div class="modal-content">
            <span class="close" onclick="hideModal('signupModal')">&times;</span>
            <h2 class="form-title">Join SoundWave</h2>
            <?php
            if (isset($_SESSION['signup_error'])) {
                echo '<div class="message error-message">' . htmlspecialchars($_SESSION['signup_error']) . '</div>';
                unset($_SESSION['signup_error']);
            }
            ?>
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

    <div id="createPlaylistModal" class="modal" style="display: <?php echo $displayCreatePlaylistModal ? 'block' : 'none'; ?>;">
        <div class="modal-content">
            <span class="close" onclick="hideModal('createPlaylistModal')">&times;</span>
            <h2 class="form-title">Create New Playlist</h2>
            <form action="home/create_playlist.php" method="POST">
                <div class="form-group">
                    <label for="playlistName">Playlist Name</label>
                    <input type="text" id="playlistName" name="playlist_name" required>
                </div>
                <button type="submit" class="btn btn-primary" style="width: 100%;">Create Playlist</button>
            </form>
        </div>
    </div>

    <div id="addSongToPlaylistModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="hideModal('addSongToPlaylistModal')">&times;</span>
            <h2 class="form-title">Add "<span id="songTitleForModal"></span>" to Playlist</h2>
            <form action="listener/add_song_to_playlist.php" method="POST">
                <input type="hidden" name="song_id" id="addSongModalSongID">
                <div class="form-group">
                    <label>Select a Playlist:</label>
                    <div class="playlist-selection" id="playlistSelectionRadios">
                        <?php if (empty($listenerPlaylistsForModal)): ?>
                            <p class="no-playlists-message">You have no playlists yet. <br>Please <span style="color: var(--primary-color); cursor: pointer; text-decoration: underline;" onclick="hideModal('addSongToPlaylistModal'); showModal('createPlaylistModal');">create one</span> first!</p>
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

        <?php if ($displayLoginModal): ?>
            showModal('loginModal');
        <?php elseif ($displaySignupModal): ?>
            showModal('signupModal');
        <?php elseif ($displayCreatePlaylistModal): ?>
            showModal('createPlaylistModal');
        <?php endif; ?>
    </script>
</body>
</html>