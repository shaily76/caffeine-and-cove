<?php

/* =========================================================
   CAFFEINE & COVE
   FEEDBACK PAGE
========================================================= */

session_start();


/* =========================================================
   DATABASE CONNECTION
========================================================= */

$conn = new mysqli(
    "localhost",
    "root",
    "",
    "caffeine_cove"
);


/* Check connection */

if ($conn->connect_error) {

    die(
        "Database connection failed: " .
        $conn->connect_error
    );

}


/* =========================================================
   VARIABLES
========================================================= */

$message = "";
$message_type = "";


/* =========================================================
   FORM SUBMISSION
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    /* Get form data */

    $name = trim(
        $_POST["name"] ?? ""
    );

    $email = trim(
        $_POST["email"] ?? ""
    );

    $rating = intval(
        $_POST["rating"] ?? 0
    );

    $subject = trim(
        $_POST["subject"] ?? ""
    );

    $feedback = trim(
        $_POST["feedback"] ?? ""
    );


    /* =====================================================
       VALIDATION
    ===================================================== */

    if (
        $name === "" ||
        $email === "" ||
        $rating === 0 ||
        $feedback === ""
    ) {

        $message =
            "Please fill in all required fields.";

        $message_type = "error";


    } elseif (
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $message =
            "Please enter a valid email address.";

        $message_type = "error";


    } elseif (
        $rating < 1 ||
        $rating > 5
    ) {

        $message =
            "Please select a rating between 1 and 5.";

        $message_type = "error";


    } else {


        /* =================================================
           INSERT FEEDBACK
        ================================================= */

        $sql = "
            INSERT INTO feedback
            (
                name,
                email,
                rating,
                subject,
                message
            )
            VALUES
            (?, ?, ?, ?, ?)
        ";


        $stmt = $conn->prepare($sql);


        if ($stmt) {


            $stmt->bind_param(
                "ssiss",
                $name,
                $email,
                $rating,
                $subject,
                $feedback
            );


            if ($stmt->execute()) {

                $message =
                    "Thank you! Your feedback has been submitted successfully.";

                $message_type = "success";


            } else {

                $message =
                    "Something went wrong. Please try again.";

                $message_type = "error";

            }


            $stmt->close();


        } else {

            $message =
                "Unable to process your feedback.";

            $message_type = "error";

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

    <title>
        Feedback | Caffeine & Cove
    </title>


    <!-- MAIN CSS -->

    <link
        rel="stylesheet"
        href="css/style.css"
    >


    <!-- FEEDBACK CSS -->

    <link
        rel="stylesheet"
        href="css/feedback.css"
    >

</head>


<body>


<?php include "include/header.php"; ?>


<!-- =========================================================
     FEEDBACK HERO
========================================================= -->

<section class="feedback-hero-new">

    <div class="feedback-hero-overlay"></div>


    <div class="feedback-hero-content">

        <span class="feedback-hero-label">

            CAFFEINE &amp; COVE

        </span>


        <h1>

            Share Your

            <em>
                Coffee Moment.
            </em>

        </h1>


        <p>

            Your experience matters to us.

            Tell us what you loved,
            what we can improve,
            and what makes your visit special.

        </p>


        <div class="feedback-hero-line"></div>

    </div>

</section>



<!-- =========================================================
     FEEDBACK SECTION
========================================================= -->

<section class="feedback-section">


    <div class="feedback-wrapper">


        <!-- =================================================
             LEFT SIDE
        ================================================== -->

        <div class="feedback-info">


            <span class="feedback-small-title">

                WE'D LOVE TO HEAR FROM YOU

            </span>


            <h2>

                Share Your

                <em>
                    Thoughts.
                </em>

            </h2>


            <p>

                Whether you loved your coffee,
                enjoyed the atmosphere or have
                an idea for us, we want to hear it.

            </p>


            <div class="feedback-points">


                <!-- POINT 1 -->

                <div class="feedback-point">


                    <div class="point-icon">
                        ☕
                    </div>


                    <div>

                        <h3>
                            Loved Your Coffee?
                        </h3>


                        <p>
                            Tell us what made your
                            visit special.
                        </p>

                    </div>

                </div>



                <!-- POINT 2 -->

                <div class="feedback-point">


                    <div class="point-icon">
                        ♡
                    </div>


                    <div>

                        <h3>
                            Have a Suggestion?
                        </h3>


                        <p>
                            Your ideas help us improve.
                        </p>

                    </div>

                </div>



                <!-- POINT 3 -->

                <div class="feedback-point">


                    <div class="point-icon">
                        ✦
                    </div>


                    <div>

                        <h3>
                            Help Us Grow
                        </h3>


                        <p>
                            Every review makes
                            Caffeine &amp; Cove better.
                        </p>

                    </div>

                </div>


            </div>

        </div>



        <!-- =================================================
             RIGHT SIDE FORM
        ================================================== -->

        <div class="feedback-card">


            <div class="feedback-card-heading">


                <span>
                    CAFFEINE &amp; COVE
                </span>


                <h2>
                    Leave a Review
                </h2>


                <p>
                    It only takes a minute.
                </p>


            </div>



            <!-- MESSAGE -->

            <?php if ($message !== ""): ?>

                <div
                    class="feedback-message
                    <?php echo $message_type; ?>"
                >

                    <?php
                    echo htmlspecialchars(
                        $message
                    );
                    ?>

                </div>

            <?php endif; ?>



            <!-- =================================================
                 FORM
            ================================================== -->

            <form
                method="POST"
                action="feedback.php"
            >


                <!-- NAME + EMAIL -->

                <div class="feedback-row">


                    <div class="feedback-field">


                        <label>
                            Your Name *
                        </label>


                        <input
                            type="text"
                            name="name"
                            placeholder="Enter your name"
                            required
                        >

                    </div>



                    <div class="feedback-field">


                        <label>
                            Email Address *
                        </label>


                        <input
                            type="email"
                            name="email"
                            placeholder="Enter your email"
                            required
                        >

                    </div>


                </div>



                <!-- RATING -->

                <div class="feedback-field">


                    <label>
                        How was your experience? *
                    </label>


                    <div class="rating-options">


                        <label class="rating-option">

                            <input
                                type="radio"
                                name="rating"
                                value="1"
                                required
                            >

                            <span>★</span>

                            <small>1</small>

                        </label>



                        <label class="rating-option">

                            <input
                                type="radio"
                                name="rating"
                                value="2"
                            >

                            <span>★</span>

                            <small>2</small>

                        </label>



                        <label class="rating-option">

                            <input
                                type="radio"
                                name="rating"
                                value="3"
                            >

                            <span>★</span>

                            <small>3</small>

                        </label>



                        <label class="rating-option">

                            <input
                                type="radio"
                                name="rating"
                                value="4"
                            >

                            <span>★</span>

                            <small>4</small>

                        </label>



                        <label class="rating-option">

                            <input
                                type="radio"
                                name="rating"
                                value="5"
                            >

                            <span>★</span>

                            <small>5</small>

                        </label>


                    </div>

                </div>



                <!-- SUBJECT -->

                <div class="feedback-field">


                    <label>
                        Subject
                    </label>


                    <input
                        type="text"
                        name="subject"
                        placeholder="What would you like to tell us?"
                    >

                </div>



                <!-- FEEDBACK -->

                <div class="feedback-field">


                    <label>
                        Your Feedback *
                    </label>


                    <textarea
                        name="feedback"
                        rows="5"
                        placeholder="Share your experience with us..."
                        required
                    ></textarea>


                </div>



                <!-- SUBMIT -->

                <button
                    type="submit"
                    class="feedback-submit"
                >

                    Submit Feedback

                    <span>
                        →
                    </span>

                </button>


                <p class="feedback-note">

                    Thank you for helping us create
                    better coffee moments.

                </p>


            </form>


        </div>


    </div>

</section>



<!-- =========================================================
     FOOTER
========================================================= -->

<?php include "include/footer.php"; ?>


</body>

</html>
