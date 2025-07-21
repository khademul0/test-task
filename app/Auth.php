<?php

namespace App;

class Auth
{
    public static function check()
    {
        session_start();
        if (!isset($_SESSION['user_id'])) {
            header('Location: ../login.php');
            exit;
        }
    }

    public static function logout()
    {
        session_start();
        session_destroy();
        header('Location: ../login.php');
        exit;
    }
}
