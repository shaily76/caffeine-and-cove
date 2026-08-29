<?php

/* =========================================================
   CAFFEINE & COVE
   ADMIN - CONTACT MESSAGES
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
    empty($_SESSION["admin_message_csrf"])
) {

    $_SESSION["admin_message_csrf"] =
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
   MESSAGE STATUS UPDATE
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["action"]) &&
    $_POST["action"] === "update_message_status"
) {


    /* -----------------------------------------------------
       CSRF CHECK
    ----------------------------------------------------- */

    $csrfToken =
        $_POST["csrf_token"] ?? "";


    $sessionToken =
        $_SESSION["admin_message_csrf"] ?? "";


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


    /* -----------------------------------------------------
       MESSAGE ID
    ----------------------------------------------------- */

    $messageIdRaw =
        $_POST["message_id"] ?? "";


    if (
        $error === "" &&
        (
            !is_string($messageIdRaw) ||
            !ctype_digit($messageIdRaw) ||
            (int)$messageIdRaw <= 0
        )
    ) {

        $error =
            "Invalid message ID.";

    }


    /* -----------------------------------------------------
       STATUS
    ----------------------------------------------------- */

    $newStatus =
        trim(
            $_POST["status"] ?? ""
        );


    if (
        $error === "" &&
        !in_array(
            $newStatus,
            [
                "unread",
                "read"
            ],
            true
        )
    ) {

        $error =
            "Invalid message status.";

    }


    /* -----------------------------------------------------
       UPDATE
    ----------------------------------------------------- */

    if (
        $error === ""
    ) {

        $messageId =
            (int)$messageIdRaw;


        $sql = "
            UPDATE contact_messages
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
            $stmt === false
        ) {

            error_log(
                "Admin message status prepare failed: " .
                mysqli_error($link)
            );


            $error =
                "A database error occurred.";

        } else {


            mysqli_stmt_bind_param(
                $stmt,
                "si",
                $newStatus,
                $messageId
            );


            if (
                !mysqli_stmt_execute($stmt)
            ) {

                error_log(
                    "Admin message status update failed: " .
                    mysqli_stmt_error($stmt)
                );


                $error =
                    "Unable to update message status.";

            } else {

                $affectedRows =
                    mysqli_stmt_affected_rows(
                        $stmt
                    );


                if (
                    $affectedRows >= 0
                ) {

                    $success =
                        "Message status updated successfully.";

                }

            }


            mysqli_stmt_close(
                $stmt
            );

        }

    }

}


/* =========================================================
   GET MESSAGES
========================================================= */

$sql = "
    SELECT
        id,
        name,
        email,
        subject,
        message,
        status,
        created_at
    FROM contact_messages
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
        "Admin messages query failed: " .
        mysqli_error($link)
    );


    $error =
        "Unable to load messages right now.";

}


/* =========================================================
   COUNTS
========================================================= */

$totalMessages = 0;

$unreadMessages = 0;


$countSql = "
    SELECT
        COUNT(*) AS total_messages,
        COALESCE(
            SUM(
                CASE
                    WHEN status = 'unread'
                    THEN 1
                    ELSE 0
                END
            ),
            0
        ) AS unread_messages
    FROM contact_messages
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

        $totalMessages =
            (int)$countRow[
                "total_messages"
            ];


        $unreadMessages =
            (int)$countRow[
                "unread_messages"
            ];

    }

} else {

    error_log(
        "Admin message count query failed: " .
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
                            class="fas fa-envelope mr-2"
                            style="color:#7B4728;"
                        ></i>

                        Messages

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
                            class="breadcrumb-item active"
                        >

                            Messages

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

                <div class="col-md-4">

                    <div class="info-box">

                        <span
                            class="info-box-icon"
                        >

                            <i
                                class="
                                    fas
                                    fa-envelope
                                "
                            ></i>

                        </span>


                        <div
                            class="info-box-content"
                        >

                            <span
                                class="info-box-text"
                            >

                                Total Messages

                            </span>


                            <span
                                class="info-box-number"
                            >

                                <?php

                                echo $totalMessages;

                                ?>

                            </span>

                        </div>

                    </div>

                </div>


                <!-- READ -->

                <div class="col-md-4">

                    <div class="info-box">

                        <span
                            class="info-box-icon"
                        >

                            <i
                                class="
                                    fas
                                    fa-envelope-open
                                "
                            ></i>

                        </span>


                        <div
                            class="info-box-content"
                        >

                            <span
                                class="info-box-text"
                            >

                                Read

                            </span>


                            <span
                                class="info-box-number"
                            >

                                <?php

                                echo
                                    $totalMessages -
                                    $unreadMessages;

                                ?>

                            </span>

                        </div>

                    </div>

                </div>


                <!-- UNREAD -->

                <div class="col-md-4">

                    <div class="info-box">

                        <span
                            class="info-box-icon"
                        >

                            <i
                                class="
                                    fas
                                    fa-envelope
                                "
                            ></i>

                        </span>


                        <div
                            class="info-box-content"
                        >

                            <span
                                class="info-box-text"
                            >

                                Unread

                            </span>


                            <span
                                class="info-box-number"
                            >

                                <?php

                                echo $unreadMessages;

                                ?>

                            </span>

                        </div>

                    </div>

                </div>


            </div>


            <!-- =================================================
                 MESSAGES TABLE
            ================================================== -->

            <div class="card">


                <div class="card-header">

                    <h3
                        class="card-title"
                    >

                        <i
                            class="
                                fas
                                fa-inbox
                                mr-2
                            "
                        ></i>

                        Contact Messages

                    </h3>


                    <div class="card-tools">

                        <a
                            href="feedback.php"
                            class="
                                btn
                                btn-sm
                                btn-gold
                            "
                        >

                            <i
                                class="
                                    fas
                                    fa-star
                                    mr-1
                                "
                            ></i>

                            Customer Feedback

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
                                        Subject
                                    </th>

                                    <th>
                                        Message
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
                                    $message =
                                    mysqli_fetch_assoc(
                                        $result
                                    )
                                ): ?>


                                    <?php

                                    $messageId =
                                        (int)
                                        $message["id"];


                                    $name =
                                        (string)
                                        $message["name"];


                                    $email =
                                        (string)
                                        $message["email"];


                                    $subject =
                                        (string)
                                        $message["subject"];


                                    $messageText =
                                        trim(
                                            (string)
                                            $message["message"]
                                        );


                                    $status =
                                        strtolower(
                                            trim(
                                                (string)
                                                $message["status"]
                                            )
                                        );


                                    $createdAt =
                                        (string)
                                        $message["created_at"];


                                    /* -----------------------------------------
                                       SHORT MESSAGE
                                    ----------------------------------------- */

                                    $shortMessage =
                                        $messageText;


                                    if (
                                        strlen(
                                            $shortMessage
                                        ) > 70
                                    ) {

                                        $shortMessage =
                                            substr(
                                                $shortMessage,
                                                0,
                                                70
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

                                                echo $messageId;

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

                                            <?php endif; ?>

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
                                                    $subject,
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

                                            <?php if (
                                                $status === "unread"
                                            ): ?>

                                                <span
                                                    class="
                                                        badge
                                                        badge-gold
                                                    "
                                                >

                                                    <i
                                                        class="
                                                            fas
                                                            fa-envelope
                                                            mr-1
                                                        "
                                                    ></i>

                                                    Unread

                                                </span>

                                            <?php else: ?>

                                                <span
                                                    class="
                                                        badge
                                                        badge-secondary
                                                    "
                                                >

                                                    <i
                                                        class="
                                                            fas
                                                            fa-envelope-open
                                                            mr-1
                                                        "
                                                    ></i>

                                                    Read

                                                </span>

                                            <?php endif; ?>

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
                                                data-target="#messageModal<?php

                                                echo $messageId;

                                                ?>"
                                                title="View Message"
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
                                         MESSAGE MODAL
                                    ================================================== -->

                                    <div
                                        class="modal fade"
                                        id="messageModal<?php

                                        echo $messageId;

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
                                                class="modal-content"
                                            >


                                                <!-- MODAL HEADER -->

                                                <div
                                                    class="modal-header"
                                                >

                                                    <h5
                                                        class="modal-title"
                                                    >

                                                        <?php

                                                        echo htmlspecialchars(
                                                            $subject !== ""
                                                                ? $subject
                                                                : "Contact Message",
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
                                                    class="modal-body"
                                                >

                                                    <div
                                                        class="mb-3"
                                                    >

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


                                                        <br>


                                                        <strong>
                                                            Email:
                                                        </strong>


                                                        <?php if (
                                                            $email !== ""
                                                        ): ?>

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

                                                        <?php else: ?>

                                                            <span
                                                                style="
                                                                    color:#8A7468;
                                                                "
                                                            >

                                                                Not provided

                                                            </span>

                                                        <?php endif; ?>

                                                    </div>


                                                    <hr>


                                                    <div
                                                        style="
                                                            white-space:pre-wrap;
                                                            color:#6F5548;
                                                            line-height:1.6;
                                                        "
                                                    >

                                                        <?php

                                                        echo htmlspecialchars(
                                                            $messageText,
                                                            ENT_QUOTES,
                                                            "UTF-8"
                                                        );

                                                        ?>

                                                    </div>

                                                </div>


                                                <!-- MODAL FOOTER -->

                                                <div
                                                    class="modal-footer"
                                                >


                                                    <form
                                                        method="POST"
                                                        action=""
                                                    >

                                                        <input
                                                            type="hidden"
                                                            name="action"
                                                            value="update_message_status"
                                                        >


                                                        <input
                                                            type="hidden"
                                                            name="message_id"
                                                            value="<?php

                                                            echo $messageId;

                                                            ?>"
                                                        >


                                                        <input
                                                            type="hidden"
                                                            name="csrf_token"
                                                            value="<?php

                                                            echo htmlspecialchars(
                                                                $_SESSION[
                                                                    "admin_message_csrf"
                                                                ],
                                                                ENT_QUOTES,
                                                                "UTF-8"
                                                            );

                                                            ?>"
                                                        >


                                                        <input
                                                            type="hidden"
                                                            name="status"
                                                            value="<?php

                                                            echo
                                                                $status === "unread"
                                                                    ? "read"
                                                                    : "unread";

                                                            ?>"
                                                        >


                                                        <button
                                                            type="submit"
                                                            class="
                                                                btn
                                                                btn-coffee
                                                            "
                                                        >

                                                            <?php if (
                                                                $status === "unread"
                                                            ): ?>

                                                                <i
                                                                    class="
                                                                        fas
                                                                        fa-envelope-open
                                                                        mr-2
                                                                    "
                                                                ></i>

                                                                Mark as Read

                                                            <?php else: ?>

                                                                <i
                                                                    class="
                                                                        fas
                                                                        fa-envelope
                                                                        mr-2
                                                                    "
                                                                ></i>

                                                                Mark as Unread

                                                            <?php endif; ?>

                                                        </button>

                                                    </form>


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
                                        colspan="7"
                                        class="text-center"
                                        style="
                                            padding:70px 20px;
                                        "
                                    >

                                        <i
                                            class="
                                                fas
                                                fa-inbox
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

                                            No Messages Found

                                        </h4>


                                        <p
                                            style="
                                                color:#8A7468;
                                            "
                                        >

                                            No customer messages are
                                            currently available.

                                        </p>

                                    </td>

                                </tr>

                            <?php endif; ?>


                            </tbody>

                        </table>

                    </div>

                </div>


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