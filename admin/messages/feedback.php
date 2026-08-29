<?php

/* =========================================================
   CAFFEINE & COVE
   ADMIN - CUSTOMER FEEDBACK
   SECURE VERSION
========================================================= */


/* =========================================================
   SESSION
========================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


/* =========================================================
   ADMIN AUTHENTICATION
========================================================= */

require_once "../admin_auth.php";


/* =========================================================
   DATABASE CONNECTION
========================================================= */

require_once "../../include/config.php";


/* =========================================================
   CSRF TOKEN
========================================================= */

if (
    empty($_SESSION["admin_feedback_csrf"])
) {

    $_SESSION["admin_feedback_csrf"] =
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
   UPDATE FEEDBACK STATUS
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST"
) {


    /* =====================================================
       CSRF CHECK
    ====================================================== */

    $csrfToken =
        $_POST["csrf_token"] ?? "";


    $sessionToken =
        $_SESSION["admin_feedback_csrf"] ?? "";


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

        $error =
            "Security validation failed. Please try again.";

    }


    /* =====================================================
       FEEDBACK ID
    ====================================================== */

    $feedbackIdRaw =
        $_POST["feedback_id"] ?? "";


    if (
        $error === "" &&
        (
            !is_string($feedbackIdRaw) ||
            !ctype_digit($feedbackIdRaw) ||
            (int)$feedbackIdRaw <= 0
        )
    ) {

        $error =
            "Invalid feedback ID.";

    }


    /* =====================================================
       STATUS
    ====================================================== */

    $newStatus =
        trim(
            (string)(
                $_POST["status"] ?? ""
            )
        );


    $allowedStatuses = [

        "pending",
        "approved",
        "rejected"

    ];


    if (
        $error === "" &&
        !in_array(
            $newStatus,
            $allowedStatuses,
            true
        )
    ) {

        $error =
            "Invalid feedback status.";

    }


    /* =====================================================
       UPDATE
    ====================================================== */

    if (
        $error === ""
    ) {


        $feedbackId =
            (int)$feedbackIdRaw;


        /* -------------------------------------------------
           CHECK FEEDBACK EXISTS
        ------------------------------------------------- */

        $checkSql = "
            SELECT
                id
            FROM feedback
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
                "Admin feedback check prepare failed: " .
                mysqli_error($link)
            );


            $error =
                "A database error occurred.";

        } else {


            mysqli_stmt_bind_param(
                $checkStmt,
                "i",
                $feedbackId
            );


            if (
                !mysqli_stmt_execute(
                    $checkStmt
                )
            ) {

                error_log(
                    "Admin feedback check execute failed: " .
                    mysqli_stmt_error($checkStmt)
                );


                $error =
                    "A database error occurred.";

            } else {


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

                    $error =
                        "Feedback not found.";

                }

            }


            mysqli_stmt_close(
                $checkStmt
            );

        }


        /* -------------------------------------------------
           UPDATE STATUS
        ------------------------------------------------- */

        if (
            $error === ""
        ) {


            $sql = "
                UPDATE feedback
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
                    "Admin feedback update prepare failed: " .
                    mysqli_error($link)
                );


                $error =
                    "A database error occurred.";

            } else {


                mysqli_stmt_bind_param(
                    $stmt,
                    "si",
                    $newStatus,
                    $feedbackId
                );


                if (
                    mysqli_stmt_execute(
                        $stmt
                    )
                ) {

                    $success =
                        "Feedback status updated successfully.";

                } else {

                    error_log(
                        "Admin feedback update failed: " .
                        mysqli_stmt_error($stmt)
                    );


                    $error =
                        "Unable to update feedback status.";

                }


                mysqli_stmt_close(
                    $stmt
                );

            }

        }

    }

}


/* =========================================================
   GET FEEDBACK
========================================================= */

$sql = "
    SELECT
        id,
        user_id,
        name,
        email,
        rating,
        subject,
        message,
        status,
        created_at
    FROM feedback
    ORDER BY id DESC
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
        "Admin feedback query failed: " .
        mysqli_error($link)
    );


    $error =
        "Unable to load feedback right now.";

}


/* =========================================================
   FEEDBACK COUNTS
========================================================= */

$totalFeedback = 0;

$pendingFeedback = 0;

$approvedFeedback = 0;

$rejectedFeedback = 0;


$countSql = "
    SELECT
        COUNT(*) AS total_feedback,

        COALESCE(
            SUM(
                CASE
                    WHEN status = 'pending'
                    THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS pending_feedback,

        COALESCE(
            SUM(
                CASE
                    WHEN status = 'approved'
                    THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS approved_feedback,

        COALESCE(
            SUM(
                CASE
                    WHEN status = 'rejected'
                    THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS rejected_feedback

    FROM feedback
";


$countResult =
    mysqli_query(
        $link,
        $countSql
    );


if (
    $countResult !== false
) {

    $countRow =
        mysqli_fetch_assoc(
            $countResult
        );


    if (
        $countRow
    ) {

        $totalFeedback =
            (int)$countRow[
                "total_feedback"
            ];


        $pendingFeedback =
            (int)$countRow[
                "pending_feedback"
            ];


        $approvedFeedback =
            (int)$countRow[
                "approved_feedback"
            ];


        $rejectedFeedback =
            (int)$countRow[
                "rejected_feedback"
            ];

    }

} else {

    error_log(
        "Admin feedback count query failed: " .
        mysqli_error($link)
    );

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


                <div class="col-sm-6">

                    <h1 class="m-0">

                        <i
                            class="fas fa-star mr-2"
                            style="color:#D8A15B;"
                        ></i>

                        Customer Feedback

                    </h1>

                </div>


                <div class="col-sm-6">

                    <ol
                        class="
                            breadcrumb
                            float-sm-right
                        "
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
                            class="breadcrumb-item"
                        >

                            <a
                                href="messages.php"
                            >

                                Messages

                            </a>

                        </li>


                        <li
                            class="breadcrumb-item active"
                        >

                            Feedback

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
                        class="
                            fas
                            fa-check-circle
                            mr-2
                        "
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
                        class="
                            fas
                            fa-exclamation-circle
                            mr-2
                        "
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
                 SUMMARY
            ================================================== -->

            <div class="row">


                <!-- TOTAL -->

                <div class="col-md-3">

                    <div class="info-box">

                        <span class="info-box-icon">

                            <i
                                class="
                                    fas
                                    fa-star
                                "
                            ></i>

                        </span>


                        <div
                            class="info-box-content"
                        >

                            <span
                                class="info-box-text"
                            >

                                Total Feedback

                            </span>


                            <span
                                class="info-box-number"
                            >

                                <?php

                                echo $totalFeedback;

                                ?>

                            </span>

                        </div>

                    </div>

                </div>


                <!-- PENDING -->

                <div class="col-md-3">

                    <div class="info-box">

                        <span class="info-box-icon">

                            <i
                                class="
                                    fas
                                    fa-clock
                                "
                            ></i>

                        </span>


                        <div
                            class="info-box-content"
                        >

                            <span
                                class="info-box-text"
                            >

                                Pending

                            </span>


                            <span
                                class="info-box-number"
                            >

                                <?php

                                echo $pendingFeedback;

                                ?>

                            </span>

                        </div>

                    </div>

                </div>


                <!-- APPROVED -->

                <div class="col-md-3">

                    <div class="info-box">

                        <span class="info-box-icon">

                            <i
                                class="
                                    fas
                                    fa-check
                                "
                            ></i>

                        </span>


                        <div
                            class="info-box-content"
                        >

                            <span
                                class="info-box-text"
                            >

                                Approved

                            </span>


                            <span
                                class="info-box-number"
                            >

                                <?php

                                echo $approvedFeedback;

                                ?>

                            </span>

                        </div>

                    </div>

                </div>


                <!-- REJECTED -->

                <div class="col-md-3">

                    <div class="info-box">

                        <span class="info-box-icon">

                            <i
                                class="
                                    fas
                                    fa-times
                                "
                            ></i>

                        </span>


                        <div
                            class="info-box-content"
                        >

                            <span
                                class="info-box-text"
                            >

                                Rejected

                            </span>


                            <span
                                class="info-box-number"
                            >

                                <?php

                                echo $rejectedFeedback;

                                ?>

                            </span>

                        </div>

                    </div>

                </div>


            </div>


            <!-- =================================================
                 FEEDBACK CARD
            ================================================== -->

            <div class="card">


                <div class="card-header">

                    <h3 class="card-title">

                        <i
                            class="
                                fas
                                fa-comment-dots
                                mr-2
                            "
                        ></i>

                        Customer Feedback

                    </h3>


                    <div class="card-tools">

                        <a
                            href="messages.php"
                            class="
                                btn
                                btn-sm
                                btn-coffee
                            "
                        >

                            <i
                                class="
                                    fas
                                    fa-envelope
                                    mr-1
                                "
                            ></i>

                            Contact Messages

                        </a>

                    </div>

                </div>


                <div
                    class="
                        card-body
                        p-0
                    "
                >

                    <div
                        class="
                            table-responsive
                        "
                    >

                        <table
                            class="
                                table
                                table-hover
                                mb-0
                            "
                        >


                            <thead>

                                <tr>

                                    <th>
                                        ID
                                    </th>

                                    <th>
                                        Customer
                                    </th>

                                    <th>
                                        Rating
                                    </th>

                                    <th>
                                        Subject
                                    </th>

                                    <th>
                                        Feedback
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                    <th>
                                        Date
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
                                mysqli_num_rows($result) > 0
                            ): ?>


                                <?php while (
                                    $feedback =
                                    mysqli_fetch_assoc(
                                        $result
                                    )
                                ): ?>


                                    <?php

                                    $feedbackId =
                                        (int)
                                        $feedback["id"];


                                    $name =
                                        (string)
                                        $feedback["name"];


                                    $email =
                                        (string)
                                        $feedback["email"];


                                    $rating =
                                        (int)
                                        $feedback["rating"];


                                    if (
                                        $rating < 0
                                    ) {

                                        $rating = 0;

                                    }


                                    if (
                                        $rating > 5
                                    ) {

                                        $rating = 5;

                                    }


                                    $subject =
                                        (string)
                                        $feedback["subject"];


                                    $message =
                                        trim(
                                            (string)
                                            $feedback["message"]
                                        );


                                    $status =
                                        strtolower(
                                            trim(
                                                (string)
                                                $feedback["status"]
                                            )
                                        );


                                    $createdAt =
                                        (string)
                                        $feedback["created_at"];


                                    /* -----------------------------------------
                                       SHORT MESSAGE
                                    ----------------------------------------- */

                                    $shortMessage =
                                        $message;


                                    if (
                                        strlen(
                                            $shortMessage
                                        ) > 65
                                    ) {

                                        $shortMessage =
                                            substr(
                                                $shortMessage,
                                                0,
                                                65
                                            ) .
                                            "...";

                                    }


                                    /* -----------------------------------------
                                       DATE
                                    ----------------------------------------- */

                                    $date = "-";


                                    if (
                                        $createdAt !== ""
                                    ) {

                                        $timestamp =
                                            strtotime(
                                                $createdAt
                                            );


                                        if (
                                            $timestamp !== false
                                        ) {

                                            $date =
                                                date(
                                                    "d M Y, h:i A",
                                                    $timestamp
                                                );

                                        }

                                    }


                                    /* -----------------------------------------
                                       STATUS BADGE
                                    ----------------------------------------- */

                                    switch (
                                        $status
                                    ) {

                                        case "approved":

                                            $statusClass =
                                                "badge-success";

                                            break;


                                        case "rejected":

                                            $statusClass =
                                                "badge-danger";

                                            break;


                                        case "pending":

                                            $statusClass =
                                                "badge-warning";

                                            break;


                                        default:

                                            $statusClass =
                                                "badge-secondary";

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

                                                echo $feedbackId;

                                                ?>

                                            </strong>

                                        </td>


                                        <!-- CUSTOMER -->

                                        <td>

                                            <strong
                                                style="
                                                    color:#4A2C1D;
                                                "
                                            >

                                                <?php

                                                echo htmlspecialchars(
                                                    $name,
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                );

                                                ?>

                                            </strong>


                                            <?php if (
                                                $email !== ""
                                            ): ?>

                                                <div
                                                    style="
                                                        color:#8A7468;
                                                        font-size:12px;
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

                                            <?php endif; ?>

                                        </td>


                                        <!-- RATING -->

                                        <td>

                                            <span
                                                style="
                                                    color:#D8A15B;
                                                    font-size:17px;
                                                    white-space:nowrap;
                                                "
                                            >

                                                <?php

                                                for (
                                                    $i = 1;
                                                    $i <= 5;
                                                    $i++
                                                ) {

                                                    echo
                                                        $i <= $rating
                                                            ? "★"
                                                            : "☆";

                                                }

                                                ?>

                                            </span>

                                            <small
                                                style="
                                                    color:#8A7468;
                                                "
                                            >

                                                <?php

                                                echo $rating;

                                                ?>/5

                                            </small>

                                        </td>


                                        <!-- SUBJECT -->

                                        <td>

                                            <strong
                                                style="
                                                    color:#6F5548;
                                                "
                                            >

                                                <?php

                                                echo htmlspecialchars(
                                                    $subject !== ""
                                                        ? $subject
                                                        : "No Subject",
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                );

                                                ?>

                                            </strong>

                                        </td>


                                        <!-- MESSAGE -->

                                        <td>

                                            <span
                                                style="
                                                    color:#8A7468;
                                                    font-size:13px;
                                                "
                                            >

                                                <?php

                                                echo htmlspecialchars(
                                                    $shortMessage,
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                );

                                                ?>

                                            </span>

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


                                        <!-- DATE -->

                                        <td>

                                            <span
                                                style="
                                                    color:#6F5548;
                                                    font-size:12px;
                                                "
                                            >

                                                <?php

                                                echo htmlspecialchars(
                                                    $date,
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                );

                                                ?>

                                            </span>

                                        </td>


                                        <!-- ACTION -->

                                        <td
                                            class="text-center"
                                        >

                                            <button
                                                type="button"
                                                class="
                                                    btn
                                                    btn-sm
                                                    btn-gold
                                                "
                                                data-toggle="modal"
                                                data-target="#feedbackModal<?php

                                                echo $feedbackId;

                                                ?>"
                                                title="View Feedback"
                                            >

                                                <i
                                                    class="
                                                        fas
                                                        fa-eye
                                                    "
                                                ></i>

                                            </button>

                                        </td>

                                    </tr>


                                    <!-- =================================================
                                         FEEDBACK MODAL
                                    ================================================== -->

                                    <div
                                        class="modal fade"
                                        id="feedbackModal<?php

                                        echo $feedbackId;

                                        ?>"
                                        tabindex="-1"
                                        role="dialog"
                                        aria-hidden="true"
                                    >

                                        <div
                                            class="
                                                modal-dialog
                                                modal-lg
                                            "
                                            role="document"
                                        >

                                            <div
                                                class="
                                                    modal-content
                                                "
                                            >


                                                <!-- MODAL HEADER -->

                                                <div
                                                    class="
                                                        modal-header
                                                    "
                                                >

                                                    <h5
                                                        class="
                                                            modal-title
                                                        "
                                                    >

                                                        <?php

                                                        echo htmlspecialchars(
                                                            $subject !== ""
                                                                ? $subject
                                                                : "Customer Feedback",
                                                            ENT_QUOTES,
                                                            "UTF-8"
                                                        );

                                                        ?>

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


                                                <!-- MODAL BODY -->

                                                <div
                                                    class="
                                                        modal-body
                                                    "
                                                >


                                                    <!-- RATING -->

                                                    <div
                                                        style="
                                                            color:#D8A15B;
                                                            font-size:22px;
                                                            margin-bottom:15px;
                                                        "
                                                    >

                                                        <?php

                                                        for (
                                                            $i = 1;
                                                            $i <= 5;
                                                            $i++
                                                        ) {

                                                            echo
                                                                $i <= $rating
                                                                    ? "★"
                                                                    : "☆";

                                                        }

                                                        ?>


                                                        <span
                                                            style="
                                                                color:#8A7468;
                                                                font-size:13px;
                                                                margin-left:8px;
                                                            "
                                                        >

                                                            <?php

                                                            echo $rating;

                                                            ?>/5

                                                        </span>

                                                    </div>


                                                    <!-- CUSTOMER -->

                                                    <p>

                                                        <strong>
                                                            From:
                                                        </strong>

                                                        <?php

                                                        echo htmlspecialchars(
                                                            $name,
                                                            ENT_QUOTES,
                                                            "UTF-8"
                                                        );

                                                        ?>

                                                    </p>


                                                    <!-- EMAIL -->

                                                    <?php if (
                                                        $email !== ""
                                                    ): ?>

                                                        <p>

                                                            <strong>
                                                                Email:
                                                            </strong>


                                                            <a
                                                                href="mailto:<?php

                                                                echo htmlspecialchars(
                                                                    $email,
                                                                    ENT_QUOTES,
                                                                    "UTF-8"
                                                                );

                                                                ?>"
                                                            >

                                                                <?php

                                                                echo htmlspecialchars(
                                                                    $email,
                                                                    ENT_QUOTES,
                                                                    "UTF-8"
                                                                );

                                                                ?>

                                                            </a>

                                                        </p>

                                                    <?php endif; ?>


                                                    <hr>


                                                    <!-- MESSAGE -->

                                                    <div
                                                        style="
                                                            white-space:pre-wrap;
                                                            color:#6F5548;
                                                            line-height:1.7;
                                                        "
                                                    >

                                                        <?php

                                                        echo htmlspecialchars(
                                                            $message,
                                                            ENT_QUOTES,
                                                            "UTF-8"
                                                        );

                                                        ?>

                                                    </div>

                                                </div>


                                                <!-- MODAL FOOTER -->

                                                <div
                                                    class="
                                                        modal-footer
                                                    "
                                                >


                                                    <!-- STATUS FORM -->

                                                    <form
                                                        method="POST"
                                                        action=""
                                                        class="d-flex"
                                                        style="
                                                            gap:8px;
                                                        "
                                                    >


                                                        <!-- ACTION -->

                                                        <input
                                                            type="hidden"
                                                            name="action"
                                                            value="update_feedback_status"
                                                        >


                                                        <!-- CSRF -->

                                                        <input
                                                            type="hidden"
                                                            name="csrf_token"
                                                            value="<?php

                                                            echo htmlspecialchars(
                                                                $_SESSION[
                                                                    "admin_feedback_csrf"
                                                                ],
                                                                ENT_QUOTES,
                                                                "UTF-8"
                                                            );

                                                            ?>"
                                                        >


                                                        <!-- FEEDBACK ID -->

                                                        <input
                                                            type="hidden"
                                                            name="feedback_id"
                                                            value="<?php

                                                            echo $feedbackId;

                                                            ?>"
                                                        >


                                                        <!-- STATUS -->

                                                        <select
                                                            name="status"
                                                            class="form-control"
                                                            style="
                                                                width:150px;
                                                            "
                                                            required
                                                        >

                                                            <option
                                                                value="pending"
                                                                <?php

                                                                echo
                                                                    $status ===
                                                                    "pending"
                                                                        ? "selected"
                                                                        : "";

                                                                ?>
                                                            >

                                                                Pending

                                                            </option>


                                                            <option
                                                                value="approved"
                                                                <?php

                                                                echo
                                                                    $status ===
                                                                    "approved"
                                                                        ? "selected"
                                                                        : "";

                                                                ?>
                                                            >

                                                                Approved

                                                            </option>


                                                            <option
                                                                value="rejected"
                                                                <?php

                                                                echo
                                                                    $status ===
                                                                    "rejected"
                                                                        ? "selected"
                                                                        : "";

                                                                ?>
                                                            >

                                                                Rejected

                                                            </option>

                                                        </select>


                                                        <button
                                                            type="submit"
                                                            class="
                                                                btn
                                                                btn-coffee
                                                            "
                                                        >

                                                            <i
                                                                class="
                                                                    fas
                                                                    fa-save
                                                                    mr-2
                                                                "
                                                            ></i>

                                                            Update Status

                                                        </button>

                                                    </form>


                                                    <!-- CLOSE -->

                                                    <button
                                                        type="button"
                                                        class="
                                                            btn
                                                            btn-secondary
                                                        "
                                                        data-dismiss="modal"
                                                    >

                                                        Close

                                                    </button>

                                                </div>


                                            </div>

                                        </div>

                                    </div>


                                <?php endwhile; ?>


                            <?php else: ?>


                                <tr>

                                    <td
                                        colspan="8"
                                        class="text-center"
                                        style="
                                            padding:70px 20px;
                                        "
                                    >

                                        <i
                                            class="
                                                fas
                                                fa-star
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

                                            No Feedback Found

                                        </h4>


                                        <p
                                            style="
                                                color:#8A7468;
                                            "
                                        >

                                            No customer feedback is
                                            currently available.

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

                            Total Feedback:

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


<?php

/* =========================================================
   COMMON FOOTER
========================================================= */

include "../includes/footer.php";

?>