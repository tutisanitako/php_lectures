<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP</title>
</head>
<body>
    <div style="width: 50%; margin: auto; border: solid; padding: 20px">
        <h1>Lecture 1</h1>
        <?php
            echo "<hr><hr><hr>";
            $full_info = [
                "name" => "John",
                "age" => 30,
                "city" => "New York",
                "education" => ['GAU', "94 skola"],
            ];

            $keys = array_keys($full_info);

            foreach ($keys as $key) {
                echo $key . "<br>";
            }
            echo "<hr><hr><hr>";
            echo "Hello PHP";
            $name="tako";
            $age=20;
            echo "<h2>saxeli: $name; asaki: $age</h2>";
            $info = ["tako", 18, 3.8, true, "GAU"];
            echo "<h2>saxeli: $info[0]; uni: $info[4]</h2>";
            echo "<hr>";
            echo implode("<br>", $info);
            echo "<hr>";
            echo $info[0] . "<br>";
            echo $info[1] . "<br>";
            echo $info[2] . "<br>";
            echo $info[3] . "<br>";
            echo $info[4] . "<br>";
            echo "<hr>";
            foreach ($info as $item) {
                echo $item . "<br>";
            }

        ?>
        
        <hr>
        <div>end of code</div>
        <hr>
    </div>
</body>
</html>