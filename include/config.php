<?php

/* =========================================================
   CAFFEINE & COVE
   DATABASE CONFIGURATION
   Local XAMPP + Vercel / TiDB Cloud
========================================================= */


/* =========================================================
   DETECT VERCEL
========================================================= */

$isVercel = (getenv('VERCEL') === '1');


/* =========================================================
   DATABASE SETTINGS
========================================================= */

if ($isVercel) {

    // TiDB Cloud values from Vercel Environment Variables
    $db_host = getenv('TIDB_HOST');
    $db_user = getenv('TIDB_USER');
    $db_pass = getenv('TIDB_PASSWORD');
    $db_name = getenv('TIDB_DATABASE');
    $db_port = getenv('TIDB_PORT');

} else {

    // Local XAMPP / MariaDB
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'caffeine_cove';
    $db_port = 3306;

}


/* =========================================================
   VALIDATE DATABASE SETTINGS
========================================================= */

if (
    empty($db_host) ||
    empty($db_user) ||
    empty($db_name) ||
    empty($db_port)
) {
    die('Database configuration is incomplete.');
}


/* =========================================================
   INITIALIZE MYSQLI
========================================================= */

$link = mysqli_init();

if (!$link) {
    die('Unable to initialize database connection.');
}


/* =========================================================
   VERCEL / TIDB CLOUD CONNECTION
========================================================= */

if ($isVercel) {

    /*
     * TiDB Cloud public endpoints require TLS.
     * MYSQLI_CLIENT_SSL enables the encrypted connection.
     */

    if (
        !mysqli_real_connect(
            $link,
            $db_host,
            $db_user,
            $db_pass,
            $db_name,
            (int)$db_port,
            null,
            MYSQLI_CLIENT_SSL
        )
    ) {
        die(
            'Database connection failed: ' .
            mysqli_connect_error()
        );
    }

} else {

    /* =====================================================
       LOCAL XAMPP CONNECTION
    ===================================================== */

    if (
        !mysqli_real_connect(
            $link,
            $db_host,
            $db_user,
            $db_pass,
            $db_name,
            (int)$db_port
        )
    ) {
        die(
            'Database connection failed: ' .
            mysqli_connect_error()
        );
    }

}


/* =========================================================
   CHARACTER SET
========================================================= */

if (!mysqli_set_charset($link, 'utf8mb4')) {
    die(
        'Failed to set database character set: ' .
        mysqli_error($link)
    );
}

?>
