<?php
$files_dir = 'files/';
$file_count = count(scandir($files_dir)) - 2;

if (isset($_POST['upload'])) {
    $file = $_FILES['file'];
    $allowed_extensions = ['png', 'jpg', 'gif'];
    $file_size_limit = 100 * 1024 * 1024; 

    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $file_size = $file['size'];
    $upload_date = date('d-m-Y');
    
    if (in_array(strtolower($file_extension), $allowed_extensions) && $file_size <= $file_size_limit) {
        $file_count++;
        $filename = $file_count . "-" . $upload_date . "." . $file_extension;
        move_uploaded_file($file['tmp_name'], $files_dir . $filename);
    } else {
        echo "Invalid file type or file size exceeds limit.";
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
    <title>Upload Image</title>
</head>
<body>
    <header>
        <h1>Upload Image (PNG, JPG, GIF)</h1>
        <form action="" method="post" enctype="multipart/form-data">
            <input type="file" name="file" required>
            <button type="submit" name="upload">Upload File</button>
        </form>
    </header>

    <h3>Uploaded Files:</h3>
    <ul>
        <?php
        $files = scandir($files_dir);
        foreach ($files as $file) {
            if ($file != '.' && $file != '..') {
                echo "<li><a href='$files_dir$file'>$file</a> <a href='?delete=$file'>Delete</a></li>";
            }
        }
        ?>
    </ul>
</body>
</html>
