<?php

    // if(isset($_POST["cfold"])){
    //     $n_dir = $_POST["dir"];
        
    // }
    // if(empty($n_dir)){
    //    echo "its empty";
    // }
    // else {  
    //     mkdir("storage/".$n_dir);
    // }

    $err_d = "";
    $err_f = "";

    if(isset($_POST['c_folder'])){
        $n_dir = $_POST['dir'];
        if(!empty($n_dir) && !is_dir("storage/".$n_dir)){
            mkdir("storage/".$n_dir);
        }else{
            $err_d = "Folder already exists or is empty";
        }
    }

    if(isset($_POST['c_file'])){
        $n_file = $_POST['file'];
        if(!empty($n_file) && !is_file("storage/".$n_file.".txt")){
            fopen("storage/".$n_file.".txt", 'w');
        }else{
            $err_f = "File already exists or is empty";
        }
    }
    

?>