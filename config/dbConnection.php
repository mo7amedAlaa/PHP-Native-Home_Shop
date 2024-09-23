<?php
$host = "localhost";
$db_name = "e_commerce";
$db_type = "mysql";
$user = "root";
$password="";
try {
    $conn = new PDO("$db_type:host=$host;dbname=$db_name", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
}catch(PDOException $e){
    echo "<p class='text-red-600 text-center ' >Connection failed:". $e->getMessage()."</p>";
}