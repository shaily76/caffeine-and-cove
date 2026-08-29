<?php

/* =========================================================
   CAFFEINE & COVE
   CUSTOMER ORDER DETAILS
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
        "Please login to view your order.";

    $_SESSION["redirect_after_login"] =
        "my_orders.php";

    header("Location: login.php");

    exit;
}


/* =========================================================
   USER ID
========================================================= */

$userId =
    (int)(
        $_SESSION["user_id"] ?? 0
    );


if ($userId <= 0) {

    session_unset();

    session_destroy();

    header("Location: login.php");

    exit;
}


/* =========================================================
   ORDER ID
========================================================= */

$orderIdRaw =
    $_GET["order_id"] ?? "";


if (
    !is_string($orderIdRaw) ||
    !ctype_digit($orderIdRaw) ||
    (int)$orderIdRaw <= 0
) {

    header(
        "Location: my_orders.php"
    );

    exit;
}


$orderId =
    (int)$orderIdRaw;


/* =========================================================
   GET ORDER
   SECURITY:
   ORDER MUST BELONG TO LOGGED-IN USER
========================================================= */

$sql = "
    SELECT
        id,
        user_id,
        customer_name,
        email,
        phone,
        order_type,
        subtotal,
        tax,
        total,
        payment_method,
        status,
        created_at
    FROM orders
    WHERE id = ?
      AND user_id = ?
    LIMIT 1
";


$stmt =
    mysqli_prepare(
        $link,
        $sql
    );


if (!$stmt) {

    error_log(
        "Customer order details prepare failed: " .
        mysqli_error($link)
    );

    header(
        "Location: my_orders.php"
    );

    exit;
}


mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $orderId,
    $userId
);


if (
    !mysqli_stmt_execute(
        $stmt
    )
) {

    error_log(
        "Customer order details execute failed: " .
        mysqli_stmt_error($stmt)
    );

    mysqli_stmt_close(
        $stmt
    );

    header(
        "Location: my_orders.php"
    );

    exit;
}


$result =
    mysqli_stmt_get_result(
        $stmt
    );


if (
    !$result ||
    mysqli_num_rows($result) !== 1
) {

    mysqli_stmt_close(
        $stmt
    );

    header(
        "Location: my_orders.php"
    );

    exit;
}


$order =
    mysqli_fetch_assoc(
        $result
    );


mysqli_stmt_close(
    $stmt
);


/* =========================================================
   GET ORDER ITEMS
========================================================= */

$itemSql = "
    SELECT
        id,
        product_id,
        product_name,
        price,
        quantity,
        item_total
    FROM order_details
    WHERE order_id = ?
    ORDER BY id ASC
";


$itemStmt =
    mysqli_prepare(
        $link,
        $itemSql
    );


$itemResult = false;


if ($itemStmt) {

    mysqli_stmt_bind_param(
        $itemStmt,
        "i",
        $orderId
    );


    if (
        mysqli_stmt_execute(
            $itemStmt
        )
    ) {

        $itemResult =
            mysqli_stmt_get_result(
                $itemStmt
            );

    } else {

        error_log(
            "Customer order items execute failed: " .
            mysqli_stmt_error($itemStmt)
        );

    }

} else {

    error_log(
        "Customer order items prepare failed: " .
        mysqli_error($link)
    );

}


/* =========================================================
   ESCAPE FUNCTION
========================================================= */

function coveOrderEscape($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}


/* =========================================================
   STATUS CLASS
========================================================= */

function coveOrderStatusClass($status)
{

    $status =
        strtolower(
            trim(
                (string)$status
            )
        );


    switch ($status) {

        case "confirmed":

        case "preparing":

        case "ready":

        case "processing":

            return "status-processing";


        case "completed":

            return "status-completed";


        case "cancelled":

        case "canceled":

            return "status-cancelled";


        case "pending":

        default:

            return "status-pending";

    }

}


/* =========================================================
   STATUS LABEL
========================================================= */

function coveOrderStatusLabel($status)
{

    $status =
        strtolower(
            trim(
                (string)$status
            )
        );


    switch ($status) {

        case "confirmed":

            return "Confirmed";


        case "preparing":

            return "Preparing";


        case "ready":

            return "Ready";


        case "processing":

            return "Processing";


        case "completed":

            return "Completed";


        case "cancelled":

        case "canceled":

            return "Cancelled";


        case "pending":

        default:

            return "Pending";

    }

}


/* =========================================================
   STATUS
========================================================= */

$status =
    strtolower(
        trim(
            (string)(
                $order["status"]
                ?? "pending"
            )
        )
    );


$displayStatus =
    coveOrderStatusLabel(
        $status
    );


$statusClass =
    coveOrderStatusClass(
        $status
    );


/* =========================================================
   CAN CUSTOMER CANCEL?
========================================================= */

$canCancel =
    in_array(
        $status,
        [
            "pending",
            "confirmed"
        ],
        true
    );


/* =========================================================
   CANCEL CSRF TOKEN
========================================================= */

if (
    empty(
        $_SESSION["cancel_order_csrf"]
    )
) {

    $_SESSION["cancel_order_csrf"] =
        bin2hex(
            random_bytes(32)
        );

}


/* =========================================================
   ORDER DATE
========================================================= */

$orderDate =
    "N/A";


if (
    !empty(
        $order["created_at"]
    )
) {

    $timestamp =
        strtotime(
            $order["created_at"]
        );


    if (
        $timestamp !== false
    ) {

        $orderDate =
            date(
                "d M Y, h:i A",
                $timestamp
            );

    }

}


/* =========================================================
   ORDER TYPE
========================================================= */

$orderType =
    trim(
        (string)(
            $order["order_type"]
            ?? ""
        )
    );


if (
    !in_array(
        $orderType,
        [
            "Dine-In",
            "Takeaway"
        ],
        true
    )
) {

    $orderType =
        "Dine-In";

}


/* =========================================================
   PAYMENT METHOD
========================================================= */

$paymentMethod =
    trim(
        (string)(
            $order["payment_method"]
            ?? ""
        )
    );


if (
    $paymentMethod !== "Pay at Café"
) {

    $paymentMethod =
        "Pay at Café";

}


/* =========================================================
   TOTALS
========================================================= */

$subtotal =
    max(
        0,
        (float)(
            $order["subtotal"] ?? 0
        )
    );


$tax =
    max(
        0,
        (float)(
            $order["tax"] ?? 0
        )
    );


$total =
    max(
        0,
        (float)(
            $order["total"] ?? 0
        )
    );

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

        Order #<?php
        echo (int)$order["id"];
        ?>

        | Caffeine &amp; Cove

    </title>


    <link
        rel="stylesheet"
        href="css/style.css"
    >


    <link
        rel="preconnect"
        href="https://fonts.googleapis.com"
    >


    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
    >


    <link
        rel="preconnect"
        href="https://fonts.gstatic.com"
        crossorigin
    >


    <link
        href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap"
        rel="stylesheet"
    >


    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            font-family:
                "Poppins",
                Arial,
                sans-serif;

            background:
                #FFF8F2;

            color:
                #4A2C1D;

        }


        .cove-details-page {

            min-height:
                75vh;

            padding:
                55px 20px 70px;

        }


        .cove-details-container {

            width:
                100%;

            max-width:
                1050px;

            margin:
                0 auto;

        }


        .cove-details-heading {

            text-align:
                center;

            margin-bottom:
                30px;

        }


        .cove-details-heading h1 {

            margin:
                0 0 8px;

            color:
                #4A2C1D;

            font-size:
                32px;

            font-weight:
                700;

        }


        .cove-details-heading p {

            margin:
                0;

            color:
                #777;

            font-size:
                14px;

        }


        /* =====================================================
           ORDER SUMMARY
        ====================================================== */

        .cove-order-summary {

            background:
                #ffffff;

            border:
                1px solid #eadfd6;

            border-radius:
                16px;

            padding:
                24px;

            margin-bottom:
                22px;

            box-shadow:
                0 8px 25px rgba(
                    74,
                    44,
                    29,
                    0.07
                );

        }


        .cove-summary-header {

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            gap:
                15px;

            padding-bottom:
                18px;

            border-bottom:
                1px solid #eadfd6;

        }


        .cove-summary-order-number {

            font-size:
                20px;

            font-weight:
                700;

        }


        .cove-summary-date {

            color:
                #777;

            font-size:
                13px;

        }


        .cove-summary-grid {

            display:
                grid;

            grid-template-columns:
                repeat(4, minmax(0, 1fr));

            gap:
                14px;

            margin-top:
                20px;

        }


        .cove-summary-box {

            background:
                #FFF8F2;

            border:
                1px solid #eadfd6;

            border-radius:
                10px;

            padding:
                14px;

        }


        .cove-summary-label {

            display:
                block;

            color:
                #888;

            font-size:
                11px;

            margin-bottom:
                5px;

        }


        .cove-summary-value {

            display:
                block;

            color:
                #4A2C1D;

            font-size:
                14px;

            font-weight:
                600;

            word-break:
                break-word;

        }


        /* =====================================================
           ITEMS
        ====================================================== */

        .cove-items-card {

            background:
                #ffffff;

            border:
                1px solid #eadfd6;

            border-radius:
                16px;

            overflow:
                hidden;

            box-shadow:
                0 8px 25px rgba(
                    74,
                    44,
                    29,
                    0.07
                );

        }


        .cove-items-header {

            background:
                #4A2C1D;

            color:
                #ffffff;

            padding:
                18px 22px;

            font-size:
                18px;

            font-weight:
                600;

        }


        .cove-item-heading {

            display:
                grid;

            grid-template-columns:
                1fr 100px 80px 120px;

            gap:
                15px;

            padding:
                12px 22px;

            background:
                #FFF8F2;

            border-bottom:
                1px solid #eadfd6;

            color:
                #888;

            font-size:
                11px;

            font-weight:
                600;

            text-transform:
                uppercase;

        }


        .cove-item-row {

            display:
                grid;

            grid-template-columns:
                1fr 100px 80px 120px;

            align-items:
                center;

            gap:
                15px;

            padding:
                18px 22px;

            border-bottom:
                1px solid #eadfd6;

        }


        .cove-item-row:last-child {

            border-bottom:
                none;

        }


        .cove-item-name {

            color:
                #4A2C1D;

            font-size:
                14px;

            font-weight:
                600;

        }


        .cove-item-price {

            color:
                #777;

            font-size:
                13px;

        }


        .cove-item-quantity {

            color:
                #4A2C1D;

            font-size:
                13px;

            text-align:
                center;

        }


        .cove-item-total {

            color:
                #4A2C1D;

            font-size:
                15px;

            font-weight:
                700;

            text-align:
                right;

        }


        .cove-no-items {

            padding:
                35px 20px;

            text-align:
                center;

            color:
                #777;

        }


        /* =====================================================
           PRICE
        ====================================================== */

        .cove-price-wrapper {

            padding:
                22px;

        }


        .cove-price-summary {

            max-width:
                500px;

            margin:
                0 0 0 auto;

            padding:
                22px;

            background:
                #FFF8F2;

            border:
                1px solid #eadfd6;

            border-radius:
                12px;

        }


        .cove-price-row {

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            padding:
                7px 0;

            color:
                #777;

            font-size:
                13px;

        }


        .cove-price-row strong {

            color:
                #4A2C1D;

        }


        .cove-price-total {

            display:
                flex;

            align-items:
                center;

            justify-content:
                space-between;

            border-top:
                1px solid #eadfd6;

            margin-top:
                10px;

            padding-top:
                15px;

        }


        .cove-price-total span {

            color:
                #4A2C1D;

            font-size:
                15px;

            font-weight:
                600;

        }


        .cove-price-total strong {

            color:
                #4A2C1D;

            font-size:
                22px;

            font-weight:
                700;

        }


        /* =====================================================
           CUSTOMER
        ====================================================== */

        .cove-customer-card {

            background:
                #ffffff;

            border:
                1px solid #eadfd6;

            border-radius:
                16px;

            padding:
                24px;

            margin-top:
                22px;

            box-shadow:
                0 8px 25px rgba(
                    74,
                    44,
                    29,
                    0.07
                );

        }


        .cove-section-title {

            margin:
                0 0 18px;

            color:
                #4A2C1D;

            font-size:
                18px;

            font-weight:
                600;

        }


        .cove-customer-grid {

            display:
                grid;

            grid-template-columns:
                repeat(3, minmax(0, 1fr));

            gap:
                15px;

        }


        .cove-customer-box {

            background:
                #FFF8F2;

            border:
                1px solid #eadfd6;

            border-radius:
                10px;

            padding:
                14px;

        }


        .cove-customer-label {

            display:
                block;

            color:
                #888;

            font-size:
                11px;

            margin-bottom:
                5px;

        }


        .cove-customer-value {

            display:
                block;

            color:
                #4A2C1D;

            font-size:
                13px;

            font-weight:
                600;

            word-break:
                break-word;

        }


        /* =====================================================
           STATUS
        ====================================================== */

        .cove-status {

            display:
                inline-block;

            padding:
                7px 14px;

            border-radius:
                20px;

            font-size:
                11px;

            font-weight:
                600;

        }


        .status-pending {

            background:
                #FFF3CD;

            color:
                #856404;

        }


        .status-processing {

            background:
                #E8DDF5;

            color:
                #5B3A82;

        }


        .status-completed {

            background:
                #DFF3E4;

            color:
                #276738;

        }


        .status-cancelled {

            background:
                #F8D7DA;

            color:
                #842029;

        }


        /* =====================================================
           ACTIONS
        ====================================================== */

        .cove-details-actions {

            display:
                flex;

            justify-content:
                center;

            gap:
                12px;

            margin-top:
                30px;

            flex-wrap:
                wrap;

        }


        .cove-action-btn {

            display:
                inline-block;

            padding:
                12px 24px;

            border-radius:
                8px;

            text-decoration:
                none;

            font-size:
                13px;

            font-weight:
                600;

            transition:
                0.25s ease;

            cursor:
                pointer;

        }


        .cove-back-btn {

            background:
                #4A2C1D;

            color:
                #ffffff;

        }


        .cove-back-btn:hover {

            background:
                #8B4513;

            color:
                #ffffff;

        }


        .cove-menu-btn {

            background:
                #ffffff;

            color:
                #4A2C1D;

            border:
                1px solid #D8A15B;

        }


        .cove-menu-btn:hover {

            background:
                #F5E8DA;

            color:
                #4A2C1D;

        }


        /* =====================================================
           CANCEL BUTTON
        ====================================================== */

        .cove-cancel-form {

            margin:
                0;

        }


        .cove-cancel-btn {

            border:
                1px solid #c94a4a;

            background:
                #ffffff;

            color:
                #b52f2f;

        }


        .cove-cancel-btn:hover {

            background:
                #b52f2f;

            color:
                #ffffff;

        }


        /* =====================================================
           RESPONSIVE
        ====================================================== */

        @media (max-width: 800px) {

            .cove-summary-grid {

                grid-template-columns:
                    repeat(2, 1fr);

            }


            .cove-customer-grid {

                grid-template-columns:
                    1fr;

            }

        }


        @media (max-width: 650px) {

            .cove-details-page {

                padding:
                    40px 15px 55px;

            }


            .cove-details-heading h1 {

                font-size:
                    27px;

            }


            .cove-summary-header {

                flex-direction:
                    column;

                align-items:
                    flex-start;

            }


            .cove-item-heading {

                display:
                    none;

            }


            .cove-item-row {

                grid-template-columns:
                    1fr 1fr;

                gap:
                    10px;

            }


            .cove-item-price {

                text-align:
                    left;

            }


            .cove-item-quantity {

                text-align:
                    right;

            }


            .cove-item-total {

                grid-column:
                    1 / -1;

                text-align:
                    left;

            }


            .cove-price-summary {

                max-width:
                    100%;

            }


            .cove-details-actions {

                flex-direction:
                    column;

            }


            .cove-action-btn {

                width:
                    100%;

                text-align:
                    center;

            }


            .cove-cancel-form {

                width:
                    100%;

            }


            .cove-cancel-btn {

                width:
                    100%;

            }

        }


        @media (max-width: 450px) {

            .cove-summary-grid {

                grid-template-columns:
                    1fr;

            }

        }

    </style>

</head>


<body>


<?php

if (
    file_exists(
        "include/header.php"
    )
) {

    include "include/header.php";

}

?>


<main class="cove-details-page">

    <div class="cove-details-container">


        <!-- =================================================
             HEADING
        ================================================== -->

        <div
            class="cove-details-heading"
        >

            <h1>

                Order Details

            </h1>


            <p>

                View complete information about
                your Caffeine &amp; Cove order.

            </p>

        </div>


        <!-- =================================================
             ORDER SUMMARY
        ================================================== -->

        <div
            class="cove-order-summary"
        >


            <div
                class="cove-summary-header"
            >

                <div
                    class="cove-summary-order-number"
                >

                    Order #

                    <?php

                    echo (int)
                        $order["id"];

                    ?>

                </div>


                <div
                    class="cove-summary-date"
                >

                    <?php

                    echo coveOrderEscape(
                        $orderDate
                    );

                    ?>

                </div>

            </div>


            <div
                class="cove-summary-grid"
            >


                <div
                    class="cove-summary-box"
                >

                    <span
                        class="cove-summary-label"
                    >

                        Order Type

                    </span>


                    <span
                        class="cove-summary-value"
                    >

                        <?php

                        echo coveOrderEscape(
                            $orderType
                        );

                        ?>

                    </span>

                </div>


                <div
                    class="cove-summary-box"
                >

                    <span
                        class="cove-summary-label"
                    >

                        Payment

                    </span>


                    <span
                        class="cove-summary-value"
                    >

                        <?php

                        echo coveOrderEscape(
                            $paymentMethod
                        );

                        ?>

                    </span>

                </div>


                <div
                    class="cove-summary-box"
                >

                    <span
                        class="cove-summary-label"
                    >

                        Total

                    </span>


                    <span
                        class="cove-summary-value"
                    >

                        ₹<?php

                        echo number_format(
                            $total,
                            2
                        );

                        ?>

                    </span>

                </div>


                <div
                    class="cove-summary-box"
                >

                    <span
                        class="cove-summary-label"
                    >

                        Status

                    </span>


                    <span
                        class="
                            cove-summary-value
                            cove-status
                            <?php

                            echo coveOrderEscape(
                                $statusClass
                            );

                            ?>
                        "
                    >

                        <?php

                        echo coveOrderEscape(
                            $displayStatus
                        );

                        ?>

                    </span>

                </div>


            </div>

        </div>


        <!-- =================================================
             ORDER ITEMS
        ================================================== -->

        <div
            class="cove-items-card"
        >


            <div
                class="cove-items-header"
            >

                Order Items

            </div>


            <?php if (
                $itemResult &&
                mysqli_num_rows(
                    $itemResult
                ) > 0
            ): ?>


                <div
                    class="cove-item-heading"
                >

                    <div>
                        Product
                    </div>

                    <div>
                        Price
                    </div>

                    <div>
                        Qty
                    </div>

                    <div>
                        Total
                    </div>

                </div>


                <?php while (
                    $item =
                    mysqli_fetch_assoc(
                        $itemResult
                    )
                ): ?>


                    <div
                        class="cove-item-row"
                    >


                        <div
                            class="cove-item-name"
                        >

                            <?php

                            echo coveOrderEscape(
                                $item[
                                    "product_name"
                                ] ?? "Product"
                            );

                            ?>

                        </div>


                        <div
                            class="cove-item-price"
                        >

                            ₹<?php

                            echo number_format(
                                (float)(
                                    $item[
                                        "price"
                                    ] ?? 0
                                ),
                                2
                            );

                            ?>

                        </div>


                        <div
                            class="cove-item-quantity"
                        >

                            <?php

                            echo (int)(
                                $item[
                                    "quantity"
                                ] ?? 1
                            );

                            ?>

                        </div>


                        <div
                            class="cove-item-total"
                        >

                            ₹<?php

                            echo number_format(
                                (float)(
                                    $item[
                                        "item_total"
                                    ] ?? 0
                                ),
                                2
                            );

                            ?>

                        </div>


                    </div>


                <?php endwhile; ?>


            <?php else: ?>


                <div
                    class="cove-no-items"
                >

                    No order items were found.

                </div>


            <?php endif; ?>


            <!-- =================================================
                 PRICE SUMMARY
            ================================================== -->

            <div
                class="cove-price-wrapper"
            >

                <div
                    class="cove-price-summary"
                >


                    <div
                        class="cove-price-row"
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
                        class="cove-price-row"
                    >

                        <span>
                            Tax
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
                        class="cove-price-total"
                    >

                        <span>
                            Grand Total
                        </span>


                        <strong>

                            ₹<?php

                            echo number_format(
                                $total,
                                2
                            );

                            ?>

                        </strong>

                    </div>


                </div>

            </div>


        </div>


        <!-- =================================================
             CUSTOMER INFORMATION
        ================================================== -->

        <div
            class="cove-customer-card"
        >

            <h2
                class="cove-section-title"
            >

                Customer Information

            </h2>


            <div
                class="cove-customer-grid"
            >


                <div
                    class="cove-customer-box"
                >

                    <span
                        class="cove-customer-label"
                    >

                        Name

                    </span>


                    <span
                        class="cove-customer-value"
                    >

                        <?php

                        echo coveOrderEscape(
                            $order[
                                "customer_name"
                            ] ?? "N/A"
                        );

                        ?>

                    </span>

                </div>


                <div
                    class="cove-customer-box"
                >

                    <span
                        class="cove-customer-label"
                    >

                        Email

                    </span>


                    <span
                        class="cove-customer-value"
                    >

                        <?php

                        echo coveOrderEscape(
                            $order[
                                "email"
                            ] ?? "N/A"
                        );

                        ?>

                    </span>

                </div>


                <div
                    class="cove-customer-box"
                >

                    <span
                        class="cove-customer-label"
                    >

                        Phone

                    </span>


                    <span
                        class="cove-customer-value"
                    >

                        <?php

                        echo coveOrderEscape(
                            $order[
                                "phone"
                            ] ?? "N/A"
                        );

                        ?>

                    </span>

                </div>


            </div>

        </div>


        <!-- =================================================
             ACTIONS
        ================================================== -->

        <div
            class="cove-details-actions"
        >


            <a
                href="my_orders.php"
                class="
                    cove-action-btn
                    cove-back-btn
                "
            >

                ← Back to My Orders

            </a>


            <?php if (
                $canCancel
            ): ?>

                <form
                    method="POST"
                    action="cancel_order.php"
                    class="cove-cancel-form"
                    onsubmit="
                        return confirm(
                            'Are you sure you want to cancel Order #<?php
                            echo (int)$order["id"];
                            ?>?'
                        );
                    "
                >


                    <input
                        type="hidden"
                        name="order_id"
                        value="<?php
                            echo (int)$order["id"];
                        ?>"
                    >


                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php

                        echo coveOrderEscape(
                            $_SESSION[
                                "cancel_order_csrf"
                            ]
                        );

                        ?>"
                    >


                    <button
                        type="submit"
                        class="
                            cove-action-btn
                            cove-cancel-btn
                        "
                    >

                        Cancel Order

                    </button>


                </form>

            <?php endif; ?>


            <a
                href="menu.php"
                class="
                    cove-action-btn
                    cove-menu-btn
                "
            >

                Back to Menu

            </a>


        </div>


    </div>

</main>


<?php

if (
    file_exists(
        "include/footer.php"
    )
) {

    include "include/footer.php";

}

?>


</body>

</html>


<?php

if ($itemStmt) {

    mysqli_stmt_close(
        $itemStmt
    );

}

?>