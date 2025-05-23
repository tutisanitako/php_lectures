<?php
session_start();
include '../../db_connect.php';

if (!isset($_SESSION['userID']) || $_SESSION['roleID'] != 2) {
    header("Location: ../index.php?error=access_denied");
    exit();
}

if (isset($_GET['id'])) {
    $albumID = (int)$_GET['id'];
    $creatorID = $_SESSION['userID'];

    $conn->begin_transaction();

    try {
        $filePathStmt = $conn->prepare("SELECT FilePath FROM CreatorSongs WHERE CreatorAlbumID = ?");
        $filePathStmt->bind_param("i", $albumID);
        $filePathStmt->execute();
        $filePathResult = $filePathStmt->get_result();
        $filesToDelete = [];
        while ($row = $filePathResult->fetch_assoc()) {
            $filesToDelete[] = $row['FilePath'];
        }
        $filePathStmt->close();

        $deleteSongsStmt = $conn->prepare("
            DELETE cs FROM CreatorSongs cs
            JOIN CreatorAlbums ca ON cs.CreatorAlbumID = ca.CreatorAlbumID
            WHERE cs.CreatorAlbumID = ? AND ca.CreatorID = ?
        ");
        $deleteSongsStmt->bind_param("ii", $albumID, $creatorID);
        $deleteSongsStmt->execute();
        $deleteSongsStmt->close();

        $deleteAlbumStmt = $conn->prepare("DELETE FROM CreatorAlbums WHERE CreatorAlbumID = ? AND CreatorID = ?");
        $deleteAlbumStmt->bind_param("ii", $albumID, $creatorID);
        $deleteAlbumStmt->execute();

        if ($deleteAlbumStmt->affected_rows > 0) {
            foreach ($filesToDelete as $filePath) {
                if (!empty($filePath) && file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            $conn->commit();
            $_SESSION['creator_message'] = "Album and its songs deleted successfully!";
            $_SESSION['creator_message_type'] = "success";
        } else {
            $conn->rollback();
            $_SESSION['creator_message'] = "Album not found or you don't have permission to delete it.";
            $_SESSION['creator_message_type'] = "error";
        }
        $deleteAlbumStmt->close();

    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['creator_message'] = "An error occurred during deletion: " . $e->getMessage();
        $_SESSION['creator_message_type'] = "error";
    }

    $conn->close();
} else {
    $_SESSION['creator_message'] = "No album ID provided for deletion.";
    $_SESSION['creator_message_type'] = "error";
}

header("Location: ../dashboard.php");
exit();
?>