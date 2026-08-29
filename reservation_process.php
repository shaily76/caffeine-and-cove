<?php

/* =========================================================
   CAFFEINE & COVE
   SECURE RESERVATION PROCESS
========================================================= */

session_start();

require_once __DIR__ . "/include/config.php";


/* =========================================================
   ERROR REDIRECT HELPER
========================================================= */

function reservationError($message)
{
    $_SESSION["reservation_error"] = $message;

    header("Location: reservation.php");

    exit;
}


/* =========================================================
   LOGIN REQUIRED
========================================================= */

if (
    !isset($_SESSION["logged_in"]) ||
    $_SESSION["logged_in"] !== true
) {

    $_SESSION["login_required_message"] =
        "Please login to book a table.";

    $_SESSION["redirect_after_login"] =
        "reservation.php";

    header("Location: login.php");

    exit;
}


/* =========================================================
   POST ONLY
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] !== "POST"
) {

    header("Location: reservation.php");

    exit;
}


/* =========================================================
   USER ID
========================================================= */

$user_id =
    (int)(
        $_SESSION["user_id"] ?? 0
    );


if (
    $user_id <= 0
) {

    $_SESSION["login_required_message"] =
        "Please login to book a table.";

    header("Location: login.php");

    exit;
}


/* =========================================================
   CSRF CHECK
========================================================= */

$csrf_token =
    $_POST["csrf_token"] ?? "";


$session_token =
    $_SESSION["reservation_csrf"] ?? "";


if (
    $session_token === "" ||
    $csrf_token === "" ||
    !hash_equals(
        $session_token,
        $csrf_token
    )
) {

    reservationError(
        "Your reservation session expired. Please try again."
    );

}


/* =========================================================
   GET FORM DATA
========================================================= */

$guest_name =
    trim(
        $_POST["guest_name"] ?? ""
    );


$phone =
    trim(
        $_POST["phone"] ?? ""
    );


$email =
    trim(
        $_POST["email"] ?? ""
    );


$reservation_date =
    trim(
        $_POST["reservation_date"] ?? ""
    );


$reservation_time =
    trim(
        $_POST["reservation_time"] ?? ""
    );


$guests =
    (int)(
        $_POST["guests"] ?? 0
    );


$special_request =
    trim(
        $_POST["special_request"] ?? ""
    );


/* =========================================================
   NAME VALIDATION
========================================================= */

if (
    $guest_name === ""
) {

    reservationError(
        "Please enter your full name."
    );

}


if (
    mb_strlen($guest_name) < 2 ||
    mb_strlen($guest_name) > 100
) {

    reservationError(
        "Name must be between 2 and 100 characters."
    );

}


/* =========================================================
   PHONE VALIDATION
========================================================= */

$phoneClean =
    preg_replace(
        "/[^0-9]/",
        "",
        $phone
    );


if (
    strlen($phoneClean) < 10 ||
    strlen($phoneClean) > 15
) {

    reservationError(
        "Please enter a valid phone number."
    );

}


/* =========================================================
   EMAIL VALIDATION
========================================================= */

if (
    !filter_var(
        $email,
        FILTER_VALIDATE_EMAIL
    )
) {

    reservationError(
        "Please enter a valid email address."
    );

}


if (
    strlen($email) > 150
) {

    reservationError(
        "Email address is too long."
    );

}


/* =========================================================
   DATE VALIDATION
========================================================= */

$dateObject =
    DateTime::createFromFormat(
        "Y-m-d",
        $reservation_date
    );


$dateErrors =
    DateTime::getLastErrors();


if (
    $dateObject === false
) {

    reservationError(
        "Please select a valid reservation date."
    );

}


if (
    is_array($dateErrors) &&
    (
        $dateErrors["warning_count"] > 0 ||
        $dateErrors["error_count"] > 0
    )
) {

    reservationError(
        "Please select a valid reservation date."
    );

}


/* =========================================================
   DATE MUST NOT BE IN THE PAST
========================================================= */

$today =
    new DateTime(
        date("Y-m-d")
    );


if (
    $dateObject < $today
) {

    reservationError(
        "You cannot book a table for a past date."
    );

}


/* =========================================================
   TIME VALIDATION
========================================================= */

$allowed_times = [

    "17:00",
    "18:00",
    "19:00",
    "20:00",
    "21:00",
    "22:00",
    "23:00",
    "00:00",
    "00:30",
    "01:00"

];


if (
    !in_array(
        $reservation_time,
        $allowed_times,
        true
    )
) {

    reservationError(
        "Please select a valid reservation time."
    );

}


/* =========================================================
   SAME-DAY TIME CHECK
========================================================= */

if (
    $reservation_date === date("Y-m-d")
) {

    $selectedDateTime =
        strtotime(
            $reservation_date .
            " " .
            $reservation_time
        );


    /*
     * After midnight belongs to the café's
     * late-night closing period.
     *
     * For today's date, 12:00 AM / 12:30 AM /
     * 1:00 AM are already past once the day
     * has moved beyond them.
     */

    if (
        $reservation_time === "00:00" ||
        $reservation_time === "00:30" ||
        $reservation_time === "01:00"
    ) {

        reservationError(
            "That time has already passed for today. Please choose another date."
        );

    }


    if (
        $selectedDateTime !== false &&
        $selectedDateTime <= time()
    ) {

        reservationError(
            "Please select a future reservation time."
        );

    }

}


/* =========================================================
   GUEST VALIDATION
========================================================= */

if (
    $guests < 1 ||
    $guests > 10
) {

    reservationError(
        "Number of guests must be between 1 and 10."
    );

}


/* =========================================================
   SPECIAL REQUEST LIMIT
========================================================= */

if (
    mb_strlen($special_request) > 500
) {

    reservationError(
        "Special request cannot exceed 500 characters."
    );

}


/* =========================================================
   DATABASE CONNECTION
========================================================= */

if (
    !$link
) {

    error_log(
        "Caffeine & Cove: Database connection failed."
    );

    reservationError(
        "Unable to process your reservation right now."
    );

}


/* =========================================================
   DUPLICATE BOOKING CHECK
========================================================= */

$duplicateSql = "
    SELECT
        id
    FROM reservations
    WHERE
        user_id = ?
        AND reservation_date = ?
        AND reservation_time = ?
        AND status IN ('pending', 'confirmed')
    LIMIT 1
";


$duplicateStmt =
    mysqli_prepare(
        $link,
        $duplicateSql
    );


if (
    !$duplicateStmt
) {

    error_log(
        "Reservation duplicate query failed: " .
        mysqli_error($link)
    );

    reservationError(
        "Unable to check reservation availability."
    );

}


mysqli_stmt_bind_param(
    $duplicateStmt,
    "iss",
    $user_id,
    $reservation_date,
    $reservation_time
);


mysqli_stmt_execute(
    $duplicateStmt
);


$duplicateResult =
    mysqli_stmt_get_result(
        $duplicateStmt
    );


$duplicate =
    $duplicateResult
        ? mysqli_fetch_assoc(
            $duplicateResult
        )
        : null;


mysqli_stmt_close(
    $duplicateStmt
);


if (
    $duplicate
) {

    reservationError(
        "You already have a reservation for this date and time."
    );

}


/* =========================================================
   INSERT RESERVATION
========================================================= */

$sql = "
    INSERT INTO reservations
    (
        user_id,
        guest_name,
        phone,
        email,
        reservation_date,
        reservation_time,
        guests,
        special_request,
        status
    )
    VALUES
    (
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        ?,
        'pending'
    )
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
        "Reservation prepare failed: " .
        mysqli_error($link)
    );

    reservationError(
        "Unable to book your table right now."
    );

}


/* =========================================================
   BIND
========================================================= */

mysqli_stmt_bind_param(
    $stmt,
    "isssssis",
    $user_id,
    $guest_name,
    $phone,
    $email,
    $reservation_date,
    $reservation_time,
    $guests,
    $special_request
);


/* =========================================================
   EXECUTE
========================================================= */

if (
    !mysqli_stmt_execute($stmt)
) {

    error_log(
        "Reservation insert failed: " .
        mysqli_stmt_error($stmt)
    );

    mysqli_stmt_close($stmt);

    reservationError(
        "Unable to book your table. Please try again."
    );

}


/* =========================================================
   RESERVATION ID
========================================================= */

$reservation_id =
    mysqli_insert_id($link);


mysqli_stmt_close($stmt);


/* =========================================================
   SUCCESS SESSION DATA
========================================================= */

$_SESSION["reservation_success"] =
    "Your table reservation has been booked successfully.";


$_SESSION["reservation_id"] =
    $reservation_id;


$_SESSION["last_reservation_date"] =
    $reservation_date;


$_SESSION["last_reservation_time"] =
    $reservation_time;


/* =========================================================
   REDIRECT
========================================================= */

header(
    "Location: my_reservations.php"
);

exit;

?>