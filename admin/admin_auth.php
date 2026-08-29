<?php

/* =========================================================
   CAFFEINE & COVE
   ADMIN AUTHENTICATION
   ========================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================================================
   CHECK ADMIN LOGIN
   ========================================================= */

if (
    !isset($_SESSION["admin_logged_in"]) ||
    $_SESSION["admin_logged_in"] !== true
) {

    header("Location: login.php");
    exit;
}

?>