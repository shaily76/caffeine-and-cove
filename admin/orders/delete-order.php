<?php

/* =========================================================
   CAFFEINE & COVE
   ADMIN - DELETE ORDER
   SECURE POST + CSRF VERSION
========================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================================================
   ADMIN AUTH
========================================================= */

require_once "../admin_auth.php";


/* =========================================================
   DATABASE
========================================================= */

require_once "../../include/config.php";


/* =========================================================
   ONLY POST ALLOWED
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] !== "POST"
) {

    header(
        "Location: orders.php?error=invalid_request"
    );

    exit;
}


/* =========================================================
   CSRF CHECK
========================================================= */

$csrfToken =
    $_POST["csrf_token"] ?? "";

$sessionToken =
    $_SESSION["admin_order_csrf"] ?? "";


if (
    $sessionToken === "" ||
    $csrfToken === "" ||
    !hash_equals(
        $sessionToken,
        $csrfToken
    )
) {

    header(
        "Location: orders.php?error=invalid_token"
    );

    exit;
}


/* =========================================================
   ORDER ID
========================================================= */

$orderId =
    trim(
        $_POST["order_id"] ?? ""
    );


if (
    !ctype_digit($orderId) ||
    (int)$orderId <= 0
) {

    header(
        "Location: orders.php?error=invalid_id"
    );

    exit;
}


$orderId =
    (int)$orderId;


/* =========================================================
   TRANSACTION
========================================================= */

mysqli_begin_transaction($link);


try {


    /* =====================================================
       CHECK ORDER EXISTS
    ===================================================== */

    $checkSql = "
        SELECT
            id
        FROM orders
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

        throw new Exception(
            "Unable to prepare order check."
        );

    }


    mysqli_stmt_bind_param(
        $checkStmt,
        "i",
        $orderId
    );


    if (
        !mysqli_stmt_execute(
            $checkStmt
        )
    ) {

        mysqli_stmt_close(
            $checkStmt
        );

        throw new Exception(
            "Unable to check order."
        );

    }


    $checkResult =
        mysqli_stmt_get_result(
            $checkStmt
        );


    if (
        !$checkResult ||
        mysqli_num_rows(
            $checkResult
        ) !== 1
    ) {

        mysqli_stmt_close(
            $checkStmt
        );

        throw new Exception(
            "Order not found."
        );

    }


    mysqli_stmt_close(
        $checkStmt
    );


    /* =====================================================
       DELETE ORDER DETAILS FIRST
    ===================================================== */

    $detailsSql = "
        DELETE FROM order_details
        WHERE order_id = ?
    ";


    $detailsStmt =
        mysqli_prepare(
            $link,
            $detailsSql
        );


    if (
        !$detailsStmt
    ) {

        throw new Exception(
            "Unable to prepare order item deletion."
        );

    }


    mysqli_stmt_bind_param(
        $detailsStmt,
        "i",
        $orderId
    );


    if (
        !mysqli_stmt_execute(
            $detailsStmt
        )
    ) {

        mysqli_stmt_close(
            $detailsStmt
        );

        throw new Exception(
            "Unable to delete order items."
        );

    }


    mysqli_stmt_close(
        $detailsStmt
    );


    /* =====================================================
       DELETE ORDER
    ===================================================== */

    $orderSql = "
        DELETE FROM orders
        WHERE id = ?
        LIMIT 1
    ";


    $orderStmt =
        mysqli_prepare(
            $link,
            $orderSql
        );


    if (
        !$orderStmt
    ) {

        throw new Exception(
            "Unable to prepare order deletion."
        );

    }


    mysqli_stmt_bind_param(
        $orderStmt,
        "i",
        $orderId
    );


    if (
        !mysqli_stmt_execute(
            $orderStmt
        )
    ) {

        mysqli_stmt_close(
            $orderStmt
        );

        throw new Exception(
            "Unable to delete order."
        );

    }


    $deletedRows =
        mysqli_stmt_affected_rows(
            $orderStmt
        );


    mysqli_stmt_close(
        $orderStmt
    );


    if (
        $deletedRows !== 1
    ) {

        throw new Exception(
            "Order was not deleted."
        );

    }


    /* =====================================================
       COMMIT
    ===================================================== */

    mysqli_commit(
        $link
    );


    header(
        "Location: orders.php?success=deleted"
    );

    exit;


} catch (
    Throwable $e
) {


    /* =====================================================
       ROLLBACK
    ===================================================== */

    mysqli_rollback(
        $link
    );


    /* =====================================================
       LOG REAL ERROR
    ===================================================== */

    error_log(
        "Caffeine & Cove admin delete-order failed: " .
        $e->getMessage()
    );


    /* =====================================================
       SAFE USER MESSAGE
    ===================================================== */

    header(
        "Location: orders.php?error=delete_failed"
    );

    exit;

}