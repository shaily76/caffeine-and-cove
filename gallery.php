<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<?php include "include/header.php"; ?>


<!-- =========================================================
     GALLERY HERO
========================================================= -->

<section class="cove-gallery-hero">

    <div class="gallery-hero-content">

        <span class="gallery-eyebrow">
            OUR GALLERY
        </span>

        <h1>
            Snapshots of
            <span>Caffeine &amp; Cove</span>
        </h1>

        <div class="gallery-hero-line"></div>

        <p>
            A collection of moments, flavors and spaces
            that make our café so special.
        </p>

    </div>


    <div class="gallery-hero-image">

        <img
            src="img/gallery-coffee.jpg"
            alt="Caffeine & Cove Coffee"
        >

    </div>

</section>



<!-- =========================================================
     EXPLORE OUR WORLD
========================================================= -->

<section class="gallery-world-section">

    <div class="gallery-world-container">


        <!-- LEFT INTRO -->
        <div class="gallery-world-intro">

            <div class="gallery-small-icon">
                ☕
            </div>

            <span class="gallery-eyebrow brown">
                EXPLORE OUR WORLD
            </span>

            <h2>
                More Than
                Just <span>Coffee</span>
            </h2>

            <p>
                From handcrafted beverages to delicious bites
                and cozy corners, every frame has a story.
            </p>

            <a href="about.php" class="gallery-primary-button">
                Visit Our Café
                <span>→</span>
            </a>

        </div>



        <!-- PHOTO COLLAGE -->
        <div class="gallery-collage">


            <!-- SPACE -->
            <a href="#"
               class="gallery-photo gallery-photo-space">

                <img
                    src="img/cafe-space.jpg"
                    alt="Caffeine & Cove Café"
                >

                <span class="gallery-photo-label">
                    ☕ Our Space
                </span>

            </a>



            <!-- COFFEE -->
            <a href="#"
               class="gallery-photo gallery-photo-coffee">

                <img
                    src="img/coffee-moment.jpg"
                    alt="Coffee Moment"
                >

                <span class="gallery-photo-label">
                    ☕ Coffee Moments
                </span>

            </a>



            <!-- FOOD -->
            <a href="#"
               class="gallery-photo gallery-photo-food">

                <img
                    src="img/food.jpg"
                    alt="Café Food"
                >

                <span class="gallery-photo-label">
                    🍴 Delicious Bites
                </span>

            </a>



            <!-- DESSERT -->
            <a href="#"
               class="gallery-photo gallery-photo-dessert">

                <img
                    src="img/dessert.jpg"
                    alt="Café Dessert"
                >

                <span class="gallery-photo-label">
                    🍰 Sweet Treats
                </span>

            </a>



            <!-- COLD COFFEE -->
            <a href="#"
               class="gallery-photo gallery-photo-cold">

                <img
                    src="img/cold-coffee-icecream.jpg"
                    alt="Cold Coffee"
                >

                <span class="gallery-photo-label">
                    🥤 Chilled Favorites
                </span>

            </a>


        </div>

    </div>

</section>



<!-- =========================================================
     MOMENTS / QUOTE SECTION
========================================================= -->

<section class="gallery-moments-section">

    <div class="gallery-moments-container">


        <!-- QUOTE -->

        <div class="gallery-quote">

            <div class="quote-mark">
                “
            </div>

            <p>
                Good coffee, good food,
                <br>
                great moments.
                <br>
                That's our recipe.
            </p>

            <span>
                Caffeine &amp; Cove
            </span>

        </div>



        <!-- MINI IMAGE STRIP -->

        <div class="gallery-mini-images">


            <div class="gallery-mini-image">

                <img
                    src="img/cafe-interior.jpg"
                    alt="Café Interior"
                >

            </div>


            <div class="gallery-mini-image">

                <img
                    src="img/coffee-top.jpg"
                    alt="Coffee"
                >

            </div>


            <div class="gallery-mini-image">

                <img
                    src="img/cafe-light.jpg"
                    alt="Café Light"
                >

            </div>


            <div class="gallery-mini-image">

                <img
                    src="img/latte-art.jpg"
                    alt="Latte Art"
                >

            </div>


        </div>



        <!-- INSTAGRAM CARD -->

        <div class="gallery-instagram-card">

            <div class="instagram-icon">
                ◎
            </div>

            <h3>
                Capture Your Moments
            </h3>

            <p>
                Tag us on Instagram and get
                featured on our page!
            </p>

            <span>
                ◎ @caffeineandcove
            </span>

        </div>

    </div>

</section>



<!-- =========================================================
     GALLERY CTA
========================================================= -->

<section class="gallery-bottom-cta">

    <div>

        <span>
            COFFEE • COMFORT • MOMENTS
        </span>

        <h2>
            Every Visit Has
            <em>A Story.</em>
        </h2>

        <p>
            Come create yours at Caffeine &amp; Cove.
        </p>

        <a href="menu.php">
            Explore Our Menu
            <span>→</span>
        </a>

    </div>

</section>



<?php include "include/footer.php"; ?>


<!-- =========================================================
     GALLERY CSS
========================================================= -->

<link
    rel="stylesheet"
    href="css/gallery.css"
>