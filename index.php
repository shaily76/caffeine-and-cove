<?php

/* =========================================================
   CAFFEINE & COVE
   HOME PAGE
========================================================= */

session_start();

require_once "include/config.php";


/* =========================================================
   CUSTOMER REVIEWS
   LOAD FEEDBACK FROM DATABASE
========================================================= */

$feedback_sql = "
    SELECT
        name,
        rating,
        message,
        created_at
    FROM feedback
    ORDER BY created_at DESC
    LIMIT 6
";


$feedback_result = mysqli_query(
    $link,
    $feedback_sql
);


/* =========================================================
   CHECK FEEDBACK QUERY
========================================================= */

if (!$feedback_result) {

    die(
        "Feedback Error: " .
        mysqli_error($link)
    );

}

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Caffeine &amp; Cove
    </title>

    <meta
        name="description"
        content="Caffeine & Cove - A cozy cafe where every cup tells a story."
    >

    <!-- =====================================================
         GOOGLE FONTS
    ====================================================== -->

    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >

    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >

    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap"
        rel="stylesheet"
    >


    <!-- =====================================================
         HEADER CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="css/header.css"
    >


    <!-- =====================================================
         HOME CSS
    ====================================================== -->

    <link
        rel="stylesheet"
        href="css/home.css"
    >

</head>


<body>


<?php

include("include/header.php");

?>


<!-- =========================================================
     HERO SECTION
========================================================= -->

<section class="sipaura-hero">

    <div class="hero-container">


        <!-- =================================================
             HERO CONTENT
        ================================================== -->

        <div class="hero-content">

            <span class="hero-small-title">
                WELCOME TO Caffeine &amp; Cove
            </span>


            <h1>

                Where every cup

                <span>
                    tells a story
                </span>

            </h1>


            <p>

                At our cafe, every sip unveils a new tale,
                inviting you to explore rich flavors and aromas
                that make each cup special.

            </p>


            <div class="hero-buttons">


                <a
                    href="menu.php"
                    class="hero-primary-btn"
                >

                    View Menu

                </a>


                <a
                    href="about.php"
                    class="hero-secondary-btn"
                >

                    Our Story

                </a>


            </div>

        </div>


        <!-- =================================================
             HERO IMAGE
        ================================================== -->

        <div class="hero-image-wrapper">

            <img
                src="img/hero-coffee.jpg"
                alt="Freshly brewed coffee"
                class="hero-image"
            >

        </div>


    </div>

</section>



<!-- =========================================================
     CAFE FEATURES
========================================================= -->

<section class="cove-features">

    <div class="cove-features-container">


        <!-- Freshly Brewed -->

        <div class="cove-feature">

            <div class="cove-feature-icon">
                ☕
            </div>

            <div class="cove-feature-text">

                <h3>
                    Freshly Brewed
                </h3>

                <p>
                    Every Cup, Every Time
                </p>

            </div>

        </div>


        <!-- Premium Beans -->

        <div class="cove-feature">

            <div class="cove-feature-icon">
                🌿
            </div>

            <div class="cove-feature-text">

                <h3>
                    Premium Beans
                </h3>

                <p>
                    100% Quality Coffee
                </p>

            </div>

        </div>


        <!-- Cozy Ambience -->

        <div class="cove-feature">

            <div class="cove-feature-icon">
                🛋
            </div>

            <div class="cove-feature-text">

                <h3>
                    Cozy Ambience
                </h3>

                <p>
                    Your Comfort Zone
                </p>

            </div>

        </div>


        <!-- Fast WiFi -->

        <div class="cove-feature">

            <div class="cove-feature-icon">
                ◉
            </div>

            <div class="cove-feature-text">

                <h3>
                    Fast WiFi
                </h3>

                <p>
                    Stay Connected
                </p>

            </div>

        </div>


        <!-- Easy Booking -->

        <div class="cove-feature">

            <div class="cove-feature-icon">
                ✓
            </div>

            <div class="cove-feature-text">

                <h3>
                    Easy Booking
                </h3>

                <p>
                    Reserve in Seconds
                </p>

            </div>

        </div>


        <!-- Made With Love -->

        <div class="cove-feature">

            <div class="cove-feature-icon">
                ♡
            </div>

            <div class="cove-feature-text">

                <h3>
                    Made With Love
                </h3>

                <p>
                    In Every Detail
                </p>

            </div>

        </div>


    </div>

</section>



<!-- =========================================================
     OUR COFFEE
========================================================= -->

<section class="coffee-section">

    <div class="coffee-container">


        <!-- Section Heading -->

        <div class="section-heading">

            <span>
                DISCOVER
            </span>

            <h2>
                Our Coffee
            </h2>

            <div class="heading-line">

                <span></span>

                <small>
                    ☕
                </small>

                <span></span>

            </div>

        </div>


        <!-- =================================================
             COFFEE GRID
        ================================================== -->

        <div class="coffee-grid">


            <!-- =================================================
                 CAPPUCCINO
            ================================================== -->

            <div class="coffee-card">

                <div class="coffee-image">

                    <img
                        src="img/cappuccino.jpg"
                        alt="Cappuccino"
                    >

                </div>


                <div class="coffee-info">

                    <div>

                        <h3>
                            Cappuccino
                        </h3>

                        <p>
                            A creamy blend of espresso,
                            steamed milk and delicate foam.
                        </p>

                    </div>


                    <div class="coffee-bottom">

                        <strong>
                            ₹140
                        </strong>


                        <button
                            type="button"
                            class="add-product-btn"
                            onclick="addToCart('Cappuccino', 140)"
                            aria-label="Add Cappuccino to cart"
                        >

                            +

                        </button>

                    </div>

                </div>

            </div>



            <!-- =================================================
                 CAFE LATTE
            ================================================== -->

            <div class="coffee-card">

                <div class="coffee-image">

                    <img
                        src="img/cafe-latte.jpg"
                        alt="Cafe Latte"
                    >

                </div>


                <div class="coffee-info">

                    <div>

                        <h3>
                            Cafe Latte
                        </h3>

                        <p>
                            Smooth espresso combined with
                            warm steamed milk.
                        </p>

                    </div>


                    <div class="coffee-bottom">

                        <strong>
                            ₹150
                        </strong>


                        <button
                            type="button"
                            class="add-product-btn"
                            onclick="addToCart('Cafe Latte', 150)"
                            aria-label="Add Cafe Latte to cart"
                        >

                            +

                        </button>

                    </div>

                </div>

            </div>



            <!-- =================================================
                 AMERICANO
            ================================================== -->

            <div class="coffee-card">

                <div class="coffee-image">

                    <img
                        src="img/americano.jpg"
                        alt="Americano"
                    >

                </div>


                <div class="coffee-info">

                    <div>

                        <h3>
                            Americano
                        </h3>

                        <p>
                            Rich espresso with hot water
                            for a bold and smooth taste.
                        </p>

                    </div>


                    <div class="coffee-bottom">

                        <strong>
                            ₹120
                        </strong>


                        <button
                            type="button"
                            class="add-product-btn"
                            onclick="addToCart('Americano', 120)"
                            aria-label="Add Americano to cart"
                        >

                            +

                        </button>

                    </div>

                </div>

            </div>


        </div>


        <!-- View Full Menu -->

        <div class="section-button">

            <a
                href="menu.php"
                class="view-menu-btn"
            >

                View Full Menu

            </a>

        </div>


    </div>

</section>



<!-- =========================================================
     WHY SIPAURA
========================================================= -->

<section class="why-section">

    <div class="why-container">


        <div class="why-heading">

            <span>
                WHY SIPAURA
            </span>

            <h2>

                Made for your
                perfect coffee moment

            </h2>

        </div>


        <div class="why-grid">


            <!-- Premium Coffee -->

            <div class="why-card">

                <div class="why-icon">
                    ☕
                </div>

                <h3>
                    Premium Coffee
                </h3>

                <p>
                    Carefully selected beans and
                    freshly prepared coffee in every cup.
                </p>

            </div>


            <!-- Crafted With Care -->

            <div class="why-card">

                <div class="why-icon">
                    ✦
                </div>

                <h3>
                    Crafted With Care
                </h3>

                <p>
                    Every drink is prepared with
                    attention to taste and quality.
                </p>

            </div>


            <!-- Cozy Experience -->

            <div class="why-card">

                <div class="why-icon">
                    ♡
                </div>

                <h3>
                    Cozy Experience
                </h3>

                <p>
                    A calm and welcoming space to relax,
                    connect and enjoy your coffee.
                </p>

            </div>


        </div>

    </div>

</section>



<!-- =========================================================
     ABOUT PREVIEW
========================================================= -->

<section class="story-section">

    <div class="story-container">


        <!-- Image -->

        <div class="story-image">

            <img
                src="img/cafe-interior.jpg"
                alt="Caffeine & Cove cafe interior"
            >

        </div>


        <!-- Content -->

        <div class="story-content">

            <span>
                OUR STORY
            </span>


            <h2>
                More than coffee,
                it's a feeling.
            </h2>


            <p>

                Caffeine &amp; Cove was created for people
                who enjoy the little moments — a warm cup
                of coffee, meaningful conversations and a
                cozy place to slow down.

            </p>


            <p>

                From handcrafted coffee to delicious bites,
                everything we serve is made to bring a little
                more warmth to your day.

            </p>


            <a
                href="about.php"
                class="story-btn"
            >

                Discover Our Story

            </a>


        </div>

    </div>

</section>



<!-- =========================================================
     SPECIAL OFFER
========================================================= -->

<section class="offer-section">

    <div class="offer-container">


        <!-- Offer Content -->

        <div class="offer-content">

            <span class="offer-label">
            </span>


            <h2>

                Your second coffee
                just got better.

            </h2>


            <p>

                Buy one selected coffee and get
                the second one at 50% off.

            </p>


            <a
                href="menu.php"
                class="offer-btn"
            >

                Explore Offer

            </a>


        </div>


        <!-- Offer Image -->

        <div class="offer-image">

            <img
                src="img/special-coffee.jpg"
                alt="Special coffee offer"
            >

        </div>


    </div>

</section>



<!-- =========================================================
     CUSTOMER REVIEWS
========================================================= -->

<section class="reviews-section">

    <div class="reviews-container">


        <!-- =================================================
             REVIEW HEADING
        ================================================== -->

        <div class="reviews-heading">

            <span>
                CUSTOMER LOVE
            </span>


            <h2>
                What our customers say
            </h2>


            <p>

                Good coffee, cozy moments and memories
                worth coming back for.

            </p>

        </div>



        <!-- =================================================
             REVIEW GRID
        ================================================== -->

        <div class="reviews-grid">


            <?php if (mysqli_num_rows($feedback_result) > 0): ?>


                <?php while ($review = mysqli_fetch_assoc($feedback_result)): ?>


                    <div class="review-card">


                        <!-- Quote -->

                        <div class="quote-mark">
                            “
                        </div>


                        <!-- Message -->

                        <p>

                            <?php

                            echo htmlspecialchars(
                                $review["message"],
                                ENT_QUOTES,
                                "UTF-8"
                            );

                            ?>

                        </p>


                        <!-- Rating -->

                        <div class="review-stars">

                            <?php

                            $rating =
                                (int) $review["rating"];


                            if ($rating < 1) {
                                $rating = 1;
                            }


                            if ($rating > 5) {
                                $rating = 5;
                            }


                            for (
                                $i = 1;
                                $i <= 5;
                                $i++
                            ) {

                                if ($i <= $rating) {

                                    echo "★";

                                } else {

                                    echo "☆";

                                }

                            }

                            ?>

                        </div>


                        <!-- Customer Name -->

                        <div class="review-name">

                            <?php

                            echo htmlspecialchars(
                                $review["name"],
                                ENT_QUOTES,
                                "UTF-8"
                            );

                            ?>

                        </div>


                        <!-- Customer -->

                        <div class="review-role">

                            Customer

                        </div>


                    </div>


                <?php endwhile; ?>


            <?php else: ?>


                <!-- =================================================
                     NO REVIEWS
                ================================================== -->

                <div class="no-reviews">


                    <div class="quote-mark">
                        “
                    </div>


                    <h3>
                        Be our first reviewer
                    </h3>


                    <p>

                        Share your Caffeine &amp; Cove
                        experience and your review
                        will appear here.

                    </p>


                    <a href="feedback.php">

                        Leave Feedback →

                    </a>


                </div>


            <?php endif; ?>


        </div>

    </div>

</section>



<!-- =========================================================
     FOOTER
========================================================= -->

<?php

include("include/footer.php");

?>



<!-- =========================================================
     HOME JAVASCRIPT
========================================================= -->

<script>

function addToCart(productName, price) {

    /*
     * Temporary frontend function.
     *
     * The actual menu/cart system is handled
     * separately through the PHP cart system.
     */

    alert(
        productName +
        " added to cart!"
    );

}

</script>


</body>

</html>