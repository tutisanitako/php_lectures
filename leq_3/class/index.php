<?php 
    include 'questions.php';
    // print_r($questions);
    
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecture 3</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <form method="post" action="lecturer.php">
    <table>
            <tr>
                <th>Questions</th>
                <th>Answers</th>
                <th>Max Point</th>
            </tr>
            <?php
                for($i=0; $i<count($questions);$i++){
            ?>
            <tr>
                <td><?=$questions[$i]['question']?></td>
                <td><input type="text" name="answer[]"></td>
                <td><?=$questions[$i]['point']?></td>

            </tr>
            <?php
                }
            ?>
            <tr>
            <td colspan="3">
                student: 
                <input type="text" placeholder="name" name="firstname">
                <input type="text" placeholder="lastname" name="lastname">
                <button>send</button>
            </td>
            </tr>
        </table>
    </form>

    
</body>
</html>