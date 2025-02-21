<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lecture 2</title>
</head>
<body>
    
    <div style="width: 50%; margin: auto; border: solid; padding: 20px">
        <?php
            $students = [
                'tako' => rand(0, 100),
                'zizi' => rand(0, 100),
                'ani' => rand(0, 100),
                'qeti' => rand(0, 100),
                'lizi' => rand(0, 100),
            ];
            echo"</pre>";
            print_r($students);
            echo"</pre>";
            $sum = 0;
            $max_point = 0;
            foreach ($students as $student => $grade) {
                echo "<div> Student: $student, Grade: $grade</div>";
                $sum += $grade;
                if ($max_point < $grade) $max_point = $grade;
            }

            echo "<h1>$sum</h1>";
            // $average = $sum / count($students);
            // echo "<h1>$average</h1>";
            echo "<h1>".$sum/count($students)."</h1>";
            echo "<h1>$max_point</h1>";

            foreach ($students as $student => $grade) {
                if ($grade >= $average)
                    echo "<div> Student: $student, Grade: $grade</div>";
            }

            // $keys = array_keys($full_info);

            // foreach ($keys as $key) {
            //     echo $key . "<br>";
            // }
            // echo "<hr><hr><hr>";
            // echo "Hello PHP";
            // $name="tako";
            // $age=20;
            // echo "<h2>saxeli: $name; asaki: $age</h2>";
            // $info = ["tako", 18, 3.8, true, "GAU"];
            // echo "<h2>saxeli: $info[0]; uni: $info[4]</h2>";
            // echo "<hr>";
            // echo implode("<br>", $info);
            // echo "<hr>";
            // echo $info[0] . "<br>";
            // echo $info[1] . "<br>";
            // echo $info[2] . "<br>";
            // echo $info[3] . "<br>";
            // echo $info[4] . "<br>";
            // echo "<hr>";
            // foreach ($info as $item) {
            //     echo $item . "<br>";
            // }

        ?>
        
        <hr>
        <!-- <div>end of code</div> -->
        <hr>
    </div>
</body>
</html>