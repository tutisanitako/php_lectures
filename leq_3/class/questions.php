<?php
    $questions = [
        ['question' => 'What is HTML?', 'point' => 8, 'name' => 'html'],
        ['question' => 'What is Angular?', 'point' => 10, 'name' => 'angular'],
        ['question' => 'What is React?', 'point' => 9, 'name' => 'react'],
        ['question' => 'What is CSS?', 'point' => 10, 'name' => 'css'],
        ['question' => 'What is PHP?', 'point' => 10, 'name' => 'php'],
        ['question' => 'What is JS?', 'point' => 8, 'name' => 'js']
    ];

    shuffle($questions);

    // foreach ($questions as $q) {
    //     echo "<tr>";
    //     echo "<td>{$q['question']}</td>";
    //     echo "<td><input type='text' name='{$q['name']}'></td>";
    //     echo "<td>{$q['point']}</td>";
    //     echo "</tr>";
    // }

    // if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    //     $firstname = $_POST['firstname'] ?? '';
    //     $lastname = $_POST['lastname'] ?? '';

    //     $answers = [];
    //     foreach ($questions as $question) {
    //         $answers[$question['name']] = $_POST[$question['name']] ?? '';
    //     }
        
    // }
?>
