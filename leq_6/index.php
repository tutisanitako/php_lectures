<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <form action="" method="post">
        <div>
            <input type="text" name="cr_folder"> -
            <input type="submit" name="submit">
        </div>
        <br>
        <div>
        <input type="text" name="cr_file"> -
        <input type="submit" name="submit">
        </div>

        <?php
        if(isset($_POST["cr_folder"])){
            $name = $_POST["cr_folder"];
            mkdir($name);
            echo "folder created";
        }

        if(isset($_POST["cr_file"])){
            $name = $_POST["cr_file"];
            file_put_contents($_POST['cr_file'], ""); 
            echo "file created";
        }
        ?>
    </form>
</body>
</html>