<?php
$directory = 'files/';

if (isset($_POST['action'])) {
    $filename = $directory . $_POST['filename'];

    if ($_POST['action'] == 'create') {
        file_put_contents($filename . '.txt', '');
    }

    if ($_POST['action'] == 'edit' && isset($_POST['text'])) {
        $text = $_POST['text'];
        file_put_contents($filename, $text);
    }

    if ($_POST['action'] == 'delete') {
        unlink($filename);
    }

    if ($_POST['action'] == 'export') {
        $content = file_get_contents($filename);
        echo nl2br($content);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create and Edit TXT Files</title>
</head>
<body>
    <header>
        <h1>Create, Edit, and Delete TXT Files</h1>
    </header>

    <form action="" method="post">
        <select name="action" required>
            <option value="create">Create File</option>
            <option value="edit">Edit File</option>
            <option value="delete">Delete File</option>
            <option value="export">Export File</option>
        </select>

        <input type="text" name="filename" placeholder="Enter file name" required>

        <?php if (isset($_POST['action']) && $_POST['action'] == 'edit'): ?>
            <textarea name="text" placeholder="Enter text to edit file" required></textarea>
        <?php endif; ?>

        <button type="submit">Submit</button>
    </form>

    <h3>Existing Files:</h3>
    <ul>
        <?php
        if (is_dir($directory)) {
            $files = array_diff(scandir($directory), array('..', '.'));
            foreach ($files as $file) {
                echo "<li>$file</li>";
            }
        }
        ?>
    </ul>
</body>
</html>
