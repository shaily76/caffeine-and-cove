<?php

/* =========================================================
   CAFFEINE & COVE
   SECURE ORDER SUCCESS PAGE
========================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/include/config.php";


/* =========================================================
   LOGIN CHECK
========================================================= */

if (
    !isset($_SESSION["logged_in"]) ||
    $_SESSION["logged_in"] !== true
) {

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


if (
    $userId <= 0
) {

    header("Location: login.php");

    exit;
}


/* =========================================================
   ORDER ID
========================================================= */

$orderIdRaw =
    $_GET["order_id"] ?? "";


/*
 * If order_id isn't present in URL,
 * use the last successfully created order.
 */

if (
    $orderIdRaw === "" &&
    isset(
        $_SESSION["last_order_id"]
    )
) {

    $orderIdRaw =
        (string)(
            $_SESSION["last_order_id"]
        );

}


/* =========================================================
   VALIDATE ORDER ID
========================================================= */

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
   IMPORTANT:
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


if (
    !$stmt
) {

    error_log(
        "Order success prepare failed: " .
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
        "Order success execute failed: " .
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


if (
    $itemStmt
) {

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

    }

}


/* =========================================================
   ESCAPE FUNCTION
========================================================= */

function successEscape(
    $value
) {

    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
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
            $order["order_type"] ?? ""
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
   PAYMENT
========================================================= */

$paymentMethod =
    trim(
        (string)(
            $order["payment_method"] ?? ""
        )
    );


if (
    $paymentMethod !== "Pay at Café"
) {

    $paymentMethod =
        "Pay at Café";

}


/* =========================================================
   STATUS
========================================================= */

$status =
    strtolower(
        trim(
            (string)(
                $order["status"] ?? "pending"
            )
        )
    );


$statusText =
    ucfirst(
        $status
    );


?>

<?php include("include/header.php"); ?>


<style>

    .success-page {

        min-height: 70vh;

        padding:
            60px 20px 80px;

        background:
            #FFF8F2;

    }


    .success-container {

        max-width:
            900px;

        margin:
            0 auto;

    }


    .success-box {

        background:
            #ffffff;

        border:
            1px solid #eadfd6;

        border-radius:
            18px;

        padding:
            45px 35px;

        text-align:
            center;

        box-shadow:
            0 10px 35px rgba(
                74,
                44,
                29,
                0.08
            );

    }


    .success-icon {

        width:
            75px;

        height:
            75px;

        margin:
            0 auto 20px;

        display:
            flex;

        align-items:
            center;

        justify-content:
            center;

        border-radius:
            50%;

        background:
            #DFF3E4;

        color:
            #276738;

        font-size:
            35px;

        font-weight:
            700;

    }


    .success-box h1 {

        margin:
            0 0 10px;

        color:
            #4A2C1D;

        font-size:
            32px;

    }


    .success-box > p {

        margin:
            0 auto 25px;

        max-width:
            600px;

        color:
            #777;

        font-size:
            14px;

        line-height:
            1.7;

    }


    .success-order-number {

        display:
            inline-block;

        margin-bottom:
            25px;

        padding:
            9px 18px;

        border-radius:
            30px;

        background:
            #F5E8DA;

        color:
            #7B4728;

        font-size:
            14px;

        font-weight:
            700;

    }


    .success-info {

        display:
            grid;

        grid-template-columns:
            repeat(3, 1fr);

        gap:
            14px;

        margin-top:
            25px;

        text-align:
            left;

    }


    .success-info-box {

        padding:
            16px;

        background:
            #FFF8F2;

        border:
            1px solid #eadfd6;

        border-radius:
            10px;

    }


    .success-info-label {

        display:
            block;

        margin-bottom:
            5px;

        color:
            #888;

        font-size:
            11px;

    }


    .success-info-value {

        display:
            block;

        color:
            #4A2C1D;

        font-size:
            13px;

        font-weight:
            600;

    }


    .success-items {

        margin-top:
            28px;

        text-align:
            left;

        border:
            1px solid #eadfd6;

        border-radius:
            12px;

        overflow:
            hidden;

    }


    .success-items-title {

        padding:
            15px 18px;

        background:
            #4A2C1D;

        color:
            #ffffff;

        font-size:
            16px;

        font-weight:
            600;

    }


    .success-item {

        display:
            grid;

        grid-template-columns:
            1fr 70px 110px;

        gap:
            15px;

        padding:
            14px 18px;

        border-bottom:
            1px solid #eadfd6;

        font-size:
            13px;

    }


    .success-item:last-child {

        border-bottom:
            none;

    }


    .success-item-name {

        color:
            #4A2C1D;

        font-weight:
            600;

    }


    .success-item-qty {

        text-align:
            center;

        color:
            #777;

    }


    .success-item-total {

        text-align:
            right;

        color:
            #4A2C1D;

        font-weight:
            700;

    }


    .success-total {

        margin-top:
            20px;

        padding:
            20px;

        background:
            #FFF8F2;

        border:
            1px solid #eadfd6;

        border-radius:
            12px;

        text-align:
            right;

    }


    .success-total-row {

        display:
            flex;

        justify-content:
            space-between;

        max-width:
            350px;

        margin:
            6px 0 6px auto;

        color:
            #777;

        font-size:
            13px;

    }


    .success-total-final {

        padding-top:
            12px;

        margin-top:
            10px;

        border-top:
            1px solid #eadfd6;

        color:
            #4A2C1D;

        font-size:
            20px;

        font-weight:
            700;

    }


    .success-actions {

        display:
            flex;

        justify-content:
            center;

        gap:
            12px;

        margin-top:
            30px;

    }


    .success-button {

        display:
            inline-block;

        padding:
            12px 22px;

        border-radius:
            8px;

        text-decoration:
            none;

        font-size:
            13px;

        font-weight:
            600;

    }


    .success-primary {

        background:
            #4A2C1D;

        color:
            #ffffff;

    }


    .success-primary:hover {

        background:
            #8B4513;

        color:
            #ffffff;

    }


    .success-secondary {

        background:
            #ffffff;

        color:
            #4A2C1D;

        border:
            1px solid #D8A15B;

    }


    .success-secondary:hover {

        background:
            #F5E8DA;

        color:
            #4A2C1D;

    }


    @media (
        max-width: 700px
    ) {

        .success-info {

            grid-template-columns:
                1fr;

        }


        .success-box {

            padding:
                35px 20px;

        }


        .success-item {

            grid-template-columns:
                1fr 50px;

        }


        .success-item-total {

            grid-column:
                1 / -1;

            text-align:
                left;

        }


        .success-actions {

            flex-direction:
                column;

        }


        .success-button {

            width:
                100%;

            text-align:
                center;

        }

    }

</style>


<main class="success-page">

    <div class="success-container">


        <div class="success-box">


            <div class="success-icon">

                ✓

            </div>


            <h1>

                Order Placed Successfully!

            </h1>


            <p>

                Thank you for ordering from
                Caffeine &amp; Cove.
                Your order has been received
                and is being processed.

            </p>


            <div
                class="success-order-number"
            >

                Order #

                <?php

                echo (int)$order["id"];

                ?>

            </div>


            <!-- =================================================
                 ORDER INFORMATION
            ================================================== -->

            <div
                class="success-info"
            >


                <div
                    class="success-info-box"
                >

                    <span
                        class="success-info-label"
                    >

                        Order Type

                    </span>


                    <span
                        class="success-info-value"
                    >

                        <?php

                        echo successEscape(
                            $orderType
                        );

                        ?>

                    </span>

                </div>


                <div
                    class="success-info-box"
                >

                    <span
                        class="success-info-label"
                    >

                        Payment

                    </span>


                    <span
                        class="success-info-value"
                    >

                        <?php

                        echo successEscape(
                            $paymentMethod
                        );

                        ?>

                    </span>

                </div>


                <div
                    class="success-info-box"
                >

                    <span
                        class="success-info-label"
                    >

                        Status

                    </span>


                    <span
                        class="success-info-value"
                    >

                        <?php

                        echo successEscape(
                            $statusText
                        );

                        ?>

                    </span>

                </div>


            </div>


            <!-- =================================================
                 ITEMS
            ================================================== -->

            <div
                class="success-items"
            >


                <div
                    class="success-items-title"
                >

                    Order Items

                </div>


                <?php if (
                    $itemResult &&
                    mysqli_num_rows(
                        $itemResult
                    ) > 0
                ): ?>


                    <?php while (
                        $item =
                        mysqli_fetch_assoc(
                            $itemResult
                        )
                    ): ?>


                        <div
                            class="success-item"
                        >


                            <div
                                class="
                                    success-item-name
                                "
                            >

                                <?php

                                echo successEscape(
                                    $item[
                                        "product_name"
                                    ] ?? "Product"
                                );

                                ?>

                            </div>


                            <div
                                class="
                                    success-item-qty
                                "
                            >

                                ×

                                <?php

                                echo (int)(
                                    $item[
                                        "quantity"
                                    ] ?? 1
                                );

                                ?>

                            </div>


                            <div
                                class="
                                    success-item-total
                                "
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
                        style="
                            padding:25px;
                            text-align:center;
                            color:#777;
                        "
                    >

                        No order items found.

                    </div>


                <?php endif; ?>


            </div>


            <!-- =================================================
                 TOTAL
            ================================================== -->

            <div
                class="success-total"
            >


                <div
                    class="success-total-row"
                >

                    <span>

                        Subtotal

                    </span>


                    <strong>

                        ₹<?php

                        echo number_format(
                            (float)(
                                $order[
                                    "subtotal"
                                ] ?? 0
                            ),
                            2
                        );

                        ?>

                    </strong>

                </div>


                <div
                    class="success-total-row"
                >

                    <span>

                        Tax

                    </span>


                    <strong>

                        ₹<?php

                        echo number_format(
                            (float)(
                                $order[
                                    "tax"
                                ] ?? 0
                            ),
                            2
                        );

                        ?>

                    </strong>

                </div>


                <div
                    class="
                        success-total-row
                        success-total-final
                    "
                >

                    <span>

                        Total

                    </span>


                    <strong>

                        ₹<?php

                        echo number_format(
                            (float)(
                                $order[
                                    "total"
                                ] ?? 0
                            ),
                            2
                        );

                        ?>

                    </strong>

                </div>


            </div>


            <!-- =================================================
                 DATE
            ================================================== -->

            <p
                style="
                    margin-top:20px;
                    font-size:12px;
                    color:#888;
                "
            >

                Order placed on

                <?php

                echo successEscape(
                    $orderDate
                );

                ?>

            </p>


            <!-- =================================================
                 ACTIONS
            ================================================== -->

            <div
                class="success-actions"
            >

                <a
                    href="my_orders.php"
                    class="
                        success-button
                        success-primary
                    "
                >

                    View My Orders

                </a>


                <a
                    href="menu.php"
                    class="
                        success-button
                        success-secondary
                    "
                >

                    Back to Menu

                </a>

            </div>


        </div>

    </div>

</main>


<?php include("include/footer.php"); ?>


<?php

if (
    $itemStmt
) {

    mysqli_stmt_close(
        $itemStmt
    );

}

?>