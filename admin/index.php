<?php
require_once __DIR__ . '/../vendor/autoload.php';

use App\Auth;

Auth::check();
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/fontawesome.min.css">

</head>

<body>
    <h1>admin</h1>
    <h1>Session: <?= $_SESSION['user_email'] ?? 'Not Set' ?></h1>
    <h1>Cookie: <?= $_COOKIE['user_email'] ?? 'Not Set' ?></h1>

    <a href="logout.php">logout</a>







    <script src="../assets/js/jquery-3.7.1.min.js"></script>
    <script src="../assets/js/popper.min.js"></script>
    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>

</html>