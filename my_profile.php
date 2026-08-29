<?php

/* =========================================================
   CAFFEINE & COVE
   MY PROFILE
========================================================= */

session_start();

require_once "include/config.php";


/* =========================================================
   LOGIN REQUIRED
========================================================= */

if (
    !isset($_SESSION["logged_in"]) ||
    $_SESSION["logged_in"] !== true
) {

    $_SESSION["login_required_message"] =
        "Please login to view your profile.";

    $_SESSION["redirect_after_login"] =
        "my_profile.php";

    header("Location: login.php");

    exit;
}


/* =========================================================
   GET LOGGED-IN USER ID
========================================================= */

$user_id = (int) ($_SESSION["user_id"] ?? 0);


if ($user_id <= 0) {

    header("Location: login.php");

    exit;
}


/* =========================================================
   GET USER PROFILE
========================================================= */

$sql = "
    SELECT
        id,
        full_name,
        username,
        email,
        mobile,
        created_at
    FROM users
    WHERE id = ?
    LIMIT 1
";


$stmt = mysqli_prepare($link, $sql);


if (!$stmt) {

    die("Unable to load profile.");

}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $user_id
);


mysqli_stmt_execute($stmt);


$result = mysqli_stmt_get_result($stmt);


$user = mysqli_fetch_assoc($result);


mysqli_stmt_close($stmt);


/* =========================================================
   USER NOT FOUND
========================================================= */

if (!$user) {

    session_destroy();

    header("Location: login.php");

    exit;
}


/* =========================================================
   ESCAPE FUNCTION
========================================================= */

function e($value)
{
    return htmlspecialchars(
        (string) $value,
        ENT_QUOTES,
        "UTF-8"
    );
}


/* =========================================================
   JOIN DATE
========================================================= */

$joined_date = "N/A";

if (!empty($user["created_at"])) {

    $joined_date = date(
        "d M Y",
        strtotime($user["created_at"])
    );
}

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
        My Profile | Caffeine &amp; Cove
    </title>


    <!-- =================================================
         PROFILE CSS
    ================================================== -->

    <link
        rel="stylesheet"
        href="css/my-profile.css"
    >


</head>


<body>


<?php

/* =========================================================
   HEADER
========================================================= */

if (file_exists("include/header.php")) {

    include "include/header.php";

}

?>


<main class="cove-profile-page">


    <div class="cove-profile-container">


        <!-- =================================================
             PAGE HEADING
        ================================================== -->

        <div class="cove-profile-heading">

            <h1>
                My Profile
            </h1>

            <p>
                Manage your Caffeine &amp; Cove account.
            </p>

        </div>


        <!-- =================================================
             PROFILE CARD
        ================================================== -->

        <div class="cove-profile-card">


            <!-- PROFILE TOP -->

            <div class="cove-profile-top">


                <div class="cove-profile-avatar">

                    <?php

                    echo strtoupper(
                        substr(
                            $user["full_name"],
                            0,
                            1
                        )
                    );

                    ?>

                </div>


                <div class="cove-profile-intro">

                    <h2>

                        <?php

                        echo e(
                            $user["full_name"]
                        );

                        ?>

                    </h2>


                    <p>

                        @<?php

                        echo e(
                            $user["username"]
                        );

                        ?>

                    </p>

                </div>


            </div>


            <!-- =================================================
                 PROFILE DETAILS
            ================================================== -->

            <div class="cove-profile-details">


                <!-- FULL NAME -->

                <div class="cove-profile-detail">

                    <span class="cove-profile-label">
                        Full Name
                    </span>

                    <span class="cove-profile-value">

                        <?php

                        echo e(
                            $user["full_name"]
                        );

                        ?>

                    </span>

                </div>


                <!-- USERNAME -->

                <div class="cove-profile-detail">

                    <span class="cove-profile-label">
                        Username
                    </span>

                    <span class="cove-profile-value">

                        <?php

                        echo e(
                            $user["username"]
                        );

                        ?>

                    </span>

                </div>


                <!-- EMAIL -->

                <div class="cove-profile-detail">

                    <span class="cove-profile-label">
                        Email Address
                    </span>

                    <span class="cove-profile-value">

                        <?php

                        echo e(
                            $user["email"]
                        );

                        ?>

                    </span>

                </div>


                <!-- MOBILE -->

                <div class="cove-profile-detail">

                    <span class="cove-profile-label">
                        Mobile Number
                    </span>

                    <span class="cove-profile-value">

                        <?php

                        echo e(
                            $user["mobile"]
                        );

                        ?>

                    </span>

                </div>


                <!-- JOINED DATE -->

                <div class="cove-profile-detail">

                    <span class="cove-profile-label">
                        Member Since
                    </span>

                    <span class="cove-profile-value">

                        <?php

                        echo e(
                            $joined_date
                        );

                        ?>

                    </span>

                </div>


            </div>


            <!-- =================================================
                 PROFILE ACTIONS
            ================================================== -->

            <div class="cove-profile-actions">


                <a
                    href="my_orders.php"
                    class="cove-profile-btn"
                >
                    🛒 My Orders
                </a>


                <a
                    href="my_reservations.php"
                    class="cove-profile-btn"
                >
                    📅 My Reservations
                </a>


            </div>


        </div>


    </div>


</main>


<?php

/* =========================================================
   FOOTER
========================================================= */

if (file_exists("include/footer.php")) {

    include "include/footer.php";

}

?>


</body>

</html>