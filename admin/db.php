<?php
$host = 'localhost';
$dbname = 'task-project';
$username = 'root';
$password = ''; // Your password

$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
