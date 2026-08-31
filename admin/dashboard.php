<?php

/* =========================================================
   CAFFEINE & COVE
   ADMIN DASHBOARD
========================================================= */


/* =========================================================
   ADMIN AUTHENTICATION
========================================================= */

require_once "admin_auth.php";


/* =========================================================
   DATABASE
========================================================= */

require_once "../include/config.php";


/* =========================================================
   DASHBOARD COUNTS
========================================================= */


/* ---------------------------------------------------------
   TOTAL PRODUCTS
--------------------------------------------------------- */

$totalProducts = 0;

$result = mysqli_query(
    $link,
    "SELECT COUNT(*) AS total FROM products"
);

if ($result !== false) {

    $row = mysqli_fetch_assoc($result);

    $totalProducts = (int) $row["total"];
}


/* ---------------------------------------------------------
   TOTAL CUSTOMERS / USERS
--------------------------------------------------------- */

$totalCustomers = 0;

$result = mysqli_query(
    $link,
    "SELECT COUNT(*) AS total FROM users"
);

if ($result !== false) {

    $row = mysqli_fetch_assoc($result);

    $totalCustomers = (int) $row["total"];
}


/* ---------------------------------------------------------
   TOTAL ORDERS
--------------------------------------------------------- */

$totalOrders = 0;

$result = mysqli_query(
    $link,
    "SELECT COUNT(*) AS total FROM orders"
);

if ($result !== false) {

    $row = mysqli_fetch_assoc($result);

    $totalOrders = (int) $row["total"];
}


/* ---------------------------------------------------------
   TOTAL RESERVATIONS
--------------------------------------------------------- */

$totalReservations = 0;

$result = mysqli_query(
    $link,
    "SELECT COUNT(*) AS total FROM reservations"
);

if ($result !== false) {

    $row = mysqli_fetch_assoc($result);

    $totalReservations = (int) $row["total"];
}


/* =========================================================
   EXTRA STATISTICS
========================================================= */


/* ---------------------------------------------------------
   TODAY SALES
--------------------------------------------------------- */

$todaySales = 0;

$result = mysqli_query(
    $link,
    "
    SELECT COALESCE(SUM(total), 0) AS total_sales
    FROM orders
    WHERE status = 'completed'
    AND DATE(created_at) = CURDATE()
    "
);

if ($result !== false) {

    $row = mysqli_fetch_assoc($result);

    $todaySales = (float) $row["total_sales"];
}


/* ---------------------------------------------------------
   PENDING ORDERS
--------------------------------------------------------- */

$pendingOrders = 0;

$result = mysqli_query(
    $link,
    "
    SELECT COUNT(*) AS total
    FROM orders
    WHERE status = 'pending'
    "
);

if ($result !== false) {

    $row = mysqli_fetch_assoc($result);

    $pendingOrders = (int) $row["total"];
}


/* ---------------------------------------------------------
   PENDING RESERVATIONS
--------------------------------------------------------- */

$pendingReservations = 0;

$result = mysqli_query(
    $link,
    "
    SELECT COUNT(*) AS total
    FROM reservations
    WHERE status = 'pending'
    "
);

if ($result !== false) {

    $row = mysqli_fetch_assoc($result);

    $pendingReservations = (int) $row["total"];
}


/* ---------------------------------------------------------
   UNREAD MESSAGES
--------------------------------------------------------- */

$unreadMessages = 0;

$result = mysqli_query(
    $link,
    "
    SELECT COUNT(*) AS total
    FROM contact_messages
    WHERE status = 'unread'
    "
);

if ($result !== false) {

    $row = mysqli_fetch_assoc($result);

    $unreadMessages = (int) $row["total"];
}


/* =========================================================
   RECENT ORDERS
========================================================= */

$recentOrders = mysqli_query(
    $link,
    "
    SELECT
        id,
        customer_name,
        total,
        status,
        created_at
    FROM orders
    ORDER BY id DESC
    LIMIT 5
    "
);


/* =========================================================
   COMMON HEADER
========================================================= */

include "includes/header.php";

include "includes/sidebar.php";

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
                        Dashboard
                    </h1>

                </div>


                <div class="col-sm-6">

                    <ol class="breadcrumb float-sm-right">

                        <li class="breadcrumb-item">
                            Admin
                        </li>

                        <li class="breadcrumb-item active">
                            Dashboard
                        </li>

                    </ol>

                </div>

            </div>

        </div>

    </div>


    <!-- =====================================================
         MAIN CONTENT
    ====================================================== -->

    <section class="content">

        <div class="container-fluid">


            <!-- =================================================
                 MAIN STATISTICS
            ================================================== -->

            <div class="row">


                <!-- =================================================
                     TOTAL ORDERS
                ================================================== -->

                <div class="col-lg-3 col-6">

                    <div class="small-box bg-coffee">

                        <div class="inner">

                            <h3>
                                <?php echo $totalOrders; ?>
                            </h3>

                            <p>
                                Total Orders
                            </p>

                        </div>

                        <div class="icon">

                            <i class="fas fa-shopping-cart"></i>

                        </div>

                        <a
                            href="orders/orders.php"
                            class="small-box-footer"
                        >

                            View Orders

                            <i class="fas fa-arrow-circle-right"></i>

                        </a>

                    </div>

                </div>


                <!-- =================================================
                     TOTAL PRODUCTS
                ================================================== -->

                <div class="col-lg-3 col-6">

                    <div class="small-box bg-gold">

                        <div class="inner">

                            <h3>
                                <?php echo $totalProducts; ?>
                            </h3>

                            <p>
                                Products
                            </p>

                        </div>

                        <div class="icon">

                            <i class="fas fa-coffee"></i>

                        </div>

                        <a
                            href="products/products.php"
                            class="small-box-footer"
                        >

                            Manage Products

                            <i class="fas fa-arrow-circle-right"></i>

                        </a>

                    </div>

                </div>


                <!-- =================================================
                     TOTAL CUSTOMERS
                ================================================== -->

                <div class="col-lg-3 col-6">

                    <div class="small-box bg-dark-coffee">

                        <div class="inner">

                            <h3>
                                <?php echo $totalCustomers; ?>
                            </h3>

                            <p>
                                Customers
                            </p>

                        </div>

                        <div class="icon">

                            <i class="fas fa-users"></i>

                        </div>

                        <a
                            href="user/user.php"
                            class="small-box-footer"
                        >

                            View Users

                            <i class="fas fa-arrow-circle-right"></i>

                        </a>

                    </div>

                </div>


                <!-- =================================================
                     TOTAL RESERVATIONS
                ================================================== -->

                <div class="col-lg-3 col-6">

                    <div class="small-box bg-coffee">

                        <div class="inner">

                            <h3>
                                <?php echo $totalReservations; ?>
                            </h3>

                            <p>
                                Reservations
                            </p>

                        </div>

                        <div class="icon">

                            <i class="fas fa-calendar-check"></i>

                        </div>

                        <a
                            href="reservations/reservations.php"
                            class="small-box-footer"
                        >

                            View Reservations

                            <i class="fas fa-arrow-circle-right"></i>

                        </a>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 EXTRA STATISTICS
            ================================================== -->

            <div class="row">


                <!-- =================================================
                     TODAY SALES
                ================================================== -->

                <div class="col-lg-3 col-md-6">

                    <div class="info-box">

                        <span class="info-box-icon">

                            <i class="fas fa-rupee-sign"></i>

                        </span>

                        <div class="info-box-content">

                            <span class="info-box-text">
                                Today's Sales
                            </span>

                            <span class="info-box-number">

                                ₹<?php
                                echo number_format(
                                    $todaySales,
                                    2
                                );
                                ?>

                            </span>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     PENDING ORDERS
                ================================================== -->

                <div class="col-lg-3 col-md-6">

                    <div class="info-box">

                        <span class="info-box-icon">

                            <i class="fas fa-clock"></i>

                        </span>

                        <div class="info-box-content">

                            <span class="info-box-text">
                                Pending Orders
                            </span>

                            <span class="info-box-number">
                                <?php echo $pendingOrders; ?>
                            </span>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     PENDING RESERVATIONS
                ================================================== -->

                <div class="col-lg-3 col-md-6">

                    <div class="info-box">

                        <span class="info-box-icon">

                            <i class="fas fa-calendar"></i>

                        </span>

                        <div class="info-box-content">

                            <span class="info-box-text">
                                Pending Reservations
                            </span>

                            <span class="info-box-number">
                                <?php echo $pendingReservations; ?>
                            </span>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     UNREAD MESSAGES
                ================================================== -->

                <div class="col-lg-3 col-md-6">

                    <div class="info-box">

                        <span class="info-box-icon">

                            <i class="fas fa-envelope"></i>

                        </span>

                        <div class="info-box-content">

                            <span class="info-box-text">
                                Unread Messages
                            </span>

                            <span class="info-box-number">
                                <?php echo $unreadMessages; ?>
                            </span>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 RECENT ORDERS
            ================================================== -->

            <div class="row">

                <div class="col-12">

                    <div class="card">

                        <div class="card-header">

                            <h3 class="card-title">

                                <i class="fas fa-shopping-bag mr-2"></i>

                                Recent Orders

                            </h3>


                            <div class="card-tools">

                                <a
                                    href="orders/orders.php"
                                    class="btn btn-sm btn-coffee"
                                >

                                    View All

                                </a>

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
                                                Customer
                                            </th>

                                            <th>
                                                Amount
                                            </th>

                                            <th>
                                                Status
                                            </th>

                                            <th>
                                                Date
                                            </th>

                                            <th>
                                                View
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>


                                    <?php if (
                                        $recentOrders !== false &&
                                        mysqli_num_rows($recentOrders) > 0
                                    ): ?>


                                        <?php while (
                                            $order =
                                            mysqli_fetch_assoc(
                                                $recentOrders
                                            )
                                        ): ?>


                                            <?php

                                            $orderStatus =
                                                strtolower(
                                                    trim(
                                                        (string) $order["status"]
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
                                                        echo (int)
                                                            $order["id"];
                                                        ?>

                                                    </strong>

                                                </td>


                                                <!-- CUSTOMER -->

                                                <td>

                                                    <?php
                                                    echo htmlspecialchars(
                                                        $order["customer_name"]
                                                    );
                                                    ?>

                                                </td>


                                                <!-- AMOUNT -->

                                                <td>

                                                    <strong
                                                        style="
                                                            color:#7B4728;
                                                        "
                                                    >

                                                        ₹<?php
                                                        echo number_format(
                                                            (float)
                                                            $order["total"],
                                                            2
                                                        );
                                                        ?>

                                                    </strong>

                                                </td>


                                                <!-- STATUS -->

                                                <td>

                                                    <span
                                                        class="badge <?php
                                                        echo $statusClass;
                                                        ?>"
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
                                                            color:#8A7468;
                                                            font-size:12px;
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

                                                <td>

                                                    <a
                                                        href="orders/order-details.php?id=<?php echo (int) $order["id"]; ?>"
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
                                                colspan="6"
                                                class="text-center"
                                                style="
                                                    padding:45px;
                                                    color:#8A7468;
                                                "
                                            >

                                                No orders found.

                                            </td>

                                        </tr>


                                    <?php endif; ?>


                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 QUICK ACTIONS
            ================================================== -->

            <div class="row">

                <div class="col-12">

                    <div class="card">

                        <div class="card-header">

                            <h3 class="card-title">

                                <i
                                    class="fas fa-bolt mr-2"
                                    style="color:#D8A15B;"
                                ></i>

                                Quick Actions

                            </h3>

                        </div>


                        <div class="card-body">

                            <div class="row">


                                <!-- =================================================
                                     ADD PRODUCT
                                ================================================== -->

                                <div
                                    class="
                                        col-lg
                                        col-md-4
                                        col-sm-6
                                        mb-2
                                    "
                                >

                                    <a
                                        href="products/add-product.php"
                                        class="
                                            btn
                                            btn-coffee
                                            btn-block
                                        "
                                    >

                                        <i class="fas fa-plus mr-2"></i>

                                        Add Product

                                    </a>

                                </div>


                                <!-- =================================================
                                     ORDERS
                                ================================================== -->

                                <div
                                    class="
                                        col-lg
                                        col-md-4
                                        col-sm-6
                                        mb-2
                                    "
                                >

                                    <a
                                        href="orders/orders.php"
                                        class="
                                            btn
                                            btn-gold
                                            btn-block
                                        "
                                    >

                                        <i
                                            class="
                                                fas
                                                fa-shopping-cart
                                                mr-2
                                            "
                                        ></i>

                                        View Orders

                                    </a>

                                </div>


                                <!-- =================================================
                                     RESERVATIONS
                                ================================================== -->

                                <div
                                    class="
                                        col-lg
                                        col-md-4
                                        col-sm-6
                                        mb-2
                                    "
                                >

                                    <a
                                        href="reservations/reservations.php"
                                        class="
                                            btn
                                            btn-coffee
                                            btn-block
                                        "
                                    >

                                        <i
                                            class="
                                                fas
                                                fa-calendar-plus
                                                mr-2
                                            "
                                        ></i>

                                        Reservations

                                    </a>

                                </div>


                                <!-- =================================================
                                     USERS
                                ================================================== -->

                                <div
                                    class="
                                        col-lg
                                        col-md-4
                                        col-sm-6
                                        mb-2
                                    "
                                >

                                    <a
                                        href="user/user.php"
                                        class="
                                            btn
                                            btn-gold
                                            btn-block
                                        "
                                    >

                                        <i
                                            class="
                                                fas
                                                fa-users
                                                mr-2
                                            "
                                        ></i>

                                        Users

                                    </a>

                                </div>


                                <!-- =================================================
                                     SALES ANALYTICS
                                ================================================== -->

                                <div
                                    class="
                                        col-lg
                                        col-md-4
                                        col-sm-6
                                        mb-2
                                    "
                                >

                                    <a
                                        href="analytics/sales.php"
                                        class="
                                            btn
                                            btn-coffee
                                            btn-block
                                        "
                                    >

                                        <i
                                            class="
                                                fas
                                                fa-chart-line
                                                mr-2
                                            "
                                        ></i>

                                        Sales Analytics

                                    </a>

                                </div>


                                <!-- =================================================
                                     MESSAGES
                                ================================================== -->

                                <div
                                    class="
                                        col-lg
                                        col-md-4
                                        col-sm-6
                                        mb-2
                                "
                                >

                                    <a
                                        href="messages/messages.php"
                                        class="
                                            btn
                                            btn-gold
                                            btn-block
                                        "
                                    >

                                        <i
                                            class="
                                                fas
                                                fa-envelope
                                                mr-2
                                            "
                                        ></i>

                                        Messages

                                    </a>

                                </div>


                            </div>

                        </div>

                    </div>

                </div>

            </div>


        </div>

    </section>

</div>


<?php

/* =========================================================
   FOOTER
========================================================= */

include "includes/footer.php";

?>
