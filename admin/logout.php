<?php
require_once __DIR__ . '/../vendor/autoload.php';

session_start();

// Destroy all session data
session_destroy();

// Optional: Only delete login session cookies (not remember-me)
setcookie('user_email', '', time() - 3600, '/');

// ❌ Don't delete 'remember_email' and 'remember_pass' cookies
// They will be used to pre-fill login fields

// Redirect to login page
header('Location: login.php');
exit;
