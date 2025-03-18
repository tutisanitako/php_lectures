<?php
    include "filefolder.php";
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        header, main{
            width: 700px;
            min-height: 300px;
            margin: auto;
            border: solid 1px black;
            padding: 10px;
            box-sizing: border-box;
        }

        header {
            min-height: 100px;
            margin-bottom: 10px;
        }

        .content {
            border-collapse: collapse;
            width: 100%;
        }

        .content th, td {
            border: solid 1px silver;
            padding: 7px;
        }

    </style>
</head>
<body>
    <header>
        <form action="" method="post">
            <input type="text" name="dir"> - <button name="c_folder">Create a Folder</button> 
            <span style="color: red;"> <?=$err_d?> </span>
            <br><br>
            <input type="text" name="file"> - <button name="c_file">Create a File</button> 
            <span style="color: red;"> <?=$err_f?> </span>
        </form>
    </header>
    <main>
        <table class="content">
            <tr>
                <th>status</th>
                <th>size</th>
                <th>name</th>
                <th>action</th>
            </tr>
        
        <?php
            // echo "<pre>";
            // print_r(scandir("storage"));
            // echo "</pre>";
            $dir_content = scandir("storage");
            for ($i=2; $i < count($dir_content) ; $i++) { 
                $path = "storage/" . $dir_content[$i];
                $size = is_file($path) ? filesize($path) . " bytes" : "-";
        ?>
            <tr>
                <td><?= is_file($path) ? "File" : "Folder" ?></td>
                <td><?= $size ?></td>
                <td><?=$dir_content[$i]?></td>
                <td></td>
            </tr>

        <?php }?>
        </table>
        
    </main>
</body>

</html>