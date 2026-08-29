<?php

/* =========================================================
   CAFFEINE & COVE
   MY RESERVATIONS
========================================================= */

session_start();

require_once __DIR__ . "/include/config.php";


/* =========================================================
   LOGIN REQUIRED
========================================================= */

if (
    !isset($_SESSION["logged_in"]) ||
    $_SESSION["logged_in"] !== true
) {

    $_SESSION["login_required_message"] =
        "Please login to view your reservations.";

    $_SESSION["redirect_after_login"] =
        "my_reservations.php";

    header("Location: login.php");

    exit;
}


/* =========================================================
   USER ID
========================================================= */

$user_id =
    (int)(
        $_SESSION["user_id"] ?? 0
    );


if ($user_id <= 0) {

    $_SESSION["login_required_message"] =
        "Please login to view your reservations.";

    header("Location: login.php");

    exit;
}


/* =========================================================
   HELPER
========================================================= */

function reservationEscape($value)
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

function reservationStatusClass($status)
{
    $status =
        strtolower(
            trim(
                (string)$status
            )
        );

    switch ($status) {

        case "confirmed":
            return "reservation-confirmed";

        case "completed":
            return "reservation-completed";

        case "cancelled":
        case "canceled":
            return "reservation-cancelled";

        case "pending":
        default:
            return "reservation-pending";
    }
}


/* =========================================================
   SUCCESS / ERROR MESSAGE
========================================================= */

$reservation_error =
    $_SESSION["reservation_error"] ?? "";

$reservation_success =
    $_SESSION["reservation_success"] ?? "";

unset(
    $_SESSION["reservation_error"]
);

unset(
    $_SESSION["reservation_success"]
);


/* =========================================================
   LOAD RESERVATIONS
========================================================= */

$sql = "
    SELECT
        id,
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
    WHERE user_id = ?
    ORDER BY
        reservation_date DESC,
        reservation_time DESC,
        id DESC
";


$stmt =
    mysqli_prepare(
        $link,
        $sql
    );


$result = false;


if ($stmt) {

    mysqli_stmt_bind_param(
        $stmt,
        "i",
        $user_id
    );


    if (
        mysqli_stmt_execute($stmt)
    ) {

        $result =
            mysqli_stmt_get_result(
                $stmt
            );

    } else {

        error_log(
            "Caffeine & Cove: Reservation query failed: " .
            mysqli_stmt_error($stmt)
        );

    }

} else {

    error_log(
        "Caffeine & Cove: Reservation query prepare failed: " .
        mysqli_error($link)
    );

}


$reservation_count = 0;


if (
    $result instanceof mysqli_result
) {

    $reservation_count =
        mysqli_num_rows($result);

}

?>


<?php include __DIR__ . "/include/header.php"; ?>


<style>

/* =========================================================
   PAGE
========================================================= */

.cove-reservation-page {

    min-height: 70vh;

    padding:
        60px 20px 80px;

    background:
        #FFF8F2;

}


.cove-reservation-container {

    width: 100%;

    max-width: 1100px;

    margin: 0 auto;

}


/* =========================================================
   HEADING
========================================================= */

.cove-reservation-heading {

    text-align: center;

    margin-bottom: 40px;

}


.cove-reservation-heading h1 {

    margin: 0 0 10px;

    color: #4A2C1D;

    font-size: 38px;

    font-weight: 700;

}


.cove-reservation-heading p {

    margin: 0;

    color: #777;

    font-size: 14px;

}


.cove-reservation-count {

    display: inline-block;

    margin-top: 14px;

    padding: 7px 15px;

    border-radius: 30px;

    background: #F5E8DA;

    color: #4A2C1D;

    font-size: 12px;

    font-weight: 600;

}


/* =========================================================
   MESSAGES
========================================================= */

.cove-reservation-message {

    max-width: 700px;

    margin: 0 auto 25px;

    padding: 14px 18px;

    border-radius: 10px;

    font-size: 14px;

}


.cove-reservation-error {

    background: #F8D7DA;

    color: #842029;

    border: 1px solid #F1BFC4;

}


.cove-reservation-success {

    background: #DFF3E4;

    color: #276738;

    border: 1px solid #B9DFC1;

}


/* =========================================================
   EMPTY STATE
========================================================= */

.cove-no-reservations {

    max-width: 600px;

    margin: 50px auto;

    padding: 45px 30px;

    text-align: center;

    background: #FFFFFF;

    border: 1px solid #EADFD6;

    border-radius: 18px;

    box-shadow:
        0 10px 30px
        rgba(74, 44, 29, 0.08);

}


.cove-no-reservation-icon {

    font-size: 48px;

    margin-bottom: 15px;

}


.cove-no-reservations h2 {

    margin: 0 0 10px;

    color: #4A2C1D;

}


.cove-no-reservations p {

    max-width: 450px;

    margin: 0 auto 25px;

    color: #777;

    line-height: 1.7;

    font-size: 14px;

}


.cove-book-reservation-btn {

    display: inline-block;

    padding: 12px 25px;

    border-radius: 8px;

    text-decoration: none;

    background: #4A2C1D;

    color: #FFFFFF;

    font-size: 14px;

    font-weight: 600;

    transition: 0.25s ease;

}


.cove-book-reservation-btn:hover {

    background: #8B4513;

    color: #FFFFFF;

}


/* =========================================================
   RESERVATION GRID
========================================================= */

.cove-reservation-list {

    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 24px;

}


/* =========================================================
   CARD
========================================================= */

.cove-reservation-card {

    overflow: hidden;

    background: #FFFFFF;

    border: 1px solid #EADFD6;

    border-radius: 16px;

    box-shadow:
        0 8px 25px
        rgba(74, 44, 29, 0.07);

    transition:
        transform 0.25s ease,
        box-shadow 0.25s ease;

}


.cove-reservation-card:hover {

    transform: translateY(-3px);

    box-shadow:
        0 14px 32px
        rgba(74, 44, 29, 0.12);

}


/* =========================================================
   CARD HEADER
========================================================= */

.cove-reservation-card-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    gap: 15px;

    padding: 18px 20px;

    background: #4A2C1D;

    color: #FFFFFF;

}


.cove-reservation-number {

    font-size: 17px;

    font-weight: 600;

}


.cove-reservation-created {

    font-size: 12px;

    color: #F5E8DA;

}


/* =========================================================
   CARD BODY
========================================================= */

.cove-reservation-card-body {

    padding: 22px;

}


/* =========================================================
   DETAILS
========================================================= */

.cove-reservation-grid {

    display: grid;

    grid-template-columns:
        repeat(2, minmax(0, 1fr));

    gap: 14px;

}


.cove-reservation-box {

    min-width: 0;

    padding: 13px;

    background: #FFF8F2;

    border: 1px solid #EADFD6;

    border-radius: 10px;

}


.cove-reservation-label {

    display: block;

    margin-bottom: 5px;

    color: #888;

    font-size: 11px;

}


.cove-reservation-value {

    display: block;

    color: #4A2C1D;

    font-size: 14px;

    font-weight: 600;

    overflow-wrap: anywhere;

}


/* =========================================================
   SPECIAL REQUEST
========================================================= */

.cove-special-request {

    margin-top: 15px;

    padding: 13px;

    background: #FFF8F2;

    border: 1px solid #EADFD6;

    border-radius: 10px;

}


.cove-special-request-text {

    color: #4A2C1D;

    font-size: 13px;

    line-height: 1.6;

    overflow-wrap: anywhere;

}


/* =========================================================
   STATUS
========================================================= */

.cove-reservation-status {

    display: inline-block;

    margin-top: 18px;

    padding: 7px 13px;

    border-radius: 20px;

    font-size: 11px;

    font-weight: 600;

}


.reservation-pending {

    background: #FFF3CD;

    color: #856404;

}


.reservation-confirmed {

    background: #DFF3E4;

    color: #276738;

}


.reservation-completed {

    background: #E8DDF5;

    color: #5B3A82;

}


.reservation-cancelled {

    background: #F8D7DA;

    color: #842029;

}


/* =========================================================
   CANCEL FORM
========================================================= */

.cove-cancel-form {

    margin-top: 12px;

}


.cove-cancel-btn {

    width: 100%;

    padding: 10px 15px;

    border: 1px solid #C94A4A;

    border-radius: 8px;

    background: transparent;

    color: #C94A4A;

    font-family: inherit;

    font-size: 13px;

    font-weight: 600;

    cursor: pointer;

    transition: 0.25s ease;

}


.cove-cancel-btn:hover {

    background: #C94A4A;

    color: #FFFFFF;

}


/* =========================================================
   BOOK ANOTHER
========================================================= */

.cove-book-another {

    margin-top: 35px;

    text-align: center;

}


.cove-book-another a {

    display: inline-block;

    padding: 12px 25px;

    border: 1px solid #8B4513;

    border-radius: 8px;

    color: #8B4513;

    text-decoration: none;

    font-size: 14px;

    font-weight: 600;

    transition: 0.25s ease;

}


.cove-book-another a:hover {

    background: #8B4513;

    color: #FFFFFF;

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 750px) {

    .cove-reservation-list {

        grid-template-columns: 1fr;

    }

}


@media (max-width: 500px) {

    .cove-reservation-page {

        padding:
            40px 15px 55px;

    }


    .cove-reservation-heading h1 {

        font-size: 29px;

    }


    .cove-reservation-card-header {

        flex-direction: column;

        align-items: flex-start;

    }


    .cove-reservation-grid {

        grid-template-columns: 1fr;

    }

}

</style>


<!-- =========================================================
     MAIN
========================================================= -->

<main class="cove-reservation-page">

    <div class="cove-reservation-container">


        <!-- =================================================
             HEADING
        ================================================== -->

        <div class="cove-reservation-heading">

            <h1>
                My Reservations
            </h1>

            <p>
                View your Caffeine &amp; Cove
                table bookings.
            </p>


            <?php if (
                $reservation_count > 0
            ): ?>

                <span
                    class="cove-reservation-count"
                >

                    <?php
                    echo $reservation_count;
                    ?>

                    <?php
                    echo
                        $reservation_count === 1
                            ? " Reservation"
                            : " Reservations";
                    ?>

                </span>

            <?php endif; ?>

        </div>


        <!-- =================================================
             SUCCESS
        ================================================== -->

        <?php if (
            $reservation_success !== ""
        ): ?>

            <div
                class="
                    cove-reservation-message
                    cove-reservation-success
                "
                role="status"
            >

                <?php

                echo reservationEscape(
                    $reservation_success
                );

                ?>

            </div>

        <?php endif; ?>


        <!-- =================================================
             ERROR
        ================================================== -->

        <?php if (
            $reservation_error !== ""
        ): ?>

            <div
                class="
                    cove-reservation-message
                    cove-reservation-error
                "
                role="alert"
            >

                <?php

                echo reservationEscape(
                    $reservation_error
                );

                ?>

            </div>

        <?php endif; ?>


        <!-- =================================================
             DATABASE ERROR
        ================================================== -->

        <?php if (
            $result === false
        ): ?>

            <div
                class="
                    cove-reservation-message
                    cove-reservation-error
                "
                role="alert"
            >

                We could not load your reservations
                right now. Please try again later.

            </div>

        <?php endif; ?>


        <!-- =================================================
             EMPTY
        ================================================== -->

        <?php if (
            $result instanceof mysqli_result &&
            mysqli_num_rows($result) === 0
        ): ?>


            <div class="cove-no-reservations">

                <div class="cove-no-reservation-icon">
                    📅
                </div>


                <h2>
                    No Reservations Yet
                </h2>


                <p>
                    You haven't booked a table yet.
                    Reserve your table and enjoy
                    your time at Caffeine &amp; Cove.
                </p>


                <a
                    href="reservation.php"
                    class="cove-book-reservation-btn"
                >

                    Book a Table

                </a>

            </div>


        <?php elseif (
            $result instanceof mysqli_result
        ): ?>


            <!-- =================================================
                 RESERVATION LIST
            ================================================== -->

            <div class="cove-reservation-list">


                <?php while (
                    $reservation =
                    mysqli_fetch_assoc($result)
                ): ?>


                    <?php

                    $reservation_id =
                        (int)$reservation["id"];


                    $guest_name =
                        $reservation["guest_name"];


                    $phone =
                        $reservation["phone"];


                    $email =
                        $reservation["email"];


                    $reservation_date =
                        $reservation["reservation_date"];


                    $reservation_time =
                        $reservation["reservation_time"];


                    $guests =
                        (int)$reservation["guests"];


                    $special_request =
                        $reservation["special_request"]
                        ?? "";


                    $status =
                        $reservation["status"]
                        ?? "pending";


                    $status_lower =
                        strtolower(
                            trim($status)
                        );


                    $status_class =
                        reservationStatusClass(
                            $status
                        );


                    $display_status =
                        ucfirst(
                            $status_lower
                        );


                    /* -----------------------------------------
                       DATE
                    ----------------------------------------- */

                    $display_date = "N/A";


                    if (
                        !empty($reservation_date)
                    ) {

                        $timestamp =
                            strtotime(
                                $reservation_date
                            );


                        if (
                            $timestamp !== false
                        ) {

                            $display_date =
                                date(
                                    "d M Y",
                                    $timestamp
                                );

                        }

                    }


                    /* -----------------------------------------
                       TIME
                    ----------------------------------------- */

                    $display_time = "N/A";


                    if (
                        !empty($reservation_time)
                    ) {

                        $timestamp =
                            strtotime(
                                $reservation_time
                            );


                        if (
                            $timestamp !== false
                        ) {

                            $display_time =
                                date(
                                    "h:i A",
                                    $timestamp
                                );

                        }

                    }


                    /* -----------------------------------------
                       CREATED DATE
                    ----------------------------------------- */

                    $display_created = "N/A";


                    if (
                        !empty(
                            $reservation["created_at"]
                        )
                    ) {

                        $timestamp =
                            strtotime(
                                $reservation["created_at"]
                            );


                        if (
                            $timestamp !== false
                        ) {

                            $display_created =
                                date(
                                    "d M Y",
                                    $timestamp
                                );

                        }

                    }


                    /* -----------------------------------------
                       CAN CANCEL?
                    ----------------------------------------- */

                    $can_cancel = false;


                    if (
                        (
                            $status_lower === "pending" ||
                            $status_lower === "confirmed"
                        ) &&
                        !empty($reservation_date) &&
                        !empty($reservation_time)
                    ) {

                        $reservation_timestamp =
                            strtotime(
                                $reservation_date .
                                " " .
                                $reservation_time
                            );


                        if (
                            $reservation_timestamp !== false &&
                            $reservation_timestamp > time()
                        ) {

                            $can_cancel = true;

                        }

                    }

                    ?>


                    <!-- =================================================
                         CARD
                    ================================================== -->

                    <article
                        class="cove-reservation-card"
                    >


                        <!-- HEADER -->

                        <div
                            class="
                                cove-reservation-card-header
                            "
                        >

                            <div
                                class="
                                    cove-reservation-number
                                "
                            >

                                Reservation
                                #<?php
                                echo $reservation_id;
                                ?>

                            </div>


                            <div
                                class="
                                    cove-reservation-created
                                "
                            >

                                Booked on
                                <?php

                                echo reservationEscape(
                                    $display_created
                                );

                                ?>

                            </div>

                        </div>


                        <!-- BODY -->

                        <div
                            class="
                                cove-reservation-card-body
                            "
                        >


                            <div
                                class="
                                    cove-reservation-grid
                                "
                            >


                                <!-- NAME -->

                                <div
                                    class="
                                        cove-reservation-box
                                    "
                                >

                                    <span
                                        class="
                                            cove-reservation-label
                                        "
                                    >
                                        Guest Name
                                    </span>


                                    <span
                                        class="
                                            cove-reservation-value
                                        "
                                    >

                                        <?php

                                        echo reservationEscape(
                                            $guest_name
                                        );

                                        ?>

                                    </span>

                                </div>


                                <!-- DATE -->

                                <div
                                    class="
                                        cove-reservation-box
                                    "
                                >

                                    <span
                                        class="
                                            cove-reservation-label
                                        "
                                    >
                                        Reservation Date
                                    </span>


                                    <span
                                        class="
                                            cove-reservation-value
                                        "
                                    >

                                        <?php

                                        echo reservationEscape(
                                            $display_date
                                        );

                                        ?>

                                    </span>

                                </div>


                                <!-- TIME -->

                                <div
                                    class="
                                        cove-reservation-box
                                    "
                                >

                                    <span
                                        class="
                                            cove-reservation-label
                                        "
                                    >
                                        Reservation Time
                                    </span>


                                    <span
                                        class="
                                            cove-reservation-value
                                        "
                                    >

                                        <?php

                                        echo reservationEscape(
                                            $display_time
                                        );

                                        ?>

                                    </span>

                                </div>


                                <!-- GUESTS -->

                                <div
                                    class="
                                        cove-reservation-box
                                    "
                                >

                                    <span
                                        class="
                                            cove-reservation-label
                                        "
                                    >
                                        Number of Guests
                                    </span>


                                    <span
                                        class="
                                            cove-reservation-value
                                        "
                                    >

                                        <?php

                                        echo $guests;

                                        echo
                                            $guests === 1
                                                ? " Guest"
                                                : " Guests";

                                        ?>

                                    </span>

                                </div>


                                <!-- PHONE -->

                                <div
                                    class="
                                        cove-reservation-box
                                    "
                                >

                                    <span
                                        class="
                                            cove-reservation-label
                                        "
                                    >
                                        Phone
                                    </span>


                                    <span
                                        class="
                                            cove-reservation-value
                                        "
                                    >

                                        <?php

                                        echo reservationEscape(
                                            $phone
                                        );

                                        ?>

                                    </span>

                                </div>


                                <!-- EMAIL -->

                                <div
                                    class="
                                        cove-reservation-box
                                    "
                                >

                                    <span
                                        class="
                                            cove-reservation-label
                                        "
                                    >
                                        Email
                                    </span>


                                    <span
                                        class="
                                            cove-reservation-value
                                        "
                                    >

                                        <?php

                                        echo reservationEscape(
                                            $email
                                        );

                                        ?>

                                    </span>

                                </div>

                            </div>


                            <!-- =================================================
                                 SPECIAL REQUEST
                            ================================================== -->

                            <?php if (
                                trim(
                                    $special_request
                                ) !== ""
                            ): ?>

                                <div
                                    class="
                                        cove-special-request
                                    "
                                >

                                    <span
                                        class="
                                            cove-reservation-label
                                        "
                                    >
                                        Special Request
                                    </span>


                                    <div
                                        class="
                                            cove-special-request-text
                                        "
                                    >

                                        <?php

                                        echo nl2br(
                                            reservationEscape(
                                                $special_request
                                            )
                                        );

                                        ?>

                                    </div>

                                </div>

                            <?php endif; ?>


                            <!-- =================================================
                                 STATUS
                            ================================================== -->

                            <span
                                class="
                                    cove-reservation-status
                                    <?php
                                    echo reservationEscape(
                                        $status_class
                                    );
                                    ?>
                                "
                            >

                                <?php

                                echo reservationEscape(
                                    $display_status
                                );

                                ?>

                            </span>


                            <!-- =================================================
                                 CANCEL BUTTON
                            ================================================== -->

                            <?php if (
                                $can_cancel
                            ): ?>

                                <form
                                    action="cancel_reservation.php"
                                    method="POST"
                                    class="cove-cancel-form"
                                    onsubmit="
                                        return confirm(
                                            'Are you sure you want to cancel this reservation?'
                                        );
                                    "
                                >


                                    <input
                                        type="hidden"
                                        name="reservation_id"
                                        value="<?php
                                            echo $reservation_id;
                                        ?>"
                                    >


                                    <input
                                        type="hidden"
                                        name="csrf_token"
                                        value="<?php

                                            echo reservationEscape(
                                                $_SESSION[
                                                    "reservation_csrf"
                                                ] ?? ""
                                            );

                                        ?>"
                                    >


                                    <button
                                        type="submit"
                                        class="cove-cancel-btn"
                                    >

                                        Cancel Reservation

                                    </button>

                                </form>

                            <?php endif; ?>


                        </div>

                    </article>


                <?php endwhile; ?>


            </div>


            <!-- =================================================
                 BOOK ANOTHER
            ================================================== -->

            <div class="cove-book-another">

                <a
                    href="reservation.php"
                >

                    + Book Another Table

                </a>

            </div>


        <?php endif; ?>


    </div>

</main>


<?php include __DIR__ . "/include/footer.php"; ?>


<?php

if (
    $stmt instanceof mysqli_stmt
) {

    mysqli_stmt_close($stmt);

}

?>