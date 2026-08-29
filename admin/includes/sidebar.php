<?php

/* =========================================================
   CAFFEINE & COVE
   ADMIN SIDEBAR
   ========================================================= */


/* =========================================================
   SESSION
   ========================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================================================
   DATABASE CONNECTION
   ========================================================= */

require_once __DIR__ . "/../../include/config.php";


/* =========================================================
   ADMIN BASE PATH
   ========================================================= */

$scriptPath = $_SERVER["SCRIPT_NAME"];

$adminPosition = strpos(
    $scriptPath,
    "/admin/"
);

if ($adminPosition !== false) {

    $adminBase = substr(
        $scriptPath,
        0,
        $adminPosition + 7
    );

} else {

    $adminBase = "/";

}


/* =========================================================
   NOTIFICATION COUNTS
   ========================================================= */


/* ---------------------------------------------------------
   PENDING ORDERS
---------------------------------------------------------- */

$pendingOrders = 0;

$orderResult = mysqli_query(
    $link,
    "
    SELECT COUNT(*) AS total
    FROM orders
    WHERE status = 'pending'
    "
);

if ($orderResult !== false) {

    $orderRow = mysqli_fetch_assoc(
        $orderResult
    );

    $pendingOrders =
        (int)$orderRow["total"];

}


/* ---------------------------------------------------------
   PENDING RESERVATIONS
---------------------------------------------------------- */

$pendingReservations = 0;

$reservationResult = mysqli_query(
    $link,
    "
    SELECT COUNT(*) AS total
    FROM reservations
    WHERE status = 'pending'
    "
);

if ($reservationResult !== false) {

    $reservationRow =
        mysqli_fetch_assoc(
            $reservationResult
        );

    $pendingReservations =
        (int)$reservationRow["total"];

}


/* ---------------------------------------------------------
   UNREAD MESSAGES
---------------------------------------------------------- */

$unreadMessages = 0;

$messageResult = mysqli_query(
    $link,
    "
    SELECT COUNT(*) AS total
    FROM contact_messages
    WHERE status = 'unread'
    "
);

if ($messageResult !== false) {

    $messageRow =
        mysqli_fetch_assoc(
            $messageResult
        );

    $unreadMessages =
        (int)$messageRow["total"];

}

?>


<!-- =========================================================
     SIDEBAR
========================================================= -->

<aside class="main-sidebar sidebar-dark-primary elevation-4">


    <!-- =====================================================
         BRAND
    ====================================================== -->

    <a
        href="<?php echo $adminBase; ?>dashboard.php"
        class="brand-link text-center"
    >

        <span class="brand-text">

            Caffeine & Cove

        </span>

    </a>


    <div class="sidebar">


        <!-- =================================================
             ADMIN PROFILE
        ================================================== -->

        <div class="user-panel mt-3 pb-3 mb-3 d-flex">


            <div class="image">

                <i
                    class="fas fa-user-circle fa-2x"
                    style="color:#D8A15B;"
                ></i>

            </div>


            <div class="info">

                <a
                    href="#"
                    class="d-block"
                >

                    <?php

                    echo isset(
                        $_SESSION["admin_name"]
                    )
                        ? htmlspecialchars(
                            $_SESSION["admin_name"]
                        )
                        : "Admin";

                    ?>

                </a>

            </div>

        </div>


        <!-- =================================================
             SIDEBAR MENU
        ================================================== -->

        <nav class="mt-2">


            <ul
                class="nav nav-pills nav-sidebar flex-column"
                data-widget="treeview"
                role="menu"
                data-accordion="false"
            >


                <!-- =================================================
                     DASHBOARD
                ================================================== -->

                <li class="nav-item">

                    <a
                        href="<?php echo $adminBase; ?>dashboard.php"
                        class="nav-link"
                    >

                        <i class="nav-icon fas fa-tachometer-alt"></i>

                        <p>

                            Dashboard

                        </p>

                    </a>

                </li>


                <!-- =================================================
                     PRODUCTS
                ================================================== -->

                <li class="nav-item">


                    <a
                        href="#"
                        class="nav-link"
                    >

                        <i class="nav-icon fas fa-coffee"></i>


                        <p>

                            Products

                            <i class="right fas fa-angle-left"></i>

                        </p>

                    </a>


                    <ul class="nav nav-treeview">


                        <!-- ALL PRODUCTS -->

                        <li class="nav-item">

                            <a
                                href="<?php echo $adminBase; ?>products/products.php"
                                class="nav-link"
                            >

                                <i class="far fa-circle nav-icon"></i>

                                <p>

                                    All Products

                                </p>

                            </a>

                        </li>


                        <!-- ADD PRODUCT -->

                        <li class="nav-item">

                            <a
                                href="<?php echo $adminBase; ?>products/add-product.php"
                                class="nav-link"
                            >

                                <i class="far fa-circle nav-icon"></i>

                                <p>

                                    Add Product

                                </p>

                            </a>

                        </li>


                    </ul>

                </li>


                <!-- =================================================
                     ORDERS
                ================================================== -->

                <li class="nav-item">


                    <a
                        href="<?php echo $adminBase; ?>orders/orders.php"
                        class="nav-link"
                    >

                        <i class="nav-icon fas fa-shopping-cart"></i>


                        <p>

                            Orders


                            <?php if ($pendingOrders > 0): ?>

                                <span
                                    class="right badge"
                                    style="
                                        background:#D8A15B;
                                        color:#4A2C1D;
                                    "
                                >

                                    <?php
                                    echo $pendingOrders;
                                    ?>

                                </span>

                            <?php endif; ?>


                        </p>

                    </a>

                </li>


                <!-- =================================================
                     RESERVATIONS
                ================================================== -->

                <li class="nav-item">


                    <a
                        href="<?php echo $adminBase; ?>reservations/reservations.php"
                        class="nav-link"
                    >

                        <i
                            class="nav-icon fas fa-calendar-alt"
                        ></i>


                        <p>

                            Reservations


                            <?php if (
                                $pendingReservations > 0
                            ): ?>

                                <span
                                    class="right badge"
                                    style="
                                        background:#D8A15B;
                                        color:#4A2C1D;
                                    "
                                >

                                    <?php
                                    echo $pendingReservations;
                                    ?>

                                </span>

                            <?php endif; ?>


                        </p>

                    </a>

                </li>


                <!-- =================================================
                     CUSTOMERS
                ================================================== -->

                <li class="nav-item">


                    <a
                        href="<?php echo $adminBase; ?>customers/customers.php"
                        class="nav-link"
                    >

                        <i
                            class="nav-icon fas fa-users"
                        ></i>


                        <p>

                            Customers

                        </p>

                    </a>

                </li>


                <!-- =================================================
                     SALES ANALYTICS
                ================================================== -->

                <li class="nav-item">


                    <a
                        href="<?php echo $adminBase; ?>analytics/sales.php"
                        class="nav-link"
                    >

                        <i
                            class="nav-icon fas fa-chart-line"
                        ></i>


                        <p>

                            Sales Analytics

                        </p>

                    </a>

                </li>


                <!-- =================================================
                     MESSAGES
                ================================================== -->

                <li class="nav-item">


                    <a
                        href="#"
                        class="nav-link"
                    >

                        <i
                            class="nav-icon fas fa-envelope"
                        ></i>


                        <p>

                            Messages


                            <?php if (
                                $unreadMessages > 0
                            ): ?>

                                <span
                                    class="right badge"
                                    style="
                                        background:#D8A15B;
                                        color:#4A2C1D;
                                        margin-right:20px;
                                    "
                                >

                                    <?php
                                    echo $unreadMessages;
                                    ?>

                                </span>

                            <?php endif; ?>


                            <i class="right fas fa-angle-left"></i>

                        </p>

                    </a>


                    <ul class="nav nav-treeview">


                        <!-- CONTACT MESSAGES -->

                        <li class="nav-item">

                            <a
                                href="<?php echo $adminBase; ?>messages/messages.php"
                                class="nav-link"
                            >

                                <i
                                    class="far fa-circle nav-icon"
                                ></i>


                                <p>

                                    Contact Messages

                                </p>

                            </a>

                        </li>


                        <!-- CUSTOMER FEEDBACK -->

                        <li class="nav-item">

                            <a
                                href="<?php echo $adminBase; ?>messages/feedback.php"
                                class="nav-link"
                            >

                                <i
                                    class="far fa-circle nav-icon"
                                ></i>


                                <p>

                                    Customer Feedback

                                </p>

                            </a>

                        </li>


                    </ul>

                </li>


                <!-- =================================================
                     VIEW WEBSITE
                ================================================== -->

                <li class="nav-item mt-3">


                    <a
                        href="<?php echo $adminBase; ?>../index.php"
                        class="nav-link"
                        target="_blank"
                        rel="noopener noreferrer"
                    >

                        <i
                            class="nav-icon fas fa-globe"
                        ></i>


                        <p>

                            View Website

                        </p>

                    </a>

                </li>


                <!-- =================================================
                     LOGOUT
                ================================================== -->

                <li class="nav-item">


                    <a
                        href="<?php echo $adminBase; ?>logout.php"
                        class="nav-link"
                    >

                        <i
                            class="nav-icon fas fa-sign-out-alt"
                        ></i>


                        <p>

                            Logout

                        </p>

                    </a>

                </li>


            </ul>

        </nav>

    </div>

</aside>