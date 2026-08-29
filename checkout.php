<?php

/* =========================================================
   CAFFEINE & COVE
   SECURE CHECKOUT PAGE
========================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/include/config.php";


/* =========================================================
   LOGIN REQUIRED
========================================================= */

if (
    !isset($_SESSION["logged_in"]) ||
    $_SESSION["logged_in"] !== true
) {

    $_SESSION["login_required_message"] =
        "Please login or register before placing an order.";

    $_SESSION["redirect_after_login"] =
        "checkout.php";

    header("Location: login.php");

    exit;
}


/* =========================================================
   USER ID REQUIRED
========================================================= */

$userId =
    (int)(
        $_SESSION["user_id"] ?? 0
    );


if ($userId <= 0) {

    $_SESSION["login_required_message"] =
        "Your login session has expired. Please login again.";

    $_SESSION["redirect_after_login"] =
        "checkout.php";

    header("Location: login.php");

    exit;
}


/* =========================================================
   CART REQUIRED
========================================================= */

if (
    !isset($_SESSION["cart"]) ||
    !is_array($_SESSION["cart"]) ||
    empty($_SESSION["cart"])
) {

    header("Location: cart.php");

    exit;
}


/* =========================================================
   CHECKOUT CSRF TOKEN
========================================================= */

if (
    empty($_SESSION["checkout_csrf"])
) {

    $_SESSION["checkout_csrf"] =
        bin2hex(
            random_bytes(32)
        );

}


/* =========================================================
   VERIFY CART AGAINST DATABASE
========================================================= */

$cartItems = [];

$subtotal = 0;


/* =========================================================
   LOOP THROUGH SESSION CART
========================================================= */

foreach (
    $_SESSION["cart"]
    as $key => $item
) {


    /* =====================================================
       VALID CART KEY
    ====================================================== */

    if (
        !is_string($key) ||
        !preg_match(
            '/^product_[1-9][0-9]*$/',
            $key
        )
    ) {

        unset(
            $_SESSION["cart"][$key]
        );

        continue;
    }


    /* =====================================================
       VALID CART ITEM
    ====================================================== */

    if (
        !is_array($item)
    ) {

        unset(
            $_SESSION["cart"][$key]
        );

        continue;
    }


    /* =====================================================
       PRODUCT ID
    ====================================================== */

    $productId =
        isset($item["product_id"])
            ? (int)$item["product_id"]
            : 0;


    if (
        $productId <= 0 ||
        $key !== "product_" . $productId
    ) {

        unset(
            $_SESSION["cart"][$key]
        );

        continue;
    }


    /* =====================================================
       GET REAL PRODUCT FROM DATABASE
    ====================================================== */

    $sql = "
        SELECT
            id,
            name,
            description,
            price,
            image,
            status
        FROM products
        WHERE id = ?
        LIMIT 1
    ";


    $stmt =
        mysqli_prepare(
            $link,
            $sql
        );


    if (!$stmt) {

        error_log(
            "Checkout product prepare failed: " .
            mysqli_error($link)
        );

        $_SESSION["checkout_error"] =
            "Unable to verify your cart. Please try again.";

        header("Location: cart.php");

        exit;
    }


    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $productId
    );


    if (
        !mysqli_stmt_execute(
            $stmt
        )
    ) {

        error_log(
            "Checkout product execute failed: " .
            mysqli_stmt_error($stmt)
        );

        mysqli_stmt_close(
            $stmt
        );

        $_SESSION["checkout_error"] =
            "Unable to verify your cart. Please try again.";

        header("Location: cart.php");

        exit;
    }


    $result =
        mysqli_stmt_get_result(
            $stmt
        );


    $product =
        $result
            ? mysqli_fetch_assoc($result)
            : null;


    mysqli_stmt_close(
        $stmt
    );


    /* =====================================================
       PRODUCT NOT FOUND
    ====================================================== */

    if (!$product) {

        unset(
            $_SESSION["cart"][$key]
        );

        continue;
    }


    /* =====================================================
       PRODUCT MUST BE ACTIVE
    ====================================================== */

    $productStatus =
        strtolower(
            trim(
                (string)(
                    $product["status"]
                    ?? ""
                )
            )
        );


    if (
        $productStatus !== "active"
    ) {

        unset(
            $_SESSION["cart"][$key]
        );

        continue;
    }


    /* =====================================================
       REAL PRODUCT NAME
    ====================================================== */

    $productName =
        trim(
            (string)(
                $product["name"]
                ?? ""
            )
        );


    if (
        $productName === ""
    ) {

        unset(
            $_SESSION["cart"][$key]
        );

        continue;
    }


    /* =====================================================
       REAL DATABASE PRICE
    ====================================================== */

    $price =
        (float)(
            $product["price"]
            ?? 0
        );


    if (
        $price <= 0
    ) {

        unset(
            $_SESSION["cart"][$key]
        );

        continue;
    }


    /* =====================================================
       QUANTITY
    ====================================================== */

    $quantity =
        isset($item["quantity"])
            ? (int)$item["quantity"]
            : 1;


    if (
        $quantity < 1
    ) {

        unset(
            $_SESSION["cart"][$key]
        );

        continue;
    }


    /*
     * Same maximum used by cart.php
     */

    if (
        $quantity > 20
    ) {

        $quantity = 20;

    }


    /* =====================================================
       PRODUCT IMAGE
    ====================================================== */

    $productImage =
        basename(
            trim(
                (string)(
                    $product["image"]
                    ?? ""
                )
            )
        );


    if (
        $productImage === ""
    ) {

        $productImage =
            "default-coffee.jpg";

    }


    /* =====================================================
       DESCRIPTION
    ====================================================== */

    $productDescription =
        trim(
            (string)(
                $product["description"]
                ?? ""
            )
        );


    if (
        $productDescription === ""
    ) {

        $productDescription =
            "Freshly prepared at Caffeine & Cove.";

    }


    /* =====================================================
       TRUSTED SESSION CART DATA
    ====================================================== */

    $_SESSION["cart"][$key] = [

        "product_id" =>
            $productId,

        "name" =>
            $productName,

        "price" =>
            $price,

        "quantity" =>
            $quantity,

        "image" =>
            $productImage,

        "description" =>
            $productDescription

    ];


    /* =====================================================
       ITEM TOTAL
    ====================================================== */

    $itemTotal =
        round(
            $price * $quantity,
            2
        );


    $subtotal =
        round(
            $subtotal + $itemTotal,
            2
        );


    /* =====================================================
       CHECKOUT ITEM
    ====================================================== */

    $cartItems[] = [

        "key" =>
            $key,

        "product_id" =>
            $productId,

        "name" =>
            $productName,

        "price" =>
            $price,

        "quantity" =>
            $quantity,

        "image" =>
            $productImage,

        "total" =>
            $itemTotal

    ];

}


/* =========================================================
   CART EMPTY AFTER VERIFICATION
========================================================= */

if (
    empty($cartItems)
) {

    $_SESSION["checkout_error"] =
        "Your cart is empty or the selected products are no longer available.";

    header("Location: cart.php");

    exit;
}


/* =========================================================
   TAX
========================================================= */

$taxRate =
    0.05;


$tax =
    round(
        $subtotal * $taxRate,
        2
    );


/* =========================================================
   GRAND TOTAL
========================================================= */

$grandTotal =
    round(
        $subtotal + $tax,
        2
    );


/* =========================================================
   USER DETAILS
========================================================= */

$userName =
    trim(
        (string)(
            $_SESSION["user_name"]
            ?? ""
        )
    );


$userEmail =
    trim(
        (string)(
            $_SESSION["user_email"]
            ?? ""
        )
    );


$userMobile =
    trim(
        (string)(
            $_SESSION["user_mobile"]
            ?? ""
        )
    );


/* =========================================================
   HTML ESCAPE
========================================================= */

function checkoutEscape($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}

?>


<?php include("include/header.php"); ?>


<link
    rel="stylesheet"
    href="css/checkout.css"
>


<main class="checkout-page">


    <!-- =====================================================
         HERO
    ====================================================== -->

    <section class="checkout-hero">

        <div class="checkout-hero-content">

            <span class="checkout-eyebrow">

                CAFFEINE &amp; COVE

            </span>


            <h1>

                Checkout

            </h1>


            <p>

                One last step before your coffee moment.

            </p>

        </div>

    </section>


    <!-- =====================================================
         CHECKOUT
    ====================================================== -->

    <section class="checkout-section">

        <div class="checkout-layout">


            <!-- =================================================
                 LEFT SIDE
            ================================================== -->

            <div
                class="
                    checkout-card
                    checkout-form-card
                "
            >


                <div class="section-heading">

                    <span>

                        YOUR DETAILS

                    </span>


                    <h2>

                        Contact &amp; Order Details

                    </h2>


                    <p>

                        Confirm your information
                        before placing your order.

                    </p>

                </div>


                <!-- =================================================
                     ERROR
                ================================================== -->

                <?php if (
                    !empty(
                        $_SESSION["checkout_error"]
                    )
                ): ?>

                    <div
                        class="checkout-error"
                    >

                        <?php

                        echo checkoutEscape(
                            $_SESSION[
                                "checkout_error"
                            ]
                        );

                        unset(
                            $_SESSION[
                                "checkout_error"
                            ]
                        );

                        ?>

                    </div>

                <?php endif; ?>


                <!-- =================================================
                     FORM
                ================================================== -->

                <form
                    action="place_order.php"
                    method="POST"
                    class="checkout-form"
                    id="checkoutForm"
                >


                    <!-- CSRF -->

                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php

                        echo checkoutEscape(
                            $_SESSION[
                                "checkout_csrf"
                            ]
                        );

                        ?>"
                    >


                    <!-- NAME -->

                    <div class="field full">

                        <label
                            for="customer_name"
                        >

                            Full Name

                        </label>


                        <input
                            type="text"
                            id="customer_name"
                            name="customer_name"
                            value="<?php

                            echo checkoutEscape(
                                $userName
                            );

                            ?>"
                            placeholder="Enter your full name"
                            maxlength="100"
                            autocomplete="name"
                            required
                        >

                    </div>


                    <!-- EMAIL -->

                    <div class="field">

                        <label
                            for="email"
                        >

                            Email Address

                        </label>


                        <input
                            type="email"
                            id="email"
                            name="email"
                            value="<?php

                            echo checkoutEscape(
                                $userEmail
                            );

                            ?>"
                            placeholder="Enter your email"
                            maxlength="150"
                            autocomplete="email"
                            required
                        >

                    </div>


                    <!-- PHONE -->

                    <div class="field">

                        <label
                            for="phone"
                        >

                            Mobile Number

                        </label>


                        <input
                            type="tel"
                            id="phone"
                            name="phone"
                            value="<?php

                            echo checkoutEscape(
                                $userMobile
                            );

                            ?>"
                            placeholder="Enter your mobile number"
                            maxlength="15"
                            autocomplete="tel"
                            required
                        >

                    </div>


                    <!-- =================================================
                         ORDER TYPE
                    ================================================== -->

                    <div
                        class="
                            order-section
                            full
                        "
                    >

                        <div class="mini-heading">

                            <span>

                                ORDER TYPE

                            </span>


                            <h3>

                                How would you like
                                your order?

                            </h3>

                        </div>


                        <div
                            class="
                                order-type-grid
                            "
                        >


                            <!-- DINE-IN -->

                            <label
                                class="order-card"
                            >

                                <input
                                    type="radio"
                                    name="order_type"
                                    value="Dine-In"
                                    checked
                                    required
                                >


                                <span
                                    class="
                                        order-card-content
                                    "
                                >

                                    <span
                                        class="order-icon"
                                    >

                                        ☕

                                    </span>


                                    <span
                                        class="order-card-text"
                                    >

                                        <strong>

                                            Dine-In

                                        </strong>


                                        <small>

                                            Enjoy it at
                                            Caffeine &amp; Cove

                                        </small>

                                    </span>


                                    <span
                                        class="custom-radio"
                                    ></span>

                                </span>

                            </label>


                            <!-- TAKEAWAY -->

                            <label
                                class="order-card"
                            >

                                <input
                                    type="radio"
                                    name="order_type"
                                    value="Takeaway"
                                    required
                                >


                                <span
                                    class="
                                        order-card-content
                                    "
                                >

                                    <span
                                        class="order-icon"
                                    >

                                        🥡

                                    </span>


                                    <span
                                        class="
                                            order-card-text
                                        "
                                    >

                                        <strong>

                                            Takeaway

                                        </strong>


                                        <small>

                                            Pick up your
                                            order from café

                                        </small>

                                    </span>


                                    <span
                                        class="custom-radio"
                                    ></span>

                                </span>

                            </label>


                        </div>

                    </div>


                    <!-- =================================================
                         PAYMENT
                    ================================================== -->

                    <div
                        class="
                            payment-section
                            full
                        "
                    >

                        <div class="mini-heading">

                            <span>

                                PAYMENT

                            </span>


                            <h3>

                                Payment Method

                            </h3>

                        </div>


                        <label
                            class="payment-card"
                        >

                            <input
                                type="radio"
                                name="payment_method"
                                value="Pay at Café"
                                checked
                                required
                            >


                            <span
                                class="payment-radio"
                            ></span>


                            <span
                                class="payment-info"
                            >

                                <strong>

                                    Pay at Café

                                </strong>


                                <small>

                                    Pay when you
                                    visit the café.

                                </small>

                            </span>


                            <span
                                class="payment-icon"
                            >

                                ₹

                            </span>

                        </label>

                    </div>


                    <!-- =================================================
                         PLACE ORDER
                    ================================================== -->

                    <button
                        type="submit"
                        class="place-order-button"
                        id="placeOrderButton"
                    >

                        <span>

                            Place My Order

                        </span>


                        <b>

                            →

                        </b>

                    </button>


                </form>

            </div>


            <!-- =================================================
                 RIGHT SIDE
            ================================================== -->

            <aside
                class="checkout-summary"
            >


                <div class="summary-top">

                    <span>

                        YOUR ORDER

                    </span>


                    <h2>

                        Order Summary

                    </h2>

                </div>


                <!-- =================================================
                     ITEMS
                ================================================== -->

                <div class="checkout-items">


                    <?php foreach (
                        $cartItems
                        as $item
                    ): ?>


                        <div
                            class="checkout-item"
                        >


                            <div
                                class="
                                    checkout-item-image
                                "
                            >

                                <img
                                    src="img/<?php

                                    echo checkoutEscape(
                                        $item["image"]
                                    );

                                    ?>"
                                    alt="<?php

                                    echo checkoutEscape(
                                        $item["name"]
                                    );

                                    ?>"
                                    onerror="
                                        this.src='img/default-coffee.jpg';
                                    "
                                >

                            </div>


                            <div
                                class="
                                    checkout-item-info
                                "
                            >

                                <h3>

                                    <?php

                                    echo checkoutEscape(
                                        $item["name"]
                                    );

                                    ?>

                                </h3>


                                <p>

                                    ₹<?php

                                    echo number_format(
                                        $item["price"],
                                        2
                                    );

                                    ?>

                                    ×

                                    <?php

                                    echo (int)
                                        $item["quantity"];

                                    ?>

                                </p>

                            </div>


                            <strong>

                                ₹<?php

                                echo number_format(
                                    $item["total"],
                                    2
                                );

                                ?>

                            </strong>


                        </div>


                    <?php endforeach; ?>


                </div>


                <!-- =================================================
                     TOTALS
                ================================================== -->

                <div
                    class="summary-details"
                >


                    <div
                        class="summary-row"
                    >

                        <span>
                            Subtotal
                        </span>


                        <strong>

                            ₹<?php

                            echo number_format(
                                $subtotal,
                                2
                            );

                            ?>

                        </strong>

                    </div>


                    <div
                        class="summary-row"
                    >

                        <span>
                            Tax (5%)
                        </span>


                        <strong>

                            ₹<?php

                            echo number_format(
                                $tax,
                                2
                            );

                            ?>

                        </strong>

                    </div>


                    <div
                        class="summary-line"
                    ></div>


                    <div
                        class="summary-total"
                    >

                        <span>
                            Total
                        </span>


                        <strong>

                            ₹<?php

                            echo number_format(
                                $grandTotal,
                                2
                            );

                            ?>

                        </strong>

                    </div>


                </div>


                <!-- BACK -->

                <a
                    href="cart.php"
                    class="back-to-cart"
                >

                    ← Back to Cart

                </a>


            </aside>


        </div>

    </section>


</main>


<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const form =
            document.getElementById(
                "checkoutForm"
            );


        const button =
            document.getElementById(
                "placeOrderButton"
            );


        if (
            form &&
            button
        ) {

            form.addEventListener(
                "submit",
                function () {

                    /*
                     * Prevent accidental
                     * double-click orders.
                     */

                    button.disabled =
                        true;


                    button.style.opacity =
                        "0.7";


                    button.querySelector(
                        "span"
                    ).textContent =
                        "Placing Order...";

                }
            );

        }

    }
);

</script>


<?php include("include/footer.php"); ?>