<?php

/* =========================================================
   CAFFEINE & COVE
   ADMIN - ORDER DETAILS
========================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

$orderId = 0;

$error = "";

$success = "";

$order = null;

$orderItems = null;


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
   GET ORDER ID
========================================================= */

$orderIdRaw =
    $_GET["id"] ?? "";


if (
    !is_string($orderIdRaw) ||
    !ctype_digit($orderIdRaw) ||
    (int)$orderIdRaw <= 0
) {

    $error =
        "Invalid order ID.";

} else {

    $orderId =
        (int)$orderIdRaw;

}


/* =========================================================
   PROCESS POST
========================================================= */

if (
    $error === "" &&
    $_SERVER["REQUEST_METHOD"] === "POST"
) {


    /* =====================================================
       CSRF CHECK
    ====================================================== */

    $csrfToken =
        $_POST["csrf_token"] ?? "";

    $sessionToken =
        $_SESSION["admin_order_csrf"] ?? "";


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

        $error =
            "Security validation failed. Please try again.";

    }


    /* =====================================================
       POST ORDER ID
    ====================================================== */

    if (
        $error === ""
    ) {

        $postOrderId =
            $_POST["order_id"] ?? "";


        if (
            !is_string($postOrderId) ||
            !ctype_digit($postOrderId) ||
            (int)$postOrderId <= 0
        ) {

            $error =
                "Invalid order ID.";

        } else {

            $postedOrderId =
                (int)$postOrderId;


            /*
             * Make sure the POST order ID
             * matches the page order ID.
             */

            if (
                $orderId !== $postedOrderId
            ) {

                $error =
                    "Invalid order request.";

            }

        }

    }


    /* =====================================================
       ACTION
    ====================================================== */

    $action =
        $_POST["action"] ?? "";


    /* =====================================================
       UPDATE STATUS
    ====================================================== */

    if (
        $error === "" &&
        $action === "update_status"
    ) {


        $newStatus =
            strtolower(
                trim(
                    (string)(
                        $_POST["status"] ?? ""
                    )
                )
            );


        /* -------------------------------------------------
           STATUS VALIDATION
        ------------------------------------------------- */

        if (
            !in_array(
                $newStatus,
                $allowedStatuses,
                true
            )
        ) {

            $error =
                "Invalid order status.";

        } else {


            /* ---------------------------------------------
               CHECK ORDER EXISTS
            --------------------------------------------- */

            $checkSql = "
                SELECT
                    id
                FROM orders
                WHERE id = ?
                LIMIT 1
            ";


            $checkStmt =
                mysqli_prepare(
                    $link,
                    $checkSql
                );


            if (
                !$checkStmt
            ) {

                error_log(
                    "Admin order status check prepare failed: " .
                    mysqli_error($link)
                );

                $error =
                    "A database error occurred.";

            } else {


                mysqli_stmt_bind_param(
                    $checkStmt,
                    "i",
                    $orderId
                );


                if (
                    !mysqli_stmt_execute(
                        $checkStmt
                    )
                ) {

                    error_log(
                        "Admin order status check execute failed: " .
                        mysqli_stmt_error(
                            $checkStmt
                        )
                    );

                    $error =
                        "A database error occurred.";

                } else {


                    $checkResult =
                        mysqli_stmt_get_result(
                            $checkStmt
                        );


                    if (
                        !$checkResult ||
                        mysqli_num_rows(
                            $checkResult
                        ) !== 1
                    ) {

                        $error =
                            "Order not found.";

                    }

                }


                mysqli_stmt_close(
                    $checkStmt
                );

            }


            /* ---------------------------------------------
               UPDATE STATUS
            --------------------------------------------- */

            if (
                $error === ""
            ) {

                $updateSql = "
                    UPDATE orders
                    SET
                        status = ?
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
                        "Admin order status update prepare failed: " .
                        mysqli_error($link)
                    );

                    $error =
                        "A database error occurred.";

                } else {


                    mysqli_stmt_bind_param(
                        $updateStmt,
                        "si",
                        $newStatus,
                        $orderId
                    );


                    if (
                        mysqli_stmt_execute(
                            $updateStmt
                        )
                    ) {

                        $success =
                            "Order status updated successfully.";

                    } else {

                        error_log(
                            "Admin order status update failed: " .
                            mysqli_stmt_error(
                                $updateStmt
                            )
                        );

                        $error =
                            "Unable to update order status.";

                    }


                    mysqli_stmt_close(
                        $updateStmt
                    );

                }

            }

        }

    }

}


/* =========================================================
   GET ORDER
========================================================= */

if (
    $error === "" &&
    $orderId > 0
) {


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
            created_at,
            updated_at
        FROM orders
        WHERE id = ?
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
            "Admin order details prepare failed: " .
            mysqli_error($link)
        );

        $error =
            "A database error occurred.";

    } else {


        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $orderId
        );


        if (
            !mysqli_stmt_execute(
                $stmt
            )
        ) {

            error_log(
                "Admin order details execute failed: " .
                mysqli_stmt_error(
                    $stmt
                )
            );

            $error =
                "Unable to load order.";

        } else {


            $result =
                mysqli_stmt_get_result(
                    $stmt
                );


            if (
                $result &&
                mysqli_num_rows(
                    $result
                ) === 1
            ) {

                $order =
                    mysqli_fetch_assoc(
                        $result
                    );

            } else {

                $error =
                    "Order not found.";

            }

        }


        mysqli_stmt_close(
            $stmt
        );

    }

}


/* =========================================================
   GET ORDER ITEMS
========================================================= */

if (
    $order !== null &&
    $error === ""
) {


    $sql = "
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


    $stmt =
        mysqli_prepare(
            $link,
            $sql
        );


    if (
        !$stmt
    ) {

        error_log(
            "Admin order items prepare failed: " .
            mysqli_error($link)
        );

        $error =
            "Unable to load order items.";

    } else {


        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $orderId
        );


        if (
            !mysqli_stmt_execute(
                $stmt
            )
        ) {

            error_log(
                "Admin order items execute failed: " .
                mysqli_stmt_error(
                    $stmt
                )
            );

            $error =
                "Unable to load order items.";

        } else {

            $orderItems =
                mysqli_stmt_get_result(
                    $stmt
                );

        }


        mysqli_stmt_close(
            $stmt
        );

    }

}


/* =========================================================
   HELPER - ESCAPE
========================================================= */

function adminOrderEscape($value)
{

    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );

}


/* =========================================================
   HELPER - STATUS LABEL
========================================================= */

function adminOrderStatusLabel($status)
{

    switch (
        strtolower(
            trim(
                (string)$status
            )
        )
    ) {

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
   HELPER - STATUS CLASS
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

            return "cc-status-pending";


        case "confirmed":

            return "cc-status-confirmed";


        case "preparing":

            return "cc-status-preparing";


        case "ready":

            return "cc-status-ready";


        case "completed":

            return "cc-status-completed";


        case "cancelled":

            return "cc-status-cancelled";


        default:

            return "cc-status-unknown";

    }

}


/* =========================================================
   HEADER
========================================================= */

include "../includes/header.php";

include "../includes/sidebar.php";

?>


<style>

/* =========================================================
   ORDER DETAILS
========================================================= */

.cc-order-details {

    padding-bottom: 40px;

}


/* =========================================================
   STATUS BADGES
========================================================= */

.cc-status-badge {

    display: inline-block;

    padding: 7px 13px;

    border-radius: 20px;

    font-size: 12px;

    font-weight: 700;

}


.cc-status-pending {

    background: #FFF3CD;

    color: #856404;

}


.cc-status-confirmed {

    background: #F5E8C8;

    color: #7B4728;

}


.cc-status-preparing {

    background: #E8DDF5;

    color: #5B3A82;

}


.cc-status-ready {

    background: #D9EEF7;

    color: #245B73;

}


.cc-status-completed {

    background: #DFF3E4;

    color: #276738;

}


.cc-status-cancelled {

    background: #F8D7DA;

    color: #842029;

}


.cc-status-unknown {

    background: #E2E3E5;

    color: #41464B;

}


/* =========================================================
   INFO TEXT
========================================================= */

.cc-info-row {

    margin-bottom: 14px;

}


.cc-info-label {

    display: block;

    margin-bottom: 4px;

    color: #8A7468;

    font-size: 11px;

    text-transform: uppercase;

    letter-spacing: .5px;

}


.cc-info-value {

    color: #4A2C1D;

    font-size: 14px;

    font-weight: 600;

    word-break: break-word;

}


/* =========================================================
   ORDER ID
========================================================= */

.cc-order-id {

    color: #7B4728;

    font-size: 18px;

    font-weight: 700;

}


/* =========================================================
   TOTAL
========================================================= */

.cc-grand-total {

    color: #7B4728;

    font-size: 22px;

    font-weight: 700;

}


/* =========================================================
   ITEM NAME
========================================================= */

.cc-product-name {

    color: #4A2C1D;

    font-weight: 600;

}


/* =========================================================
   ITEM TOTAL
========================================================= */

.cc-item-total {

    color: #7B4728;

    font-weight: 700;

}


/* =========================================================
   ACTION AREA
========================================================= */

.cc-action-area {

    display: flex;

    align-items: center;

    gap: 8px;

    flex-wrap: wrap;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (
    max-width: 600px
) {

    .cc-action-area {

        flex-direction: column;

        align-items: stretch;

    }


    .cc-action-area .btn {

        width: 100%;

    }

}

</style>


<div
    class="
        content-wrapper
        cc-order-details
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
                                fa-file-invoice
                                mr-2
                            "
                            style="
                                color:#7B4728;
                            "
                        ></i>

                        Order Details

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
                            class="breadcrumb-item"
                        >

                            <a
                                href="orders.php"
                            >

                                Orders

                            </a>

                        </li>


                        <li
                            class="breadcrumb-item active"
                        >

                            Order Details

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

                    echo adminOrderEscape(
                        $success
                    );

                    ?>

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

                    echo adminOrderEscape(
                        $error
                    );

                    ?>

                </div>


                <a
                    href="orders.php"
                    class="
                        btn
                        btn-secondary
                        mb-4
                    "
                >

                    <i
                        class="
                            fas
                            fa-arrow-left
                            mr-2
                        "
                    ></i>

                    Back to Orders

                </a>

            <?php endif; ?>


            <?php if (
                $order !== null
            ): ?>


                <?php

                $currentStatus =
                    strtolower(
                        trim(
                            (string)(
                                $order["status"]
                                ?? ""
                            )
                        )
                    );


                $orderType =
                    trim(
                        (string)(
                            $order[
                                "order_type"
                            ] ?? ""
                        )
                    );


                $paymentMethod =
                    trim(
                        (string)(
                            $order[
                                "payment_method"
                            ] ?? ""
                        )
                    );


                $createdDate = "-";


                if (
                    !empty(
                        $order[
                            "created_at"
                        ]
                    )
                ) {

                    $timestamp =
                        strtotime(
                            $order[
                                "created_at"
                            ]
                        );


                    if (
                        $timestamp !== false
                    ) {

                        $createdDate =
                            date(
                                "d M Y, h:i A",
                                $timestamp
                            );

                    }

                }


                $updatedDate = "-";


                if (
                    !empty(
                        $order[
                            "updated_at"
                        ]
                    )
                ) {

                    $timestamp =
                        strtotime(
                            $order[
                                "updated_at"
                            ]
                        );


                    if (
                        $timestamp !== false
                    ) {

                        $updatedDate =
                            date(
                                "d M Y, h:i A",
                                $timestamp
                            );

                    }

                }


                $statusLabel =
                    adminOrderStatusLabel(
                        $currentStatus
                    );


                $statusClass =
                    adminOrderStatusClass(
                        $currentStatus
                    );

                ?>


                <!-- =================================================
                     TOP SUMMARY
                ================================================== -->

                <div class="row">


                    <!-- CUSTOMER -->

                    <div
                        class="col-md-6"
                    >

                        <div class="card">

                            <div
                                class="card-header"
                            >

                                <h3
                                    class="card-title"
                                >

                                    <i
                                        class="
                                            fas
                                            fa-user
                                            mr-2
                                        "
                                    ></i>

                                    Customer Information

                                </h3>

                            </div>


                            <div
                                class="card-body"
                            >


                                <div
                                    class="cc-info-row"
                                >

                                    <span
                                        class="
                                            cc-info-label
                                        "
                                    >

                                        Customer Name

                                    </span>


                                    <span
                                        class="
                                            cc-info-value
                                        "
                                    >

                                        <?php

                                        echo adminOrderEscape(
                                            $order[
                                                "customer_name"
                                            ] ?? ""
                                        );

                                        ?>

                                    </span>

                                </div>


                                <div
                                    class="cc-info-row"
                                >

                                    <span
                                        class="
                                            cc-info-label
                                        "
                                    >

                                        Email

                                    </span>


                                    <span
                                        class="
                                            cc-info-value
                                        "
                                    >

                                        <?php

                                        echo adminOrderEscape(
                                            $order[
                                                "email"
                                            ] ?? ""
                                        );

                                        ?>

                                    </span>

                                </div>


                                <div
                                    class="cc-info-row"
                                >

                                    <span
                                        class="
                                            cc-info-label
                                        "
                                    >

                                        Phone

                                    </span>


                                    <span
                                        class="
                                            cc-info-value
                                        "
                                    >

                                        <?php

                                        echo adminOrderEscape(
                                            $order[
                                                "phone"
                                            ] ?? ""
                                        );

                                        ?>

                                    </span>

                                </div>


                                <div
                                    class="
                                        cc-info-row
                                        mb-0
                                    "
                                >

                                    <span
                                        class="
                                            cc-info-label
                                        "
                                    >

                                        Order Type

                                    </span>


                                    <span
                                        class="
                                            cc-info-value
                                        "
                                    >

                                        <?php

                                        echo adminOrderEscape(
                                            $orderType
                                        );

                                        ?>

                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- ORDER INFO -->

                    <div
                        class="col-md-6"
                    >

                        <div class="card">

                            <div
                                class="card-header"
                            >

                                <h3
                                    class="card-title"
                                >

                                    <i
                                        class="
                                            fas
                                            fa-receipt
                                            mr-2
                                        "
                                    ></i>

                                    Order Information

                                </h3>

                            </div>


                            <div
                                class="card-body"
                            >


                                <div
                                    class="cc-info-row"
                                >

                                    <span
                                        class="
                                            cc-info-label
                                        "
                                    >

                                        Order ID

                                    </span>


                                    <span
                                        class="
                                            cc-order-id
                                        "
                                    >

                                        #<?php

                                        echo (int)
                                            $order[
                                                "id"
                                            ];

                                        ?>

                                    </span>

                                </div>


                                <div
                                    class="cc-info-row"
                                >

                                    <span
                                        class="
                                            cc-info-label
                                        "
                                    >

                                        Payment Method

                                    </span>


                                    <span
                                        class="
                                            cc-info-value
                                        "
                                    >

                                        <?php

                                        echo adminOrderEscape(
                                            $paymentMethod
                                        );

                                        ?>

                                    </span>

                                </div>


                                <div
                                    class="cc-info-row"
                                >

                                    <span
                                        class="
                                            cc-info-label
                                        "
                                    >

                                        Created At

                                    </span>


                                    <span
                                        class="
                                            cc-info-value
                                        "
                                    >

                                        <?php

                                        echo adminOrderEscape(
                                            $createdDate
                                        );

                                        ?>

                                    </span>

                                </div>


                                <div
                                    class="
                                        cc-info-row
                                        mb-0
                                    "
                                >

                                    <span
                                        class="
                                            cc-info-label
                                        "
                                    >

                                        Updated At

                                    </span>


                                    <span
                                        class="
                                            cc-info-value
                                        "
                                    >

                                        <?php

                                        echo adminOrderEscape(
                                            $updatedDate
                                        );

                                        ?>

                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     CURRENT STATUS
                ================================================== -->

                <div class="card">

                    <div
                        class="card-header"
                    >

                        <h3
                            class="card-title"
                        >

                            <i
                                class="
                                    fas
                                    fa-info-circle
                                    mr-2
                                "
                            ></i>

                            Current Status

                        </h3>

                    </div>


                    <div
                        class="card-body"
                    >

                        <span
                            class="
                                cc-status-badge
                                <?php

                                echo adminOrderEscape(
                                    $statusClass
                                );

                                ?>
                            "
                        >

                            <?php

                            echo adminOrderEscape(
                                $statusLabel
                            );

                            ?>

                        </span>

                    </div>

                </div>


                <!-- =================================================
                     UPDATE STATUS
                ================================================== -->

                <div class="card">

                    <div
                        class="card-header"
                    >

                        <h3
                            class="card-title"
                        >

                            <i
                                class="
                                    fas
                                    fa-tasks
                                    mr-2
                                "
                            ></i>

                            Update Order Status

                        </h3>

                    </div>


                    <div
                        class="card-body"
                    >

                        <form
                            method="POST"
                            action="order-details.php?id=<?php
                                echo (int)$orderId;
                            ?>"
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

                                echo (int)$orderId;

                                ?>"
                            >


                            <input
                                type="hidden"
                                name="csrf_token"
                                value="<?php

                                echo adminOrderEscape(
                                    $_SESSION[
                                        "admin_order_csrf"
                                    ]
                                );

                                ?>"
                            >


                            <div
                                class="row"
                            >

                                <div
                                    class="col-md-5"
                                >

                                    <select
                                        name="status"
                                        class="
                                            form-control
                                        "
                                        required
                                    >

                                        <?php foreach (
                                            $allowedStatuses
                                            as $statusOption
                                        ): ?>

                                            <option
                                                value="<?php

                                                echo adminOrderEscape(
                                                    $statusOption
                                                );

                                                ?>"
                                                <?php

                                                echo
                                                    $currentStatus ===
                                                    $statusOption
                                                        ? "selected"
                                                        : "";

                                                ?>
                                            >

                                                <?php

                                                echo adminOrderEscape(
                                                    adminOrderStatusLabel(
                                                        $statusOption
                                                    )
                                                );

                                                ?>

                                            </option>

                                        <?php endforeach; ?>

                                    </select>

                                </div>


                                <div
                                    class="col-md-4"
                                >

                                    <button
                                        type="submit"
                                        class="
                                            btn
                                            btn-coffee
                                        "
                                    >

                                        <i
                                            class="
                                                fas
                                                fa-save
                                                mr-2
                                            "
                                        ></i>

                                        Update Status

                                    </button>

                                </div>

                            </div>

                        </form>

                    </div>

                </div>


                <!-- =================================================
                     ORDERED PRODUCTS
                ================================================== -->

                <div class="card">

                    <div
                        class="card-header"
                    >

                        <h3
                            class="card-title"
                        >

                            <i
                                class="
                                    fas
                                    fa-shopping-basket
                                    mr-2
                                "
                            ></i>

                            Ordered Products

                        </h3>

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
                                            Product
                                        </th>

                                        <th>
                                            Price
                                        </th>

                                        <th>
                                            Quantity
                                        </th>

                                        <th>
                                            Item Total
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>


                                <?php if (
                                    $orderItems !== null &&
                                    mysqli_num_rows(
                                        $orderItems
                                    ) > 0
                                ): ?>


                                    <?php while (
                                        $item =
                                        mysqli_fetch_assoc(
                                            $orderItems
                                        )
                                    ): ?>


                                        <tr>

                                            <td>

                                                <span
                                                    class="
                                                        cc-product-name
                                                    "
                                                >

                                                    <?php

                                                    echo adminOrderEscape(
                                                        $item[
                                                            "product_name"
                                                        ] ?? ""
                                                    );

                                                    ?>

                                                </span>

                                            </td>


                                            <td>

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

                                            </td>


                                            <td>

                                                <?php

                                                echo (int)(
                                                    $item[
                                                        "quantity"
                                                    ] ?? 0
                                                );

                                                ?>

                                            </td>


                                            <td>

                                                <span
                                                    class="
                                                        cc-item-total
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

                                                </span>

                                            </td>

                                        </tr>


                                    <?php endwhile; ?>


                                <?php else: ?>


                                    <tr>

                                        <td
                                            colspan="4"
                                            class="
                                                text-center
                                            "
                                            style="
                                                padding:45px;
                                                color:#8A7468;
                                            "
                                        >

                                            No order items found.

                                        </td>

                                    </tr>


                                <?php endif; ?>


                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     PAYMENT SUMMARY
                ================================================== -->

                <div
                    class="row"
                >

                    <div
                        class="
                            col-md-6
                            ml-auto
                        "
                    >

                        <div class="card">

                            <div
                                class="card-header"
                            >

                                <h3
                                    class="card-title"
                                >

                                    <i
                                        class="
                                            fas
                                            fa-calculator
                                            mr-2
                                        "
                                    ></i>

                                    Payment Summary

                                </h3>

                            </div>


                            <div
                                class="card-body"
                            >


                                <div
                                    class="
                                        d-flex
                                        justify-content-between
                                        mb-2
                                    "
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
                                    class="
                                        d-flex
                                        justify-content-between
                                        mb-3
                                    "
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


                                <hr>


                                <div
                                    class="
                                        d-flex
                                        justify-content-between
                                    "
                                >

                                    <strong
                                        style="
                                            color:#4A2C1D;
                                            font-size:18px;
                                        "
                                    >

                                        Total

                                    </strong>


                                    <strong
                                        class="
                                            cc-grand-total
                                        "
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

                                    </strong>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     ACTIONS
                ================================================== -->

                <div
                    class="
                        cc-action-area
                        mb-4
                    "
                >

                    <a
                        href="orders.php"
                        class="
                            btn
                            btn-secondary
                        "
                    >

                        <i
                            class="
                                fas
                                fa-arrow-left
                                mr-2
                            "
                        ></i>

                        Back to Orders

                    </a>

                </div>


            <?php endif; ?>


        </div>

    </section>

</div>


<?php

include "../includes/footer.php";

?>