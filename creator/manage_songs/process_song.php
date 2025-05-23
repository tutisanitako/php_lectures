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

    $upload_dir = '../uploads/creator_songs/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    if ($action === 'create') {
        $songName = trim($_POST['song_name'] ?? '');
        $albumID = (int)$_POST['album_id'];
        $releaseYear = (int)($_POST['release_year'] ?? date('Y'));
        $isPublic = isset($_POST['is_public']) ? 1 : 0;

        if (empty($songName) || empty($albumID)) {
            $_SESSION['creator_message'] = "Song name and album are required.";
            $_SESSION['creator_message_type'] = "error";
            header("Location: dashboard.php");
            exit();
        }

        $checkAlbumStmt = $conn->prepare("SELECT CreatorAlbumID FROM CreatorAlbums WHERE CreatorAlbumID = ? AND CreatorID = ?");
        $checkAlbumStmt->bind_param("ii", $albumID, $creatorID);
        $checkAlbumStmt->execute();
        $checkAlbumStmt->store_result();
        if ($checkAlbumStmt->num_rows === 0) {
            $_SESSION['creator_message'] = "Selected album does not belong to you or does not exist.";
            $_SESSION['creator_message_type'] = "error";
            header("Location: dashboard.php");
            exit();
        }
        $checkAlbumStmt->close();

        if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] == UPLOAD_ERR_OK) {
            $fileTmpPath = $_FILES['audio_file']['tmp_name'];
            $fileName = $_FILES['audio_file']['name'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $dest_path = $upload_dir . $newFileName;

            $allowedfileExtensions = ['mp3', 'wav', 'ogg'];
            if (in_array($fileExtension, $allowedfileExtensions)) {
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    $duration = '00:00:00';

                    $stmt = $conn->prepare("INSERT INTO CreatorSongs (CreatorAlbumID, CreatorSongName, ReleaseYear, FilePath, Duration, IsPublic, CreatedAt, UpdatedAt) VALUES (?, ?, ?, ?, ?, ?, NOW(), NOW())");
                    $stmt->bind_param("issssi", $albumID, $songName, $releaseYear, $dest_path, $duration, $isPublic);

                    if ($stmt->execute()) {
                        $_SESSION['creator_message'] = "Song '{$songName}' uploaded successfully!";
                        $_SESSION['creator_message_type'] = "success";
                    } else {
                        unlink($dest_path);
                        $_SESSION['creator_message'] = "Error uploading song to database: " . $stmt->error;
                        $_SESSION['creator_message_type'] = "error";
                    }
                    $stmt->close();
                } else {
                    $_SESSION['creator_message'] = "Error moving uploaded file.";
                    $_SESSION['creator_message_type'] = "error";
                }
            } else {
                $_SESSION['creator_message'] = "Invalid file type. Only MP3, WAV, OGG allowed.";
                $_SESSION['creator_message_type'] = "error";
            }
        } else {
            $_SESSION['creator_message'] = "No file uploaded or an upload error occurred: " . $_FILES['audio_file']['error'];
            $_SESSION['creator_message_type'] = "error";
        }
        header("Location: dashboard.php");
        exit();

    } elseif ($action === 'update') {
        $songID = (int)$_POST['song_id'];
        $songName = trim($_POST['song_name'] ?? '');
        $albumID = (int)$_POST['album_id'];
        $releaseYear = (int)($_POST['release_year'] ?? date('Y'));
        $isPublic = isset($_POST['is_public']) ? 1 : 0;
        $currentFilePath = $_POST['current_file_path'] ?? '';

        if (empty($songName) || empty($albumID)) {
            $_SESSION['creator_message'] = "Song name and album are required.";
            $_SESSION['creator_message_type'] = "error";
            header("Location: manage_song.php?id={$songID}");
            exit();
        }

        $checkOwnershipStmt = $conn->prepare("
            SELECT cs.CreatorSongID, ca.CreatorAlbumID
            FROM CreatorSongs cs
            JOIN CreatorAlbums ca ON cs.CreatorAlbumID = ca.CreatorAlbumID
            WHERE cs.CreatorSongID = ? AND ca.CreatorID = ?
        ");
        $checkOwnershipStmt->bind_param("ii", $songID, $creatorID);
        $checkOwnershipStmt->execute();
        $checkOwnershipStmt->store_result();
        if ($checkOwnershipStmt->num_rows === 0) {
            $_SESSION['creator_message'] = "Unauthorized access or ownership change attempted.";
            $_SESSION['creator_message_type'] = "error";
            header("Location: dashboard.php");
            exit();
        }
        $checkOwnershipStmt->close();

        $filePathToUpdate = $currentFilePath;
        if (isset($_FILES['audio_file']) && $_FILES['audio_file']['error'] == UPLOAD_ERR_OK && $_FILES['audio_file']['size'] > 0) {
            $fileTmpPath = $_FILES['audio_file']['tmp_name'];
            $fileName = $_FILES['audio_file']['name'];
            $fileNameCmps = explode(".", $fileName);
            $fileExtension = strtolower(end($fileNameCmps));

            $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
            $dest_path = $upload_dir . $newFileName;

            $allowedfileExtensions = ['mp3', 'wav', 'ogg'];
            if (in_array($fileExtension, $allowedfileExtensions)) {
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    if (!empty($currentFilePath) && file_exists($currentFilePath)) {
                        unlink($currentFilePath);
                    }
                    $filePathToUpdate = $dest_path;
                } else {
                    $_SESSION['creator_message'] = "Error moving new uploaded file.";
                    $_SESSION['creator_message_type'] = "error";
                    header("Location: manage_song.php?id={$songID}");
                    exit();
                }
            } else {
                $_SESSION['creator_message'] = "Invalid new file type. Only MP3, WAV, OGG allowed.";
                $_SESSION['creator_message_type'] = "error";
                header("Location: manage_song.php?id={$songID}");
                exit();
            }
        }

        $stmt = $conn->prepare("UPDATE CreatorSongs SET CreatorAlbumID = ?, CreatorSongName = ?, ReleaseYear = ?, FilePath = ?, IsPublic = ?, UpdatedAt = NOW() WHERE CreatorSongID = ?");
        $stmt->bind_param("isssii", $albumID, $songName, $releaseYear, $filePathToUpdate, $isPublic, $songID);

        if ($stmt->execute()) {
            $_SESSION['creator_message'] = "Song '{$songName}' updated successfully!";
            $_SESSION['creator_message_type'] = "success";
        } else {
            $_SESSION['creator_message'] = "Error updating song: " . $stmt->error;
            $_SESSION['creator_message_type'] = "error";
        }
        $stmt->close();
        header("Location: manage_song.php?id={$songID}");
        exit();
    }
}

header("Location: dashboard.php");
exit();
?>