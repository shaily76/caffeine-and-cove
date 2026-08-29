<?php

/* =========================================================
   CAFFEINE & COVE
   ADMIN - CUSTOMERS MANAGEMENT
   ========================================================= */

require_once "../admin_auth.php";
/* =========================================================
   DATABASE CONNECTION
   ========================================================= */

require_once "../../include/config.php";


/* =========================================================
   VARIABLES
   ========================================================= */

$success = "";
$error = "";


/* =========================================================
   SUCCESS / ERROR MESSAGES
   ========================================================= */

if (isset($_GET["success"])) {

    switch ($_GET["success"]) {

        case "updated":
            $success = "Customer updated successfully.";
            break;

        case "deleted":
            $success = "Customer deleted successfully.";
            break;

        default:
            $success = "";
            break;
    }

}


if (isset($_GET["error"])) {

    switch ($_GET["error"]) {

        case "invalid_id":
            $error = "Invalid customer ID.";
            break;

        case "not_found":
            $error = "Customer not found.";
            break;

        case "update_failed":
            $error = "Unable to update customer.";
            break;

        case "delete_failed":
            $error = "Unable to delete customer.";
            break;

        case "database":
            $error = "A database error occurred.";
            break;

        default:
            $error = "Something went wrong.";
            break;
    }

}


/* =========================================================
   GET CUSTOMERS
   ========================================================= */

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
    ORDER BY id DESC
";


$result =
    mysqli_query(
        $link,
        $sql
    );


if ($result === false) {

    $error =
        "Unable to load customers: " .
        mysqli_error($link);

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


                <!-- TITLE -->

                <div class="col-sm-6">

                    <h1 class="m-0">

                        <i
                            class="fas fa-users mr-2"
                            style="color:#7B4728;"
                        ></i>

                        Customers

                    </h1>

                </div>


                <!-- BREADCRUMB -->

                <div class="col-sm-6">

                    <ol class="breadcrumb float-sm-right">

                        <li class="breadcrumb-item">

                            <a href="../dashboard.php">
                                Dashboard
                            </a>

                        </li>

                        <li class="breadcrumb-item active">

                            Customers

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
                 SUCCESS
            ================================================== -->

            <?php if ($success !== ""): ?>

                <div class="alert alert-success">

                    <i class="fas fa-check-circle mr-2"></i>

                    <?php
                    echo htmlspecialchars($success);
                    ?>

                    <button
                        type="button"
                        class="close"
                        data-dismiss="alert"
                    >

                        <span>&times;</span>

                    </button>

                </div>

            <?php endif; ?>


            <!-- =================================================
                 ERROR
            ================================================== -->

            <?php if ($error !== ""): ?>

                <div class="alert alert-danger">

                    <i class="fas fa-exclamation-circle mr-2"></i>

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                    <button
                        type="button"
                        class="close"
                        data-dismiss="alert"
                    >

                        <span>&times;</span>

                    </button>

                </div>

            <?php endif; ?>


            <!-- =================================================
                 CUSTOMERS CARD
            ================================================== -->

            <div class="card">


                <!-- CARD HEADER -->

                <div class="card-header">

                    <h3 class="card-title">

                        <i class="fas fa-user-friends mr-2"></i>

                        Registered Customers

                    </h3>


                    <div class="card-tools">

                        <span class="badge badge-gold">

                            <?php

                            if ($result !== false) {

                                echo mysqli_num_rows($result);

                            } else {

                                echo "0";

                            }

                            ?>

                            Customers

                        </span>

                    </div>

                </div>


                <!-- CARD BODY -->

                <div class="card-body p-0">


                    <div class="table-responsive">


                        <table
                            class="table table-hover mb-0"
                        >


                            <!-- =================================================
                                 TABLE HEADER
                            ================================================== -->

                            <thead>

                                <tr>

                                    <th style="width:70px;">
                                        ID
                                    </th>

                                    <th>
                                        Customer
                                    </th>

                                    <th>
                                        Username
                                    </th>

                                    <th>
                                        Email
                                    </th>

                                    <th>
                                        Mobile
                                    </th>

                                    <th>
                                        Registered
                                    </th>

                                    <th
                                        class="text-center"
                                        style="width:90px;"
                                    >
                                        Action
                                    </th>

                                </tr>

                            </thead>


                            <!-- =================================================
                                 TABLE BODY
                            ================================================== -->

                            <tbody>


                            <?php if (
                                $result !== false &&
                                mysqli_num_rows($result) > 0
                            ): ?>


                                <?php while (
                                    $customer =
                                    mysqli_fetch_assoc($result)
                                ): ?>


                                    <?php

                                    $customerId =
                                        (int)$customer["id"];

                                    $fullName =
                                        (string)$customer[
                                            "full_name"
                                        ];

                                    $username =
                                        (string)$customer[
                                            "username"
                                        ];

                                    $email =
                                        (string)$customer[
                                            "email"
                                        ];

                                    $mobile =
                                        (string)$customer[
                                            "mobile"
                                        ];

                                    $createdAt =
                                        (string)$customer[
                                            "created_at"
                                        ];


                                    $registeredDate =
                                        $createdAt !== ""
                                            ? date(
                                                "d M Y",
                                                strtotime(
                                                    $createdAt
                                                )
                                            )
                                            : "-";

                                    ?>


                                    <tr>


                                        <!-- ID -->

                                        <td>

                                            <strong
                                                style="
                                                    color:#7B4728;
                                                "
                                            >

                                                #<?php
                                                echo $customerId;
                                                ?>

                                            </strong>

                                        </td>


                                        <!-- CUSTOMER -->

                                        <td>

                                            <div
                                                class="d-flex align-items-center"
                                            >


                                                <!-- PROFILE ICON -->

                                                <div
                                                    style="
                                                        width:42px;
                                                        height:42px;
                                                        border-radius:50%;
                                                        background:#F5E8DA;
                                                        display:flex;
                                                        align-items:center;
                                                        justify-content:center;
                                                        margin-right:10px;
                                                    "
                                                >

                                                    <i
                                                        class="fas fa-user"
                                                        style="
                                                            color:#7B4728;
                                                        "
                                                    ></i>

                                                </div>


                                                <!-- NAME -->

                                                <div>

                                                    <strong
                                                        style="
                                                            color:#4A2C1D;
                                                        "
                                                    >

                                                        <?php
                                                        echo htmlspecialchars(
                                                            $fullName
                                                        );
                                                        ?>

                                                    </strong>

                                                </div>

                                            </div>

                                        </td>


                                        <!-- USERNAME -->

                                        <td>

                                            <span
                                                style="
                                                    color:#6F5548;
                                                "
                                            >

                                                @<?php
                                                echo htmlspecialchars(
                                                    $username
                                                );
                                                ?>

                                            </span>

                                        </td>


                                        <!-- EMAIL -->

                                        <td>

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

                                        </td>


                                        <!-- MOBILE -->

                                        <td>

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
                                                        color:#B3A49A;
                                                    "
                                                >
                                                    Not provided
                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <!-- REGISTERED -->

                                        <td>

                                            <span
                                                style="
                                                    color:#6F5548;
                                                    font-size:13px;
                                                "
                                            >

                                                <?php
                                                echo htmlspecialchars(
                                                    $registeredDate
                                                );
                                                ?>

                                            </span>

                                        </td>


                                        <!-- ACTION -->

                                        <td
                                            class="text-center"
                                        >

                                            <a
                                                href="customer-details.php?id=<?php
                                                echo $customerId;
                                                ?>"
                                                class="btn btn-sm btn-gold"
                                                title="View Customer"
                                            >

                                                <i
                                                    class="fas fa-eye"
                                                ></i>

                                            </a>

                                        </td>


                                    </tr>


                                <?php endwhile; ?>


                            <?php else: ?>


                                <!-- =================================================
                                     NO CUSTOMERS
                                ================================================== -->

                                <tr>

                                    <td
                                        colspan="7"
                                        class="text-center"
                                        style="
                                            padding:70px 20px;
                                        "
                                    >

                                        <i
                                            class="fas fa-users"
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

                                            No Customers Found

                                        </h4>


                                        <p
                                            style="
                                                color:#8A7468;
                                            "
                                        >

                                            No registered customers
                                            are currently available.

                                        </p>

                                    </td>

                                </tr>


                            <?php endif; ?>


                            </tbody>

                        </table>

                    </div>

                </div>


                <!-- =================================================
                     CARD FOOTER
                ================================================== -->

                <?php if (
                    $result !== false &&
                    mysqli_num_rows($result) > 0
                ): ?>

                    <div class="card-footer">

                        <span
                            style="
                                color:#8A7468;
                                font-size:13px;
                            "
                        >

                            Total Customers:

                            <strong
                                style="
                                    color:#7B4728;
                                "
                            >

                                <?php
                                echo mysqli_num_rows($result);
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

/* =========================================================
   COMMON FOOTER
   ========================================================= */

include "../includes/footer.php";

?>