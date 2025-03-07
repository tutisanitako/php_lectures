<?php
    $error = "";
    if(isset($_POST['email']) && empty($_POST['email'])){
        $error = "error";
    }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <form class="form-1" action="" method="post">
        <input type="text" placeholder="Email" name="email"> - <span class="error"></span>
        <br><br>
        <input type="text" placeholder="User" name="user"> - <span class="error"></span>
        <br><br>
        <input type="radio" name="age"> 18-26
        <input type="radio"  name="age"> 27-36
        <input type="radio" name="age"> 37-46
        <br><br>

        <button name="signup">Sign Up</button>

        <div>
            <?php
                if(isset($_POST['signup'])){
                    $email = $_POST['email'];
                    $user = $_POST['user'];
                    $age = $_POST['age'];
                    echo "<h3>$email $user $age</h3>";
                }

                

            ?>
        </div>
    </form>
</body>
</html>