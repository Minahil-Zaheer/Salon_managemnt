<?php
    $hostname = "localhost";
    $username = "root";
    $password = "";
    $db_name = "salon_management";

    $conn = mysqli_connect($hostname, $username, $password, $db_name);

    if(!$conn){
        die("Error in connection :" . mysqli_connect_error());
    };

    mysqli_set_charset($conn, "utf8mb4");
?>
