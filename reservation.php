<?php

/* =========================================================
   CAFFEINE & COVE
   SECURE RESERVATION PAGE
========================================================= */

session_start();


/* =========================================================
   LOGIN REQUIRED
========================================================= */

if (
    !isset($_SESSION["logged_in"]) ||
    $_SESSION["logged_in"] !== true
) {

    $_SESSION["login_required_message"] =
        "Please login or register before booking a table.";

    $_SESSION["redirect_after_login"] =
        "reservation.php";

    header("Location: login.php");

    exit;
}


/* =========================================================
   CREATE CSRF TOKEN
========================================================= */

if (
    empty($_SESSION["reservation_csrf"])
) {

    $_SESSION["reservation_csrf"] =
        bin2hex(
            random_bytes(32)
        );
}


/* =========================================================
   CURRENT DATE
========================================================= */

$today =
    date("Y-m-d");


/* =========================================================
   DISPLAY ERRORS / SUCCESS
========================================================= */

$reservation_error =
    $_SESSION["reservation_error"] ?? "";


$reservation_success =
    $_SESSION["reservation_success"] ?? "";


unset(
    $_SESSION["reservation_error"]
);


unset(
    $_SESSION["reservation_success"]
);

?>

<?php include("include/header.php"); ?>

<link
    rel="stylesheet"
    href="css/reservation.css"
>


<main class="reservation-page">


    <!-- =====================================================
         HERO
    ====================================================== -->

    <section class="reservation-hero">

        <div class="reservation-hero-overlay"></div>

        <div class="reservation-hero-content">

            <div class="reservation-hero-icon">
                ☕
            </div>

            <h1>
                Book Your Table
            </h1>

            <div class="reservation-divider">

                <span></span>

                <b>•</b>

                <span></span>

            </div>

            <p>
                Reserve your cozy corner and enjoy great coffee,
                good food and better company.
            </p>

        </div>

    </section>


    <!-- =====================================================
         RESERVATION CONTENT
    ====================================================== -->

    <section class="reservation-section">

        <div class="reservation-container">


            <!-- =================================================
                 INFORMATION
            ================================================== -->

            <div class="reservation-info">

                <span class="reservation-label">
                    RESERVATION
                </span>

                <h2>
                    We’d Love to<br>
                    Welcome You
                </h2>

                <div class="reservation-small-divider">

                    <span></span>

                    <b>☕</b>

                    <span></span>

                </div>

                <p class="reservation-intro">
                    Whether it’s a casual coffee catch-up,
                    a special celebration or a quiet work session,
                    we have the perfect spot for you.
                </p>


                <div class="reservation-feature">

                    <div class="feature-icon">
                        ♡
                    </div>

                    <div>

                        <h3>
                            Cozy Ambience
                        </h3>

                        <p>
                            Comfortable seating for every mood
                        </p>

                    </div>

                </div>


                <div class="reservation-feature">

                    <div class="feature-icon">
                        ◷
                    </div>

                    <div>

                        <h3>
                            Quick Confirmation
                        </h3>

                        <p>
                            Get confirmation after booking
                        </p>

                    </div>

                </div>


                <div class="reservation-feature">

                    <div class="feature-icon">
                        ☕
                    </div>

                    <div>

                        <h3>
                            Great Experience
                        </h3>

                        <p>
                            Delicious food, good vibes,
                            great memories
                        </p>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 FORM
            ================================================== -->

            <div class="reservation-form-card">


                <div class="reservation-form-heading">

                    <h2>
                        Book Your Table
                    </h2>

                    <div class="form-divider">

                        <span></span>

                        <b>•</b>

                        <span></span>

                    </div>

                </div>


                <!-- =================================================
                     ERROR
                ================================================== -->

                <?php if (
                    $reservation_error !== ""
                ): ?>

                    <div
                        class="reservation-message reservation-error"
                        role="alert"
                    >

                        <?php

                        echo htmlspecialchars(
                            $reservation_error,
                            ENT_QUOTES,
                            "UTF-8"
                        );

                        ?>

                    </div>

                <?php endif; ?>


                <!-- =================================================
                     SUCCESS
                ================================================== -->

                <?php if (
                    $reservation_success !== ""
                ): ?>

                    <div
                        class="reservation-message reservation-success"
                        role="status"
                    >

                        <?php

                        echo htmlspecialchars(
                            $reservation_success,
                            ENT_QUOTES,
                            "UTF-8"
                        );

                        ?>

                    </div>

                <?php endif; ?>


                <form
                    action="reservation_process.php"
                    method="POST"
                    class="reservation-form"
                    autocomplete="on"
                >


                    <!-- =================================================
                         CSRF
                    ================================================== -->

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php
                            echo htmlspecialchars(
                                $_SESSION["reservation_csrf"],
                                ENT_QUOTES,
                                "UTF-8"
                            );
                        ?>"
                    >


                    <!-- =================================================
                         NAME
                    ================================================== -->

                    <div class="reservation-field">

                        <label for="guest_name">
                            Full Name
                        </label>

                        <div class="reservation-input">

                            <span>♙</span>

                            <input
                                type="text"
                                id="guest_name"
                                name="guest_name"
                                placeholder="Enter your full name"
                                maxlength="100"
                                minlength="2"
                                required
                            >

                        </div>

                    </div>


                    <!-- =================================================
                         PHONE
                    ================================================== -->

                    <div class="reservation-field">

                        <label for="phone">
                            Phone Number
                        </label>

                        <div class="reservation-input">

                            <span>☎</span>

                            <input
                                type="tel"
                                id="phone"
                                name="phone"
                                placeholder="Enter your phone number"
                                maxlength="15"
                                inputmode="numeric"
                                required
                            >

                        </div>

                    </div>


                    <!-- =================================================
                         EMAIL
                    ================================================== -->

                    <div class="reservation-field">

                        <label for="email">
                            Email Address
                        </label>

                        <div class="reservation-input">

                            <span>✉</span>

                            <input
                                type="email"
                                id="email"
                                name="email"
                                placeholder="Enter your email"
                                maxlength="150"
                                required
                            >

                        </div>

                    </div>


                    <!-- =================================================
                         DATE
                    ================================================== -->

                    <div class="reservation-field">

                        <label for="reservation_date">
                            Date
                        </label>

                        <div class="reservation-input">

                            <span>▣</span>

                            <input
                                type="date"
                                id="reservation_date"
                                name="reservation_date"
                                min="<?php
                                    echo $today;
                                ?>"
                                required
                            >

                        </div>

                    </div>


                    <!-- =================================================
                         TIME
                    ================================================== -->

                    <div class="reservation-field">

                        <label for="reservation_time">
                            Time
                        </label>

                        <div class="reservation-input">

                            <span>◷</span>

                            <select
                                id="reservation_time"
                                name="reservation_time"
                                required
                            >

                                <option value="">
                                    Select time
                                </option>

                                <option value="17:00">
                                    5:00 PM
                                </option>

                                <option value="18:00">
                                    6:00 PM
                                </option>

                                <option value="19:00">
                                    7:00 PM
                                </option>

                                <option value="20:00">
                                    8:00 PM
                                </option>

                                <option value="21:00">
                                    9:00 PM
                                </option>

                                <option value="22:00">
                                    10:00 PM
                                </option>

                                <option value="23:00">
                                    11:00 PM
                                </option>

                                <option value="00:00">
                                    12:00 AM
                                </option>

                                <option value="00:30">
                                    12:30 AM
                                </option>

                                <option value="01:00">
                                    1:00 AM
                                </option>

                            </select>

                        </div>

                    </div>


                    <!-- =================================================
                         GUESTS
                    ================================================== -->

                    <div class="reservation-field">

                        <label for="guests">
                            Number of Guests
                        </label>

                        <div class="reservation-input">

                            <span>♟</span>

                            <select
                                id="guests"
                                name="guests"
                                required
                            >

                                <option value="">
                                    Select guests
                                </option>

                                <option value="1">
                                    1 Guest
                                </option>

                                <option value="2">
                                    2 Guests
                                </option>

                                <option value="3">
                                    3 Guests
                                </option>

                                <option value="4">
                                    4 Guests
                                </option>

                                <option value="5">
                                    5 Guests
                                </option>

                                <option value="6">
                                    6 Guests
                                </option>

                                <option value="7">
                                    7 Guests
                                </option>

                                <option value="8">
                                    8 Guests
                                </option>

                                <option value="9">
                                    9 Guests
                                </option>

                                <option value="10">
                                    10 Guests
                                </option>

                            </select>

                        </div>

                    </div>


                    <!-- =================================================
                         SPECIAL REQUEST
                    ================================================== -->

                    <div class="reservation-field reservation-full">

                        <label for="special_request">

                            Special Request

                            <span>
                                (Optional)
                            </span>

                        </label>

                        <div class="reservation-textarea">

                            <span>▤</span>

                            <textarea
                                id="special_request"
                                name="special_request"
                                placeholder="Any special request?"
                                rows="3"
                                maxlength="500"
                            ></textarea>

                        </div>

                    </div>


                    <!-- =================================================
                         SUBMIT
                    ================================================== -->

                    <button
                        type="submit"
                        class="reservation-submit"
                    >

                        <span>
                            ▣
                        </span>

                        Book My Table

                    </button>


                </form>

            </div>

        </div>

    </section>


    <!-- =====================================================
         EXPERIENCE
    ====================================================== -->

    <section class="reservation-experience">

        <div class="experience-container">

            <div class="experience-image">

                <img
                    src="img/reservation-coffee.jpg"
                    alt="Coffee at Caffeine & Cove"
                >

            </div>


            <div class="experience-content">

                <span>
                    CAFFEINE &amp; COVE
                </span>

                <h2>
                    Good Coffee. Good Food.
                    <br>
                    Great Moments.
                </h2>

                <div class="experience-divider">

                    <span></span>

                    <b>☕</b>

                    <span></span>

                </div>

                <p>
                    From intimate dates to friendly gatherings,
                    our café is the perfect place to make memories
                    that last a lifetime.
                </p>

            </div>

        </div>

    </section>


</main>


<?php include("include/footer.php"); ?>