<?php

$host = "sql304.infinityfree.com";
$username = "if0_43106187"; 
$password = "ZakqR755lB7z6V";
$database = "if0_43106187_al_zahi";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");

?>