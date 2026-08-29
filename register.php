<?php
session_start();
?>

<?php include("include/header.php"); ?>

<link rel="stylesheet" href="css/register.css">

<main class="register-page">

    <section class="register-card">


        <!-- =================================================
             LEFT / WELCOME SIDE
        ================================================== -->

        <div class="register-welcome">

            <div class="welcome-content">


                <!-- Coffee illustration -->

                <div class="welcome-cup">

                    <svg
                        viewBox="0 0 100 100"
                        xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true"
                    >

                        <!-- Stem -->

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


                <h2>
                    Welcome to
                </h2>

                <h1>
                    Caffeine &amp; Cove
                </h1>


                <!-- Divider -->

                <div class="welcome-divider">

                    <span></span>

                    <b>•</b>

                    <span></span>

                </div>


                <p>
                    Create your account and be part of our
                    coffee family.
                </p>


                <!-- Decorative leaves -->

                <div class="welcome-leaves">

                    <svg
                        viewBox="0 0 180 60"
                        xmlns="http://www.w3.org/2000/svg"
                        aria-hidden="true"
                    >

                        <path
                            d="M10 35C40 30 58 30 90 30C120 30 140 30 170 35"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                            stroke-linecap="round"
                        />

                        <path
                            d="M49 30C42 18 35 16 29 19C33 28 40 31 49 30"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        />

                        <path
                            d="M63 30C57 18 50 15 44 18C48 27 55 31 63 30"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        />

                        <path
                            d="M117 30C123 18 130 15 136 18C132 27 125 31 117 30"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        />

                        <path
                            d="M131 30C138 18 145 16 151 19C147 28 140 31 131 30"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="1.5"
                        />

                    </svg>

                </div>


                <!-- Coffee beans -->

                <div class="coffee-beans">

                    <span></span>
                    <span></span>
                    <span></span>

                </div>

            </div>

        </div>


        <!-- =================================================
             RIGHT / FORM SIDE
        ================================================== -->

        <div class="register-form-area">

            <div class="register-form-container">


                <div class="form-heading">

                    <h2>
                        Create Your Account
                    </h2>

                    <p>
                        Fill in your details to get started
                    </p>

                </div>


                <!-- Registration Error -->

                <?php

                if (isset($_SESSION["register_error"])) {

                    echo '
                    <div class="register-message error">
                        ' .
                        htmlspecialchars(
                            $_SESSION["register_error"]
                        )
                        . '
                    </div>
                    ';

                    unset($_SESSION["register_error"]);
                }

                ?>


                <form
                    action="register_process.php"
                    method="POST"
                    class="register-form"
                >


                    <!-- =================================================
                         FULL NAME
                    ================================================== -->

                    <div class="form-group full-width">

                        <label for="full_name">
                            Full Name
                        </label>


                        <div class="input-wrap">

                            <span class="field-icon">

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
                                id="full_name"
                                name="full_name"
                                placeholder="Enter your full name"
                                autocomplete="name"
                                required
                            >

                        </div>

                    </div>


                    <!-- =================================================
                         USERNAME
                    ================================================== -->

                    <div class="form-group">

                        <label for="username">
                            Username
                        </label>


                        <div class="input-wrap">

                            <span class="field-icon">

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
                                placeholder="Choose a username"
                                autocomplete="username"
                                required
                            >

                        </div>

                    </div>


                    <!-- =================================================
                         EMAIL
                    ================================================== -->

                    <div class="form-group">

                        <label for="email">
                            Email Address
                        </label>


                        <div class="input-wrap">

                            <span class="field-icon">

                                <svg viewBox="0 0 24 24">

                                    <rect
                                        x="3"
                                        y="5"
                                        width="18"
                                        height="14"
                                        rx="2"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                    />

                                    <path
                                        d="M4 7L12 13L20 7"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.7"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                    />

                                </svg>

                            </span>


                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="Enter your email"
                                autocomplete="email"
                                required
                            >

                        </div>

                    </div>


                    <!-- =================================================
                         MOBILE
                    ================================================== -->

                    <div class="form-group full-width">

                        <label for="mobile">
                            Mobile Number
                        </label>


                        <div class="input-wrap">

                            <span class="field-icon">

                                <svg viewBox="0 0 24 24">

                                    <path
                                        d="M6.5 3.5L9.5 5L8 9L6 8C5.3 9.7 5.8 11.7 7.2 13.2L10.3 16.3C11.8 17.7 13.8 18.2 15.5 17.5L14.5 15.5L18.5 14L20 17C20.5 18 19.9 19.2 18.9 19.7C15.5 21.3 11.2 20.3 8.4 17.5L5.8 14.9C2.9 12.1 1.9 7.8 3.5 4.4C4 3.4 5.2 2.8 6.5 3.5Z"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="1.5"
                                        stroke-linejoin="round"
                                    />

                                </svg>

                            </span>


                            <input
                                type="tel"
                                id="mobile"
                                name="mobile"
                                placeholder="Enter your mobile number"
                                autocomplete="tel"
                                required
                            >

                        </div>

                    </div>


                    <!-- =================================================
                         PASSWORD
                    ================================================== -->

                    <div class="form-group">

                        <label for="password">
                            Password
                        </label>


                        <div class="input-wrap">

                            <span class="field-icon">

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
                                placeholder="Create a password"
                                autocomplete="new-password"
                                required
                            >


                            <button
                                type="button"
                                class="show-password"
                                data-target="password"
                                aria-label="Show password"
                            >
                                ◉
                            </button>

                        </div>

                    </div>


                    <!-- =================================================
                         CONFIRM PASSWORD
                    ================================================== -->

                    <div class="form-group">

                        <label for="confirm_password">
                            Confirm Password
                        </label>


                        <div class="input-wrap">

                            <span class="field-icon">

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
                                id="confirm_password"
                                name="confirm_password"
                                placeholder="Confirm your password"
                                autocomplete="new-password"
                                required
                            >


                            <button
                                type="button"
                                class="show-password"
                                data-target="confirm_password"
                                aria-label="Show password"
                            >
                                ◉
                            </button>

                        </div>

                    </div>


                    

                    <!-- =================================================
                         CREATE ACCOUNT
                    ================================================== -->

                    <button
                        type="submit"
                        class="register-button full-width"
                    >

                        <span class="button-user">
                            +
                        </span>

                        Create Account

                    </button>


                    <!-- =================================================
                         LOGIN
                    ================================================== -->

                    <p class="login-prompt full-width">

                        Already have an account?

                        <a href="login.php">
                            Login
                        </a>

                    </p>


                </form>

            </div>

        </div>

    </section>

</main>


<!-- =========================================================
     PASSWORD TOGGLE
========================================================= -->

<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const passwordButtons =
            document.querySelectorAll(
                ".show-password"
            );


        passwordButtons.forEach(
            function (button) {

                button.addEventListener(
                    "click",
                    function () {

                        const input =
                            document.getElementById(
                                this.dataset.target
                            );


                        if (!input) {
                            return;
                        }


                        if (
                            input.type === "password"
                        ) {

                            input.type = "text";

                        } else {

                            input.type = "password";

                        }

                    }
                );

            }
        );

    }
);

</script>


<?php include("include/footer.php"); ?>