<?php

/* =========================================================
   CAFFEINE & COVE
   ADMIN - RESERVATION DETAILS
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

$reservationId = 0;

$error = "";

$success = "";

$reservation = null;


/* =========================================================
   GET RESERVATION ID
========================================================= */

if (
    isset($_GET["id"]) &&
    ctype_digit($_GET["id"])
) {

    $reservationId =
        (int)$_GET["id"];

}

elseif (
    isset($_POST["reservation_id"]) &&
    ctype_digit($_POST["reservation_id"])
) {

    $reservationId =
        (int)$_POST["reservation_id"];

}

else {

    $error =
        "Invalid reservation ID.";

}


/* =========================================================
   UPDATE STATUS
========================================================= */

if (
    $error === "" &&
    $_SERVER["REQUEST_METHOD"] === "POST"
) {


    /* -----------------------------------------------------
       CSRF TOKEN
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

        $error =
            "Security validation failed. Please try again.";

    }


    /* -----------------------------------------------------
       STATUS
    ----------------------------------------------------- */

    if (
        $error === ""
    ) {

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

            $error =
                "Invalid reservation status.";

        }

    }


    /* -----------------------------------------------------
       CHECK RESERVATION EXISTS
    ----------------------------------------------------- */

    if (
        $error === ""
    ) {

        $checkSql = "
            SELECT
                id
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
                "Reservation details check failed: " .
                mysqli_error($link)
            );

            $error =
                "A database error occurred.";

        }

        else {

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

                error_log(
                    "Reservation details check execute failed: " .
                    mysqli_stmt_error($checkStmt)
                );

                $error =
                    "A database error occurred.";

            }

            else {

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
                        "Reservation not found.";

                }

            }


            mysqli_stmt_close(
                $checkStmt
            );

        }

    }


    /* -----------------------------------------------------
       UPDATE STATUS
    ----------------------------------------------------- */

    if (
        $error === ""
    ) {

        $sql = "
            UPDATE reservations
            SET
                status = ?,
                updated_at = CURRENT_TIMESTAMP
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
                "Reservation details update prepare failed: " .
                mysqli_error($link)
            );

            $error =
                "A database error occurred.";

        }

        else {

            mysqli_stmt_bind_param(
                $stmt,
                "si",
                $newStatus,
                $reservationId
            );


            if (
                mysqli_stmt_execute(
                    $stmt
                )
            ) {

                $success =
                    "Reservation status updated successfully.";

            }

            else {

                error_log(
                    "Reservation details update failed: " .
                    mysqli_stmt_error($stmt)
                );

                $error =
                    "Unable to update reservation status.";

            }


            mysqli_stmt_close(
                $stmt
            );

        }

    }

}


/* =========================================================
   GET RESERVATION
========================================================= */

if (
    $reservationId > 0
) {

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
            created_at,
            updated_at
        FROM reservations
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
            "Reservation details fetch prepare failed: " .
            mysqli_error($link)
        );

        if (
            $error === ""
        ) {

            $error =
                "A database error occurred.";

        }

    }

    else {

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $reservationId
        );


        if (
            mysqli_stmt_execute(
                $stmt
            )
        ) {

            $result =
                mysqli_stmt_get_result(
                    $stmt
                );


            if (
                $result &&
                mysqli_num_rows(
                    $result
                ) === 1
            ) {

                $reservation =
                    mysqli_fetch_assoc(
                        $result
                    );

            }

            else {

                if (
                    $error === ""
                ) {

                    $error =
                        "Reservation not found.";

                }

            }

        }

        else {

            error_log(
                "Reservation details fetch failed: " .
                mysqli_stmt_error($stmt)
            );

            if (
                $error === ""
            ) {

                $error =
                    "Unable to load reservation.";

            }

        }


        mysqli_stmt_close(
            $stmt
        );

    }

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
                            class="fas fa-calendar-check mr-2"
                            style="color:#7B4728;"
                        ></i>

                        Reservation Details

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
                                href="reservations.php"
                            >
                                Reservations
                            </a>

                        </li>


                        <li
                            class="breadcrumb-item active"
                        >

                            Details

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

                </div>


                <a
                    href="reservations.php"
                    class="btn btn-coffee"
                >

                    <i
                        class="
                            fas
                            fa-arrow-left
                            mr-2
                        "
                    ></i>

                    Back to Reservations

                </a>

            <?php endif; ?>


            <?php if (
                $reservation !== null
            ): ?>


                <?php

                /* =================================================
                   CURRENT VALUES
                ================================================= */

                $currentStatus =
                    strtolower(
                        trim(
                            (string)
                            $reservation["status"]
                        )
                    );


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


                /* =================================================
                   DATE
                ================================================= */

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


                /* =================================================
                   TIME
                ================================================= */

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


                /* =================================================
                   CREATED
                ================================================= */

                $createdDate =
                    "-";


                if (
                    !empty(
                        $reservation["created_at"]
                    )
                ) {

                    $createdTimestamp =
                        strtotime(
                            $reservation[
                                "created_at"
                            ]
                        );


                    if (
                        $createdTimestamp !== false
                    ) {

                        $createdDate =
                            date(
                                "d M Y, h:i A",
                                $createdTimestamp
                            );

                    }

                }


                /* =================================================
                   UPDATED
                ================================================= */

                $updatedDate =
                    "-";


                if (
                    !empty(
                        $reservation["updated_at"]
                    )
                ) {

                    $updatedTimestamp =
                        strtotime(
                            $reservation[
                                "updated_at"
                            ]
                        );


                    if (
                        $updatedTimestamp !== false
                    ) {

                        $updatedDate =
                            date(
                                "d M Y, h:i A",
                                $updatedTimestamp
                            );

                    }

                }


                /* =================================================
                   SPECIAL REQUEST
                ================================================= */

                $specialRequest =
                    trim(
                        (string)
                        $reservation[
                            "special_request"
                        ]
                    );

                ?>


                <!-- =================================================
                     INFORMATION CARDS
                ================================================== -->

                <div class="row">


                    <!-- GUEST INFORMATION -->

                    <div class="col-md-6">

                        <div class="card">


                            <div
                                class="card-header"
                            >

                                <h3
                                    class="card-title"
                                >

                                    <i
                                        class="
                                            fas
                                            fa-user
                                            mr-2
                                        "
                                    ></i>

                                    Guest Information

                                </h3>

                            </div>


                            <div
                                class="card-body"
                            >


                                <!-- NAME -->

                                <div
                                    class="mb-3"
                                >

                                    <small
                                        style="
                                            color:#8A7468;
                                        "
                                    >

                                        Guest Name

                                    </small>


                                    <div
                                        style="
                                            color:#4A2C1D;
                                            font-weight:600;
                                            font-size:18px;
                                        "
                                    >

                                        <?php

                                        echo htmlspecialchars(
                                            $reservation[
                                                "guest_name"
                                            ],
                                            ENT_QUOTES,
                                            "UTF-8"
                                        );

                                        ?>

                                    </div>

                                </div>


                                <!-- EMAIL -->

                                <div
                                    class="mb-3"
                                >

                                    <small
                                        style="
                                            color:#8A7468;
                                        "
                                    >

                                        Email

                                    </small>


                                    <div>

                                        <a
                                            href="mailto:<?php

                                            echo htmlspecialchars(
                                                $reservation[
                                                    "email"
                                                ],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            );

                                            ?>"
                                        >

                                            <?php

                                            echo htmlspecialchars(
                                                $reservation[
                                                    "email"
                                                ],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            );

                                            ?>

                                        </a>

                                    </div>

                                </div>


                                <!-- PHONE -->

                                <div>

                                    <small
                                        style="
                                            color:#8A7468;
                                        "
                                    >

                                        Phone

                                    </small>


                                    <div>

                                        <a
                                            href="tel:<?php

                                            echo htmlspecialchars(
                                                $reservation[
                                                    "phone"
                                                ],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            );

                                            ?>"
                                        >

                                            <?php

                                            echo htmlspecialchars(
                                                $reservation[
                                                    "phone"
                                                ],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            );

                                            ?>

                                        </a>

                                    </div>

                                </div>


                            </div>

                        </div>

                    </div>


                    <!-- RESERVATION INFORMATION -->

                    <div class="col-md-6">

                        <div class="card">


                            <div
                                class="card-header"
                            >

                                <h3
                                    class="card-title"
                                >

                                    <i
                                        class="
                                            fas
                                            fa-calendar-alt
                                            mr-2
                                        "
                                    ></i>

                                    Reservation Information

                                </h3>

                            </div>


                            <div
                                class="card-body"
                            >


                                <div
                                    class="row"
                                >


                                    <div
                                        class="col-sm-6"
                                    >

                                        <small
                                            style="
                                                color:#8A7468;
                                            "
                                        >

                                            Date

                                        </small>


                                        <div
                                            style="
                                                color:#4A2C1D;
                                                font-weight:600;
                                                font-size:17px;
                                            "
                                        >

                                            <?php

                                            echo htmlspecialchars(
                                                $formattedDate,
                                                ENT_QUOTES,
                                                "UTF-8"
                                            );

                                            ?>

                                        </div>

                                    </div>


                                    <div
                                        class="col-sm-6"
                                    >

                                        <small
                                            style="
                                                color:#8A7468;
                                            "
                                        >

                                            Time

                                        </small>


                                        <div
                                            style="
                                                color:#4A2C1D;
                                                font-weight:600;
                                                font-size:17px;
                                            "
                                        >

                                            <?php

                                            echo htmlspecialchars(
                                                $formattedTime,
                                                ENT_QUOTES,
                                                "UTF-8"
                                            );

                                            ?>

                                        </div>

                                    </div>

                                </div>


                                <hr>


                                <div
                                    class="row"
                                >


                                    <div
                                        class="col-sm-6"
                                    >

                                        <small
                                            style="
                                                color:#8A7468;
                                            "
                                        >

                                            Number of Guests

                                        </small>


                                        <div>

                                            <span
                                                style="
                                                    display:inline-block;
                                                    background:#F5E8DA;
                                                    color:#7B4728;
                                                    padding:7px 12px;
                                                    border-radius:20px;
                                                    font-weight:600;
                                                "
                                            >

                                                <i
                                                    class="
                                                        fas
                                                        fa-users
                                                        mr-1
                                                    "
                                                ></i>

                                                <?php

                                                echo (int)
                                                    $reservation[
                                                        "guests"
                                                    ];

                                                ?>

                                            </span>

                                        </div>

                                    </div>


                                    <div
                                        class="col-sm-6"
                                    >

                                        <small
                                            style="
                                                color:#8A7468;
                                            "
                                        >

                                            Reservation ID

                                        </small>


                                        <div
                                            style="
                                                color:#7B4728;
                                                font-weight:600;
                                                font-size:17px;
                                            "
                                        >

                                            #

                                            <?php

                                            echo (int)
                                                $reservation[
                                                    "id"
                                                ];

                                            ?>

                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     SPECIAL REQUEST
                ================================================== -->

                <div class="card">


                    <div
                        class="card-header"
                    >

                        <h3
                            class="card-title"
                        >

                            <i
                                class="
                                    fas
                                    fa-comment-alt
                                    mr-2
                                "
                            ></i>

                            Special Request

                        </h3>

                    </div>


                    <div
                        class="card-body"
                    >


                        <?php if (
                            $specialRequest !== ""
                        ): ?>

                            <div
                                style="
                                    background:#FFF8F2;
                                    border-left:4px solid #D8A15B;
                                    padding:15px;
                                    border-radius:6px;
                                    color:#6F5548;
                                    line-height:1.6;
                                "
                            >

                                <?php

                                echo nl2br(
                                    htmlspecialchars(
                                        $specialRequest,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    )
                                );

                                ?>

                            </div>

                        <?php else: ?>

                            <p
                                class="mb-0"
                                style="
                                    color:#9A8477;
                                "
                            >

                                No special request was provided.

                            </p>

                        <?php endif; ?>


                    </div>

                </div>


                <!-- =================================================
                     STATUS MANAGEMENT
                ================================================== -->

                <div class="card">


                    <div
                        class="card-header"
                    >

                        <h3
                            class="card-title"
                        >

                            <i
                                class="
                                    fas
                                    fa-tasks
                                    mr-2
                                "
                            ></i>

                            Reservation Status

                        </h3>

                    </div>


                    <div
                        class="card-body"
                    >


                        <form
                            method="POST"
                            action=""
                            onsubmit="
                                return confirm(
                                    'Are you sure you want to update this reservation status?'
                                );
                            "
                        >


                            <!-- CSRF -->

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


                            <!-- RESERVATION ID -->

                            <input
                                type="hidden"
                                name="reservation_id"
                                value="<?php

                                echo (int)
                                    $reservation[
                                        "id"
                                    ];

                                ?>"
                            >


                            <div
                                class="row"
                            >


                                <div
                                    class="col-md-6"
                                >

                                    <label
                                        for="status"
                                    >

                                        Status

                                    </label>


                                    <select
                                        id="status"
                                        name="status"
                                        class="form-control"
                                        required
                                    >


                                        <option
                                            value="pending"
                                            <?php

                                            echo
                                                $currentStatus ===
                                                "pending"
                                                    ? "selected"
                                                    : "";

                                            ?>
                                        >

                                            Pending

                                        </option>


                                        <option
                                            value="confirmed"
                                            <?php

                                            echo
                                                $currentStatus ===
                                                "confirmed"
                                                    ? "selected"
                                                    : "";

                                            ?>
                                        >

                                            Confirmed

                                        </option>


                                        <option
                                            value="completed"
                                            <?php

                                            echo
                                                $currentStatus ===
                                                "completed"
                                                    ? "selected"
                                                    : "";

                                            ?>
                                        >

                                            Completed

                                        </option>


                                        <option
                                            value="cancelled"
                                            <?php

                                            echo
                                                $currentStatus ===
                                                "cancelled"
                                                    ? "selected"
                                                    : "";

                                            ?>
                                        >

                                            Cancelled

                                        </option>


                                    </select>

                                </div>


                                <div
                                    class="
                                        col-md-6
                                        d-flex
                                        align-items-end
                                    "
                                >

                                    <button
                                        type="submit"
                                        class="btn btn-coffee"
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

                                </div>


                            </div>

                        </form>

                    </div>

                </div>


                <!-- =================================================
                     TIMESTAMPS
                ================================================== -->

                <div class="card">


                    <div
                        class="card-body"
                    >


                        <div
                            class="row"
                        >


                            <div
                                class="col-md-6"
                            >

                                <span
                                    style="
                                        color:#8A7468;
                                    "
                                >

                                    Created On

                                </span>


                                <div
                                    style="
                                        color:#4A2C1D;
                                        font-weight:600;
                                    "
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $createdDate,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );

                                    ?>

                                </div>

                            </div>


                            <div
                                class="col-md-6"
                            >

                                <span
                                    style="
                                        color:#8A7468;
                                    "
                                >

                                    Last Updated

                                </span>


                                <div
                                    style="
                                        color:#4A2C1D;
                                        font-weight:600;
                                    "
                                >

                                    <?php

                                    echo htmlspecialchars(
                                        $updatedDate,
                                        ENT_QUOTES,
                                        "UTF-8"
                                    );

                                    ?>

                                </div>

                            </div>

                        </div>


                        <hr>


                        <div>

                            <span
                                style="
                                    color:#8A7468;
                                "
                            >

                                Current Status

                            </span>


                            <div
                                style="
                                    color:#7B4728;
                                    font-weight:600;
                                    margin-top:4px;
                                "
                            >

                                <?php

                                echo htmlspecialchars(
                                    ucfirst(
                                        $currentStatus
                                    ),
                                    ENT_QUOTES,
                                    "UTF-8"
                                );

                                ?>

                            </div>

                        </div>


                    </div>

                </div>


                <!-- =================================================
                     BACK
                ================================================== -->

                <a
                    href="reservations.php"
                    class="
                        btn
                        btn-secondary
                        mb-4
                    "
                >

                    <i
                        class="
                            fas
                            fa-arrow-left
                            mr-2
                        "
                    ></i>

                    Back to Reservations

                </a>


            <?php endif; ?>


        </div>

    </section>

</div>


<?php

/* =========================================================
   FOOTER
========================================================= */

include "../includes/footer.php";

?>