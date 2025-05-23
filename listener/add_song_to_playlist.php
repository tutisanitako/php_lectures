<?php
session_start();
include '../db_connect.php';

if (!isset($_SESSION['userID']) || $_SESSION['roleID'] != 3) {
    $_SESSION['login_error'] = "You must be logged in as a listener to add songs to playlists.";
    $_SESSION['modal_to_open'] = 'login';
    header("Location: ../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $playlistID = isset($_POST['playlist_id']) ? intval($_POST['playlist_id']) : 0;
    $songID = isset($_POST['song_id']) ? intval($_POST['song_id']) : 0;
    $userID = $_SESSION['userID'];

    if ($playlistID <= 0 || $songID <= 0) {
        $_SESSION['song_action_message'] = "Invalid playlist or song ID.";
        $_SESSION['song_action_message_type'] = "error";
        header("Location: ../index.php");
        exit();
    }

    $stmt_owner = $conn->prepare("SELECT UserID FROM Playlists WHERE PlaylistID = ?");
    $stmt_owner->bind_param("i", $playlistID);
    $stmt_owner->execute();
    $result_owner = $stmt_owner->get_result();
    $playlist = $result_owner->fetch_assoc();
    $stmt_owner->close();

    if (!$playlist || $playlist['UserID'] != $userID) {
        $_SESSION['song_action_message'] = "You do not have permission to add songs to this playlist.";
        $_SESSION['song_action_message_type'] = "error";
        header("Location: ../index.php");
        exit();
    }

    $stmt_check = $conn->prepare("SELECT PlaylistSongID FROM PlaylistSongs WHERE PlaylistID = ? AND SongID = ?");
    $stmt_check->bind_param("ii", $playlistID, $songID);
    $stmt_check->execute();
    $stmt_check->store_result();

    if ($stmt_check->num_rows > 0) {
        $_SESSION['song_action_message'] = "Song is already in this playlist.";
        $_SESSION['song_action_message_type'] = "error";
    } else {
        $stmt_insert = $conn->prepare("INSERT INTO PlaylistSongs (PlaylistID, SongID) VALUES (?, ?)");
        $stmt_insert->bind_param("ii", $playlistID, $songID);

        if ($stmt_insert->execute()) {
            $_SESSION['song_action_message'] = "Song added to playlist successfully!";
            $_SESSION['song_action_message_type'] = "success";
        } else {
            $_SESSION['song_action_message'] = "Error adding song to playlist: " . $stmt_insert->error;
            $_SESSION['song_action_message_type'] = "error";
        }
        $stmt_insert->close();
    }
    $stmt_check->close();
    $conn->close();

    header("Location: ../index.php");
    exit();
} else {
    header("Location: ../index.php");
    exit();
}