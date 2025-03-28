<?php
if (isset($_POST['upload'])) {
    $file = $_FILES['file'];
    $allowed_extensions = ['png', 'jpg', 'gif'];
    $file_size_limit = 100 * 1024 * 1024;

    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $upload_date = date('d-m-Y');
    
    if (in_array(strtolower($file_extension), $allowed_extensions) && $file['size'] <= $file_size_limit) {
        $upload_dir = 'files/';
        $filename = uniqid() . "-" . $upload_date . "." . $file_extension;
        if (move_uploaded_file($file['tmp_name'], $upload_dir . $filename)) {
            $message = "warmatebulad aitvirta";
        } else {
            $message = "ar aitvrita";
        }
    } else {
        $message = "zoma acharbebs limits, an ar aris PNG, JPG, GIF";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PNG, JPG, GIF</title>
</head>
<body>
    <h1>ატვირთე სურათი (PNG, JPG, GIF)</h1>
    <form action="" method="post" enctype="multipart/form-data">
        <input type="file" name="file" required>
        <button type="submit" name="upload">Upload File</button>
    </form>
    
    <?php if (isset($message)) { echo "<p>$message</p>"; } ?>
</body>
</html>
