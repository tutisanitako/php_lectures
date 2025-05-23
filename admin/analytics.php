<?php
session_start();

if (!isset($_SESSION['userID']) || $_SESSION['roleID'] != 1) {
    header("Location: ../index.php?error=access_denied");
    exit();
}

include '../db_connect.php';
include '../log_page_view.php';
$page_views = [];
$sql_page_views = "SELECT ViewID, PageURL, VisitTime, VisitorIP FROM PageViews ORDER BY VisitTime DESC LIMIT 200";
$result_page_views = $conn->query($sql_page_views);

if ($result_page_views && $result_page_views->num_rows > 0) {
    while ($row = $result_page_views->fetch_assoc()) {
        $page_views[] = $row;
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Site Analytics - Admin Dashboard</title>
    <link rel="stylesheet" href="style.css"> </head>
<body>
    <div class="container">
        <a href="dashboard.php" class="back-link">&larr; Back to Dashboard</a>
        <h1>Site Analytics</h1>

        <p>This shows a log of recent page visits.</p>

        <table>
            <thead>
                <tr>
                    <th>View ID</th>
                    <th>Page URL</th>
                    <th>Time of Visit</th>
                    <th>Visitor IP</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($page_views)): ?>
                    <tr><td colspan="4">No page views recorded yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($page_views as $view): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($view['ViewID']); ?></td>
                            <td><?php echo htmlspecialchars($view['PageURL']); ?></td>
                            <td><?php echo htmlspecialchars($view['VisitTime']); ?></td>
                            <td><?php echo htmlspecialchars($view['VisitorIP']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>