<?php
$upload_dir = 'files/';
$file_size_limit = 50 * 1024 * 1024;


if (isset($_POST['upload'])) {
    $file = $_FILES['file'];
    if ($file['size'] <= $file_size_limit) {
        move_uploaded_file($file['tmp_name'], $upload_dir . $file['name']);
    } else {
        echo "zoma acharbebs limits";
    }
}


if (isset($_GET['delete'])) {
    $file_to_delete = $upload_dir . $_GET['delete'];
    if (file_exists($file_to_delete)) {
        unlink($file_to_delete);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>nebismieri faili (max 50MB)</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>atvirte nebismieri faili (max 50MB)</h1>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="file" name="file" required>
        <button type="submit" name="upload">Upload File</button>
    </form>

    <h3>Uploaded Files:</h3>
    <ul>
        <?php
        $files = scandir($upload_dir);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                echo "<li><a href='$upload_dir$file'>$file</a> <a href='?delete=$file'>Delete</a></li>";
            }
        }
        ?>
    </ul>
</body>
</html>
