<?php
session_start();
include '../../db_connect.php';

if (!isset($_SESSION['userID']) || $_SESSION['roleID'] != 2) {
    header("Location: ../index.php?error=access_denied");
    exit();
}

if (isset($_GET['id'])) {
    $songID = (int)$_GET['id'];
    $creatorID = $_SESSION['userID'];

    $conn->begin_transaction();

    try {
        $filePath = null;
        $getFilePathStmt = $conn->prepare("
            SELECT cs.FilePath
            FROM CreatorSongs cs
            JOIN CreatorAlbums ca ON cs.CreatorAlbumID = ca.CreatorAlbumID
            WHERE cs.CreatorSongID = ? AND ca.CreatorID = ?
        ");
        $getFilePathStmt->bind_param("ii", $songID, $creatorID);
        $getFilePathStmt->execute();
        $getFilePathStmt->bind_result($filePath);
        $getFilePathStmt->fetch();
        $getFilePathStmt->close();

        if ($filePath === null) {
            $conn->rollback();
            $_SESSION['creator_message'] = "Song not found or you don't have permission to delete it.";
            $_SESSION['creator_message_type'] = "error";
            header("Location: ../dashboard.php");
            exit();
        }

        $deleteSongStmt = $conn->prepare("
            DELETE cs FROM CreatorSongs cs
            JOIN CreatorAlbums ca ON cs.CreatorAlbumID = ca.CreatorAlbumID
            WHERE cs.CreatorSongID = ? AND ca.CreatorID = ?
        ");
        $deleteSongStmt->bind_param("ii", $songID, $creatorID);
        $deleteSongStmt->execute();

        if ($deleteSongStmt->affected_rows > 0) {
            if (!empty($filePath) && file_exists($filePath)) {
                unlink($filePath);
            }
            $conn->commit();
            $_SESSION['creator_message'] = "Song deleted successfully!";
            $_SESSION['creator_message_type'] = "success";
        } else {
            $conn->rollback();
            $_SESSION['creator_message'] = "Song not found or you don't have permission to delete it.";
            $_SESSION['creator_message_type'] = "error";
        }
        $deleteSongStmt->close();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['creator_message'] = "An error occurred during deletion: " . $e->getMessage();
        $_SESSION['creator_message_type'] = "error";
    }

    $conn->close();
} else {
    $_SESSION['creator_message'] = "No song ID provided for deletion.";
    $_SESSION['creator_message_type'] = "error";
}

$redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../dashboard.php';
if (strpos($redirect_url, 'delete_song.php') !== false) {
    $redirect_url = '../dashboard.php';
}
header("Location: " . $redirect_url);
exit();
?>