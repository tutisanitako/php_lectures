<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['userID']) || $_SESSION['roleID'] != 3) {
    $_SESSION['login_error'] = "You must be logged in as a listener to create playlists.";
    $_SESSION['modal_to_open'] = 'login';
    header("Location: ../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $playlistName = trim($_POST['playlist_name']);
    $userID = $_SESSION['userID'];

    if (empty($playlistName)) {
        $_SESSION['playlist_message'] = "Playlist name cannot be empty.";
        $_SESSION['playlist_message_type'] = "error";
        $_SESSION['modal_to_open'] = 'createPlaylist';
        header("Location: ../index.php");
        exit();
    }

    $stmt = $conn->prepare("SELECT PlaylistID FROM Playlists WHERE UserID = ? AND PlaylistName = ?");
    $stmt->bind_param("is", $userID, $playlistName);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $_SESSION['playlist_message'] = "You already have a playlist with this name.";
        $_SESSION['playlist_message_type'] = "error";
        $_SESSION['modal_to_open'] = 'createPlaylist';
    } else {
        $stmt_insert = $conn->prepare("INSERT INTO Playlists (UserID, PlaylistName) VALUES (?, ?)");
        $stmt_insert->bind_param("is", $userID, $playlistName);

        if ($stmt_insert->execute()) {
            $_SESSION['playlist_message'] = "Playlist '" . htmlspecialchars($playlistName) . "' created successfully!";
            $_SESSION['playlist_message_type'] = "success";
        } else {
            $_SESSION['playlist_message'] = "Error creating playlist: " . $stmt_insert->error;
            $_SESSION['playlist_message_type'] = "error";
            $_SESSION['modal_to_open'] = 'createPlaylist';
        }
        $stmt_insert->close();
    }
    $stmt->close();
    $conn->close();

    header("Location: ../index.php");
    exit();
} else {
    header("Location: ../index.php");
    exit();
}
?>