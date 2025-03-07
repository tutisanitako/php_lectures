<?php
    // function f1(){
    //     echo "<h1>Hello World!</h1>";
    //     echo "<h1>Hello pookie</h1>";
    // }

    // function f2(){
    //     return "<h1>Hello World!</h1>";
    //     // echo "<h1>Hello pookie</h1>";
    // }

    // function f3($a, $b, $c=9){
    //     echo $a + $b + $c;
    // }

    // f3(1, 2, 3);
    // f1();
    // f2();


    function f3() {
        $num1 = $_POST['number1'];
        $num2 = $_POST['number2'];
        $num3 = $_POST['number3'];
    
            return $num1 + $num2 + $num3;
    }
    
    $result = $_POST ? f3() : null;

    

?>

