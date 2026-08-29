<?php

/* =========================================================
   CAFFEINE & COVE
   SECURE RESERVATION CANCELLATION
========================================================= */

session_start();

require_once __DIR__ . "/include/config.php";


/* =========================================================
   HELPER
========================================================= */

function cancelReservationError($message)
{
    $_SESSION["reservation_error"] = $message;

    header("Location: my_reservations.php");

    exit;
}


/* =========================================================
   LOGIN CHECK
========================================================= */

if (
    !isset($_SESSION["logged_in"]) ||
    $_SESSION["logged_in"] !== true
) {

    $_SESSION["login_required_message"] =
        "Please login to manage your reservations.";

    $_SESSION["redirect_after_login"] =
        "my_reservations.php";

    header("Location: login.php");

    exit;
}


/* =========================================================
   POST ONLY
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] !== "POST"
) {

    header("Location: my_reservations.php");

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

    header("Location: login.php");

    exit;
}


/* =========================================================
   CSRF TOKEN CHECK
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

    cancelReservationError(
        "Your session expired. Please try again."
    );

}


/* =========================================================
   RESERVATION ID
========================================================= */

$reservation_id =
    (int)(
        $_POST["reservation_id"] ?? 0
    );


if (
    $reservation_id <= 0
) {

    cancelReservationError(
        "Invalid reservation."
    );

}


/* =========================================================
   FIND RESERVATION
   IMPORTANT:
   user_id is included so one customer cannot
   cancel another customer's reservation.
========================================================= */

$sql = "
    SELECT
        id,
        user_id,
        reservation_date,
        reservation_time,
        status
    FROM reservations
    WHERE
        id = ?
        AND user_id = ?
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
        "Caffeine & Cove: Reservation lookup failed. " .
        mysqli_error($link)
    );

    cancelReservationError(
        "Unable to process your cancellation."
    );

}


mysqli_stmt_bind_param(
    $stmt,
    "ii",
    $reservation_id,
    $user_id
);


if (
    !mysqli_stmt_execute($stmt)
) {

    error_log(
        "Caffeine & Cove: Reservation lookup execute failed. " .
        mysqli_stmt_error($stmt)
    );

    mysqli_stmt_close($stmt);

    cancelReservationError(
        "Unable to process your cancellation."
    );

}


$result =
    mysqli_stmt_get_result(
        $stmt
    );


$reservation =
    $result
        ? mysqli_fetch_assoc($result)
        : null;


mysqli_stmt_close($stmt);


/* =========================================================
   RESERVATION MUST BELONG TO USER
========================================================= */

if (
    !$reservation
) {

    cancelReservationError(
        "Reservation not found."
    );

}


/* =========================================================
   CHECK STATUS
========================================================= */

$current_status =
    strtolower(
        trim(
            $reservation["status"] ?? ""
        )
    );


if (
    $current_status === "cancelled" ||
    $current_status === "canceled"
) {

    cancelReservationError(
        "This reservation has already been cancelled."
    );

}


if (
    $current_status === "completed"
) {

    cancelReservationError(
        "A completed reservation cannot be cancelled."
    );

}


/* =========================================================
   CHECK RESERVATION DATE/TIME
========================================================= */

$reservationDate =
    $reservation["reservation_date"];


$reservationTime =
    $reservation["reservation_time"];


$reservationTimestamp =
    strtotime(
        $reservationDate .
        " " .
        $reservationTime
    );


/*
 * Do not allow cancellation after the
 * reservation time has already passed.
 */

if (
    $reservationTimestamp !== false &&
    $reservationTimestamp <= time()
) {

    cancelReservationError(
        "A reservation whose time has passed cannot be cancelled."
    );

}


/* =========================================================
   UPDATE ONLY THIS USER'S RESERVATION
========================================================= */

$updateSql = "
    UPDATE reservations
    SET
        status = 'cancelled'
    WHERE
        id = ?
        AND user_id = ?
        AND status IN ('pending', 'confirmed')
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
        "Caffeine & Cove: Reservation cancellation prepare failed. " .
        mysqli_error($link)
    );

    cancelReservationError(
        "Unable to cancel your reservation."
    );

}


mysqli_stmt_bind_param(
    $updateStmt,
    "ii",
    $reservation_id,
    $user_id
);


if (
    !mysqli_stmt_execute($updateStmt)
) {

    error_log(
        "Caffeine & Cove: Reservation cancellation failed. " .
        mysqli_stmt_error($updateStmt)
    );

    mysqli_stmt_close($updateStmt);

    cancelReservationError(
        "Unable to cancel your reservation."
    );

}


/* =========================================================
   CHECK WHETHER UPDATE ACTUALLY HAPPENED
========================================================= */

$affectedRows =
    mysqli_stmt_affected_rows(
        $updateStmt
    );


mysqli_stmt_close(
    $updateStmt
);


if (
    $affectedRows !== 1
) {

    cancelReservationError(
        "This reservation could not be cancelled. It may already have been updated."
    );

}


/* =========================================================
   SUCCESS
========================================================= */

$_SESSION["reservation_success"] =
    "Your reservation has been cancelled successfully.";


/* =========================================================
   REDIRECT
========================================================= */

header(
    "Location: my_reservations.php"
);

exit;

?>