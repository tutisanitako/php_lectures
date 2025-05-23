<?php
include '../db_connect.php';
session_start();

// Ensure the user is logged in and is an Artist (RoleID = 2)
if (!isset($_SESSION['userID']) || $_SESSION['roleID'] != 2) {
    header("Location: ../index.php?error=access_denied");
    exit();
}

$artistID = null;
// Get ArtistID
$stmt = $conn->prepare("SELECT ArtistID FROM Artists WHERE UserID = ?");
$stmt->bind_param("i", $_SESSION['userID']);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows > 0) {
    $artistData = $result->fetch_assoc();
    $artistID = $artistData['ArtistID'];
} else {
    $_SESSION['album_message'] = "Error: Artist profile not found.";
    $_SESSION['album_message_type'] = "error";
    header("Location: manage_albums.php");
    exit();
}
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action'])) {
    $action = $_POST['action'];

    switch ($action) {
        case 'add':
            $albumName = trim($_POST['album_name'] ?? '');
            $releaseYear = filter_var($_POST['release_year'] ?? '', FILTER_VALIDATE_INT);

            if (empty($albumName) || !$releaseYear) {
                $_SESSION['album_message'] = "Album name and valid release year are required.";
                $_SESSION['album_message_type'] = "error";
            } else {
                $stmt = $conn->prepare("INSERT INTO Albums (AlbumName, ArtistID, ReleaseYear) VALUES (?, ?, ?)");
                if ($stmt) {
                    $stmt->bind_param("sii", $albumName, $artistID, $releaseYear);
                    if ($stmt->execute()) {
                        $_SESSION['album_message'] = "Album '{$albumName}' added successfully!";
                        $_SESSION['album_message_type'] = "success";
                    } else {
                        $_SESSION['album_message'] = "Error adding album: " . $stmt->error;
                        $_SESSION['album_message_type'] = "error";
                    }
                    $stmt->close();
                } else {
                    $_SESSION['album_message'] = "Database error preparing statement for add album.";
                    $_SESSION['album_message_type'] = "error";
                }
            }
            break;

        case 'edit':
            $albumID = filter_var($_POST['album_id'] ?? '', FILTER_VALIDATE_INT);
            $albumName = trim($_POST['album_name'] ?? '');
            $releaseYear = filter_var($_POST['release_year'] ?? '', FILTER_VALIDATE_INT);

            if (!$albumID || empty($albumName) || !$releaseYear) {
                $_SESSION['album_message'] = "Invalid album data for editing.";
                $_SESSION['album_message_type'] = "error";
            } else {
                // Ensure the album belongs to the current artist before updating
                $stmt = $conn->prepare("UPDATE Albums SET AlbumName = ?, ReleaseYear = ? WHERE AlbumID = ? AND ArtistID = ?");
                if ($stmt) {
                    $stmt->bind_param("siii", $albumName, $releaseYear, $albumID, $artistID);
                    if ($stmt->execute()) {
                        if ($stmt->affected_rows > 0) {
                            $_SESSION['album_message'] = "Album '{$albumName}' updated successfully!";
                            $_SESSION['album_message_type'] = "success";
                        } else {
                            $_SESSION['album_message'] = "Album not found or you don't have permission to edit it.";
                            $_SESSION['album_message_type'] = "error";
                        }
                    } else {
                        $_SESSION['album_message'] = "Error updating album: " . $stmt->error;
                        $_SESSION['album_message_type'] = "error";
                    }
                    $stmt->close();
                } else {
                    $_SESSION['album_message'] = "Database error preparing statement for edit album.";
                    $_SESSION['album_message_type'] = "error";
                }
            }
            break;

        case 'delete':
            $albumID = filter_var($_POST['album_id'] ?? '', FILTER_VALIDATE_INT);

            if (!$albumID) {
                $_SESSION['album_message'] = "Invalid album ID for deletion.";
                $_SESSION['album_message_type'] = "error";
            } else {
                // IMPORTANT: Before deleting album, delete all associated songs first
                // Ensure songs also belong to this artist (via album)
                $stmt_songs = $conn->prepare("DELETE FROM Songs WHERE AlbumID = ? AND AlbumID IN (SELECT AlbumID FROM Albums WHERE ArtistID = ?)");
                if ($stmt_songs) {
                    $stmt_songs->bind_param("ii", $albumID, $artistID);
                    $stmt_songs->execute();
                    $stmt_songs->close(); // Songs deleted (or 0 affected rows if none exist)
                }

                // Now delete the album, ensuring it belongs to the artist
                $stmt_album = $conn->prepare("DELETE FROM Albums WHERE AlbumID = ? AND ArtistID = ?");
                if ($stmt_album) {
                    $stmt_album->bind_param("ii", $albumID, $artistID);
                    if ($stmt_album->execute()) {
                        if ($stmt_album->affected_rows > 0) {
                            $_SESSION['album_message'] = "Album and its songs deleted successfully!";
                            $_SESSION['album_message_type'] = "success";
                        } else {
                            $_SESSION['album_message'] = "Album not found or you don't have permission to delete it.";
                            $_SESSION['album_message_type'] = "error";
                        }
                    } else {
                        $_SESSION['album_message'] = "Error deleting album: " . $stmt_album->error;
                        $_SESSION['album_message_type'] = "error";
                    }
                    $stmt_album->close();
                } else {
                    $_SESSION['album_message'] = "Database error preparing statement for delete album.";
                    $_SESSION['album_message_type'] = "error";
                }
            }
            break;

        default:
            $_SESSION['album_message'] = "Invalid action.";
            $_SESSION['album_message_type'] = "error";
            break;
    }
} else {
    $_SESSION['album_message'] = "Invalid request method.";
    $_SESSION['album_message_type'] = "error";
}

$conn->close();
header("Location: manage_albums.php");
exit();
?>