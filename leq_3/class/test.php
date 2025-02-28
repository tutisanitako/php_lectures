<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecture 3</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
    <form method="post" action="questions.php">
        <table>
            <tr>
                <th>Questions</th>
                <th>Answers</th>
                <th>Max Point</th>
            </tr>
            <tr>
                <td>What is HTML?</td>
                <td><input type="text" name="html"></td>
                <td>8</td>
            </tr>
            <tr>
                <td>What is Angular?</td>
                <td><input type="text" name="html"></td>
                <td>10</td>
            </tr>
            <tr>
                <td>What is React?</td>
                <td><input type="text" name="html"></td>
                <td>9</td>
            </tr>
            <tr>
                <td>What is CSS?</td>
                <td><input type="text" name="html"></td>
                <td>10</td>
            </tr>
            <tr>
                <td>What is PHP?</td>
                <td><input type="text" name="php"></td>
                <td>10</td>
                </tr>
                <tr>
                <td>What is JS?</td>
                <td><input type="text" name="js"></td>
                <td>8</td>
            </tr>
        </table>
        <br>
        <div class="student">
            Student: <input type="text" name="firstname"> <input type="text" name="lastname"> 
            <input type="submit" value="Send">
        </div>
        
    </form>
    <?php
    if (isset($_POST['html'])) {
        $questions = [
            'html' => 8,
            'angular' => 10,
            'react' => 9,
            'css' => 10,
            'php' => 10,
            'js' => 8
        ];
        $sum = 0;
        $max_point = 0;
        foreach ($questions as $question => $point) {
            $sum += $point;
            if($max_point < $point) $max_point = $point;
        }
        echo  "<h1>$sum</h1>";
        echo  "<h1>". $sum/count($questions)."</h1>"; 
        echo  "<h1> $max_point</h1>";        
        foreach ($questions as $question => $point) {
            if($point >= $sum/count($questions))
                echo "<div> Question: $question, Point: $point</div>";
        }
    }
    ?>
    
</body>
</html>