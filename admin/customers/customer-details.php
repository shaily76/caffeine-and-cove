<?php

/* =========================================================
   CAFFEINE & COVE
   ADMIN - ORDER DETAILS
   ========================================================= */


/* =========================================================
   ADMIN AUTHENTICATION
   ========================================================= */

require_once "../admin_auth.php";


/* =========================================================
   DATABASE CONNECTION
   ========================================================= */

require_once "../../include/config.php";
/* =========================================================
   VARIABLES
========================================================= */

$customerId = 0;

$error = "";

$customer = null;

$orders = null;


/* =========================================================
   GET CUSTOMER ID
========================================================= */

if (
    isset($_GET["id"]) &&
    ctype_digit($_GET["id"])
) {

    $customerId = (int)$_GET["id"];

} else {

    $error = "Invalid customer ID.";

}


/* =========================================================
   GET CUSTOMER
========================================================= */

if ($customerId > 0) {

    $sql = "
        SELECT
            id,
            full_name,
            username,
            email,
            mobile,
            created_at,
            updated_at
        FROM users
        WHERE id = ?
        LIMIT 1
    ";

    $stmt = mysqli_prepare(
        $link,
        $sql
    );

    if ($stmt === false) {

        $error =
            "Database error: " .
            mysqli_error($link);

    } else {

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $customerId
        );

        mysqli_stmt_execute($stmt);

        $result =
            mysqli_stmt_get_result($stmt);

        if (
            $result &&
            mysqli_num_rows($result) === 1
        ) {

            $customer =
                mysqli_fetch_assoc($result);

        } else {

            $error = "Customer not found.";

        }

        mysqli_stmt_close($stmt);

    }

}


/* =========================================================
   GET CUSTOMER ORDERS
========================================================= */

if (
    $customer !== null &&
    $error === ""
) {

    $sql = "
        SELECT
            id,
            order_type,
            subtotal,
            tax,
            total,
            payment_method,
            status,
            created_at
        FROM orders
        WHERE user_id = ?
        ORDER BY id DESC
    ";

    $stmt = mysqli_prepare(
        $link,
        $sql
    );

    if ($stmt !== false) {

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $customerId
        );

        mysqli_stmt_execute($stmt);

        $orders =
            mysqli_stmt_get_result($stmt);

        mysqli_stmt_close($stmt);

    } else {

        $error =
            "Unable to load customer orders: " .
            mysqli_error($link);

    }

}


/* =========================================================
   COMMON HEADER
========================================================= */

include "../includes/header.php";

include "../includes/sidebar.php";

?>


<!-- =========================================================
     CONTENT WRAPPER
========================================================= -->

<div class="content-wrapper">


    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->

    <div class="content-header">

        <div class="container-fluid">

            <div class="row mb-2">


                <div class="col-sm-6">

                    <h1 class="m-0">

                        <i
                            class="fas fa-user mr-2"
                            style="color:#7B4728;"
                        ></i>

                        Customer Details

                    </h1>

                </div>


                <div class="col-sm-6">

                    <ol class="breadcrumb float-sm-right">

                        <li class="breadcrumb-item">

                            <a href="../dashboard.php">
                                Dashboard
                            </a>

                        </li>

                        <li class="breadcrumb-item">

                            <a href="customers.php">
                                Customers
                            </a>

                        </li>

                        <li class="breadcrumb-item active">
                            Details
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
                 ERROR
            ================================================== -->

            <?php if ($error !== ""): ?>

                <div class="alert alert-danger">

                    <i class="fas fa-exclamation-circle mr-2"></i>

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>


                <a
                    href="customers.php"
                    class="btn btn-coffee"
                >

                    <i class="fas fa-arrow-left mr-2"></i>

                    Back to Customers

                </a>

            <?php endif; ?>


            <?php if ($customer !== null): ?>


                <?php

                $fullName =
                    (string)$customer["full_name"];

                $username =
                    (string)$customer["username"];

                $email =
                    (string)$customer["email"];

                $mobile =
                    (string)$customer["mobile"];

                $createdAt =
                    (string)$customer["created_at"];

                $updatedAt =
                    (string)$customer["updated_at"];


                $registeredDate =
                    $createdAt !== ""
                        ? date(
                            "d M Y, h:i A",
                            strtotime($createdAt)
                        )
                        : "-";


                $updatedDate =
                    $updatedAt !== ""
                        ? date(
                            "d M Y, h:i A",
                            strtotime($updatedAt)
                        )
                        : "-";

                ?>


                <!-- =================================================
                     CUSTOMER PROFILE
                ================================================== -->

                <div class="card">

                    <div class="card-header">

                        <h3 class="card-title">

                            <i class="fas fa-user-circle mr-2"></i>

                            Customer Information

                        </h3>

                    </div>


                    <div class="card-body">


                        <div class="row">


                            <!-- PROFILE -->

                            <div class="col-md-4 text-center">

                                <div
                                    style="
                                        width:95px;
                                        height:95px;
                                        border-radius:50%;
                                        background:#F5E8DA;
                                        display:flex;
                                        align-items:center;
                                        justify-content:center;
                                        margin:0 auto 15px;
                                    "
                                >

                                    <i
                                        class="fas fa-user"
                                        style="
                                            color:#7B4728;
                                            font-size:42px;
                                        "
                                    ></i>

                                </div>


                                <h4
                                    style="
                                        color:#4A2C1D;
                                        margin-bottom:4px;
                                    "
                                >

                                    <?php
                                    echo htmlspecialchars(
                                        $fullName
                                    );
                                    ?>

                                </h4>


                                <div
                                    style="
                                        color:#8A7468;
                                    "
                                >

                                    @<?php
                                    echo htmlspecialchars(
                                        $username
                                    );
                                    ?>

                                </div>

                            </div>


                            <!-- DETAILS -->

                            <div class="col-md-8">

                                <div class="row">


                                    <div class="col-md-6 mb-4">

                                        <small
                                            style="
                                                color:#8A7468;
                                            "
                                        >
                                            Customer ID
                                        </small>

                                        <div
                                            style="
                                                color:#7B4728;
                                                font-weight:600;
                                                font-size:17px;
                                            "
                                        >

                                            #<?php
                                            echo $customerId;
                                            ?>

                                        </div>

                                    </div>


                                    <div class="col-md-6 mb-4">

                                        <small
                                            style="
                                                color:#8A7468;
                                            "
                                        >
                                            Username
                                        </small>

                                        <div
                                            style="
                                                color:#4A2C1D;
                                                font-weight:600;
                                            "
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $username
                                            );
                                            ?>

                                        </div>

                                    </div>


                                    <div class="col-md-6 mb-4">

                                        <small
                                            style="
                                                color:#8A7468;
                                            "
                                        >
                                            Email
                                        </small>

                                        <div>

                                            <a
                                                href="mailto:<?php
                                                echo htmlspecialchars(
                                                    $email
                                                );
                                                ?>"
                                            >

                                                <?php
                                                echo htmlspecialchars(
                                                    $email
                                                );
                                                ?>

                                            </a>

                                        </div>

                                    </div>


                                    <div class="col-md-6 mb-4">

                                        <small
                                            style="
                                                color:#8A7468;
                                            "
                                        >
                                            Mobile
                                        </small>

                                        <div>

                                            <?php if ($mobile !== ""): ?>

                                                <a
                                                    href="tel:<?php
                                                    echo htmlspecialchars(
                                                        $mobile
                                                    );
                                                    ?>"
                                                >

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $mobile
                                                    );
                                                    ?>

                                                </a>

                                            <?php else: ?>

                                                <span
                                                    style="
                                                        color:#9A8477;
                                                    "
                                                >
                                                    Not provided
                                                </span>

                                            <?php endif; ?>

                                        </div>

                                    </div>


                                    <div class="col-md-6">

                                        <small
                                            style="
                                                color:#8A7468;
                                            "
                                        >
                                            Registered On
                                        </small>

                                        <div
                                            style="
                                                color:#4A2C1D;
                                                font-weight:600;
                                            "
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $registeredDate
                                            );
                                            ?>

                                        </div>

                                    </div>


                                    <div class="col-md-6">

                                        <small
                                            style="
                                                color:#8A7468;
                                            "
                                        >
                                            Last Updated
                                        </small>

                                        <div
                                            style="
                                                color:#4A2C1D;
                                                font-weight:600;
                                            "
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                $updatedDate
                                            );
                                            ?>

                                        </div>

                                    </div>


                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     ORDER HISTORY
                ================================================== -->

                <div class="card">

                    <div class="card-header">

                        <h3 class="card-title">

                            <i
                                class="fas fa-shopping-cart mr-2"
                            ></i>

                            Order History

                        </h3>


                        <div class="card-tools">

                            <span class="badge badge-gold">

                                <?php

                                if ($orders !== null) {

                                    echo mysqli_num_rows(
                                        $orders
                                    );

                                } else {

                                    echo "0";

                                }

                                ?>

                                Orders

                            </span>

                        </div>

                    </div>


                    <div class="card-body p-0">

                        <div class="table-responsive">

                            <table class="table table-hover mb-0">


                                <thead>

                                    <tr>

                                        <th>
                                            Order ID
                                        </th>

                                        <th>
                                            Type
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
                                            View
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>


                                <?php if (
                                    $orders !== null &&
                                    mysqli_num_rows($orders) > 0
                                ): ?>


                                    <?php while (
                                        $order =
                                        mysqli_fetch_assoc(
                                            $orders
                                        )
                                    ): ?>


                                        <?php

                                        $orderStatus =
                                            strtolower(
                                                trim(
                                                    (string)$order[
                                                        "status"
                                                    ]
                                                )
                                            );


                                        switch ($orderStatus) {

                                            case "pending":
                                                $statusClass =
                                                    "badge-warning";
                                                break;

                                            case "confirmed":
                                                $statusClass =
                                                    "badge-gold";
                                                break;

                                            case "preparing":
                                                $statusClass =
                                                    "badge-coffee";
                                                break;

                                            case "ready":
                                                $statusClass =
                                                    "badge-success";
                                                break;

                                            case "completed":
                                                $statusClass =
                                                    "badge-success";
                                                break;

                                            case "cancelled":
                                                $statusClass =
                                                    "badge-danger";
                                                break;

                                            default:
                                                $statusClass =
                                                    "badge-secondary";
                                                break;

                                        }


                                        $orderDate =
                                            !empty(
                                                $order["created_at"]
                                            )
                                                ? date(
                                                    "d M Y",
                                                    strtotime(
                                                        $order["created_at"]
                                                    )
                                                )
                                                : "-";

                                        ?>


                                        <tr>


                                            <!-- ORDER ID -->

                                            <td>

                                                <strong
                                                    style="
                                                        color:#7B4728;
                                                    "
                                                >

                                                    #<?php
                                                    echo (int)$order["id"];
                                                    ?>

                                                </strong>

                                            </td>


                                            <!-- TYPE -->

                                            <td>

                                                <?php
                                                echo htmlspecialchars(
                                                    ucfirst(
                                                        str_replace(
                                                            "_",
                                                            " ",
                                                            $order["order_type"]
                                                        )
                                                    )
                                                );
                                                ?>

                                            </td>


                                            <!-- TOTAL -->

                                            <td>

                                                <strong
                                                    style="
                                                        color:#7B4728;
                                                    "
                                                >

                                                    ₹<?php
                                                    echo number_format(
                                                        (float)$order["total"],
                                                        2
                                                    );
                                                    ?>

                                                </strong>

                                            </td>


                                            <!-- PAYMENT -->

                                            <td>

                                                <?php
                                                echo htmlspecialchars(
                                                    ucfirst(
                                                        str_replace(
                                                            "_",
                                                            " ",
                                                            $order["payment_method"]
                                                        )
                                                    )
                                                );
                                                ?>

                                            </td>


                                            <!-- STATUS -->

                                            <td>

                                                <span
                                                    class="badge <?php
                                                    echo $statusClass;
                                                    ?>"
                                                    style="
                                                        padding:7px 9px;
                                                    "
                                                >

                                                    <?php
                                                    echo htmlspecialchars(
                                                        ucfirst(
                                                            $orderStatus
                                                        )
                                                    );
                                                    ?>

                                                </span>

                                            </td>


                                            <!-- DATE -->

                                            <td>

                                                <span
                                                    style="
                                                        color:#6F5548;
                                                        font-size:13px;
                                                    "
                                                >

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $orderDate
                                                    );
                                                    ?>

                                                </span>

                                            </td>


                                            <!-- VIEW -->

                                            <td
                                                class="text-center"
                                            >

                                                <a
                                                    href="../orders/order-details.php?id=<?php
                                                    echo (int)$order["id"];
                                                    ?>"
                                                    class="btn btn-sm btn-gold"
                                                    title="View Order"
                                                >

                                                    <i
                                                        class="fas fa-eye"
                                                    ></i>

                                                </a>

                                            </td>


                                        </tr>


                                    <?php endwhile; ?>


                                <?php else: ?>


                                    <tr>

                                        <td
                                            colspan="7"
                                            class="text-center"
                                            style="
                                                padding:60px 20px;
                                            "
                                        >

                                            <i
                                                class="fas fa-shopping-bag"
                                                style="
                                                    color:#D8A15B;
                                                    font-size:42px;
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
                                                    margin-bottom:0;
                                                "
                                            >

                                                This customer has not
                                                placed any orders yet.

                                            </p>

                                        </td>

                                    </tr>


                                <?php endif; ?>


                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     BACK BUTTON
                ================================================== -->

                <a
                    href="customers.php"
                    class="btn btn-secondary mb-4"
                >

                    <i
                        class="fas fa-arrow-left mr-2"
                    ></i>

                    Back to Customers

                </a>


            <?php endif; ?>


        </div>

    </section>

</div>


<?php

/* =========================================================
   COMMON FOOTER
========================================================= */

include "../includes/footer.php";

?>