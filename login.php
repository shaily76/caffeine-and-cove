<?php
session_start();
?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Login | Caffeine &amp; Cove</title>

    <!-- Google Fonts -->
    <link rel="preconnect"
          href="https://fonts.googleapis.com">

    <link rel="preconnect"
          href="https://fonts.gstatic.com"
          crossorigin>

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap"
        rel="stylesheet"
    >

    <!-- Header CSS -->
    <link rel="stylesheet"
          href="css/header.css">

    <!-- Login CSS -->
    <link rel="stylesheet"
          href="css/login.css">

</head>


<body>

<?php

if (isset($_SESSION["login_success"])) {

    echo '
    <div class="login-message success">
        ' .
        htmlspecialchars(
            $_SESSION["login_success"]
        ) .
        '
    </div>
    ';

    unset($_SESSION["login_success"]);
}

?>
<?php
include("include/header.php");
?>


<!-- =========================================================
     LOGIN PAGE
========================================================= -->

<main class="login-page">


    <section class="login-card">


        <!-- =================================================
             COFFEE ICON
        ================================================== -->

        <div class="login-cup">

            <svg
                viewBox="0 0 100 100"
                xmlns="http://www.w3.org/2000/svg"
                aria-hidden="true"
            >

                <!-- Steam / Stem -->

                <path
                    d="M50 38C50 28 52 20 57 14"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                />


                <!-- Left leaf -->

                <path
                    d="M51 29C43 20 35 20 29 27C37 29 44 31 51 36"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                />


                <!-- Right leaf -->

                <path
                    d="M53 27C59 19 68 18 73 24C67 28 60 31 52 36"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                />


                <!-- Cup -->

                <path
                    d="M25 42H68V57C68 69 59 76 46 76C33 76 25 69 25 57V42Z"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                />


                <!-- Handle -->

                <path
                    d="M68 48H75C81 48 84 52 84 57C84 62 80 65 75 65H69"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                />


                <!-- Saucer -->

                <path
                    d="M18 80H76"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2.5"
                    stroke-linecap="round"
                />

            </svg>

        </div>


        <!-- =================================================
             HEADING
        ================================================== -->

        <div class="login-heading">

            <h1>
                Welcome Back
            </h1>

            <h2>
                to Caffeine &amp; Cove
            </h2>


            <div class="login-divider">

                <span></span>

                <b>•</b>

                <span></span>

            </div>


            <p>
                Login to continue your coffee journey
            </p>

        </div>


        <!-- =================================================
             LOGIN FORM
        ================================================== -->
        <?php

if (isset($_SESSION["login_error"])) {

    echo '
    <div class="login-message error">
        ' .
        htmlspecialchars(
            $_SESSION["login_error"]
        )
        . '
    </div>
    ';

    unset($_SESSION["login_error"]);
}

?>
        <form
            action="login_process.php"
            method="POST"
            class="login-form"
        >


            <!-- USERNAME / EMAIL -->

            <div class="login-field">

                <label for="username">
                    Username or Email
                </label>

                <div class="login-input-wrap">


                    <span class="login-field-icon">

                        <svg viewBox="0 0 24 24">

                            <circle
                                cx="12"
                                cy="8"
                                r="3.5"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                            />

                            <path
                                d="M5 20C5.8 16.5 8.1 14.8 12 14.8C15.9 14.8 18.2 16.5 19 20"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                                stroke-linecap="round"
                            />

                        </svg>

                    </span>


                    <input
                        type="text"
                        id="username"
                        name="username"
                        placeholder="Enter your username or email"
                        autocomplete="username"
                        required
                    >

                </div>

            </div>


            <!-- PASSWORD -->

            <div class="login-field">

                <label for="password">
                    Password
                </label>

                <div class="login-input-wrap">


                    <span class="login-field-icon">

                        <svg viewBox="0 0 24 24">

                            <rect
                                x="5"
                                y="10"
                                width="14"
                                height="10"
                                rx="2"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                            />

                            <path
                                d="M8 10V7A4 4 0 0 1 16 7V10"
                                fill="none"
                                stroke="currentColor"
                                stroke-width="1.7"
                            />

                        </svg>

                    </span>


                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                        required
                    >


                    <button
                        type="button"
                        class="login-password-toggle"
                        data-target="password"
                        aria-label="Show password"
                    >
                        ◉
                    </button>

                </div>

            </div>


            <!-- OPTIONS -->

            <div class="login-options">


                <label class="remember-me">

                    <input
                        type="checkbox"
                        name="remember"
                    >

                    <span>
                        Remember me
                    </span>

                </label>



            </div>


            <!-- LOGIN BUTTON -->

            <button
                type="submit"
                class="login-button"
            >

                <span class="login-button-icon">
                    +
                </span>

                Login

            </button>


            <!-- REGISTER -->

            <p class="register-prompt">

                Don't have an account?

                <a href="register.php">
                    Register
                </a>

            </p>


        </form>


    </section>


</main>

<?php

include("include/footer.php");

?>

<!-- =========================================================
     PASSWORD TOGGLE
========================================================= -->

<script>

document.addEventListener("DOMContentLoaded", function () {

    const buttons =
        document.querySelectorAll(".login-password-toggle");


    buttons.forEach(function (button) {

        button.addEventListener("click", function () {

            const input =
                document.getElementById(
                    this.dataset.target
                );


            if (!input) {
                return;
            }


            if (input.type === "password") {

                input.type = "text";

            } else {

                input.type = "password";

            }

        });

    });

});

</script>


</body>
</html>