<?php
session_start();
include '../../db_connect.php';

if (!isset($_SESSION['userID']) || $_SESSION['roleID'] != 2) {
    header("Location: ../index.php?error=access_denied");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $action = $_POST['action'] ?? '';
    $creatorID = $_SESSION['userID'];

    if ($action === 'create') {
        $albumName = trim($_POST['album_name'] ?? '');
        $releaseYear = (int)($_POST['release_year'] ?? date('Y'));
        $isPublic = isset($_POST['is_public']) ? 1 : 0;

        if (empty($albumName)) {
            $_SESSION['creator_message'] = "Album name cannot be empty.";
            $_SESSION['creator_message_type'] = "error";
            header("Location: ../dashboard.php");
            exit();
        }

        $stmt = $conn->prepare("INSERT INTO CreatorAlbums (CreatorID, CreatorAlbumName, ReleaseYear, IsPublic, CreatedAt, UpdatedAt) VALUES (?, ?, ?, ?, NOW(), NOW())");
        $stmt->bind_param("isii", $creatorID, $albumName, $releaseYear, $isPublic);

        if ($stmt->execute()) {
            $_SESSION['creator_message'] = "Album '{$albumName}' created successfully!";
            $_SESSION['creator_message_type'] = "success";
        } else {
            $_SESSION['creator_message'] = "Error creating album: " . $stmt->error;
            $_SESSION['creator_message_type'] = "error";
        }
        $stmt->close();

    } elseif ($action === 'update') {
        $albumID = (int)$_POST['album_id'];
        $albumName = trim($_POST['album_name'] ?? '');
        $releaseYear = (int)($_POST['release_year'] ?? date('Y'));
        $isPublic = isset($_POST['is_public']) ? 1 : 0;

        if (empty($albumName)) {
            $_SESSION['creator_message'] = "Album name cannot be empty.";
            $_SESSION['creator_message_type'] = "error";
            header("Location: manage_album.php?id={$albumID}");
            exit();
        }

        $checkStmt = $conn->prepare("SELECT CreatorAlbumID FROM CreatorAlbums WHERE CreatorAlbumID = ? AND CreatorID = ?");
        $checkStmt->bind_param("ii", $albumID, $creatorID);
        $checkStmt->execute();
        $checkStmt->store_result();
        if ($checkStmt->num_rows === 0) {
            $_SESSION['creator_message'] = "Unauthorized access to album.";
            $_SESSION['creator_message_type'] = "error";
            header("Location: ../dashboard.php");
            exit();
        }
        $checkStmt->close();

        $stmt = $conn->prepare("UPDATE CreatorAlbums SET CreatorAlbumName = ?, ReleaseYear = ?, IsPublic = ?, UpdatedAt = NOW() WHERE CreatorAlbumID = ? AND CreatorID = ?");
        $stmt->bind_param("siiii", $albumName, $releaseYear, $isPublic, $albumID, $creatorID);

        if ($stmt->execute()) {
            $_SESSION['creator_message'] = "Album '{$albumName}' updated successfully!";
            $_SESSION['creator_message_type'] = "success";
        } else {
            $_SESSION['creator_message'] = "Error updating album: " . $stmt->error;
            $_SESSION['creator_message_type'] = "error";
        }
        $stmt->close();
        header("Location: manage_album.php?id={$albumID}");
        exit();
    }
}

header("Location: ../dashboard.php");
exit();
?>