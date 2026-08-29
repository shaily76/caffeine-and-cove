<?php

/* =========================================================
   CAFFEINE & COVE
   CUSTOMER - CANCEL ORDER
========================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . "/include/config.php";


/* =========================================================
   LOGIN CHECK
========================================================= */

if (
    !isset($_SESSION["logged_in"]) ||
    $_SESSION["logged_in"] !== true
) {

    header("Location: login.php");
    exit;
}


/* =========================================================
   POST ONLY
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] !== "POST"
) {

    header("Location: my_orders.php");
    exit;
}


/* =========================================================
   USER ID
========================================================= */

$userId =
    (int)(
        $_SESSION["user_id"] ?? 0
    );


if (
    $userId <= 0
) {

    header("Location: login.php");
    exit;
}


/* =========================================================
   CSRF TOKEN
========================================================= */

$csrfToken =
    $_POST["csrf_token"] ?? "";

$sessionToken =
    $_SESSION["cancel_order_csrf"] ?? "";


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

    $_SESSION["order_cancel_error"] =
        "Security validation failed. Please try again.";

    header("Location: my_orders.php");
    exit;
}


/* =========================================================
   ORDER ID
========================================================= */

$orderIdRaw =
    $_POST["order_id"] ?? "";


if (
    !is_string($orderIdRaw) ||
    !ctype_digit($orderIdRaw) ||
    (int)$orderIdRaw <= 0
) {

    $_SESSION["order_cancel_error"] =
        "Invalid order.";

    header("Location: my_orders.php");
    exit;
}


$orderId =
    (int)$orderIdRaw;


/* =========================================================
   ATOMIC CANCELLATION
========================================================= */

/*
 * IMPORTANT:
 *
 * The database itself checks:
 *
 * 1. Order ID
 * 2. Logged-in user's ID
 * 3. Current order status
 *
 * Therefore the customer can ONLY cancel
 * their own pending/confirmed order.
 *
 * Preparing / Ready / Completed / Cancelled
 * orders cannot be cancelled.
 */

$updateSql = "
    UPDATE orders
    SET status = 'cancelled'
    WHERE id = ?
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
        "Cancel order prepare failed: " .
        mysqli_error($link)
    );

    $_SESSION["order_cancel_error"] =
        "Unable to cancel the order right now.";

    header("Location: my_orders.php");
    exit;
}


/* =========================================================
   BIND
========================================================= */

mysqli_stmt_bind_param(
    $updateStmt,
    "ii",
    $orderId,
    $userId
);


/* =========================================================
   EXECUTE
========================================================= */

if (
    !mysqli_stmt_execute(
        $updateStmt
    )
) {

    error_log(
        "Cancel order execute failed: " .
        mysqli_stmt_error($updateStmt)
    );

    mysqli_stmt_close(
        $updateStmt
    );

    $_SESSION["order_cancel_error"] =
        "Unable to cancel the order right now.";

    header("Location: my_orders.php");
    exit;
}


/* =========================================================
   AFFECTED ROWS
========================================================= */

$affectedRows =
    mysqli_stmt_affected_rows(
        $updateStmt
    );


mysqli_stmt_close(
    $updateStmt
);


/* =========================================================
   INVALIDATE CSRF TOKEN
========================================================= */

unset(
    $_SESSION["cancel_order_csrf"]
);


/* =========================================================
   RESULT
========================================================= */

if (
    $affectedRows === 1
) {

    $_SESSION["order_cancel_success"] =
        "Order #" .
        $orderId .
        " has been cancelled successfully.";

} else {

    /*
     * affectedRows = 0 means one of these:
     *
     * - order does not exist
     * - order belongs to another user
     * - order is already cancelled
     * - order is preparing
     * - order is ready
     * - order is completed
     */

    $_SESSION["order_cancel_error"] =
        "This order can no longer be cancelled.";

}


/* =========================================================
   REDIRECT
========================================================= */

header(
    "Location: my_orders.php"
);

exit;

?>