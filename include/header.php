<?php
/* =========================================================
   CAFFEINE & COVE
   FIXED + SHRINKING HEADER
   ========================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================================================
   CURRENT PAGE
   ========================================================= */

$current_page = basename($_SERVER['PHP_SELF']);


/* =========================================================
   CART COUNT
   ========================================================= */

$cart_count = 0;

if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {

    foreach ($_SESSION['cart'] as $item) {

        if (is_array($item) && isset($item['quantity'])) {
            $cart_count += (int) $item['quantity'];
        } else {
            $cart_count++;
        }
    }
}


/* =========================================================
   LOGIN STATUS
   ========================================================= */

$is_logged_in =
    isset($_SESSION['logged_in']) &&
    $_SESSION['logged_in'] === true;


/* =========================================================
   USERNAME
   ========================================================= */

$user_name = 'User';

if ($is_logged_in) {

    $user_name = $_SESSION['user_name'] ?? 'User';

    $user_name = trim($user_name);

    if ($user_name === '') {
        $user_name = 'User';
    }
}
?>


<!-- =======================================================
     GOOGLE FONTS
     ======================================================= -->

<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

<link
    href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500;600;700&family=Playfair+Display:wght@500;600;700&display=swap"
    rel="stylesheet"
>


<!-- =======================================================
     FONT AWESOME
     ======================================================= -->

<link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
>


<!-- =======================================================
     HEADER CSS
     ======================================================= -->

<link rel="stylesheet" href="css/header.css">


<!-- =======================================================
     FIXED HEADER
     ======================================================= -->

<div class="cove-fixed-header" id="coveFixedHeader">


    <!-- ===================================================
         TOP BAR
         =================================================== -->

    <div class="cove-topbar">

        <div class="cove-topbar-inner">

            <!-- LEFT -->

            <div class="cove-topbar-left">

                <span>
                    <i class="fa-solid fa-mug-hot"></i>
                    Freshly brewed. Thoughtfully served.
                </span>

            </div>


            <!-- RIGHT -->

            <div class="cove-topbar-right">

                <span>
                    <i class="fa-regular fa-clock"></i>
                    Mon – Sun | 5:00 PM – 1:00 AM
                </span>

                <span class="topbar-divider">
                    |
                </span>

                <span>
                    <i class="fa-solid fa-location-dot"></i>
                    Rajkot, Gujarat
                </span>

<!-- 
                SOCIAL ICONS

                <div class="cove-social-icons">

                    <a href="#" aria-label="Facebook">
                        <i class="fa-brands fa-facebook-f"></i>
                    </a>

                    <a href="#" aria-label="Instagram">
                        <i class="fa-brands fa-instagram"></i>
                    </a>

                    <a href="#" aria-label="WhatsApp">
                        <i class="fa-brands fa-whatsapp"></i>
                    </a>

                </div> -->

            </div>

        </div>

    </div>



    <!-- ===================================================
         MAIN HEADER
         =================================================== -->

    <header class="cove-new-header">

        <div class="cove-header-inner">


            <!-- =============================================
                 LOGO
                 ============================================= -->

            <a
                href="index.php"
                class="cove-new-logo"
                aria-label="Caffeine & Cove Home"
            >

                <img
                    src="img/logo.png"
                    alt="Caffeine & Cove"
                >

            </a>



            <!-- =============================================
                 NAVIGATION
                 ============================================= -->

            <nav
                class="cove-new-nav"
                id="coveNavigation"
                aria-label="Main navigation"
            >


                <!-- HOME -->

                <a
                    href="index.php"
                    class="cove-new-nav-link <?php echo ($current_page === 'index.php') ? 'active' : ''; ?>"
                >

                    <i class="fa-solid fa-house"></i>

                    <span>Home</span>

                </a>


                <!-- ABOUT US -->

                <a
                    href="about.php"
                    class="cove-new-nav-link <?php echo ($current_page === 'about.php') ? 'active' : ''; ?>"
                >

                    <i class="fa-solid fa-users"></i>

                    <span>About Us</span>

                </a>


                <!-- MENU -->

                <a
                    href="menu.php"
                    class="cove-new-nav-link <?php echo ($current_page === 'menu.php') ? 'active' : ''; ?>"
                >

                    <i class="fa-solid fa-mug-hot"></i>

                    <span>Menu</span>

                </a>


                <!-- SERVICES -->

                <a
                    href="services.php"
                    class="cove-new-nav-link <?php echo ($current_page === 'services.php') ? 'active' : ''; ?>"
                >

                    <i class="fa-solid fa-bell-concierge"></i>

                    <span>Services</span>

                </a>


                <!-- GALLERY -->

                <a
                    href="gallery.php"
                    class="cove-new-nav-link <?php echo ($current_page === 'gallery.php') ? 'active' : ''; ?>"
                >

                    <i class="fa-regular fa-image"></i>

                    <span>Gallery</span>

                </a>


                <!-- CONTACT -->

                <a
                    href="contact.php"
                    class="cove-new-nav-link <?php echo ($current_page === 'contact.php') ? 'active' : ''; ?>"
                >

                    <i class="fa-solid fa-phone"></i>

                    <span>Contact</span>

                </a>


                <!-- FEEDBACK -->

                <a
                    href="feedback.php"
                    class="cove-new-nav-link <?php echo ($current_page === 'feedback.php') ? 'active' : ''; ?>"
                >

                    <i class="fa-regular fa-comment-dots"></i>

                    <span>Feedback</span>

                </a>



                <!-- =========================================
                     ACCOUNT
                     ========================================= -->

                <?php if ($is_logged_in): ?>

                    <div class="cove-account-wrapper">

                        <button
                            type="button"
                            class="cove-account-button"
                            id="coveAccountButton"
                            aria-expanded="false"
                        >

                            <i class="fa-solid fa-circle-user account-main-icon"></i>

                            <span class="account-user-name">
                                <?php
                                echo htmlspecialchars(
                                    $user_name,
                                    ENT_QUOTES,
                                    'UTF-8'
                                );
                                ?>
                            </span>

                            <i class="fa-solid fa-chevron-down account-arrow"></i>

                        </button>


                        <!-- DROPDOWN -->

                        <div
                            class="cove-account-dropdown"
                            id="coveAccountDropdown"
                        >

                            <a href="my_profile.php">

                                <i class="fa-solid fa-user"></i>

                                <span>
                                    My Profile
                                </span>

                            </a>


                            <a href="my_orders.php">

                                <i class="fa-solid fa-bag-shopping"></i>

                                <span>
                                    My Orders
                                </span>

                            </a>


                            <a href="my_reservations.php">

                                <i class="fa-solid fa-calendar-check"></i>

                                <span>
                                    My Reservations
                                </span>

                            </a>


                            <div class="account-dropdown-line"></div>


                            <a
                                href="logout.php"
                                class="account-logout"
                            >

                                <i class="fa-solid fa-right-from-bracket"></i>

                                <span>
                                    Logout
                                </span>

                            </a>

                        </div>

                    </div>


                <?php else: ?>

                    <!-- REGISTER -->

                    <a
                        href="register.php"
                        class="cove-new-nav-link <?php echo ($current_page === 'register.php') ? 'active' : ''; ?>"
                    >

                        <i class="fa-solid fa-user-plus"></i>

                        <span>
                            Register
                        </span>

                    </a>


                    <!-- LOGIN -->

                    <a
                        href="login.php"
                        class="cove-new-nav-link <?php echo ($current_page === 'login.php') ? 'active' : ''; ?>"
                    >

                        <i class="fa-solid fa-right-to-bracket"></i>

                        <span>
                            Login
                        </span>

                    </a>

                <?php endif; ?>

            </nav>



            <!-- =============================================
                 ACTIONS
                 ============================================= -->

            <div class="cove-new-actions">


                <!-- CART -->

                <a
                    href="cart.php"
                    class="cove-new-cart"
                    aria-label="Shopping cart"
                >

                    <i class="fa-solid fa-cart-shopping"></i>

                    <?php if ($cart_count > 0): ?>

                        <span class="cove-new-cart-count">
                            <?php echo $cart_count; ?>
                        </span>

                    <?php endif; ?>

                </a>


                <!-- BOOK A TABLE -->

                <a
                    href="reservation.php"
                    class="cove-new-book"
                >

                    <span>
                        Book a Table
                    </span>

                    <span class="book-arrow">
                        →
                    </span>

                </a>


                <!-- MOBILE -->

                <button
                    type="button"
                    class="cove-new-mobile"
                    id="coveMobileButton"
                    aria-label="Open navigation menu"
                    aria-expanded="false"
                >

                    <span></span>
                    <span></span>
                    <span></span>

                </button>

            </div>

        </div>

    </header>

</div>


<!-- =======================================================
     HEADER SPACE
     ======================================================= -->

<div
    class="cove-header-spacer"
    aria-hidden="true"
></div>



<!-- =======================================================
     JAVASCRIPT
     ======================================================= -->

<script>

document.addEventListener("DOMContentLoaded", function () {


    const fixedHeader =
        document.getElementById("coveFixedHeader");

    const spacer =
        document.querySelector(".cove-header-spacer");


    /* =====================================================
       HEADER HEIGHT
       ===================================================== */

    function updateHeaderHeight() {

        if (fixedHeader && spacer) {

            spacer.style.height =
                fixedHeader.offsetHeight + "px";

        }

    }


    updateHeaderHeight();


    window.addEventListener(
        "resize",
        updateHeaderHeight
    );



    /* =====================================================
       SCROLL SHRINK
       ===================================================== */

    function handleScroll() {

        if (!fixedHeader) {
            return;
        }


        if (window.scrollY > 40) {

            fixedHeader.classList.add("scrolled");

        } else {

            fixedHeader.classList.remove("scrolled");

        }


        updateHeaderHeight();

    }


    window.addEventListener(
        "scroll",
        handleScroll,
        { passive: true }
    );


    handleScroll();



    /* =====================================================
       MOBILE MENU
       ===================================================== */

    const mobileButton =
        document.getElementById(
            "coveMobileButton"
        );

    const navigation =
        document.getElementById(
            "coveNavigation"
        );


    if (mobileButton && navigation) {

        mobileButton.addEventListener(
            "click",
            function () {

                navigation.classList.toggle(
                    "mobile-open"
                );

                mobileButton.classList.toggle(
                    "active"
                );


                const isOpen =
                    navigation.classList.contains(
                        "mobile-open"
                    );


                mobileButton.setAttribute(
                    "aria-expanded",
                    isOpen ? "true" : "false"
                );

            }
        );

    }



    /* =====================================================
       ACCOUNT DROPDOWN
       ===================================================== */

    const accountButton =
        document.getElementById(
            "coveAccountButton"
        );

    const accountDropdown =
        document.getElementById(
            "coveAccountDropdown"
        );


    if (accountButton && accountDropdown) {


        accountButton.addEventListener(
            "click",
            function (event) {

                event.stopPropagation();


                const isOpen =
                    accountDropdown.classList.toggle(
                        "show"
                    );


                accountButton.classList.toggle(
                    "open",
                    isOpen
                );


                accountButton.setAttribute(
                    "aria-expanded",
                    isOpen ? "true" : "false"
                );

            }
        );


        document.addEventListener(
            "click",
            function (event) {

                if (
                    !accountButton.contains(event.target) &&
                    !accountDropdown.contains(event.target)
                ) {

                    accountDropdown.classList.remove(
                        "show"
                    );

                    accountButton.classList.remove(
                        "open"
                    );

                    accountButton.setAttribute(
                        "aria-expanded",
                        "false"
                    );

                }

            }
        );


        document.addEventListener(
            "keydown",
            function (event) {

                if (event.key === "Escape") {

                    accountDropdown.classList.remove(
                        "show"
                    );

                    accountButton.classList.remove(
                        "open"
                    );

                    accountButton.setAttribute(
                        "aria-expanded",
                        "false"
                    );

                }

            }
        );

    }

});
</script>