<?php

/* =========================================================
   CAFFEINE & COVE
   ADMIN - ORDERS MANAGEMENT
========================================================= */

require_once "../admin_auth.php";
require_once "../../include/config.php";


/* =========================================================
   CSRF TOKEN
========================================================= */

if (
    empty($_SESSION["admin_order_csrf"])
) {

    $_SESSION["admin_order_csrf"] =
        bin2hex(
            random_bytes(32)
        );

}


/* =========================================================
   VARIABLES
========================================================= */

$success = "";
$error = "";


/* =========================================================
   ALLOWED STATUSES
========================================================= */

$allowedStatuses = [

    "pending",

    "confirmed",

    "preparing",

    "ready",

    "completed",

    "cancelled"

];


/* =========================================================
   STATUS LABEL
========================================================= */

function adminOrderStatusLabel($status)
{

    $status =
        strtolower(
            trim(
                (string)$status
            )
        );


    switch ($status) {

        case "pending":

            return "Pending";


        case "confirmed":

            return "Confirmed";


        case "preparing":

            return "Preparing";


        case "ready":

            return "Ready";


        case "completed":

            return "Completed";


        case "cancelled":

            return "Cancelled";


        default:

            return "Unknown";

    }

}


/* =========================================================
   STATUS BADGE
========================================================= */

function adminOrderStatusClass($status)
{

    switch (
        strtolower(
            trim(
                (string)$status
            )
        )
    ) {

        case "pending":

            return "badge-warning";


        case "confirmed":

            return "badge-gold";


        case "preparing":

            return "badge-coffee";


        case "ready":

            return "badge-info";


        case "completed":

            return "badge-success";


        case "cancelled":

            return "badge-danger";


        default:

            return "badge-secondary";

    }

}


/* =========================================================
   STATUS ICON
========================================================= */

function adminOrderStatusIcon($status)
{

    switch (
        strtolower(
            trim(
                (string)$status
            )
        )
    ) {

        case "pending":

            return "fa-clock";


        case "confirmed":

            return "fa-check";


        case "preparing":

            return "fa-utensils";


        case "ready":

            return "fa-concierge-bell";


        case "completed":

            return "fa-check-double";


        case "cancelled":

            return "fa-times";


        default:

            return "fa-question";

    }

}


/* =========================================================
   RELATIVE TIME
========================================================= */

function timeAgo($datetime)
{

    if (
        empty($datetime)
    ) {

        return "";

    }


    $timestamp =
        strtotime(
            $datetime
        );


    if (
        $timestamp === false
    ) {

        return "";

    }


    $difference =
        time() - $timestamp;


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


    if (
        $difference < 3600
    ) {

        $minutes =
            floor(
                $difference / 60
            );


        return $minutes .
            " minute" .
            (
                $minutes == 1
                    ? ""
                    : "s"
            ) .
            " ago";

    }


    if (
        $difference < 86400
    ) {

        $hours =
            floor(
                $difference / 3600
            );


        return $hours .
            " hour" .
            (
                $hours == 1
                    ? ""
                    : "s"
            ) .
            " ago";

    }


    if (
        $difference < 2592000
    ) {

        $days =
            floor(
                $difference / 86400
            );


        return $days .
            " day" .
            (
                $days == 1
                    ? ""
                    : "s"
            ) .
            " ago";

    }


    if (
        $difference < 31536000
    ) {

        $months =
            floor(
                $difference / 2592000
            );


        return $months .
            " month" .
            (
                $months == 1
                    ? ""
                    : "s"
            ) .
            " ago";

    }


    $years =
        floor(
            $difference / 31536000
        );


    return $years .
        " year" .
        (
            $years == 1
                ? ""
                : "s"
        ) .
        " ago";

}


/* =========================================================
   READ GET MESSAGES
========================================================= */

if (
    isset(
        $_GET["success"]
    )
) {

    switch (
        $_GET["success"]
    ) {

        case "updated":

            $success =
                "Order status updated successfully.";

            break;


        case "deleted":

            $success =
                "Order deleted successfully.";

            break;

    }

}


if (
    isset(
        $_GET["error"]
    )
) {

    switch (
        $_GET["error"]
    ) {

        case "invalid_id":

            $error =
                "Invalid order ID.";

            break;


        case "invalid_status":

            $error =
                "Invalid order status.";

            break;


        case "invalid_token":

            $error =
                "Security validation failed. Please try again.";

            break;


        case "not_found":

            $error =
                "Order not found.";

            break;


        case "update_failed":

            $error =
                "Unable to update order status.";

            break;


        case "delete_failed":

            $error =
                "Unable to delete order.";

            break;


        case "invalid_request":

            $error =
                "Invalid request.";

            break;


        case "database":

            $error =
                "A database error occurred.";

            break;


        default:

            $error =
                "Something went wrong.";

    }

}


/* =========================================================
   UPDATE ORDER STATUS
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset(
        $_POST["action"]
    ) &&
    $_POST["action"] === "update_status"
) {


    /* -----------------------------------------------------
       CSRF
    ----------------------------------------------------- */

    $csrfToken =
        $_POST["csrf_token"] ?? "";


    $sessionToken =
        $_SESSION["admin_order_csrf"] ?? "";


    if (
        !is_string($csrfToken) ||
        !is_string($sessionToken) ||
        !hash_equals(
            $sessionToken,
            $csrfToken
        )
    ) {

        header(
            "Location: orders.php?error=invalid_token"
        );

        exit;

    }


    /* -----------------------------------------------------
       ORDER ID
    ----------------------------------------------------- */

    $orderIdRaw =
        $_POST["order_id"] ?? "";


    if (
        !is_string($orderIdRaw) ||
        !ctype_digit($orderIdRaw) ||
        (int)$orderIdRaw <= 0
    ) {

        header(
            "Location: orders.php?error=invalid_id"
        );

        exit;

    }


    $orderId =
        (int)$orderIdRaw;


    /* -----------------------------------------------------
       NEW STATUS
    ----------------------------------------------------- */

    $newStatus =
        strtolower(
            trim(
                (string)(
                    $_POST["status"] ?? ""
                )
            )
        );


    if (
        !in_array(
            $newStatus,
            $allowedStatuses,
            true
        )
    ) {

        header(
            "Location: orders.php?error=invalid_status"
        );

        exit;

    }


    /* -----------------------------------------------------
       UPDATE
    ----------------------------------------------------- */

    $updateSql = "
        UPDATE orders
        SET status = ?
        WHERE id = ?
        LIMIT 1
    ";


    $updateStmt =
        mysqli_prepare(
            $link,
            $updateSql
        );


    if (
        !$updateStmt
    ) {

        error_log(
            "Admin order status prepare failed: " .
            mysqli_error($link)
        );

        header(
            "Location: orders.php?error=update_failed"
        );

        exit;

    }


    mysqli_stmt_bind_param(
        $updateStmt,
        "si",
        $newStatus,
        $orderId
    );


    if (
        !mysqli_stmt_execute(
            $updateStmt
        )
    ) {

        error_log(
            "Admin order status update failed: " .
            mysqli_stmt_error($updateStmt)
        );

        mysqli_stmt_close(
            $updateStmt
        );

        header(
            "Location: orders.php?error=update_failed"
        );

        exit;

    }


    mysqli_stmt_close(
        $updateStmt
    );


    header(
        "Location: orders.php?success=updated"
    );

    exit;

}


/* =========================================================
   GET ORDERS
========================================================= */

$sql = "
    SELECT
        id,
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
    ORDER BY created_at DESC, id DESC
";


$result =
    mysqli_query(
        $link,
        $sql
    );


if (
    $result === false
) {

    error_log(
        "Caffeine & Cove admin orders list failed: " .
        mysqli_error($link)
    );


    $error =
        "Unable to load orders right now.";

}


/* =========================================================
   HEADER
========================================================= */

include "../includes/header.php";

include "../includes/sidebar.php";

?>


<style>

/* =========================================================
   ORDERS PAGE
========================================================= */

.cc-orders-page {

    padding-bottom: 30px;

}


/* =========================================================
   STATUS SELECT
========================================================= */

.cc-status-form {

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 5px;

}


.cc-status-select {

    min-width: 120px;

    padding: 5px 8px;

    border: 1px solid #D8C8BC;

    border-radius: 6px;

    background: #ffffff;

    color: #4A2C1D;

    font-size: 12px;

    outline: none;

}


.cc-status-select:focus {

    border-color: #D8A15B;

    box-shadow:
        0 0 0 2px
        rgba(
            216,
            161,
            91,
            .15
        );

}


.cc-status-save {

    border: none;

    background: #4A2C1D;

    color: #ffffff;

    padding: 6px 9px;

    border-radius: 6px;

    cursor: pointer;

    font-size: 11px;

}


.cc-status-save:hover {

    background: #7B4728;

}


/* =========================================================
   ORDER ID
========================================================= */

.cc-order-id {

    color: #7B4728;

    font-weight: 700;

}


/* =========================================================
   CUSTOMER
========================================================= */

.cc-customer-name {

    display: block;

    color: #4A2C1D;

    font-weight: 600;

}


.cc-customer-meta {

    display: block;

    margin-top: 2px;

    color: #8A7468;

    font-size: 11px;

    word-break: break-word;

}


/* =========================================================
   ORDER TYPE
========================================================= */

.cc-order-type {

    display: inline-block;

    padding: 5px 9px;

    border-radius: 20px;

    background: #F5E8DA;

    color: #7B4728;

    font-size: 11px;

    font-weight: 600;

}


/* =========================================================
   TOTAL
========================================================= */

.cc-total {

    color: #7B4728;

    font-weight: 700;

}


/* =========================================================
   DATE
========================================================= */

.cc-date {

    display: block;

    color: #6F5548;

    font-size: 11px;

    font-weight: 600;

}


.cc-relative {

    display: block;

    margin-top: 3px;

    color: #B0784F;

    font-size: 10px;

}


/* =========================================================
   ACTION AREA
========================================================= */

.cc-action-buttons {

    display: flex;

    align-items: center;

    justify-content: center;

    gap: 6px;

}


/* =========================================================
   VIEW BUTTON
========================================================= */

.cc-view-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    width: 32px;

    height: 32px;

    border-radius: 6px;

    background: #7B4728;

    color: #ffffff;

    text-decoration: none;

}


.cc-view-btn:hover {

    background: #4A2C1D;

    color: #ffffff;

}


/* =========================================================
   DELETE BUTTON
========================================================= */

.cc-delete-btn {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    width: 32px;

    height: 32px;

    border: none;

    border-radius: 6px;

    background: #C94A4A;

    color: #ffffff;

    cursor: pointer;

    transition: 0.2s ease;

}


.cc-delete-btn:hover {

    background: #A93232;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (
    max-width: 768px
) {

    .cc-status-form {

        flex-direction: column;

    }


    .cc-status-select {

        min-width: 110px;

    }


    .cc-action-buttons {

        flex-direction: column;

    }

}

</style>


<div
    class="
        content-wrapper
        cc-orders-page
    "
>


    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->

    <div class="content-header">

        <div class="container-fluid">

            <div class="row mb-2">

                <div class="col-sm-6">

                    <h1 class="m-0">

                        <i
                            class="
                                fas
                                fa-shopping-cart
                                mr-2
                            "
                            style="
                                color:#7B4728;
                            "
                        ></i>

                        Orders

                    </h1>

                </div>


                <div class="col-sm-6">

                    <ol
                        class="
                            breadcrumb
                            float-sm-right
                        "
                    >

                        <li
                            class="breadcrumb-item"
                        >

                            <a
                                href="../dashboard.php"
                            >

                                Dashboard

                            </a>

                        </li>


                        <li
                            class="
                                breadcrumb-item
                                active
                            "
                        >

                            Orders

                        </li>

                    </ol>

                </div>

            </div>

        </div>

    </div>


    <!-- =====================================================
         CONTENT
    ====================================================== -->

    <section class="content">

        <div class="container-fluid">


            <!-- =================================================
                 SUCCESS
            ================================================== -->

            <?php if (
                $success !== ""
            ): ?>

                <div
                    class="
                        alert
                        alert-success
                    "
                >

                    <i
                        class="
                            fas
                            fa-check-circle
                            mr-2
                        "
                    ></i>

                    <?php

                    echo htmlspecialchars(
                        $success,
                        ENT_QUOTES,
                        "UTF-8"
                    );

                    ?>


                    <button
                        type="button"
                        class="close"
                        data-dismiss="alert"
                    >

                        <span>
                            &times;
                        </span>

                    </button>

                </div>

            <?php endif; ?>


            <!-- =================================================
                 ERROR
            ================================================== -->

            <?php if (
                $error !== ""
            ): ?>

                <div
                    class="
                        alert
                        alert-danger
                    "
                >

                    <i
                        class="
                            fas
                            fa-exclamation-circle
                            mr-2
                        "
                    ></i>

                    <?php

                    echo htmlspecialchars(
                        $error,
                        ENT_QUOTES,
                        "UTF-8"
                    );

                    ?>


                    <button
                        type="button"
                        class="close"
                        data-dismiss="alert"
                    >

                        <span>
                            &times;
                        </span>

                    </button>

                </div>

            <?php endif; ?>


            <!-- =================================================
                 ORDERS CARD
            ================================================== -->

            <div class="card">


                <div class="card-header">

                    <h3 class="card-title">

                        <i
                            class="
                                fas
                                fa-list
                                mr-2
                            "
                        ></i>

                        All Orders

                    </h3>


                    <div class="card-tools">

                        <span
                            class="
                                badge
                                badge-gold
                            "
                        >

                            <?php

                            if (
                                $result !== false
                            ) {

                                echo mysqli_num_rows(
                                    $result
                                );

                            } else {

                                echo "0";

                            }

                            ?>

                            Orders

                        </span>

                    </div>

                </div>


                <div
                    class="
                        card-body
                        p-0
                    "
                >

                    <div
                        class="
                            table-responsive
                        "
                    >

                        <table
                            class="
                                table
                                table-hover
                                mb-0
                            "
                        >

                            <thead>

                                <tr>

                                    <th>
                                        Order ID
                                    </th>

                                    <th>
                                        Customer
                                    </th>

                                    <th>
                                        Order Type
                                    </th>

                                    <th>
                                        Total
                                    </th>

                                    <th>
                                        Payment
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                    <th>
                                        Date
                                    </th>

                                    <th
                                        class="text-center"
                                    >
                                        Action
                                    </th>

                                </tr>

                            </thead>


                            <tbody>


                            <?php if (
                                $result !== false &&
                                mysqli_num_rows(
                                    $result
                                ) > 0
                            ): ?>


                                <?php while (
                                    $order =
                                    mysqli_fetch_assoc(
                                        $result
                                    )
                                ): ?>


                                    <?php

                                    $orderId =
                                        (int)(
                                            $order["id"]
                                            ?? 0
                                        );


                                    if (
                                        $orderId <= 0
                                    ) {

                                        continue;

                                    }


                                    $customerName =
                                        (string)(
                                            $order[
                                                "customer_name"
                                            ] ?? ""
                                        );


                                    $email =
                                        (string)(
                                            $order[
                                                "email"
                                            ] ?? ""
                                        );


                                    $phone =
                                        (string)(
                                            $order[
                                                "phone"
                                            ] ?? ""
                                        );


                                    $orderType =
                                        trim(
                                            (string)(
                                                $order[
                                                    "order_type"
                                                ] ?? ""
                                            )
                                        );


                                    $total =
                                        (float)(
                                            $order[
                                                "total"
                                            ] ?? 0
                                        );


                                    $paymentMethod =
                                        trim(
                                            (string)(
                                                $order[
                                                    "payment_method"
                                                ] ?? ""
                                            )
                                        );


                                    $status =
                                        strtolower(
                                            trim(
                                                (string)(
                                                    $order[
                                                        "status"
                                                    ] ?? ""
                                                )
                                            )
                                        );


                                    $createdAt =
                                        (string)(
                                            $order[
                                                "created_at"
                                            ] ?? ""
                                        );


                                    $formattedOrderType =
                                        "N/A";


                                    if (
                                        $orderType === "Dine-In" ||
                                        $orderType === "Takeaway"
                                    ) {

                                        $formattedOrderType =
                                            $orderType;

                                    }


                                    $formattedPayment =
                                        "N/A";


                                    if (
                                        $paymentMethod ===
                                        "Pay at Café"
                                    ) {

                                        $formattedPayment =
                                            "Pay at Café";

                                    }


                                    $formattedDate =
                                        "-";


                                    if (
                                        $createdAt !== ""
                                    ) {

                                        $timestamp =
                                            strtotime(
                                                $createdAt
                                            );


                                        if (
                                            $timestamp !== false
                                        ) {

                                            $formattedDate =
                                                date(
                                                    "d M Y, h:i A",
                                                    $timestamp
                                                );

                                        }

                                    }


                                    $relativeTime =
                                        timeAgo(
                                            $createdAt
                                        );


                                    $statusClass =
                                        adminOrderStatusClass(
                                            $status
                                        );


                                    $statusIcon =
                                        adminOrderStatusIcon(
                                            $status
                                        );


                                    $statusLabel =
                                        adminOrderStatusLabel(
                                            $status
                                        );

                                    ?>


                                    <tr>


                                        <!-- ORDER ID -->

                                        <td>

                                            <span
                                                class="
                                                    cc-order-id
                                                "
                                            >

                                                #<?php
                                                echo $orderId;
                                                ?>

                                            </span>

                                        </td>


                                        <!-- CUSTOMER -->

                                        <td>

                                            <span
                                                class="
                                                    cc-customer-name
                                                "
                                            >

                                                <?php

                                                echo htmlspecialchars(
                                                    $customerName,
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                );

                                                ?>

                                            </span>


                                            <?php if (
                                                $email !== ""
                                            ): ?>

                                                <span
                                                    class="
                                                        cc-customer-meta
                                                    "
                                                >

                                                    <?php

                                                    echo htmlspecialchars(
                                                        $email,
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    );

                                                    ?>

                                                </span>

                                            <?php endif; ?>


                                            <?php if (
                                                $phone !== ""
                                            ): ?>

                                                <span
                                                    class="
                                                        cc-customer-meta
                                                    "
                                                >

                                                    <?php

                                                    echo htmlspecialchars(
                                                        $phone,
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    );

                                                    ?>

                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <!-- ORDER TYPE -->

                                        <td>

                                            <span
                                                class="
                                                    cc-order-type
                                                "
                                            >

                                                <?php

                                                echo htmlspecialchars(
                                                    $formattedOrderType,
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                );

                                                ?>

                                            </span>

                                        </td>


                                        <!-- TOTAL -->

                                        <td>

                                            <span
                                                class="cc-total"
                                            >

                                                ₹<?php

                                                echo number_format(
                                                    $total,
                                                    2
                                                );

                                                ?>

                                            </span>

                                        </td>


                                        <!-- PAYMENT -->

                                        <td>

                                            <?php

                                            echo htmlspecialchars(
                                                $formattedPayment,
                                                ENT_QUOTES,
                                                "UTF-8"
                                            );

                                            ?>

                                        </td>


                                        <!-- STATUS -->

                                        <td>

                                            <form
                                                method="POST"
                                                action="orders.php"
                                                class="
                                                    cc-status-form
                                                "
                                            >

                                                <input
                                                    type="hidden"
                                                    name="action"
                                                    value="update_status"
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

                                                    echo htmlspecialchars(
                                                        $_SESSION[
                                                            "admin_order_csrf"
                                                        ],
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    );

                                                    ?>"
                                                >


                                                <select
                                                    name="status"
                                                    class="
                                                        cc-status-select
                                                    "
                                                    aria-label="Order status"
                                                >

                                                    <?php foreach (
                                                        $allowedStatuses
                                                        as $statusOption
                                                    ): ?>

                                                        <option
                                                            value="<?php
                                                            echo htmlspecialchars(
                                                                $statusOption,
                                                                ENT_QUOTES,
                                                                "UTF-8"
                                                            );
                                                            ?>"
                                                            <?php

                                                            if (
                                                                $status ===
                                                                $statusOption
                                                            ) {

                                                                echo "selected";

                                                            }

                                                            ?>
                                                        >

                                                            <?php

                                                            echo htmlspecialchars(
                                                                adminOrderStatusLabel(
                                                                    $statusOption
                                                                ),
                                                                ENT_QUOTES,
                                                                "UTF-8"
                                                            );

                                                            ?>

                                                        </option>

                                                    <?php endforeach; ?>

                                                </select>


                                                <button
                                                    type="submit"
                                                    class="
                                                        cc-status-save
                                                    "
                                                    title="Update Status"
                                                >

                                                    <i
                                                        class="
                                                            fas
                                                            fa-save
                                                        "
                                                    ></i>

                                                </button>

                                            </form>

                                        </td>


                                        <!-- DATE -->

                                        <td>

                                            <span
                                                class="cc-date"
                                            >

                                                <?php

                                                echo htmlspecialchars(
                                                    $formattedDate,
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                );

                                                ?>

                                            </span>


                                            <?php if (
                                                $relativeTime !== ""
                                            ): ?>

                                                <span
                                                    class="
                                                        cc-relative
                                                    "
                                                >

                                                    <i
                                                        class="
                                                            far
                                                            fa-clock
                                                            mr-1
                                                        "
                                                    ></i>

                                                    <?php

                                                    echo htmlspecialchars(
                                                        $relativeTime,
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    );

                                                    ?>

                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <!-- ACTION -->

                                        <td
                                            class="text-center"
                                        >

                                            <div
                                                class="
                                                    cc-action-buttons
                                                "
                                            >


                                                <!-- VIEW -->

                                                <a
                                                    href="order-details.php?id=<?php
                                                    echo $orderId;
                                                    ?>"
                                                    class="
                                                        cc-view-btn
                                                    "
                                                    title="View Order Details"
                                                >

                                                    <i
                                                        class="
                                                            fas
                                                            fa-eye
                                                        "
                                                    ></i>

                                                </a>


                                                <!-- DELETE -->

                                                <form
                                                    method="POST"
                                                    action="delete-order.php"
                                                    style="margin:0;"
                                                    onsubmit="
                                                        return confirm(
                                                            'Are you sure you want to permanently delete Order #<?php
                                                            echo $orderId;
                                                            ?>? This will also delete all items belonging to this order.'
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

                                                        echo htmlspecialchars(
                                                            $_SESSION[
                                                                "admin_order_csrf"
                                                            ],
                                                            ENT_QUOTES,
                                                            "UTF-8"
                                                        );

                                                        ?>"
                                                    >


                                                    <button
                                                        type="submit"
                                                        class="
                                                            cc-delete-btn
                                                        "
                                                        title="Delete Order"
                                                        aria-label="Delete Order"
                                                    >

                                                        <i
                                                            class="
                                                                fas
                                                                fa-trash
                                                            "
                                                        ></i>

                                                    </button>

                                                </form>


                                            </div>

                                        </td>


                                    </tr>


                                <?php endwhile; ?>


                            <?php else: ?>


                                <tr>

                                    <td
                                        colspan="8"
                                        class="text-center"
                                        style="
                                            padding:70px 20px;
                                        "
                                    >

                                        <i
                                            class="
                                                fas
                                                fa-shopping-cart
                                            "
                                            style="
                                                color:#D8A15B;
                                                font-size:48px;
                                                margin-bottom:15px;
                                            "
                                        ></i>


                                        <h4
                                            style="
                                                color:#4A2C1D;
                                            "
                                        >

                                            No Orders Found

                                        </h4>


                                        <p
                                            style="
                                                color:#8A7468;
                                            "
                                        >

                                            There are currently
                                            no orders in the
                                            database.

                                        </p>

                                    </td>

                                </tr>

                            <?php endif; ?>


                            </tbody>

                        </table>

                    </div>

                </div>


                <!-- =================================================
                     FOOTER
                ================================================== -->

                <?php if (
                    $result !== false &&
                    mysqli_num_rows(
                        $result
                    ) > 0
                ): ?>

                    <div
                        class="card-footer"
                    >

                        <span
                            style="
                                color:#8A7468;
                                font-size:13px;
                            "
                        >

                            Total Orders:

                            <strong
                                style="
                                    color:#7B4728;
                                "
                            >

                                <?php

                                echo mysqli_num_rows(
                                    $result
                                );

                                ?>

                            </strong>

                        </span>

                    </div>

                <?php endif; ?>


            </div>

        </div>

    </section>

</div>


<?php

include "../includes/footer.php";

?>