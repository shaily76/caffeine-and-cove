<?php
session_start();
?>

<?php include("include/header.php"); ?>

<link rel="stylesheet" href="css/contact.css">


<main class="contact-page">
<!-- =====================================================
     HERO
====================================================== -->

<section
    class="contact-hero"
    style="background-image: url('img/contact-hero.jpg');"
>

    <div class="contact-hero-overlay"></div>

    <div class="contact-hero-content">

        <div class="hero-cup">☕</div>

        <h1>Get In Touch</h1>

        <div class="hero-divider">
            <span></span>
            <b>•</b>
            <span></span>
        </div>

        <p>
            We’re here to help and answer any question
            you might have.
        </p>

    </div>

</section>


    <!-- =====================================================
         CONTACT INFORMATION + FORM
    ====================================================== -->

    <section class="contact-section">

        <div class="contact-container">


            <!-- =================================================
                 CONTACT INFORMATION
            ================================================== -->

            <div class="contact-information">

                <div class="section-heading">

                    <h2>Contact Information</h2>

                    <div class="heading-divider">
                        <span></span>
                        <b>♧</b>
                        <span></span>
                    </div>

                </div>


                <!-- Address -->

                <div class="contact-info-row">

                    <div class="contact-icon">
                        📍
                    </div>

                    <div class="contact-info-text">

                        <h3>Visit Our Café</h3>

                        <p>
                            Caffeine &amp; Cove<br>
                            150ft Ring Road, Rajkot,<br>
                            Gujarat, India – 360005
                        </p>

                    </div>

                </div>


                <!-- Phone -->

                <div class="contact-info-row">

                    <div class="contact-icon">
                        ☎
                    </div>

                    <div class="contact-info-text">

                        <h3>Call Us</h3>

                        <p>
                            +91 98765 43210
                        </p>

                    </div>

                </div>


                <!-- Email -->

                <div class="contact-info-row">

                    <div class="contact-icon">
                        ✉
                    </div>

                    <div class="contact-info-text">

                        <h3>Email Us</h3>

                        <p>
                            hello@caffeineandcove.com
                        </p>

                    </div>

                </div>


                <!-- Opening Hours -->

                <div class="contact-info-row">

                    <div class="contact-icon">
                        ◷
                    </div>

                    <div class="contact-info-text">

                        <h3>Opening Hours</h3>

                        <p>
                            Mon – Fri &nbsp;&nbsp; 8:00 AM – 10:00 PM<br>
                            Sat – Sun &nbsp;&nbsp; 9:00 AM – 11:00 PM
                        </p>

                    </div>

                </div>


                <!-- Social -->

                <div class="contact-info-row">

                    <div class="contact-icon">
                        ♥
                    </div>

                    <div class="contact-info-text">

                        <h3>Stay Connected</h3>

                        <div class="social-links">

                            <a href="#" aria-label="Instagram">
                                ◎
                            </a>

                            <a href="#" aria-label="Facebook">
                                f
                            </a>

                            <a href="#" aria-label="WhatsApp">
                                ◉
                            </a>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 CONTACT FORM
            ================================================== -->

            <div class="contact-form-area">

                <div class="section-heading">

                    <h2>Send Us a Message</h2>

                    <div class="heading-divider">
                        <span></span>
                        <b>♧</b>
                        <span></span>
                    </div>

                </div>


                <form
                    action="contact_process.php"
                    method="POST"
                    class="contact-form"
                >


                    <!-- Name -->

                    <div class="form-group">

                        <div class="input-icon">
                            ♙
                        </div>

                        <input
                            type="text"
                            name="name"
                            placeholder="Your Name"
                            required
                        >

                    </div>


                    <!-- Email -->

                    <div class="form-group">

                        <div class="input-icon">
                            ✉
                        </div>

                        <input
                            type="email"
                            name="email"
                            placeholder="Your Email"
                            required
                        >

                    </div>


                    <!-- Subject -->

                    <div class="form-group full-width">

                        <div class="input-icon">
                            ♢
                        </div>

                        <input
                            type="text"
                            name="subject"
                            placeholder="Subject"
                            required
                        >

                    </div>


                    <!-- Message -->

                    <div class="form-group textarea-group full-width">

                        <div class="input-icon">
                            ✎
                        </div>

                        <textarea
                            name="message"
                            placeholder="Your Message"
                            required
                        ></textarea>

                    </div>


                    <!-- Submit -->

                    <button
                        type="submit"
                        class="contact-submit"
                    >

                        <span>➤</span>

                        Send Message

                    </button>

                </form>

            </div>

        </div>

    </section>


    <!-- =====================================================
         FIND US / MAP
    ====================================================== -->

    <section class="find-section">

        <div class="find-container">


            <!-- Find Us Text -->

            <div class="find-content">

                <div class="find-decoration">
                    ♧
                </div>

                <h2>
                    Find Us Here
                </h2>

                <p>
                    We are easy to find and
                    always worth the visit.
                </p>


                <div class="find-coffee">
                    ☕
                </div>

                <div class="find-beans">
                    • • •
                </div>

            </div>


            <!-- Map -->

            <div class="map-container">

                <iframe
                    src="https://www.google.com/maps?q=150ft%20Ring%20Road%2C%20Rajkot%2C%20Gujarat%2C%20India&output=embed"
                    loading="lazy"
                    title="Caffeine & Cove Location"
                ></iframe>

                <div class="map-label">

                    <span class="map-pin">
                        📍
                    </span>

                    <div>

                        <strong>
                            Caffeine &amp; Cove
                        </strong>

                        <p>
                            150ft Ring Road,<br>
                            Rajkot, Gujarat, India – 360005
                        </p>

                    </div>

                </div>

            </div>

        </div>

    </section>


   

</main>


<?php include("include/footer.php"); ?>