<?php
require_once __DIR__ . '/../../vendor/autoload.php';



use App\Auth;

Auth::check();
?>




<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../assets/images/logo.png" type="image/x-icon">
    <title>Netacart - <?= ucfirst(basename($_SERVER['PHP_SELF'], '.php')) ?> </title>

    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/dataTables.bootstrap5.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <link rel="stylesheet" href="../assets/fontawesome/css/all.min.css">
    <link rel="stylesheet" href="../assets/fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="../assets/css/admin/style.css">



</head>


<body>

    <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
        <div class="container">
            <!-- Left Logo -->
            <a class="navbar-brand fw-bold" href="dashboard.php">
                <i class="fas fa-shopping-cart me-2"></i>Netacart
            </a>

            <!-- Mobile Toggle Button -->
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent"
                aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <!-- Right Navigation Items -->
            <div class="collapse navbar-collapse justify-content-end" id="navbarContent">
                <ul class="navbar-nav mb-2 mb-lg-0">
                    <!-- Home -->
                    <li class="nav-item">
                        <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : '') ?>" href="dashboard.php">
                            <i class="fas fa-home me-1"></i>Dashboard
                        </a>
                    </li>

                    <!-- slider -->

                    <li class="nav-item">
                        <a class="nav-link <?= (basename($_SERVER['PHP_SELF']) === 'slider.php' ? 'active' : '') ?>" href="slider.php">
                            <i class="fas fa-sliders-h me-1"></i>Sliders
                        </a>
                    </li>

                    <!-- works Dropdown -->
                    <li class="nav-item dropdown <?= (basename($_SERVER['PHP_SELF']) === 'works.php' || basename($_SERVER['PHP_SELF']) === 'portfolio.php') ? 'active' : '' ?>">
                        <a class="nav-link dropdown-toggle <?= (basename($_SERVER['PHP_SELF']) === 'works.php' || basename($_SERVER['PHP_SELF']) === 'portfolio.php') ? 'active' : '' ?>" href="#" id="worksDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-briefcase me-1"></i> Works
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="worksDropdown">
                            <li>
                                <a class="dropdown-item <?= (basename($_SERVER['PHP_SELF']) === 'works.php' ? 'active' : '') ?>" href="works.php">
                                    <i class="fas fa-tasks me-1"></i> Manage Works
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item <?= (basename($_SERVER['PHP_SELF']) === 'portfolio.php' ? 'active' : '') ?>" href="../portfolio.php" target="_blank">
                                    <i class="fas fa-globe me-1"></i> View Portfolio
                                </a>
                            </li>
                        </ul>
                    </li>



                    <!-- User Dropdown with Name -->
                    <li class="nav-item dropdown ">
                        <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user me-1"></i>
                            <?= isset($_SESSION['user_name']) ? htmlspecialchars($_SESSION['user_name']) : 'Hi'; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle me-1"></i>Profile</a></li>
                            <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Logout</a></li>
                        </ul>
                    </li>


                </ul>
            </div>
        </div>
    </nav>