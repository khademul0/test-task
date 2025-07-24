<?php

// app/Auth.php
namespace App;

class Auth
{
    public static function check()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['user_id'])) {
            header('Location: /task-project/admin/login.php');
            exit;
        }
    }
}
