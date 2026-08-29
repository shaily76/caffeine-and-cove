<?php

/* =========================================================
   CAFFEINE & COVE
   CUSTOMER CART
========================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/include/config.php";


/* =========================================================
   CART INITIALIZATION
========================================================= */

if (
    !isset($_SESSION["cart"]) ||
    !is_array($_SESSION["cart"])
) {

    $_SESSION["cart"] = [];

}


/* =========================================================
   CSRF TOKEN
========================================================= */

if (
    empty(
        $_SESSION["cart_csrf"]
    )
) {

    $_SESSION["cart_csrf"] =
        bin2hex(
            random_bytes(32)
        );

}


/* =========================================================
   CSRF VALIDATION HELPER
========================================================= */

function validateCartCsrf()
{

    $token =
        $_POST["csrf_token"] ?? "";


    if (
        !is_string($token) ||
        $token === ""
    ) {

        return false;

    }


    if (
        empty(
            $_SESSION["cart_csrf"]
        )
    ) {

        return false;

    }


    return hash_equals(
        $_SESSION["cart_csrf"],
        $token
    );

}


/* =========================================================
   POST ACTIONS
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
) {


    /* =====================================================
       REMOVE ITEM
    ====================================================== */

    if (
        isset(
            $_POST["remove_item"]
        )
    ) {

        if (
            !validateCartCsrf()
        ) {

            $_SESSION["cart_error"] =
                "Security validation failed. Please refresh the page.";

            header(
                "Location: cart.php"
            );

            exit;
        }


        $removeKey =
            trim(
                (string)(
                    $_POST["remove_key"]
                    ?? ""
                )
            );


        /*
         * Valid cart keys look like:
         *
         * product_123
         */

        if (
            !preg_match(
                '/^product_[1-9][0-9]*$/',
                $removeKey
            )
        ) {

            $_SESSION["cart_error"] =
                "Invalid cart item.";

            header(
                "Location: cart.php"
            );

            exit;
        }


        if (
            isset(
                $_SESSION["cart"][$removeKey]
            )
        ) {

            unset(
                $_SESSION["cart"][$removeKey]
            );

        }


        header(
            "Location: cart.php"
        );

        exit;
    }


    /* =====================================================
       UPDATE CART
    ====================================================== */

    if (
        isset(
            $_POST["update_cart"]
        )
    ) {

        if (
            !validateCartCsrf()
        ) {

            $_SESSION["cart_error"] =
                "Security validation failed. Please refresh the page.";

            header(
                "Location: cart.php"
            );

            exit;
        }


        if (
            isset($_POST["quantity"]) &&
            is_array($_POST["quantity"])
        ) {


            foreach (
                $_POST["quantity"]
                as $key => $quantity
            ) {


                $key =
                    (string)$key;


                /*
                 * Only accept product_123
                 */

                if (
                    !preg_match(
                        '/^product_[1-9][0-9]*$/',
                        $key
                    )
                ) {

                    continue;

                }


                /*
                 * Only update items
                 * that actually exist.
                 */

                if (
                    !isset(
                        $_SESSION["cart"][$key]
                    ) ||
                    !is_array(
                        $_SESSION["cart"][$key]
                    )
                ) {

                    continue;

                }


                /*
                 * Strict integer validation.
                 */

                $newQuantity =
                    filter_var(
                        $quantity,
                        FILTER_VALIDATE_INT
                    );


                /*
                 * Invalid / zero:
                 * remove item.
                 */

                if (
                    $newQuantity === false ||
                    $newQuantity < 1
                ) {

                    unset(
                        $_SESSION["cart"][$key]
                    );

                    continue;

                }


                /*
                 * Maximum 20.
                 */

                if (
                    $newQuantity > 20
                ) {

                    $newQuantity = 20;

                }


                $_SESSION["cart"][$key]["quantity"] =
                    $newQuantity;

            }

        }


        header(
            "Location: cart.php"
        );

        exit;
    }

}


/* =========================================================
   REFRESH CART FROM DATABASE
========================================================= */

$cartItems = [];


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
        isset(
            $item["product_id"]
        )
            ? (int)$item["product_id"]
            : 0;


    if (
        $productId <= 0
    ) {

        unset(
            $_SESSION["cart"][$key]
        );

        continue;
    }


    /*
     * Make sure the key and product ID
     * actually match.
     */

    if (
        $key !==
        "product_" . $productId
    ) {

        unset(
            $_SESSION["cart"][$key]
        );

        continue;
    }


    /* =====================================================
       GET REAL PRODUCT
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

        unset(
            $_SESSION["cart"][$key]
        );

        continue;
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

        mysqli_stmt_close(
            $stmt
        );

        unset(
            $_SESSION["cart"][$key]
        );

        continue;
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

    if (
        !$product
    ) {

        unset(
            $_SESSION["cart"][$key]
        );

        continue;
    }


    /* =====================================================
       ACTIVE PRODUCT
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
       REAL PRICE
    ====================================================== */

    $realPrice =
        (float)(
            $product["price"]
            ?? 0
        );


    if (
        $realPrice <= 0
    ) {

        unset(
            $_SESSION["cart"][$key]
        );

        continue;
    }


    /* =====================================================
       REAL NAME
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
       REAL IMAGE
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
       REAL DESCRIPTION
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
       QUANTITY
    ====================================================== */

    $quantity =
        isset(
            $item["quantity"]
        )
            ? (int)$item["quantity"]
            : 1;


    if (
        $quantity < 1
    ) {

        $quantity = 1;

    }


    if (
        $quantity > 20
    ) {

        $quantity = 20;

    }


    /* =====================================================
       SAVE TRUSTED DATA
    ====================================================== */

    $_SESSION["cart"][$key] = [

        "product_id" =>
            $productId,

        "name" =>
            $productName,

        "price" =>
            $realPrice,

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
        $realPrice *
        $quantity;


    /* =====================================================
       CART ITEM
    ====================================================== */

    $cartItems[] = [

        "key" =>
            $key,

        "product_id" =>
            $productId,

        "name" =>
            $productName,

        "price" =>
            $realPrice,

        "quantity" =>
            $quantity,

        "image" =>
            $productImage,

        "description" =>
            $productDescription,

        "total" =>
            $itemTotal

    ];

}


/* =========================================================
   TOTALS
========================================================= */

$subtotal =
    0;


foreach (
    $cartItems
    as $item
) {

    $subtotal +=
        (float)$item["total"];

}


/* =========================================================
   TAX
========================================================= */

$taxRate =
    0.05;


$tax =
    $subtotal *
    $taxRate;


/* =========================================================
   GRAND TOTAL
========================================================= */

$grandTotal =
    $subtotal +
    $tax;


/* =========================================================
   CART COUNT
========================================================= */

$cartCount =
    0;


foreach (
    $cartItems
    as $item
) {

    $cartCount +=
        (int)$item["quantity"];

}


/* =========================================================
   ESCAPE HELPER
========================================================= */

function cartEscape($value)
{

    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );

}


/* =========================================================
   CART ERROR
========================================================= */

$cartError =
    $_SESSION["cart_error"]
    ?? "";


unset(
    $_SESSION["cart_error"]
);

?>


<?php include("include/header.php"); ?>


<link
    rel="stylesheet"
    href="css/cart.css"
>


<main class="cart-page">


    <!-- =====================================================
         CART HERO
    ====================================================== -->

    <section class="cart-hero">

        <div class="cart-hero-overlay"></div>

        <div class="cart-hero-content">

            <div class="cart-hero-icon">
                🛒
            </div>

            <h1>
                Your Cart
            </h1>

            <div class="cart-hero-divider">

                <span></span>

                <b>•</b>

                <span></span>

            </div>

            <p>
                Your coffee selection
            </p>

        </div>

    </section>


    <!-- =====================================================
         CART CONTENT
    ====================================================== -->

    <section class="cart-section">

        <div class="cart-container">


            <?php if (
                $cartError !== ""
            ): ?>

                <div
                    style="
                        margin-bottom:20px;
                        padding:14px 18px;
                        border-radius:10px;
                        background:#F8D7DA;
                        color:#842029;
                        border:1px solid #f1bfc3;
                        font-size:13px;
                        font-weight:600;
                    "
                >

                    ⚠

                    <?php

                    echo cartEscape(
                        $cartError
                    );

                    ?>

                </div>

            <?php endif; ?>


            <?php if (
                empty($cartItems)
            ): ?>


                <!-- EMPTY CART -->

                <div
                    class="empty-cart"
                >

                    <div
                        class="empty-cart-icon"
                    >
                        ☕
                    </div>

                    <span
                        class="empty-cart-label"
                    >
                        YOUR CART
                    </span>

                    <h2>
                        Your Cart is Empty
                    </h2>

                    <p>
                        Looks like you haven’t
                        added anything yet.
                    </p>

                    <a
                        href="menu.php"
                        class="empty-cart-button"
                    >
                        Explore Menu
                    </a>

                </div>


            <?php else: ?>


                <!-- =================================================
                     CART LEFT
                ================================================== -->

                <div
                    class="cart-products"
                >


                    <div
                        class="cart-table-header"
                    >

                        <span>
                            PRODUCT
                        </span>

                        <span>
                            PRICE
                        </span>

                        <span>
                            QUANTITY
                        </span>

                        <span>
                            TOTAL
                        </span>

                        <span>
                            ACTION
                        </span>

                    </div>


                    <!-- =================================================
                         UPDATE FORM
                    ================================================== -->

                    <form
                        method="POST"
                        action="cart.php"
                    >

                        <input
                            type="hidden"
                            name="csrf_token"
                            value="<?php

                            echo cartEscape(
                                $_SESSION[
                                    "cart_csrf"
                                ]
                            );

                            ?>"
                        >


                        <input
                            type="hidden"
                            name="update_cart"
                            value="1"
                        >


                        <?php foreach (
                            $cartItems
                            as $item
                        ): ?>


                            <div
                                class="cart-item"
                            >


                                <!-- PRODUCT -->

                                <div
                                    class="cart-product"
                                >

                                    <div
                                        class="cart-product-image"
                                    >

                                        <img
                                            src="img/<?php

                                            echo cartEscape(
                                                $item[
                                                    "image"
                                                ]
                                            );

                                            ?>"
                                            alt="<?php

                                            echo cartEscape(
                                                $item[
                                                    "name"
                                                ]
                                            );

                                            ?>"
                                            onerror="
                                                this.src='img/default-coffee.jpg';
                                            "
                                        >

                                    </div>


                                    <div
                                        class="cart-product-info"
                                    >

                                        <h3>

                                            <?php

                                            echo cartEscape(
                                                $item[
                                                    "name"
                                                ]
                                            );

                                            ?>

                                        </h3>


                                        <p>

                                            <?php

                                            echo cartEscape(
                                                $item[
                                                    "description"
                                                ]
                                            );

                                            ?>

                                        </p>

                                    </div>

                                </div>


                                <!-- PRICE -->

                                <div
                                    class="cart-price"
                                >

                                    ₹<?php

                                    echo number_format(
                                        $item[
                                            "price"
                                        ],
                                        0
                                    );

                                    ?>

                                </div>


                                <!-- QUANTITY -->

                                <div
                                    class="cart-quantity"
                                >

                                    <button
                                        type="button"
                                        class="quantity-minus"
                                        data-target="qty-<?php

                                        echo cartEscape(
                                            $item["key"]
                                        );

                                        ?>"
                                        aria-label="Decrease quantity"
                                    >
                                        −
                                    </button>


                                    <input
                                        type="number"
                                        id="qty-<?php

                                        echo cartEscape(
                                            $item["key"]
                                        );

                                        ?>"
                                        name="quantity[<?php

                                        echo cartEscape(
                                            $item["key"]
                                        );

                                        ?>]"
                                        value="<?php

                                        echo (int)
                                            $item[
                                                "quantity"
                                            ];

                                        ?>"
                                        min="1"
                                        max="20"
                                        inputmode="numeric"
                                    >


                                    <button
                                        type="button"
                                        class="quantity-plus"
                                        data-target="qty-<?php

                                        echo cartEscape(
                                            $item["key"]
                                        );

                                        ?>"
                                        aria-label="Increase quantity"
                                    >
                                        +
                                    </button>

                                </div>


                                <!-- TOTAL -->

                                <div
                                    class="cart-item-total"
                                >

                                    ₹<?php

                                    echo number_format(
                                        $item[
                                            "total"
                                        ],
                                        0
                                    );

                                    ?>

                                </div>


                                <!-- REMOVE -->

                                <div
                                    class="cart-action"
                                >

                                    <button
                                        type="submit"
                                        name="remove_item"
                                        value="1"
                                        class="remove-item"
                                        formaction="cart.php"
                                        formmethod="POST"
                                        onclick="
                                            this.form.remove_key.value =
                                                '<?php

                                                echo cartEscape(
                                                    $item["key"]
                                                );

                                                ?>';

                                            return confirm(
                                                'Remove this item from your cart?'
                                            );
                                        "
                                        aria-label="Remove item"
                                    >

                                        🗑

                                    </button>

                                </div>


                            </div>


                        <?php endforeach; ?>


                        <!-- REMOVE KEY -->

                        <input
                            type="hidden"
                            name="remove_key"
                            value=""
                        >


                        <!-- UPDATE -->

                        <div
                            class="cart-update-row"
                        >

                            <button
                                type="submit"
                                name="update_cart"
                                value="1"
                                class="update-cart-button"
                            >

                                Update Cart

                            </button>

                        </div>


                    </form>


                    <!-- CONTINUE SHOPPING -->

                    <div
                        class="continue-shopping"
                    >

                        <a
                            href="menu.php"
                        >

                            <span>
                                ←
                            </span>

                            Continue Shopping

                        </a>

                    </div>

                </div>


                <!-- =================================================
                     SUMMARY
                ================================================== -->

                <aside
                    class="cart-summary"
                >


                    <div
                        class="summary-heading"
                    >

                        <h2>
                            Cart Summary
                        </h2>


                        <div
                            class="summary-divider"
                        >

                            <span></span>

                            <b>
                                ♧
                            </b>

                            <span></span>

                        </div>

                    </div>


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
                                0
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
                                0
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
                                0
                            );

                            ?>

                        </strong>

                    </div>


                    <a
                        href="checkout.php"
                        class="checkout-button"
                    >

                        <span>
                            ▣
                        </span>

                        Proceed to Checkout

                    </a>


                    <div
                        class="secure-checkout"
                    >

                        <span>
                            ♢
                        </span>


                        <p>

                            Secure checkout.<br>

                            Your data is safe with us.

                        </p>

                    </div>


                </aside>


            <?php endif; ?>


        </div>

    </section>


    <!-- =====================================================
         PROMOTION
    ====================================================== -->

    <?php if (
        !empty($cartItems)
    ): ?>

        <section
            class="cart-promotion"
        >

            <div
                class="cart-promotion-container"
            >


                <div
                    class="cart-promotion-image"
                >

                    <img
                        src="img/cart-coffee.jpg"
                        alt="Fresh coffee at Caffeine & Cove"
                        onerror="
                            this.style.display='none';
                        "
                    >

                </div>


                <div
                    class="cart-promotion-content"
                >

                    <span>
                        CAFFEINE &amp; COVE
                    </span>


                    <h2>

                        More Coffee,

                        <br>

                        More Happiness!

                    </h2>


                    <div
                        class="promotion-divider"
                    >

                        <span></span>

                        <b>
                            ☕
                        </b>

                        <span></span>

                    </div>


                    <p>

                        Discover more delicious
                        coffees, snacks and desserts
                        on our menu.

                    </p>


                    <a
                        href="menu.php"
                        class="promotion-button"
                    >

                        ☕ Explore Menu

                    </a>

                </div>

            </div>

        </section>

    <?php endif; ?>


</main>


<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {


        /* =====================================================
           MINUS
        ====================================================== */

        document
            .querySelectorAll(
                ".quantity-minus"
            )
            .forEach(
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


                            let value =
                                parseInt(
                                    input.value,
                                    10
                                );


                            if (
                                isNaN(value) ||
                                value < 1
                            ) {

                                value = 1;

                            }


                            value =
                                Math.max(
                                    1,
                                    value - 1
                                );


                            input.value =
                                value;

                        }
                    );

                }
            );


        /* =====================================================
           PLUS
        ====================================================== */

        document
            .querySelectorAll(
                ".quantity-plus"
            )
            .forEach(
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


                            let value =
                                parseInt(
                                    input.value,
                                    10
                                );


                            if (
                                isNaN(value) ||
                                value < 1
                            ) {

                                value = 1;

                            }


                            value =
                                Math.min(
                                    20,
                                    value + 1
                                );


                            input.value =
                                value;

                        }
                    );

                }
            );


        /* =====================================================
           MANUAL INPUT
        ====================================================== */

        document
            .querySelectorAll(
                '.cart-quantity input[type="number"]'
            )
            .forEach(
                function (input) {

                    input.addEventListener(
                        "input",
                        function () {

                            let value =
                                parseInt(
                                    this.value,
                                    10
                                );


                            if (
                                isNaN(value)
                            ) {

                                return;

                            }


                            if (
                                value < 1
                            ) {

                                this.value = 1;

                            }


                            if (
                                value > 20
                            ) {

                                this.value = 20;

                            }

                        }
                    );


                    input.addEventListener(
                        "blur",
                        function () {

                            let value =
                                parseInt(
                                    this.value,
                                    10
                                );


                            if (
                                isNaN(value) ||
                                value < 1
                            ) {

                                this.value = 1;

                            }


                            if (
                                value > 20
                            ) {

                                this.value = 20;

                            }

                        }
                    );

                }
            );

    }
);

</script>


<?php include("include/footer.php"); ?>