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
        <div>
            <?php
                function sum($a, $b, $c){
                    return $a + $b + $c;
                }

                function product($a, $b, $c){
                    return $a * $b * $c;
                }
                
                function checkData($d){
                    if(empty($d)) return 0;
                    else return $d;
                }

                function checkData1($d){
                    if(empty($d)) return 1;
                    else return $d;
                }

                function checkData2($d, $def_val){
                    if(empty($d)) return $def_val;
                    else return $d;
                }

                if(isset($_POST['sum'])){
                    echo "<h3>Sum = ".sum(checkData($_POST['n1']),
                    checkData($_POST['n2']),
                    checkData($_POST['n3']))."</h3>";
                
                };
                
                if(isset($_POST['product'])){
                    echo "<h3>Product = ".product(checkData1($_POST['n1']),
                    checkData1($_POST['n2']),
                    checkData1($_POST['n3']))."</h3>";
                
                };
            ?>
        </div>
        <input type="number" placeholder="number1" name="n1">
        <br><br>
        <input type="number" placeholder="number2" name="n2">
        <br><br>
        <input type="number" placeholder="number3" name="n3">
        <br><br>
        <button name="sum">ჯამი</button>
        <button name="product">ნამრავლი</button>
    </form>
</body>
</html>