<?php

function connection(){
    $conn = new PDO("mysql:host=localhost;dbname=users", "root", "123456");
    return $conn;
}




?>