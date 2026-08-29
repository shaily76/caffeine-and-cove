<?php

/* =========================================================
   CAFFEINE & COVE
   ADMIN LOGIN
   ========================================================= */

session_start();

require_once "../include/config.php";


/* =========================================================
   FORM VARIABLES
   ========================================================= */

$username = "";
$error = "";


/* =========================================================
   LOGIN SUBMISSION
   ========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";


    /* =====================================================
       VALIDATION
    ====================================================== */

    if ($username === "") {

        $error = "Please enter your username.";

    } elseif ($password === "") {

        $error = "Please enter your password.";

    } else {


        /* =================================================
           FIND ADMIN
        ================================================== */

        $sql = "
            SELECT
                id,
                name,
                username,
                email,
                password
            FROM admin_users
            WHERE username = ?
            LIMIT 1
        ";


        $stmt = mysqli_prepare($link, $sql);


        if ($stmt === false) {

            $error =
                "Database error: " .
                mysqli_error($link);

        } else {

            mysqli_stmt_bind_param(
                $stmt,
                "s",
                $username
            );


            mysqli_stmt_execute($stmt);


            $result = mysqli_stmt_get_result($stmt);


            /* =============================================
               CHECK USER
            ============================================== */

            if (
                $result !== false &&
                mysqli_num_rows($result) === 1
            ) {

                $admin = mysqli_fetch_assoc($result);


                $storedPassword =
                    (string)$admin["password"];


                /* =========================================
                   PASSWORD VERIFICATION
                ========================================== */

                $passwordValid = false;


                /*
                 * First try a secure password_hash()
                 * password.
                 */

                if (
                    password_verify(
                        $password,
                        $storedPassword
                    )
                ) {

                    $passwordValid = true;

                }


                /*
                 * Temporary compatibility with the
                 * existing SQL database if the stored
                 * password is still plain text.
                 */

                if (
                    !$passwordValid &&
                    hash_equals(
                        $storedPassword,
                        $password
                    )
                ) {

                    $passwordValid = true;

                }


                /* =========================================
                   LOGIN SUCCESS
                ========================================== */

                if ($passwordValid) {

                    session_regenerate_id(true);


                    $_SESSION["admin_logged_in"] = true;

                    $_SESSION["admin_id"] =
                        (int)$admin["id"];

                    $_SESSION["admin_name"] =
                        $admin["name"];

                    $_SESSION["admin_username"] =
                        $admin["username"];

                    $_SESSION["admin_email"] =
                        $admin["email"];


                    header(
                        "Location: dashboard.php"
                    );

                    exit;

                } else {

                    $error =
                        "Invalid username or password.";

                }

            } else {

                $error =
                    "Invalid username or password.";

            }


            mysqli_stmt_close($stmt);

        }

    }

}

?>


<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <meta
        http-equiv="X-UA-Compatible"
        content="IE=edge"
    >


    <title>
        Caffeine & Cove | Admin Login
    </title>


    <!-- =====================================================
         FONT AWESOME
    ====================================================== -->

    <link
        rel="stylesheet"
        href="assests/plugins/fontawesome-free/css/all.min.css"
    >


    <!-- =====================================================
         ADMINLTE
    ====================================================== -->

    <link
        rel="stylesheet"
        href="assests/css/adminlte.min.css"
    >


    <!-- =====================================================
         CUSTOM THEME
    ====================================================== -->

    <link
        rel="stylesheet"
        href="assests/css/admin-theme.css"
    >

</head>


<body class="login-page">


    <div class="login-box">


        <!-- =================================================
             BRAND
        ================================================== -->

        <div class="text-center mb-3">

            <h2
                style="
                    color:#4A2C1D;
                    font-weight:600;
                    margin-bottom:5px;
                "
            >

                Caffeine & Cove

            </h2>


            <p
                style="
                    color:#8A7468;
                    margin-bottom:0;
                "
            >

                Admin Panel

            </p>

        </div>


        <!-- =================================================
             LOGIN CARD
        ================================================== -->

        <div class="card">


            <div class="card-body login-card-body">


                <p
                    class="login-box-msg"
                    style="
                        color:#6F5548;
                    "
                >

                    Sign in to your admin account

                </p>


                <!-- =================================================
                     ERROR
                ================================================== -->

                <?php if ($error !== ""): ?>

                    <div class="alert alert-danger">

                        <i
                            class="fas fa-exclamation-circle mr-2"
                        ></i>

                        <?php
                        echo htmlspecialchars($error);
                        ?>

                    </div>

                <?php endif; ?>


                <!-- =================================================
                     LOGIN FORM
                ================================================== -->

                <form
                    method="POST"
                    action=""
                >


                    <!-- USERNAME -->

                    <div class="input-group mb-3">

                        <input
                            type="text"
                            name="username"
                            class="form-control"
                            placeholder="Username"
                            value="<?php
                            echo htmlspecialchars(
                                $username
                            );
                            ?>"
                            autocomplete="username"
                            required
                        >


                        <div class="input-group-append">

                            <div class="input-group-text">

                                <i
                                    class="fas fa-user"
                                    style="
                                        color:#7B4728;
                                    "
                                ></i>

                            </div>

                        </div>

                    </div>


                    <!-- PASSWORD -->

                    <div class="input-group mb-3">

                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            placeholder="Password"
                            autocomplete="current-password"
                            required
                        >


                        <div class="input-group-append">

                            <div class="input-group-text">

                                <i
                                    class="fas fa-lock"
                                    style="
                                        color:#7B4728;
                                    "
                                ></i>

                            </div>

                        </div>

                    </div>


                    <!-- LOGIN BUTTON -->

                    <div class="row">

                        <div class="col-12">

                            <button
                                type="submit"
                                class="btn btn-coffee btn-block"
                            >

                                <i
                                    class="fas fa-sign-in-alt mr-2"
                                ></i>

                                Login

                            </button>

                        </div>

                    </div>

                </form>


            </div>

        </div>


    </div>


</body>

</html>