<?php
$host = 'localhost';
$user = 'root';
$password = ''; // If you set one in phpMyAdmin, use it here
$database = 'task-project';

$conn = new mysqli($host, $user, $password, $database);

// Check connection
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
