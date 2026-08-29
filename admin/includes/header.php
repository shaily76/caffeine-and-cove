<?php
/* =========================================================
   CAFFEINE & COVE
   ADMINLTE 3 - COMMON HEADER
   ========================================================= */


/*
 * Find the /admin/ part of the current URL.
 * This makes the asset paths work from:
 *
 * admin/dashboard.php
 * admin/products/products.php
 * admin/orders/orders.php
 * etc.
 */

$scriptPath = $_SERVER['SCRIPT_NAME'];

$adminPosition = strpos($scriptPath, '/admin/');

if ($adminPosition !== false) {
    $adminBase = substr($scriptPath, 0, $adminPosition + 7);
} else {
    $adminBase = '/';
}
?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <meta http-equiv="X-UA-Compatible"
          content="IE=edge">

    <title>Caffeine & Cove | Admin</title>


    <!-- =====================================================
         FONT AWESOME
    ====================================================== -->

    <link rel="stylesheet"
          href="<?php echo $adminBase; ?>assests/plugins/fontawesome-free/css/all.min.css">


    <!-- =====================================================
         ADMINLTE
    ====================================================== -->

    <link rel="stylesheet"
          href="<?php echo $adminBase; ?>assests/css/adminlte.min.css">


    <!-- =====================================================
         CUSTOM CAFFEINE & COVE THEME
    ====================================================== -->

    <link rel="stylesheet"
          href="<?php echo $adminBase; ?>assests/css/admin-theme.css">

</head>


<body class="hold-transition sidebar-mini layout-fixed">


<div class="wrapper">


    <!-- =====================================================
         TOP NAVBAR
    ====================================================== -->

    <nav class="main-header navbar navbar-expand navbar-white navbar-light">


        <!-- LEFT -->

        <ul class="navbar-nav">

            <li class="nav-item">

                <a class="nav-link"
                   data-widget="pushmenu"
                   href="#"
                   role="button"
                   title="Toggle Sidebar">

                    <i class="fas fa-bars"></i>

                </a>

            </li>

        </ul>


        <!-- RIGHT -->

        <ul class="navbar-nav ml-auto">


            <!-- Notification -->

            <li class="nav-item">

                <a class="nav-link"
                   href="#"
                   title="Notifications">

                    <i class="fas fa-bell"></i>

                </a>

            </li>


            <!-- Admin -->

            <li class="nav-item">

                <a class="nav-link"
                   href="#"
                   title="Admin Profile">

                    <i class="fas fa-user-circle"></i>

                    <span class="ml-1">
                        Admin
                    </span>

                </a>

            </li>

        </ul>

    </nav>