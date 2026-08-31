<?php

/* =========================================================
   CAFFEINE & COVE
   ADMIN - MANAGE USERS
========================================================= */

session_start();


/* =========================================================
   ADMIN AUTHENTICATION
========================================================= */

require_once "../admin_auth.php";


/* =========================================================
   DATABASE CONNECTION
========================================================= */

require_once "../../include/config.php";


/* =========================================================
   DELETE USER
========================================================= */

if (isset($_GET['delete'])) {

    $user_id = (int) $_GET['delete'];

    if ($user_id > 0) {

        $delete_stmt = mysqli_prepare(
            $link,
            "DELETE FROM users WHERE id = ?"
        );

        if ($delete_stmt) {

            mysqli_stmt_bind_param(
                $delete_stmt,
                "i",
                $user_id
            );

            mysqli_stmt_execute($delete_stmt);

            mysqli_stmt_close($delete_stmt);
        }
    }

    header("Location: user.php");
    exit;
}


/* =========================================================
   SEARCH
========================================================= */

$search = "";

if (isset($_GET['search'])) {

    $search = trim($_GET['search']);

}


/* =========================================================
   GET USERS
========================================================= */

if ($search !== "") {

    $search_sql = "%" . $search . "%";


    $stmt = mysqli_prepare(
        $link,
        "SELECT
            id,
            full_name,
            username,
            email,
            mobile,
            created_at,
            updated_at
         FROM users
         WHERE
            full_name LIKE ?
            OR username LIKE ?
            OR email LIKE ?
            OR mobile LIKE ?
         ORDER BY id DESC"
    );


    if ($stmt) {

        mysqli_stmt_bind_param(
            $stmt,
            "ssss",
            $search_sql,
            $search_sql,
            $search_sql,
            $search_sql
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

    } else {

        $result = false;

    }


} else {


    $result = mysqli_query(
        $link,
        "SELECT
            id,
            full_name,
            username,
            email,
            mobile,
            created_at,
            updated_at
         FROM users
         ORDER BY id DESC"
    );

}


/* =========================================================
   TOTAL USERS
========================================================= */

$count_result = mysqli_query(
    $link,
    "SELECT COUNT(*) AS total_users FROM users"
);


$count_data = mysqli_fetch_assoc(
    $count_result
);


$total_users = (int) (
    $count_data['total_users'] ?? 0
);


/* =========================================================
   COMMON ADMIN HEADER + SIDEBAR
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


                <!-- TITLE -->

                <div class="col-sm-6">

                    <h1 class="m-0">

                        <i
                            class="fas fa-user-cog mr-2"
                            style="color:#7B4728;"
                        ></i>

                        Manage Users

                    </h1>

                </div>


                <!-- BREADCRUMB -->

                <div class="col-sm-6">

                    <ol class="breadcrumb float-sm-right">

                        <li class="breadcrumb-item">

                            <a href="../dashboard.php">

                                Dashboard

                            </a>

                        </li>


                        <li class="breadcrumb-item active">

                            Users

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
                 TOTAL USERS
            ================================================== -->

            <div class="row mb-3">

                <div class="col-lg-4 col-md-6 col-sm-12">

                    <div class="card user-stat-card">


                        <div class="card-body">


                            <div
                                class="d-flex justify-content-between align-items-center"
                            >


                                <div>

                                    <p class="user-stat-title">

                                        Total Users

                                    </p>


                                    <h2 class="user-stat-number">

                                        <?php
                                        echo $total_users;
                                        ?>

                                    </h2>

                                </div>


                                <div class="user-stat-icon">

                                    <i class="fas fa-users"></i>

                                </div>


                            </div>


                        </div>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 SEARCH
            ================================================== -->

            <div class="card user-search-card">


                <div class="card-body">


                    <form
                        method="GET"
                        action="user.php"
                    >


                        <div class="row align-items-center">


                            <div class="col-lg-9 col-md-8 col-sm-12">

                                <div class="input-group">


                                    <div
                                        class="input-group-prepend"
                                    >

                                        <span
                                            class="input-group-text"
                                        >

                                            <i
                                                class="fas fa-search"
                                            ></i>

                                        </span>

                                    </div>


                                    <input
                                        type="text"
                                        name="search"
                                        class="form-control"
                                        placeholder="Search by name, username, email or mobile..."
                                        value="<?php
                                        echo htmlspecialchars(
                                            $search
                                        );
                                        ?>"
                                    >


                                </div>

                            </div>


                            <div
                                class="col-lg-3 col-md-4 col-sm-12 mt-2 mt-md-0"
                            >

                                <button
                                    type="submit"
                                    class="btn btn-user-search btn-block"
                                >

                                    <i
                                        class="fas fa-search mr-1"
                                    ></i>

                                    Search

                                </button>

                            </div>


                        </div>


                    </form>


                </div>

            </div>


            <!-- =================================================
                 USERS TABLE
            ================================================== -->

            <div class="card user-table-card">


                <!-- CARD HEADER -->

                <div class="card-header">


                    <div
                        class="d-flex justify-content-between align-items-center"
                    >


                        <h3 class="card-title">


                            <i
                                class="fas fa-users mr-2"
                                style="color:#7B4728;"
                            ></i>


                            Registered Users


                        </h3>


                        <?php if ($search !== ""): ?>

                            <span
                                class="badge user-search-badge"
                            >

                                Search:
                                <?php
                                echo htmlspecialchars(
                                    $search
                                );
                                ?>

                            </span>

                        <?php endif; ?>


                    </div>


                </div>


                <!-- TABLE -->

                <div class="card-body p-0">


                    <div class="table-responsive">


                        <table
                            class="table table-hover user-table mb-0"
                        >


                            <thead>


                                <tr>


                                    <th width="70">

                                        ID

                                    </th>


                                    <th>

                                        User

                                    </th>


                                    <th>

                                        Username

                                    </th>


                                    <th>

                                        Email

                                    </th>


                                    <th>

                                        Mobile

                                    </th>


                                    <th>

                                        Created

                                    </th>


                                    <th width="90">

                                        Action

                                    </th>


                                </tr>


                            </thead>


                            <tbody>


                            <?php
                            if (
                                $result &&
                                mysqli_num_rows($result) > 0
                            ):
                            ?>


                                <?php
                                while (
                                    $user =
                                    mysqli_fetch_assoc($result)
                                ):
                                ?>


                                    <tr>


                                        <!-- ID -->

                                        <td>

                                            <strong>

                                                #
                                                <?php
                                                echo (int)
                                                    $user['id'];
                                                ?>

                                            </strong>

                                        </td>


                                        <!-- USER -->

                                        <td>


                                            <div
                                                class="d-flex align-items-center user-info"
                                            >


                                                <div
                                                    class="user-avatar"
                                                >

                                                    <i
                                                        class="fas fa-user"
                                                    ></i>

                                                </div>


                                                <strong>

                                                    <?php

                                                    echo htmlspecialchars(
                                                        $user['full_name']
                                                        ?? ''
                                                    );

                                                    ?>

                                                </strong>


                                            </div>


                                        </td>


                                        <!-- USERNAME -->

                                        <td>


                                            <span
                                                class="user-username"
                                            >

                                                @<?php

                                                echo htmlspecialchars(
                                                    $user['username']
                                                    ?? ''
                                                );

                                                ?>

                                            </span>


                                        </td>


                                        <!-- EMAIL -->

                                        <td>

                                            <?php

                                            echo htmlspecialchars(
                                                $user['email']
                                                ?? ''
                                            );

                                            ?>

                                        </td>


                                        <!-- MOBILE -->

                                        <td>

                                            <?php

                                            echo htmlspecialchars(
                                                $user['mobile']
                                                ?? ''
                                            );

                                            ?>

                                        </td>


                                        <!-- CREATED -->

                                        <td>

                                            <?php

                                            if (
                                                !empty(
                                                    $user['created_at']
                                                )
                                            ) {

                                                echo date(
                                                    "d M Y, h:i A",
                                                    strtotime(
                                                        $user['created_at']
                                                    )
                                                );

                                            } else {

                                                echo "-";

                                            }

                                            ?>

                                        </td>


                                        <!-- ACTION -->

                                        <td>


                                            <a
                                                href="user.php?delete=<?php
                                                echo (int)
                                                    $user['id'];
                                                ?>"
                                                class="btn btn-sm btn-user-delete"
                                                onclick="
                                                    return confirm(
                                                        'Are you sure you want to delete this user? This action cannot be undone.'
                                                    );
                                                "
                                                title="Delete User"
                                            >

                                                <i
                                                    class="fas fa-trash"
                                                ></i>

                                            </a>


                                        </td>


                                    </tr>


                                <?php endwhile; ?>


                            <?php else: ?>


                                <!-- NO USERS -->

                                <tr>


                                    <td
                                        colspan="7"
                                        class="text-center py-5"
                                    >


                                        <div
                                            class="user-empty-state"
                                        >


                                            <i
                                                class="fas fa-user-slash fa-2x mb-3"
                                            ></i>


                                            <p>

                                                No users found.

                                            </p>


                                            <?php
                                            if (
                                                $search !== ""
                                            ):
                                            ?>


                                                <a
                                                    href="user.php"
                                                    class="btn btn-sm btn-outline-secondary"
                                                >

                                                    Clear Search

                                                </a>


                                            <?php endif; ?>


                                        </div>


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

                <?php

                if (
                    $result &&
                    mysqli_num_rows($result) > 0
                ):

                ?>


                    <div class="card-footer">


                        <span class="user-total-text">

                            Total Users:

                            <strong>

                                <?php
                                echo $total_users;
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
   COMMON ADMIN FOOTER
========================================================= */

include "../includes/footer.php";


/* =========================================================
   CLOSE SEARCH STATEMENT
========================================================= */

if (
    isset($stmt) &&
    $stmt
) {

    mysqli_stmt_close($stmt);

}

?>