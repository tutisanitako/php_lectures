<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <button><a href="classwork.php">home</a></button>
    <h2>Student Registration</h2>
        <form method="get">
            Name: <input type="text" name="name" required><br>
            Surname: <input type="text" name="surname" required><br>
            Position: <input type="date" name="position" required><br>
            Salary: <input type="text" name="Salary" required><br>
            Income Tax %: <input type="number" name="tax" value="20" required><br>
            <input type="submit" name="submit" value="Calculate">
        </form>

    <hr>
    

    <?php
        error_reporting(E_ALL);  
        ini_set('display_errors', 1);

        if (isset($_GET['submit'])) {
            $first_name = $_GET['name'];
            $last_name = $_GET['surname'];
            $position = $_GET['position'];
            $salary = $_GET['Salary'];
            $tax_percentage = $_GET['tax'];


            $withheld_income = ($salary * $tax_percentage) / 100;

            $dar_xel = $salary - $withheld_income;

            echo "<h2>Payroll Details</h2>";
            echo "<table border='1' cellpadding='10'>";
            echo "<tr>
                    <th>First Name</th>
                    <th>Last Name</th>
                    <th>Position</th>
                    <th>Salary</th>
                    <th>Dakavebuli sash</th>
                    <th>Daricxuli Xelfasi</th>
                </tr>";
            echo "<tr>
                    <td>$first_name</td>
                    <td>$last_name</td>
                    <td>$position</td>
                    <td>$salary</td>
                    <td>$withheld_income</td>
                    <td>$dar_xel</td>
                </tr>";
            echo "</table>";
        }
?>

    
</body>
</html>