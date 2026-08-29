<?php

/* =========================================================
   CAFFEINE & COVE
   SECURE PLACE ORDER
========================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/include/config.php";
require_once __DIR__ . "/include/mail.php";


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
   ONLY POST
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] !== "POST"
) {

    header(
        "Location: checkout.php"
    );

    exit;
}


/* =========================================================
   USER ID
========================================================= */

$userId =
    (int)(
        $_SESSION["user_id"] ?? 0
    );


if (
    $userId <= 0
) {

    $_SESSION["login_required_message"] =
        "Your login session has expired. Please login again.";

    $_SESSION["redirect_after_login"] =
        "checkout.php";

    header(
        "Location: login.php"
    );

    exit;
}


/* =========================================================
   CSRF PROTECTION
========================================================= */

$csrfToken =
    $_POST["csrf_token"] ?? "";

$sessionToken =
    $_SESSION["checkout_csrf"] ?? "";


if (
    !is_string($csrfToken) ||
    !is_string($sessionToken) ||
    $csrfToken === "" ||
    $sessionToken === "" ||
    !hash_equals(
        $sessionToken,
        $csrfToken
    )
) {

    $_SESSION["checkout_error"] =
        "Security validation failed. Please refresh checkout and try again.";

    header(
        "Location: checkout.php"
    );

    exit;
}


/* =========================================================
   CART CHECK
========================================================= */

if (
    !isset($_SESSION["cart"]) ||
    !is_array($_SESSION["cart"]) ||
    empty($_SESSION["cart"])
) {

    $_SESSION["checkout_error"] =
        "Your cart is empty.";

    header(
        "Location: cart.php"
    );

    exit;
}


/* =========================================================
   CUSTOMER DATA
========================================================= */

$customerName =
    trim(
        (string)(
            $_POST["customer_name"] ?? ""
        )
    );


$email =
    trim(
        (string)(
            $_POST["email"] ?? ""
        )
    );


$phone =
    trim(
        (string)(
            $_POST["phone"] ?? ""
        )
    );


$orderType =
    trim(
        (string)(
            $_POST["order_type"] ?? ""
        )
    );


$paymentMethod =
    trim(
        (string)(
            $_POST["payment_method"] ?? ""
        )
    );


/* =========================================================
   ORDER TYPE
========================================================= */

$allowedOrderTypes = [

    "Dine-In",

    "Takeaway"

];


if (
    !in_array(
        $orderType,
        $allowedOrderTypes,
        true
    )
) {

    $_SESSION["checkout_error"] =
        "Please select a valid order type.";

    header(
        "Location: checkout.php"
    );

    exit;
}


/* =========================================================
   PAYMENT METHOD
========================================================= */

if (
    $paymentMethod !== "Pay at Café"
) {

    $_SESSION["checkout_error"] =
        "Please select Pay at Café.";

    header(
        "Location: checkout.php"
    );

    exit;
}


/* =========================================================
   CUSTOMER NAME VALIDATION
========================================================= */

if (
    $customerName === "" ||
    mb_strlen(
        $customerName,
        "UTF-8"
    ) > 100
) {

    $_SESSION["checkout_error"] =
        "Please enter a valid name.";

    header(
        "Location: checkout.php"
    );

    exit;
}


/* =========================================================
   EMAIL VALIDATION
========================================================= */

if (
    !filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    ) ||
    mb_strlen(
        $email,
        "UTF-8"
    ) > 150
) {

    $_SESSION["checkout_error"] =
        "Please enter a valid email address.";

    header(
        "Location: checkout.php"
    );

    exit;
}


/* =========================================================
   PHONE VALIDATION
========================================================= */

$phoneDigits =
    preg_replace(
        "/\D/",
        "",
        $phone
    );


if (
    !is_string($phoneDigits) ||
    strlen($phoneDigits) < 10 ||
    strlen($phoneDigits) > 15
) {

    $_SESSION["checkout_error"] =
        "Please enter a valid mobile number.";

    header(
        "Location: checkout.php"
    );

    exit;
}


/* =========================================================
   VERIFY CART
========================================================= */

$cartItems = [];

$subtotal = 0.00;


/* =========================================================
   PRODUCT STATEMENT
========================================================= */

$productSql = "
    SELECT
        id,
        name,
        price,
        status
    FROM products
    WHERE id = ?
    LIMIT 1
";


$productStmt =
    mysqli_prepare(
        $link,
        $productSql
    );


if (
    !$productStmt
) {

    error_log(
        "Place order product prepare failed: " .
        mysqli_error($link)
    );

    $_SESSION["checkout_error"] =
        "Unable to verify your cart. Please try again.";

    header(
        "Location: checkout.php"
    );

    exit;
}


/* =========================================================
   CHECK EVERY CART ITEM
========================================================= */

foreach (
    $_SESSION["cart"]
    as $cartKey => $item
) {


    /* =====================================================
       CART KEY VALIDATION
    ====================================================== */

    if (
        !is_string($cartKey) ||
        !preg_match(
            '/^product_[1-9][0-9]*$/',
            $cartKey
        )
    ) {

        mysqli_stmt_close(
            $productStmt
        );

        $_SESSION["checkout_error"] =
            "Your cart contains an invalid item. Please return to your cart.";

        header(
            "Location: cart.php"
        );

        exit;
    }


    /* =====================================================
       ITEM ARRAY VALIDATION
    ====================================================== */

    if (
        !is_array($item)
    ) {

        mysqli_stmt_close(
            $productStmt
        );

        $_SESSION["checkout_error"] =
            "Your cart contains an invalid item. Please refresh your cart.";

        header(
            "Location: cart.php"
        );

        exit;
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
        $productId <= 0 ||
        $cartKey !==
        "product_" . $productId
    ) {

        mysqli_stmt_close(
            $productStmt
        );

        $_SESSION["checkout_error"] =
            "Your cart contains an invalid product.";

        header(
            "Location: cart.php"
        );

        exit;
    }


    /* =====================================================
       QUANTITY
    ====================================================== */

    $quantity =
        filter_var(
            $item["quantity"] ?? null,
            FILTER_VALIDATE_INT
        );


    if (
        $quantity === false ||
        $quantity < 1 ||
        $quantity > 20
    ) {

        mysqli_stmt_close(
            $productStmt
        );

        $_SESSION["checkout_error"] =
            "Invalid quantity detected in your cart.";

        header(
            "Location: cart.php"
        );

        exit;
    }


    $quantity =
        (int)$quantity;


    /* =====================================================
       GET REAL PRODUCT
    ====================================================== */

    mysqli_stmt_bind_param(
        $productStmt,
        "i",
        $productId
    );


    if (
        !mysqli_stmt_execute(
            $productStmt
        )
    ) {

        error_log(
            "Place order product query failed: " .
            mysqli_stmt_error(
                $productStmt
            )
        );

        mysqli_stmt_close(
            $productStmt
        );

        $_SESSION["checkout_error"] =
            "Unable to verify your cart. Please try again.";

        header(
            "Location: checkout.php"
        );

        exit;
    }


    $productResult =
        mysqli_stmt_get_result(
            $productStmt
        );


    $product =
        $productResult
            ? mysqli_fetch_assoc(
                $productResult
            )
            : null;


    /* =====================================================
       PRODUCT MUST EXIST
    ====================================================== */

    if (
        !$product
    ) {

        mysqli_stmt_close(
            $productStmt
        );

        $_SESSION["checkout_error"] =
            "One of the products in your cart is no longer available.";

        header(
            "Location: cart.php"
        );

        exit;
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

        mysqli_stmt_close(
            $productStmt
        );

        $_SESSION["checkout_error"] =
            "One of the products in your cart is currently unavailable.";

        header(
            "Location: cart.php"
        );

        exit;
    }


    /* =====================================================
       DATABASE PRODUCT NAME
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

        mysqli_stmt_close(
            $productStmt
        );

        $_SESSION["checkout_error"] =
            "A product in your cart has invalid information.";

        header(
            "Location: cart.php"
        );

        exit;
    }


    /* =====================================================
       DATABASE PRODUCT PRICE
    ====================================================== */

    $price =
        (float)(
            $product["price"]
            ?? 0
        );


    if (
        $price <= 0
    ) {

        mysqli_stmt_close(
            $productStmt
        );

        $_SESSION["checkout_error"] =
            "A product in your cart has an invalid price.";

        header(
            "Location: cart.php"
        );

        exit;
    }


    /* =====================================================
       ITEM TOTAL
    ====================================================== */

    $itemTotal =
        round(
            $price * $quantity,
            2
        );


    /* =====================================================
       SUBTOTAL
    ====================================================== */

    $subtotal =
        round(
            $subtotal + $itemTotal,
            2
        );


    /* =====================================================
       VERIFIED CART ITEM
    ====================================================== */

    $cartItems[] = [

        "product_id" =>
            $productId,

        "name" =>
            $productName,

        "price" =>
            $price,

        "quantity" =>
            $quantity,

        "item_total" =>
            $itemTotal

    ];

}


/* =========================================================
   CLOSE PRODUCT STATEMENT
========================================================= */

mysqli_stmt_close(
    $productStmt
);


/* =========================================================
   FINAL CART CHECK
========================================================= */

if (
    empty($cartItems) ||
    $subtotal <= 0
) {

    $_SESSION["checkout_error"] =
        "Your cart is empty or invalid.";

    header(
        "Location: cart.php"
    );

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


if (
    $grandTotal <= 0
) {

    $_SESSION["checkout_error"] =
        "Invalid order total.";

    header(
        "Location: cart.php"
    );

    exit;
}


/* =========================================================
   DATABASE TRANSACTION
========================================================= */

if (
    !mysqli_begin_transaction(
        $link
    )
) {

    error_log(
        "Place order transaction could not start: " .
        mysqli_error($link)
    );

    $_SESSION["checkout_error"] =
        "Unable to place your order right now. Please try again.";

    header(
        "Location: checkout.php"
    );

    exit;
}


try {


    /* =====================================================
       INSERT ORDER
    ====================================================== */

    $orderSql = "
        INSERT INTO orders
        (
            user_id,
            customer_name,
            email,
            phone,
            order_type,
            subtotal,
            tax,
            total,
            payment_method,
            status
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            ?,
            'pending'
        )
    ";


    $orderStmt =
        mysqli_prepare(
            $link,
            $orderSql
        );


    if (
        !$orderStmt
    ) {

        throw new Exception(
            "Unable to prepare order."
        );

    }


    mysqli_stmt_bind_param(
        $orderStmt,
        "issssddds",
        $userId,
        $customerName,
        $email,
        $phone,
        $orderType,
        $subtotal,
        $tax,
        $grandTotal,
        $paymentMethod
    );


    if (
        !mysqli_stmt_execute(
            $orderStmt
        )
    ) {

        mysqli_stmt_close(
            $orderStmt
        );

        throw new Exception(
            "Unable to save order."
        );

    }


    $orderId =
        (int)
        mysqli_insert_id(
            $link
        );


    mysqli_stmt_close(
        $orderStmt
    );


    if (
        $orderId <= 0
    ) {

        throw new Exception(
            "Unable to create order ID."
        );

    }


    /* =====================================================
       INSERT ORDER DETAILS
    ====================================================== */

    $detailSql = "
        INSERT INTO order_details
        (
            order_id,
            product_id,
            product_name,
            price,
            quantity,
            item_total
        )
        VALUES
        (
            ?,
            ?,
            ?,
            ?,
            ?,
            ?
        )
    ";


    $detailStmt =
        mysqli_prepare(
            $link,
            $detailSql
        );


    if (
        !$detailStmt
    ) {

        throw new Exception(
            "Unable to prepare order items."
        );

    }


    foreach (
        $cartItems
        as $item
    ) {


        mysqli_stmt_bind_param(
            $detailStmt,
            "iisdid",
            $orderId,
            $item["product_id"],
            $item["name"],
            $item["price"],
            $item["quantity"],
            $item["item_total"]
        );


        if (
            !mysqli_stmt_execute(
                $detailStmt
            )
        ) {

            mysqli_stmt_close(
                $detailStmt
            );

            throw new Exception(
                "Unable to save order item."
            );

        }

    }


    mysqli_stmt_close(
        $detailStmt
    );


    /* =====================================================
       COMMIT
    ====================================================== */

    if (
        !mysqli_commit(
            $link
        )
    ) {

        throw new Exception(
            "Unable to complete order."
        );

    }


} catch (
    Throwable $e
) {


    mysqli_rollback(
        $link
    );


    error_log(
        "Caffeine & Cove place_order failed: " .
        $e->getMessage()
    );


    $_SESSION["checkout_error"] =
        "Unable to place your order right now. Please try again.";

    header(
        "Location: checkout.php"
    );

    exit;
}


/* =========================================================
   EMAIL
========================================================= */

$emailRows = "";


foreach (
    $cartItems
    as $item
) {

    $safeName =
        htmlspecialchars(
            $item["name"],
            ENT_QUOTES,
            "UTF-8"
        );


    $quantity =
        (int)$item["quantity"];


    $itemTotal =
        number_format(
            $item["item_total"],
            2
        );


    $emailRows .= "

        <tr>

            <td
                style=\"
                    padding:10px;
                    border-bottom:1px solid #eee;
                \"
            >

                {$safeName}

            </td>


            <td
                style=\"
                    padding:10px;
                    border-bottom:1px solid #eee;
                    text-align:center;
                \"
            >

                {$quantity}

            </td>


            <td
                style=\"
                    padding:10px;
                    border-bottom:1px solid #eee;
                    text-align:right;
                \"
            >

                ₹{$itemTotal}

            </td>

        </tr>

    ";

}


$safeCustomerName =
    htmlspecialchars(
        $customerName,
        ENT_QUOTES,
        "UTF-8"
    );


$safeOrderType =
    htmlspecialchars(
        $orderType,
        ENT_QUOTES,
        "UTF-8"
    );


$safePaymentMethod =
    htmlspecialchars(
        $paymentMethod,
        ENT_QUOTES,
        "UTF-8"
    );


$safeOrderId =
    (int)$orderId;


$emailBody = "

<!DOCTYPE html>

<html>

<head>

    <meta charset=\"UTF-8\">

    <title>
        Order Confirmation
    </title>

</head>


<body
    style=\"
        margin:0;
        padding:20px;
        background:#FFF8F2;
        font-family:Arial,sans-serif;
        color:#4A2C1D;
    \"
>


<div
    style=\"
        max-width:650px;
        margin:auto;
        background:#ffffff;
        border:1px solid #eadfd6;
        border-radius:12px;
        overflow:hidden;
    \"
>


    <div
        style=\"
            background:#4A2C1D;
            color:#ffffff;
            padding:25px;
            text-align:center;
        \"
    >

        <h1
            style=\"
                margin:0;
                font-size:26px;
            \"
        >

            Caffeine &amp; Cove

        </h1>


        <p
            style=\"
                margin:8px 0 0;
            \"
        >

            Order Confirmation

        </p>

    </div>


    <div
        style=\"
            padding:25px;
        \"
    >

        <h2
            style=\"
                margin-top:0;
            \"
        >

            Thank you,
            {$safeCustomerName}! ☕

        </h2>


        <p>

            Your order has been placed successfully.

        </p>


        <p>

            <strong>
                Order #{$safeOrderId}
            </strong>

        </p>


        <table
            width=\"100%\"
            cellpadding=\"0\"
            cellspacing=\"0\"
            style=\"
                border-collapse:collapse;
                margin-top:20px;
            \"
        >

            <thead>

                <tr
                    style=\"
                        background:#FFF8F2;
                    \"
                >

                    <th
                        style=\"
                            padding:10px;
                            text-align:left;
                        \"
                    >

                        Item

                    </th>


                    <th
                        style=\"
                            padding:10px;
                            text-align:center;
                        \"
                    >

                        Qty

                    </th>


                    <th
                        style=\"
                            padding:10px;
                            text-align:right;
                        \"
                    >

                        Total

                    </th>

                </tr>

            </thead>


            <tbody>

                {$emailRows}

            </tbody>

        </table>


        <div
            style=\"
                margin-top:20px;
                padding:15px;
                background:#FFF8F2;
                border-radius:8px;
            \"
        >

            <p>

                <strong>
                    Subtotal:
                </strong>

                ₹" .
                number_format(
                    $subtotal,
                    2
                ) .
                "

            </p>


            <p>

                <strong>
                    Tax:
                </strong>

                ₹" .
                number_format(
                    $tax,
                    2
                ) .
                "

            </p>


            <p
                style=\"
                    font-size:18px;
                    margin-bottom:0;
                \"
            >

                <strong>
                    Total:
                </strong>

                ₹" .
                number_format(
                    $grandTotal,
                    2
                ) .
                "

            </p>

        </div>


        <p>

            <strong>
                Order Type:
            </strong>

            {$safeOrderType}

        </p>


        <p>

            <strong>
                Payment:
            </strong>

            {$safePaymentMethod}

        </p>


        <p
            style=\"
                margin-top:25px;
                color:#777;
            \"
        >

            We look forward to serving you
            at Caffeine &amp; Cove.

        </p>

    </div>

</div>


</body>

</html>
";


/* =========================================================
   SEND CONFIRMATION EMAIL
========================================================= */

$emailSent =
    sendCoveEmail(
        $email,
        $customerName,
        "Order Confirmation - Caffeine & Cove #" .
        $orderId,
        $emailBody
    );


/*
 * Email failure MUST NOT undo
 * an already successful order.
 */

if (
    !$emailSent
) {

    error_log(
        "Order confirmation email failed for Order #" .
        $orderId
    );

}


/* =========================================================
   CLEAR CART
========================================================= */

unset(
    $_SESSION["cart"]
);


/* =========================================================
   REMOVE USED CHECKOUT TOKEN
========================================================= */

unset(
    $_SESSION["checkout_csrf"]
);


/* =========================================================
   SAVE LAST ORDER
========================================================= */

$_SESSION["last_order_id"] =
    $orderId;


/* =========================================================
   SUCCESS REDIRECT
========================================================= */

header(
    "Location: order_success.php?order_id=" .
    $orderId
);

exit;

?>