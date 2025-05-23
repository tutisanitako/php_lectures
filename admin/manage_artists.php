<?php
session_start();

if (!isset($_SESSION['userID']) || $_SESSION['roleID'] != 1) {
    header("Location: ../index.php?error=access_denied");
    exit();
}

include '../db_connect.php';

$message = '';
$message_type = '';

$confirm_delete_artist_id = null;
$confirm_delete_artist_name = null;

$edit_artist_id = null;
$edit_artist_name = '';
$edit_company_id = '';


if (isset($_GET['confirm_delete_id'])) {
    $temp_artist_id = filter_var($_GET['confirm_delete_id'], FILTER_SANITIZE_NUMBER_INT);

    $stmt_fetch_artist = $conn->prepare("SELECT ArtistName FROM Artists WHERE ArtistID = ? LIMIT 1");
    $stmt_fetch_artist->bind_param("i", $temp_artist_id);
    $stmt_fetch_artist->execute();
    $stmt_fetch_artist->store_result();
    if ($stmt_fetch_artist->num_rows > 0) {
        $stmt_fetch_artist->bind_result($artist_name_to_delete);
        $stmt_fetch_artist->fetch();
        $confirm_delete_artist_id = $temp_artist_id;
        $confirm_delete_artist_name = $artist_name_to_delete;
    } else {
        $_SESSION['admin_message'] = "Artist not found for deletion confirmation.";
        $_SESSION['admin_message_type'] = "error";
        $_SESSION['admin_message_time'] = time();
        header("Location: manage_artists.php");
        exit();
    }
    $stmt_fetch_artist->close();
}

if (isset($_GET['edit_id'])) {
    $temp_artist_id = filter_var($_GET['edit_id'], FILTER_SANITIZE_NUMBER_INT);

    $stmt_fetch_artist_details = $conn->prepare("SELECT ArtistName, CompanyID FROM Artists WHERE ArtistID = ? LIMIT 1");
    $stmt_fetch_artist_details->bind_param("i", $temp_artist_id);
    $stmt_fetch_artist_details->execute();
    $stmt_fetch_artist_details->store_result();
    if ($stmt_fetch_artist_details->num_rows > 0) {
        $stmt_fetch_artist_details->bind_result($artist_name, $company_id);
        $stmt_fetch_artist_details->fetch();
        $edit_artist_id = $temp_artist_id; 
        $edit_artist_name = $artist_name;
        $edit_company_id = $company_id;
    } else {
        $_SESSION['admin_message'] = "Artist not found for editing.";
        $_SESSION['admin_message_type'] = "error";
        $_SESSION['admin_message_time'] = time();
        header("Location: manage_artists.php");
        exit();
    }
    $stmt_fetch_artist_details->close();
}



if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_SESSION['admin_message'])) {
        unset($_SESSION['admin_message']);
        unset($_SESSION['admin_message_type']);
        unset($_SESSION['admin_message_time']);
    }

    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        if ($action == 'add_artist' && isset($_POST['artist_name'])) {
            $artistName = filter_var($_POST['artist_name'], FILTER_SANITIZE_STRING);
            $companyID = filter_var($_POST['company_id'], FILTER_SANITIZE_NUMBER_INT);

            $companyID_for_db = !empty($companyID) ? $companyID : null;

            $stmt = $conn->prepare("INSERT INTO Artists (ArtistName, CompanyID) VALUES (?, ?)");

            $stmt->bind_param("si", $artistName, $companyID_for_db);
            if ($stmt->execute()) {
                $_SESSION['admin_message'] = "Artist '{$artistName}' added successfully!";
                $_SESSION['admin_message_type'] = "success";
                $_SESSION['admin_message_time'] = time();
            } else {
                $_SESSION['admin_message'] = "Error adding artist: " . $stmt->error;
                $_SESSION['admin_message_type'] = "error";
                $_SESSION['admin_message_time'] = time();
            }
            $stmt->close();

        } elseif ($action == 'update_artist' && isset($_POST['artist_id']) && isset($_POST['artist_name'])) {
            $artistID = filter_var($_POST['artist_id'], FILTER_SANITIZE_NUMBER_INT);
            $artistName = filter_var($_POST['artist_name'], FILTER_SANITIZE_STRING);
            $companyID = filter_var($_POST['company_id'], FILTER_SANITIZE_NUMBER_INT);

            $companyID_for_db = !empty($companyID) ? $companyID : null;

            $stmt = $conn->prepare("UPDATE Artists SET ArtistName = ?, CompanyID = ? WHERE ArtistID = ?");
            $stmt->bind_param("sii", $artistName, $companyID_for_db, $artistID);
            if ($stmt->execute()) {
                $_SESSION['admin_message'] = "Artist '{$artistName}' updated successfully!";
                $_SESSION['admin_message_type'] = "success";
                $_SESSION['admin_message_time'] = time();
            } else {
                $_SESSION['admin_message'] = "Error updating artist: " . $stmt->error;
                $_SESSION['admin_message_type'] = "error";
                $_SESSION['admin_message_time'] = time();
            }
            $stmt->close();

        } elseif ($action == 'delete_artist' && isset($_POST['artist_id'])) {
            $artistID = filter_var($_POST['artist_id'], FILTER_SANITIZE_NUMBER_INT);

            $stmt = $conn->prepare("DELETE FROM Artists WHERE ArtistID = ?");
            $stmt->bind_param("i", $artistID);
            if ($stmt->execute()) {
                $_SESSION['admin_message'] = "Artist deleted successfully!";
                $_SESSION['admin_message_type'] = "success";
                $_SESSION['admin_message_time'] = time();
            } else {
                $_SESSION['admin_message'] = "Error deleting artist: " . $stmt->error . " (Perhaps albums/songs are still linked?)";
                $_SESSION['admin_message_type'] = "error";
                $_SESSION['admin_message_time'] = time();
            }
            $stmt->close();
        }
    }
    header("Location: manage_artists.php");
    exit();
}

$display_message_html = false;
$seconds_to_show = 5;

if (isset($_SESSION['admin_message']) && isset($_SESSION['admin_message_time'])) {
    $message = $_SESSION['admin_message'];
    $message_type = $_SESSION['admin_message_type'];
    $message_timestamp = $_SESSION['admin_message_time'];

    if ((time() - $message_timestamp) >= $seconds_to_show) {
        unset($_SESSION['admin_message']);
        unset($_SESSION['admin_message_type']);
        unset($_SESSION['admin_message_time']);
    } else {
        $display_message_html = true;
    }
}



$artists = [];
$sql_artists = "SELECT ar.ArtistID, ar.ArtistName, ar.CompanyID, c.CompanyName
                FROM Artists ar
                LEFT JOIN Company c ON ar.CompanyID = c.CompanyID
                ORDER BY ar.ArtistName ASC";
$result_artists = $conn->query($sql_artists);
if ($result_artists && $result_artists->num_rows > 0) {
    while ($row = $result_artists->fetch_assoc()) {
        $artists[] = $row;
    }
}

$companies = [];
$sql_companies = "SELECT CompanyID, CompanyName FROM Company ORDER BY CompanyName ASC";
$result_companies = $conn->query($sql_companies);
if ($result_companies && $result_companies->num_rows > 0) {
    while ($row = $result_companies->fetch_assoc()) {
        $companies[] = $row;
    }
}

$conn->close(); 
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Artists - Admin Dashboard</title>
    <link rel="stylesheet" href="style.css"> <?php if ($display_message_html): ?>
        <meta http-equiv="refresh" content="<?php echo $seconds_to_show; ?>;URL=manage_artists.php">
    <?php endif; ?>
</head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-link">&larr; Back to Dashboard</a>
        <h1>Manage Artists</h1>

        <?php if ($display_message_html): ?>
            <div class="message <?php echo $message_type; ?>-message fade-out">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <?php if ($confirm_delete_artist_id !== null):?>
            <div class="confirmation-box delete-confirm">
                <h2>Confirm Deletion</h2>
                <p>Are you absolutely sure you want to delete the artist <strong><?php echo htmlspecialchars($confirm_delete_artist_name); ?></strong> (ID: <?php echo htmlspecialchars($confirm_delete_artist_id); ?>)? This action cannot be undone and may affect associated albums and songs.</p>
                <div class="confirmation-actions">
                    <form action="manage_artists.php" method="POST">
                        <input type="hidden" name="artist_id" value="<?php echo htmlspecialchars($confirm_delete_artist_id); ?>">
                        <input type="hidden" name="action" value="delete_artist">
                        <button type="submit" class="btn btn-confirm">Yes, Delete Permanently</button>
                    </form>
                    <a href="manage_artists.php" class="btn btn-cancel">No, Cancel</a>
                </div>
            </div>
        <?php else:?>

            <div class="form-section">
                <h2><?php echo ($edit_artist_id !== null) ? 'Edit Artist' : 'Add New Artist'; ?></h2>
                <form action="manage_artists.php" method="POST" class="add-edit-form">
                    <?php if ($edit_artist_id !== null): ?>
                        <input type="hidden" name="artist_id" value="<?php echo htmlspecialchars($edit_artist_id); ?>">
                        <input type="hidden" name="action" value="update_artist">
                    <?php else: ?>
                        <input type="hidden" name="action" value="add_artist">
                    <?php endif; ?>

                    <div class="form-group">
                        <label for="artist_name">Artist Name:</label>
                        <input type="text" id="artist_name" name="artist_name" value="<?php echo htmlspecialchars($edit_artist_name); ?>" required>
                    </div>
                    <div class="form-group">
                        <label for="company_id">Company:</label>
                        <select id="company_id" name="company_id">
                            <option value="">-- No Company Assigned --</option>
                            <?php foreach ($companies as $company): ?>
                                <option value="<?php echo htmlspecialchars($company['CompanyID']); ?>"
                                    <?php echo ($company['CompanyID'] == $edit_company_id) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($company['CompanyName']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary"><?php echo ($edit_artist_id !== null) ? 'Update Artist' : 'Add Artist'; ?></button>
                    <?php if ($edit_artist_id !== null): ?>
                        <a href="manage_artists.php" class="btn btn-secondary">Cancel Edit</a>
                    <?php endif; ?>
                </form>
            </div>

            <hr style="border-color: #4a4d52; margin: 40px 0;">

            <h2>Existing Artists</h2>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Artist Name</th>
                        <th>Company</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($artists)): ?>
                        <tr><td colspan="4">No artists found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($artists as $artist): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($artist['ArtistID']); ?></td>
                                <td><?php echo htmlspecialchars($artist['ArtistName']); ?></td>
                                <td><?php echo htmlspecialchars($artist['CompanyName'] ?: 'N/A'); ?></td>
                                <td>
                                    <form action="manage_artists.php" method="GET" class="action-form">
                                        <input type="hidden" name="edit_id" value="<?php echo $artist['ArtistID']; ?>">
                                        <button type="submit" class="btn btn-primary">Edit</button>
                                    </form>
                                    <form action="manage_artists.php" method="GET" class="action-form">
                                        <input type="hidden" name="confirm_delete_id" value="<?php echo $artist['ArtistID']; ?>">
                                        <button type="submit" class="delete-button-trigger">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</body>
</html>