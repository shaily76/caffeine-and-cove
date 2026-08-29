<?php

/* =========================================================
   CAFFEINE & COVE
   ADMIN - RESERVATIONS MANAGEMENT
========================================================= */

session_start();

require_once "../admin_auth.php";
require_once "../../include/config.php";


/* =========================================================
   CSRF TOKEN
========================================================= */

if (
    empty($_SESSION["admin_reservation_csrf"])
) {

    $_SESSION["admin_reservation_csrf"] =
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
   TIME AGO
========================================================= */

function timeAgo($datetime)
{
    if (empty($datetime)) {
        return "";
    }

    $timestamp = strtotime($datetime);

    if ($timestamp === false) {
        return "";
    }

    $difference =
        time() - $timestamp;


    if ($difference < 0) {
        return "Just now";
    }


    if ($difference < 60) {
        return "Just now";
    }


    if ($difference < 3600) {

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


    if ($difference < 86400) {

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


    if ($difference < 2592000) {

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


    if ($difference < 31536000) {

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
   SUCCESS / ERROR FROM REDIRECT
========================================================= */

if (
    isset($_GET["success"])
) {

    if (
        $_GET["success"] === "updated"
    ) {

        $success =
            "Reservation status updated successfully.";

    }

}


if (
    isset($_GET["error"])
) {

    switch (
        $_GET["error"]
    ) {

        case "invalid_id":

            $error =
                "Invalid reservation ID.";

            break;


        case "invalid_status":

            $error =
                "Invalid reservation status.";

            break;


        case "invalid_token":

            $error =
                "Security validation failed. Please try again.";

            break;


        case "not_found":

            $error =
                "Reservation not found.";

            break;


        case "update_failed":

            $error =
                "Unable to update reservation status.";

            break;


        case "database":

            $error =
                "A database error occurred.";

            break;


        default:

            $error =
                "Something went wrong.";

            break;

    }

}


/* =========================================================
   UPDATE STATUS
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
) {


    /* -----------------------------------------------------
       CSRF CHECK
    ----------------------------------------------------- */

    $csrfToken =
        $_POST["csrf_token"] ?? "";


    $sessionToken =
        $_SESSION["admin_reservation_csrf"] ?? "";


    if (
        $sessionToken === "" ||
        $csrfToken === "" ||
        !hash_equals(
            $sessionToken,
            $csrfToken
        )
    ) {

        header(
            "Location: reservations.php?error=invalid_token"
        );

        exit;

    }


    /* -----------------------------------------------------
       RESERVATION ID
    ----------------------------------------------------- */

    $reservationId =
        trim(
            $_POST["reservation_id"] ?? ""
        );


    if (
        !ctype_digit($reservationId) ||
        (int)$reservationId <= 0
    ) {

        header(
            "Location: reservations.php?error=invalid_id"
        );

        exit;

    }


    $reservationId =
        (int)$reservationId;


    /* -----------------------------------------------------
       STATUS
    ----------------------------------------------------- */

    $newStatus =
        trim(
            $_POST["status"] ?? ""
        );


    $allowedStatuses = [

        "pending",

        "confirmed",

        "completed",

        "cancelled"

    ];


    if (
        !in_array(
            $newStatus,
            $allowedStatuses,
            true
        )
    ) {

        header(
            "Location: reservations.php?error=invalid_status"
        );

        exit;

    }


    /* -----------------------------------------------------
       CHECK RESERVATION EXISTS
    ----------------------------------------------------- */

    $checkSql = "
        SELECT id
        FROM reservations
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
            "Admin reservation check failed: " .
            mysqli_error($link)
        );

        header(
            "Location: reservations.php?error=database"
        );

        exit;

    }


    mysqli_stmt_bind_param(
        $checkStmt,
        "i",
        $reservationId
    );


    if (
        !mysqli_stmt_execute(
            $checkStmt
        )
    ) {

        mysqli_stmt_close(
            $checkStmt
        );

        header(
            "Location: reservations.php?error=database"
        );

        exit;

    }


    $checkResult =
        mysqli_stmt_get_result(
            $checkStmt
        );


    $exists =
        $checkResult &&
        mysqli_num_rows(
            $checkResult
        ) === 1;


    mysqli_stmt_close(
        $checkStmt
    );


    if (
        !$exists
    ) {

        header(
            "Location: reservations.php?error=not_found"
        );

        exit;

    }


    /* -----------------------------------------------------
       UPDATE
    ----------------------------------------------------- */

    $sql = "
        UPDATE reservations
        SET status = ?
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
            "Admin reservation update prepare failed: " .
            mysqli_error($link)
        );

        header(
            "Location: reservations.php?error=database"
        );

        exit;

    }


    mysqli_stmt_bind_param(
        $stmt,
        "si",
        $newStatus,
        $reservationId
    );


    if (
        !mysqli_stmt_execute(
            $stmt
        )
    ) {

        error_log(
            "Admin reservation update failed: " .
            mysqli_stmt_error($stmt)
        );

        mysqli_stmt_close(
            $stmt
        );

        header(
            "Location: reservations.php?error=update_failed"
        );

        exit;

    }


    mysqli_stmt_close(
        $stmt
    );


    /* -----------------------------------------------------
       SUCCESS
    ----------------------------------------------------- */

    header(
        "Location: reservations.php?success=updated"
    );

    exit;

}


/* =========================================================
   GET RESERVATIONS
========================================================= */

$sql = "
    SELECT
        id,
        user_id,
        guest_name,
        phone,
        email,
        reservation_date,
        reservation_time,
        guests,
        special_request,
        status,
        created_at
    FROM reservations
    ORDER BY id ASC
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
        "Admin reservations list failed: " .
        mysqli_error($link)
    );

    $error =
        "Unable to load reservations right now.";

}


/* =========================================================
   HEADER
========================================================= */

include "../includes/header.php";

include "../includes/sidebar.php";

?>


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
                            class="fas fa-calendar-alt mr-2"
                            style="color:#7B4728;"
                        ></i>

                        Reservations

                    </h1>

                </div>


                <div class="col-sm-6">

                    <ol
                        class="breadcrumb float-sm-right"
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
                            class="breadcrumb-item active"
                        >

                            Reservations

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
                    class="alert alert-success"
                >

                    <i
                        class="fas fa-check-circle mr-2"
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
                    class="alert alert-danger"
                >

                    <i
                        class="fas fa-exclamation-circle mr-2"
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
                 RESERVATIONS CARD
            ================================================== -->

            <div class="card">


                <div class="card-header">


                    <h3 class="card-title">

                        <i
                            class="fas fa-list mr-2"
                        ></i>

                        All Reservations

                    </h3>


                    <div
                        class="card-tools"
                    >

                        <span
                            class="badge badge-gold"
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

                            Reservations

                        </span>

                    </div>

                </div>


                <div
                    class="card-body p-0"
                >

                    <div
                        class="table-responsive"
                    >


                        <table
                            class="table table-hover mb-0"
                        >


                            <thead>

                                <tr>

                                    <th>
                                        ID
                                    </th>

                                    <th>
                                        Guest
                                    </th>

                                    <th>
                                        Date &amp; Time
                                    </th>

                                    <th>
                                        Guests
                                    </th>

                                    <th>
                                        Special Request
                                    </th>

                                    <th>
                                        Status
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
                                    $reservation =
                                    mysqli_fetch_assoc(
                                        $result
                                    )
                                ): ?>


                                    <?php

                                    $reservationId =
                                        (int)
                                        $reservation["id"];


                                    $guestName =
                                        (string)
                                        $reservation[
                                            "guest_name"
                                        ];


                                    $phone =
                                        (string)
                                        $reservation[
                                            "phone"
                                        ];


                                    $email =
                                        (string)
                                        $reservation[
                                            "email"
                                        ];


                                    $reservationDate =
                                        (string)
                                        $reservation[
                                            "reservation_date"
                                        ];


                                    $reservationTime =
                                        (string)
                                        $reservation[
                                            "reservation_time"
                                        ];


                                    $guestCount =
                                        (int)
                                        $reservation[
                                            "guests"
                                        ];


                                    $specialRequest =
                                        trim(
                                            (string)
                                            $reservation[
                                                "special_request"
                                            ]
                                        );


                                    $status =
                                        strtolower(
                                            trim(
                                                (string)
                                                $reservation[
                                                    "status"
                                                ]
                                            )
                                        );


                                    $createdAt =
                                        (string)
                                        $reservation[
                                            "created_at"
                                        ];


                                    /* DATE */

                                    $formattedDate =
                                        "-";


                                    if (
                                        $reservationDate !== ""
                                    ) {

                                        $dateTimestamp =
                                            strtotime(
                                                $reservationDate
                                            );


                                        if (
                                            $dateTimestamp !== false
                                        ) {

                                            $formattedDate =
                                                date(
                                                    "d M Y",
                                                    $dateTimestamp
                                                );

                                        }

                                    }


                                    /* TIME */

                                    $formattedTime =
                                        "-";


                                    if (
                                        $reservationTime !== ""
                                    ) {

                                        $timeTimestamp =
                                            strtotime(
                                                $reservationTime
                                            );


                                        if (
                                            $timeTimestamp !== false
                                        ) {

                                            $formattedTime =
                                                date(
                                                    "h:i A",
                                                    $timeTimestamp
                                                );

                                        }

                                    }


                                    $timeAgo =
                                        timeAgo(
                                            $createdAt
                                        );


                                    /* SHORT REQUEST */

                                    $shortRequest =
                                        $specialRequest;


                                    if (
                                        strlen(
                                            $shortRequest
                                        ) > 45
                                    ) {

                                        $shortRequest =
                                            substr(
                                                $shortRequest,
                                                0,
                                                45
                                            ) .
                                            "...";

                                    }


                                    /* STATUS STYLE */

                                    switch (
                                        $status
                                    ) {

                                        case "pending":

                                            $statusClass =
                                                "badge-warning";

                                            $statusIcon =
                                                "fa-clock";

                                            break;


                                        case "confirmed":

                                            $statusClass =
                                                "badge-gold";

                                            $statusIcon =
                                                "fa-check";

                                            break;


                                        case "completed":

                                            $statusClass =
                                                "badge-success";

                                            $statusIcon =
                                                "fa-check-circle";

                                            break;


                                        case "cancelled":

                                            $statusClass =
                                                "badge-danger";

                                            $statusIcon =
                                                "fa-times";

                                            break;


                                        default:

                                            $statusClass =
                                                "badge-secondary";

                                            $statusIcon =
                                                "fa-question";

                                            break;

                                    }

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

                                                echo
                                                    $reservationId;

                                                ?>

                                            </strong>

                                        </td>


                                        <!-- GUEST -->

                                        <td>

                                            <strong
                                                style="
                                                    color:#4A2C1D;
                                                "
                                            >

                                                <?php

                                                echo htmlspecialchars(
                                                    $guestName,
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                );

                                                ?>

                                            </strong>


                                            <div
                                                style="
                                                    font-size:12px;
                                                    color:#8A7468;
                                                "
                                            >

                                                <?php

                                                echo htmlspecialchars(
                                                    $email,
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                );

                                                ?>

                                            </div>


                                            <?php if (
                                                $phone !== ""
                                            ): ?>

                                                <div
                                                    style="
                                                        font-size:12px;
                                                        color:#8A7468;
                                                    "
                                                >

                                                    <?php

                                                    echo htmlspecialchars(
                                                        $phone,
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    );

                                                    ?>

                                                </div>

                                            <?php endif; ?>

                                        </td>


                                        <!-- DATE / TIME -->

                                        <td>

                                            <strong
                                                style="
                                                    color:#4A2C1D;
                                                    font-size:16px;
                                                "
                                            >

                                                <?php

                                                echo htmlspecialchars(
                                                    $formattedDate,
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                );

                                                ?>

                                            </strong>


                                            <div
                                                style="
                                                    font-size:12px;
                                                    color:#8A7468;
                                                    margin-top:3px;
                                                "
                                            >

                                                <i
                                                    class="far fa-clock mr-1"
                                                ></i>

                                                <?php

                                                echo htmlspecialchars(
                                                    $formattedTime,
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                );

                                                ?>

                                            </div>


                                            <?php if (
                                                $timeAgo !== ""
                                            ): ?>

                                                <div
                                                    style="
                                                        font-size:11px;
                                                        color:#B0784F;
                                                        margin-top:4px;
                                                        font-weight:500;
                                                    "
                                                >

                                                    <i
                                                        class="far fa-calendar-plus mr-1"
                                                    ></i>

                                                    <?php

                                                    echo htmlspecialchars(
                                                        $timeAgo,
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    );

                                                    ?>

                                                </div>

                                            <?php endif; ?>

                                        </td>


                                        <!-- GUESTS -->

                                        <td>

                                            <span
                                                style="
                                                    background:#F5E8DA;
                                                    color:#7B4728;
                                                    padding:7px 11px;
                                                    border-radius:20px;
                                                    font-weight:600;
                                                    font-size:12px;
                                                "
                                            >

                                                <i
                                                    class="fas fa-users mr-1"
                                                ></i>

                                                <?php

                                                echo $guestCount;

                                                ?>

                                            </span>

                                        </td>


                                        <!-- SPECIAL REQUEST -->

                                        <td>

                                            <?php if (
                                                $shortRequest !== ""
                                            ): ?>

                                                <span
                                                    style="
                                                        color:#6F5548;
                                                        font-size:13px;
                                                    "
                                                >

                                                    <?php

                                                    echo htmlspecialchars(
                                                        $shortRequest,
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    );

                                                    ?>

                                                </span>

                                            <?php else: ?>

                                                <span
                                                    style="
                                                        color:#B3A49A;
                                                        font-size:13px;
                                                    "
                                                >

                                                    No special request

                                                </span>

                                            <?php endif; ?>

                                        </td>


                                        <!-- STATUS -->

                                        <td>

                                            <span
                                                class="
                                                    badge
                                                    <?php
                                                    echo $statusClass;
                                                    ?>
                                                "
                                                style="
                                                    padding:7px 9px;
                                                "
                                            >

                                                <i
                                                    class="
                                                        fas
                                                        <?php
                                                        echo $statusIcon;
                                                        ?>
                                                        mr-1
                                                    "
                                                ></i>

                                                <?php

                                                echo htmlspecialchars(
                                                    ucfirst(
                                                        $status
                                                    ),
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                );

                                                ?>

                                            </span>

                                        </td>


                                        <!-- ACTION -->

                                        <td>

                                            <div
                                                class="
                                                    d-flex
                                                    justify-content-center
                                                "
                                                style="
                                                    gap:6px;
                                                "
                                            >


                                                <!-- VIEW -->

                                                <a
                                                    href="
                                                        reservation-details.php?id=<?php
                                                        echo $reservationId;
                                                        ?>
                                                    "
                                                    class="
                                                        btn
                                                        btn-sm
                                                        btn-gold
                                                    "
                                                    title="
                                                        View Reservation
                                                    "
                                                >

                                                    <i
                                                        class="fas fa-eye"
                                                    ></i>

                                                </a>


                                                <!-- STATUS -->

                                                <button
                                                    type="button"
                                                    class="
                                                        btn
                                                        btn-sm
                                                        btn-coffee
                                                    "
                                                    data-toggle="modal"
                                                    data-target="#statusModal"
                                                    data-id="<?php
                                                    echo $reservationId;
                                                    ?>"
                                                    data-guest="<?php
                                                    echo htmlspecialchars(
                                                        $guestName,
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    );
                                                    ?>"
                                                    data-status="<?php
                                                    echo htmlspecialchars(
                                                        $status,
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    );
                                                    ?>"
                                                    title="
                                                        Update Status
                                                    "
                                                >

                                                    <i
                                                        class="fas fa-edit"
                                                    ></i>

                                                </button>

                                            </div>

                                        </td>

                                    </tr>


                                <?php endwhile; ?>


                            <?php else: ?>


                                <tr>

                                    <td
                                        colspan="7"
                                        class="text-center"
                                        style="
                                            padding:70px 20px;
                                        "
                                    >

                                        <i
                                            class="
                                                fas
                                                fa-calendar-alt
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

                                            No Reservations Found

                                        </h4>


                                        <p
                                            style="
                                                color:#8A7468;
                                            "
                                        >

                                            There are currently
                                            no reservations.

                                        </p>

                                    </td>

                                </tr>

                            <?php endif; ?>


                            </tbody>

                        </table>

                    </div>

                </div>


                <!-- FOOTER -->

                <?php if (
                    $result !== false &&
                    mysqli_num_rows(
                        $result
                    ) > 0
                ): ?>

                    <div class="card-footer">

                        <span
                            style="
                                color:#8A7468;
                                font-size:13px;
                            "
                        >

                            Total Reservations:

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


<!-- =========================================================
     SINGLE STATUS MODAL
========================================================= -->

<div
    class="modal fade"
    id="statusModal"
    tabindex="-1"
    role="dialog"
    aria-hidden="true"
>

    <div
        class="modal-dialog"
        role="document"
    >

        <div class="modal-content">


            <div class="modal-header">

                <h5 class="modal-title">

                    Update Reservation Status

                </h5>


                <button
                    type="button"
                    class="close"
                    data-dismiss="modal"
                    aria-label="Close"
                >

                    <span>
                        &times;
                    </span>

                </button>

            </div>


            <form
                method="POST"
                action="reservations.php"
            >

                <div class="modal-body">


                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?php

                        echo htmlspecialchars(
                            $_SESSION[
                                "admin_reservation_csrf"
                            ],
                            ENT_QUOTES,
                            "UTF-8"
                        );

                        ?>"
                    >


                    <input
                        type="hidden"
                        name="reservation_id"
                        id="modalReservationId"
                        value=""
                    >


                    <div
                        class="form-group"
                    >

                        <label>
                            Guest
                        </label>


                        <input
                            type="text"
                            id="modalGuestName"
                            class="form-control"
                            readonly
                        >

                    </div>


                    <div
                        class="form-group"
                    >

                        <label
                            for="modalStatus"
                        >
                            Status
                        </label>


                        <select
                            name="status"
                            id="modalStatus"
                            class="form-control"
                            required
                        >

                            <option
                                value="pending"
                            >
                                Pending
                            </option>

                            <option
                                value="confirmed"
                            >
                                Confirmed
                            </option>

                            <option
                                value="completed"
                            >
                                Completed
                            </option>

                            <option
                                value="cancelled"
                            >
                                Cancelled
                            </option>

                        </select>

                    </div>

                </div>


                <div class="modal-footer">


                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-dismiss="modal"
                    >

                        Cancel

                    </button>


                    <button
                        type="submit"
                        class="btn btn-coffee"
                    >

                        <i
                            class="fas fa-save mr-2"
                        ></i>

                        Update Status

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<!-- =========================================================
     MODAL SCRIPT
========================================================= -->

<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

        var statusButtons =
            document.querySelectorAll(
                '[data-target="#statusModal"]'
            );


        statusButtons.forEach(
            function (button) {

                button.addEventListener(
                    "click",
                    function () {

                        var id =
                            this.getAttribute(
                                "data-id"
                            );


                        var guest =
                            this.getAttribute(
                                "data-guest"
                            );


                        var status =
                            this.getAttribute(
                                "data-status"
                            );


                        document.getElementById(
                            "modalReservationId"
                        ).value = id;


                        document.getElementById(
                            "modalGuestName"
                        ).value = guest;


                        document.getElementById(
                            "modalStatus"
                        ).value = status;

                    }
                );

            }
        );

    }
);

</script>


<?php

include "../includes/footer.php";

?>