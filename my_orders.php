<?php

/* =========================================================
   CAFFEINE & COVE
   CUSTOMER - MY ORDERS
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

    $_SESSION["login_required_message"] =
        "Please login to view your orders.";

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


if (
    $userId <= 0
) {

    session_unset();

    session_destroy();

    header("Location: login.php");

    exit;
}


/* =========================================================
   CANCEL ORDER CSRF TOKEN
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
   GET ONLY CURRENT USER'S ORDERS
========================================================= */

$sql = "
    SELECT
        id,
        customer_name,
        order_type,
        subtotal,
        tax,
        total,
        payment_method,
        status,
        created_at
    FROM orders
    WHERE user_id = ?
    ORDER BY created_at DESC, id DESC
";


$stmt =
    mysqli_prepare(
        $link,
        $sql
    );


$orders = [];


if (
    !$stmt
) {

    error_log(
        "My Orders prepare failed: " .
        mysqli_error($link)
    );

} else {

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $userId
    );


    if (
        !mysqli_stmt_execute(
            $stmt
        )
    ) {

        error_log(
            "My Orders execute failed: " .
            mysqli_stmt_error($stmt)
        );

    } else {

        $result =
            mysqli_stmt_get_result(
                $stmt
            );


        if (
            $result
        ) {

            while (
                $row =
                mysqli_fetch_assoc(
                    $result
                )
            ) {

                $orders[] =
                    $row;

            }

        }

    }


    mysqli_stmt_close(
        $stmt
    );

}


/* =========================================================
   ESCAPE
========================================================= */

function myOrdersEscape($value)
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

function myOrdersStatusClass($status)
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

            return "order-status-processing";


        case "completed":

            return "order-status-completed";


        case "cancelled":

        case "canceled":

            return "order-status-cancelled";


        case "pending":

        default:

            return "order-status-pending";

    }

}


/* =========================================================
   STATUS LABEL
========================================================= */

function myOrdersStatusLabel($status)
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
   ORDER TYPE
========================================================= */

function myOrdersType($type)
{

    $type =
        trim(
            (string)$type
        );


    if (
        $type === "Dine-In"
    ) {

        return "Dine-In";

    }


    if (
        $type === "Takeaway"
    ) {

        return "Takeaway";

    }


    return "N/A";

}


/* =========================================================
   PAYMENT
========================================================= */

function myOrdersPayment($payment)
{

    $payment =
        trim(
            (string)$payment
        );


    if (
        $payment === "Pay at Café"
    ) {

        return "Pay at Café";

    }


    return "N/A";

}


/* =========================================================
   EXACT DATE
========================================================= */

function myOrdersDate($date)
{

    if (
        empty($date)
    ) {

        return "N/A";

    }


    $timestamp =
        strtotime(
            $date
        );


    if (
        $timestamp === false
    ) {

        return "N/A";

    }


    return date(
        "d M Y, h:i A",
        $timestamp
    );

}


/* =========================================================
   RELATIVE TIME
========================================================= */

function myOrdersRelativeTime($date)
{

    if (
        empty($date)
    ) {

        return "N/A";

    }


    $timestamp =
        strtotime(
            $date
        );


    if (
        $timestamp === false
    ) {

        return "N/A";

    }


    $now =
        time();


    $difference =
        $now - $timestamp;


    /*
     * Future timestamps
     */

    if (
        $difference < 0
    ) {

        return "Just now";

    }


    if (
        $difference < 60
    ) {

        return "Just now";

    }


    $minutes =
        floor(
            $difference / 60
        );


    if (
        $minutes < 60
    ) {

        return $minutes .
            " min ago";

    }


    $hours =
        floor(
            $minutes / 60
        );


    if (
        $hours < 24
    ) {

        return $hours .
            (
                $hours === 1
                    ? " hour ago"
                    : " hours ago"
            );

    }


    $days =
        floor(
            $hours / 24
        );


    if (
        $days < 7
    ) {

        return $days .
            (
                $days === 1
                    ? " day ago"
                    : " days ago"
            );

    }


    return date(
        "d M Y",
        $timestamp
    );

}


/* =========================================================
   DISPLAY CANCEL MESSAGE
========================================================= */

$cancelSuccess =
    $_SESSION["order_cancel_success"]
    ?? "";


$cancelError =
    $_SESSION["order_cancel_error"]
    ?? "";


unset(
    $_SESSION["order_cancel_success"]
);


unset(
    $_SESSION["order_cancel_error"]
);

?>


<?php include("include/header.php"); ?>


<style>

/* =========================================================
   MAIN PAGE
========================================================= */

.my-orders-page {

    min-height: 70vh;

    padding: 55px 20px 80px;

    background: #FFF8F2;

}


.my-orders-container {

    width: 100%;

    max-width: 1100px;

    margin: 0 auto;

}


/* =========================================================
   HEADING
========================================================= */

.my-orders-heading {

    text-align: center;

    margin-bottom: 35px;

}


.my-orders-heading span {

    display: block;

    margin-bottom: 7px;

    color: #8B4513;

    font-size: 11px;

    font-weight: 700;

    letter-spacing: 2px;

}


.my-orders-heading h1 {

    margin: 0 0 8px;

    color: #4A2C1D;

    font-size: 32px;

    font-weight: 700;

}


.my-orders-heading p {

    margin: 0;

    color: #777;

    font-size: 14px;

}


/* =========================================================
   MESSAGES
========================================================= */

.order-message {

    margin-bottom: 20px;

    padding: 14px 18px;

    border-radius: 10px;

    font-size: 13px;

    font-weight: 600;

}


.order-message-success {

    background: #DFF3E4;

    color: #276738;

    border: 1px solid #b9dfc2;

}


.order-message-error {

    background: #F8D7DA;

    color: #842029;

    border: 1px solid #f1bfc3;

}


/* =========================================================
   EMPTY
========================================================= */

.orders-empty {

    background: #ffffff;

    border: 1px solid #eadfd6;

    border-radius: 18px;

    padding: 55px 25px;

    text-align: center;

    box-shadow:
        0 10px 30px rgba(
            74,
            44,
            29,
            .07
        );

}


.orders-empty-icon {

    width: 70px;

    height: 70px;

    margin: 0 auto 18px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    background: #F5E8DA;

    color: #7B4728;

    font-size: 30px;

}


.orders-empty h2 {

    margin: 0 0 8px;

    color: #4A2C1D;

    font-size: 22px;

}


.orders-empty p {

    margin: 0 auto 22px;

    max-width: 500px;

    color: #777;

    font-size: 13px;

    line-height: 1.7;

}


.orders-menu-btn {

    display: inline-block;

    padding: 12px 22px;

    border-radius: 8px;

    background: #4A2C1D;

    color: #ffffff;

    text-decoration: none;

    font-size: 13px;

    font-weight: 600;

}


.orders-menu-btn:hover {

    background: #8B4513;

    color: #ffffff;

}


/* =========================================================
   ORDER CARD
========================================================= */

.order-card {

    background: #ffffff;

    border: 1px solid #eadfd6;

    border-radius: 16px;

    margin-bottom: 18px;

    overflow: hidden;

    box-shadow:
        0 8px 25px rgba(
            74,
            44,
            29,
            .06
        );

    transition:
        transform .2s ease,
        box-shadow .2s ease;

}


.order-card:hover {

    transform: translateY(-2px);

    box-shadow:
        0 12px 30px rgba(
            74,
            44,
            29,
            .10
        );

}


/* =========================================================
   TOP
========================================================= */

.order-card-top {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 20px;

    padding: 20px 22px;

    border-bottom: 1px solid #eadfd6;

}


.order-number {

    color: #4A2C1D;

    font-size: 17px;

    font-weight: 700;

}


.order-date {

    margin-top: 4px;

    color: #888;

    font-size: 12px;

}


.order-relative-time {

    margin-top: 3px;

    color: #A06A42;

    font-size: 11px;

    font-weight: 600;

}


/* =========================================================
   STATUS
========================================================= */

.order-status {

    display: inline-block;

    padding: 7px 13px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: 700;

    white-space: nowrap;

}


.order-status-pending {

    background: #FFF3CD;

    color: #856404;

}


.order-status-processing {

    background: #E8DDF5;

    color: #5B3A82;

}


.order-status-completed {

    background: #DFF3E4;

    color: #276738;

}


.order-status-cancelled {

    background: #F8D7DA;

    color: #842029;

}


/* =========================================================
   BODY
========================================================= */

.order-card-body {

    padding: 20px 22px;

}


.order-info-grid {

    display: grid;

    grid-template-columns:
        repeat(4, 1fr);

    gap: 14px;

}


.order-info-box {

    padding: 14px;

    background: #FFF8F2;

    border: 1px solid #eadfd6;

    border-radius: 10px;

}


.order-info-label {

    display: block;

    margin-bottom: 5px;

    color: #888;

    font-size: 10px;

    text-transform: uppercase;

    letter-spacing: .5px;

}


.order-info-value {

    display: block;

    color: #4A2C1D;

    font-size: 13px;

    font-weight: 600;

    word-break: break-word;

}


/* =========================================================
   FOOTER
========================================================= */

.order-card-footer {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    padding: 16px 22px;

    background: #FFF8F2;

    border-top: 1px solid #eadfd6;

}


.order-total-label {

    color: #777;

    font-size: 12px;

}


.order-total {

    margin-left: 5px;

    color: #4A2C1D;

    font-size: 18px;

    font-weight: 700;

}


/* =========================================================
   ACTIONS
========================================================= */

.order-actions {

    display: flex;

    align-items: center;

    justify-content: flex-end;

    gap: 8px;

    flex-wrap: wrap;

}


/* =========================================================
   VIEW
========================================================= */

.view-order-btn {

    display: inline-block;

    padding: 10px 18px;

    border-radius: 8px;

    background: #4A2C1D;

    color: #ffffff;

    text-decoration: none;

    font-size: 12px;

    font-weight: 600;

    border: none;

}


.view-order-btn:hover {

    background: #8B4513;

    color: #ffffff;

}


/* =========================================================
   CANCEL
========================================================= */

.cancel-order-btn {

    border: 1px solid #c94a4a;

    background: #ffffff;

    color: #b52f2f;

    padding: 10px 18px;

    border-radius: 8px;

    font-size: 12px;

    font-weight: 600;

    cursor: pointer;

    transition: .25s ease;

}


.cancel-order-btn:hover {

    background: #b52f2f;

    color: #ffffff;

}


.cancel-order-btn:disabled {

    opacity: .6;

    cursor: not-allowed;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 800px) {

    .order-info-grid {

        grid-template-columns:
            repeat(2, 1fr);

    }

}


@media (max-width: 600px) {

    .my-orders-page {

        padding:
            40px 15px 60px;

    }


    .my-orders-heading h1 {

        font-size: 27px;

    }


    .order-card-top {

        flex-direction: column;

        align-items: flex-start;

    }


    .order-info-grid {

        grid-template-columns: 1fr;

    }


    .order-card-footer {

        flex-direction: column;

        align-items: stretch;

    }


    .order-actions {

        flex-direction: column;

        align-items: stretch;

    }


    .order-actions form,

    .order-actions a {

        width: 100%;

    }


    .order-actions a,

    .order-actions button {

        width: 100%;

        text-align: center;

        box-sizing: border-box;

    }

}

</style>


<main class="my-orders-page">

    <div class="my-orders-container">


        <!-- =================================================
             SUCCESS
        ================================================== -->

        <?php if (
            $cancelSuccess !== ""
        ): ?>

            <div
                class="
                    order-message
                    order-message-success
                "
            >

                ✓

                <?php

                echo myOrdersEscape(
                    $cancelSuccess
                );

                ?>

            </div>

        <?php endif; ?>


        <!-- =================================================
             ERROR
        ================================================== -->

        <?php if (
            $cancelError !== ""
        ): ?>

            <div
                class="
                    order-message
                    order-message-error
                "
            >

                ⚠

                <?php

                echo myOrdersEscape(
                    $cancelError
                );

                ?>

            </div>

        <?php endif; ?>


        <!-- =================================================
             HEADING
        ================================================== -->

        <div
            class="my-orders-heading"
        >

            <span>

                CAFFEINE &amp; COVE

            </span>


            <h1>

                My Orders

            </h1>


            <p>

                View your order history and
                track your café orders.

            </p>

        </div>


        <?php if (
            empty($orders)
        ): ?>


            <!-- =================================================
                 EMPTY
            ================================================== -->

            <div
                class="orders-empty"
            >

                <div
                    class="orders-empty-icon"
                >

                    ☕

                </div>


                <h2>

                    No Orders Yet

                </h2>


                <p>

                    You haven't placed any orders
                    yet. Explore our menu and enjoy
                    your favourite coffee and treats.

                </p>


                <a
                    href="menu.php"
                    class="orders-menu-btn"
                >

                    Explore Menu

                </a>

            </div>


        <?php else: ?>


            <!-- =================================================
                 ORDERS
            ================================================== -->

            <?php foreach (
                $orders
                as $order
            ): ?>


                <?php

                $rawStatus =
                    strtolower(
                        trim(
                            (string)(
                                $order["status"]
                                ?? "pending"
                            )
                        )
                    );


                $status =
                    myOrdersStatusLabel(
                        $rawStatus
                    );


                $statusClass =
                    myOrdersStatusClass(
                        $rawStatus
                    );


                $orderType =
                    myOrdersType(
                        $order["order_type"]
                        ?? ""
                    );


                $payment =
                    myOrdersPayment(
                        $order["payment_method"]
                        ?? ""
                    );


                $createdAt =
                    myOrdersDate(
                        $order["created_at"]
                        ?? ""
                    );


                $relativeTime =
                    myOrdersRelativeTime(
                        $order["created_at"]
                        ?? ""
                    );


                $orderId =
                    (int)(
                        $order["id"] ?? 0
                    );


                /*
                 * Cancel is shown only when
                 * server-side cancellation
                 * will accept it.
                 */

                $canCancel =
                    in_array(
                        $rawStatus,
                        [
                            "pending",
                            "confirmed"
                        ],
                        true
                    ) &&
                    $orderId > 0;

                ?>


                <?php if (
                    $orderId <= 0
                ): ?>

                    <?php continue; ?>

                <?php endif; ?>


                <article
                    class="order-card"
                >


                    <!-- =================================================
                         TOP
                    ================================================== -->

                    <div
                        class="order-card-top"
                    >

                        <div>

                            <div
                                class="order-number"
                            >

                                Order #

                                <?php

                                echo $orderId;

                                ?>

                            </div>


                            <div
                                class="order-date"
                            >

                                <?php

                                echo myOrdersEscape(
                                    $createdAt
                                );

                                ?>

                            </div>


                            <div
                                class="
                                    order-relative-time
                                "
                            >

                                <?php

                                echo myOrdersEscape(
                                    $relativeTime
                                );

                                ?>

                            </div>

                        </div>


                        <span
                            class="
                                order-status
                                <?php

                                echo myOrdersEscape(
                                    $statusClass
                                );

                                ?>
                            "
                        >

                            <?php

                            echo myOrdersEscape(
                                $status
                            );

                            ?>

                        </span>

                    </div>


                    <!-- =================================================
                         BODY
                    ================================================== -->

                    <div
                        class="order-card-body"
                    >

                        <div
                            class="order-info-grid"
                        >


                            <!-- ORDER TYPE -->

                            <div
                                class="order-info-box"
                            >

                                <span
                                    class="
                                        order-info-label
                                    "
                                >

                                    Order Type

                                </span>


                                <span
                                    class="
                                        order-info-value
                                    "
                                >

                                    <?php

                                    echo myOrdersEscape(
                                        $orderType
                                    );

                                    ?>

                                </span>

                            </div>


                            <!-- PAYMENT -->

                            <div
                                class="order-info-box"
                            >

                                <span
                                    class="
                                        order-info-label
                                    "
                                >

                                    Payment

                                </span>


                                <span
                                    class="
                                        order-info-value
                                    "
                                >

                                    <?php

                                    echo myOrdersEscape(
                                        $payment
                                    );

                                    ?>

                                </span>

                            </div>


                            <!-- SUBTOTAL -->

                            <div
                                class="order-info-box"
                            >

                                <span
                                    class="
                                        order-info-label
                                    "
                                >

                                    Subtotal

                                </span>


                                <span
                                    class="
                                        order-info-value
                                    "
                                >

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

                                </span>

                            </div>


                            <!-- TAX -->

                            <div
                                class="order-info-box"
                            >

                                <span
                                    class="
                                        order-info-label
                                    "
                                >

                                    Tax

                                </span>


                                <span
                                    class="
                                        order-info-value
                                    "
                                >

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

                                </span>

                            </div>


                        </div>

                    </div>


                    <!-- =================================================
                         FOOTER
                    ================================================== -->

                    <div
                        class="order-card-footer"
                    >


                        <div>

                            <span
                                class="
                                    order-total-label
                                "
                            >

                                Total

                            </span>


                            <span
                                class="order-total"
                            >

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

                            </span>

                        </div>


                        <div
                            class="order-actions"
                        >


                            <!-- VIEW DETAILS -->

                            <a
                                href="order_details.php?order_id=<?php
                                    echo $orderId;
                                ?>"
                                class="view-order-btn"
                            >

                                View Order Details →

                            </a>


                            <!-- =================================================
                                 CANCEL
                            ================================================== -->

                            <?php if (
                                $canCancel
                            ): ?>

                                <form
                                    method="POST"
                                    action="cancel_order.php"
                                    style="margin:0;"
                                    onsubmit="
                                        return confirm(
                                            'Are you sure you want to cancel Order #<?php
                                            echo $orderId;
                                            ?>?'
                                        );
                                    "
                                >


                                    <input
                                        type="hidden"
                                        name="order_id"
                                        value="<?php
                                            echo $orderId;
                                        ?>"
                                    >


                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?php

                                        echo myOrdersEscape(
                                            $_SESSION[
                                                "cancel_order_csrf"
                                            ]
                                        );

                                        ?>"
                                    >


                                    <button
                                        type="submit"
                                        class="cancel-order-btn"
                                    >

                                        Cancel Order

                                    </button>


                                </form>

                            <?php endif; ?>


                        </div>

                    </div>


                </article>


            <?php endforeach; ?>


        <?php endif; ?>


    </div>

</main>


<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {


        document
            .querySelectorAll(
                ".cancel-order-btn"
            )
            .forEach(
                function (button) {

                    button
                        .closest("form")
                        .addEventListener(
                            "submit",
                            function () {

                                button.disabled =
                                    true;

                                button.textContent =
                                    "Cancelling...";

                            }
                        );

                }
            );

    }
);

</script>


<?php include("include/footer.php"); ?>