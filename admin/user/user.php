<?php
session_start();

/* =========================================================
   CAFFEINE & COVE
   ADMIN - MANAGE USERS
========================================================= */


/* =========================================================
   DATABASE CONNECTION
========================================================= */

require_once("../../include/config.php");


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

    $search_sql = "%".$search."%";

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

$count_data = mysqli_fetch_assoc($count_result);

$total_users = (int) ($count_data['total_users'] ?? 0);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport"
          content="width=device-width, initial-scale=1.0">

    <title>Manage Users | Caffeine & Cove Admin</title>

    <!-- Bootstrap -->
    <link
        rel="stylesheet"
        href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css"
    >

    <!-- Font Awesome -->
    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css"
    >

    <!-- Admin CSS -->
    <link
        rel="stylesheet"
        href="../../css/admin_css/user.css"
    >

</head>


<body class="bg-light">


<div class="container-fluid py-4">


    <!-- =====================================================
         PAGE HEADER
    ====================================================== -->

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h2 class="mb-1">
                <i class="fas fa-users"></i>
                Manage Users
            </h2>

            <p class="text-muted mb-0">
                View and manage registered customers.
            </p>

        </div>


        <div>

            <a
                href="user.php"
                class="btn btn-outline-secondary"
            >
                <i class="fas fa-sync-alt"></i>
                Refresh
            </a>

        </div>

    </div>


    <!-- =====================================================
         STAT CARD
    ====================================================== -->

    <div class="row mb-4">

        <div class="col-md-4">

            <div class="card shadow-sm border-0">

                <div class="card-body">

                    <div class="d-flex justify-content-between">

                        <div>

                            <h6 class="text-muted">
                                Total Users
                            </h6>

                            <h2 class="mb-0">
                                <?php echo $total_users; ?>
                            </h2>

                        </div>

                        <div
                            style="
                                width:50px;
                                height:50px;
                                border-radius:12px;
                                display:flex;
                                align-items:center;
                                justify-content:center;
                                background:#f3f4f6;
                            "
                        >

                            <i class="fas fa-users fa-lg"></i>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>


    <!-- =====================================================
         SEARCH
    ====================================================== -->

    <div class="card shadow-sm border-0 mb-4">

        <div class="card-body">

            <form
                method="GET"
                action="user.php"
            >

                <div class="row">

                    <div class="col-md-9">

                        <div class="input-group">

                            <div class="input-group-prepend">

                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>

                            </div>

                            <input
                                type="text"
                                name="search"
                                class="form-control"
                                placeholder="Search by name, username, email or mobile..."
                                value="<?php echo htmlspecialchars($search); ?>"
                            >

                        </div>

                    </div>


                    <div class="col-md-3 mt-2 mt-md-0">

                        <button
                            type="submit"
                            class="btn btn-dark btn-block"
                        >
                            <i class="fas fa-search"></i>
                            Search
                        </button>

                    </div>

                </div>

            </form>

        </div>

    </div>


    <!-- =====================================================
         USERS TABLE
    ====================================================== -->

    <div class="card shadow-sm border-0">

        <div class="card-header bg-white">

            <div class="d-flex justify-content-between align-items-center">

                <h5 class="mb-0">
                    Registered Users
                </h5>

                <?php if ($search !== ""): ?>

                    <span class="badge badge-secondary">
                        Search: <?php echo htmlspecialchars($search); ?>
                    </span>

                <?php endif; ?>

            </div>

        </div>


        <div class="card-body p-0">

            <div class="table-responsive">

                <table class="table table-hover mb-0">

                    <thead class="thead-light">

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

                            <th width="120">
                                Action
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                    <?php if ($result && mysqli_num_rows($result) > 0): ?>

                        <?php while ($user = mysqli_fetch_assoc($result)): ?>

                            <tr>

                                <!-- ID -->

                                <td>

                                    <strong>
                                        #<?php echo (int) $user['id']; ?>
                                    </strong>

                                </td>


                                <!-- NAME -->

                                <td>

                                    <div class="d-flex align-items-center">

                                        <div
                                            style="
                                                width:40px;
                                                height:40px;
                                                border-radius:50%;
                                                display:flex;
                                                align-items:center;
                                                justify-content:center;
                                                background:#eeeeee;
                                                margin-right:10px;
                                            "
                                        >

                                            <i class="fas fa-user"></i>

                                        </div>


                                        <div>

                                            <strong>
                                                <?php
                                                echo htmlspecialchars(
                                                    $user['full_name'] ?? ''
                                                );
                                                ?>
                                            </strong>

                                        </div>

                                    </div>

                                </td>


                                <!-- USERNAME -->

                                <td>

                                    <span class="text-muted">

                                        @<?php
                                        echo htmlspecialchars(
                                            $user['username'] ?? ''
                                        );
                                        ?>

                                    </span>

                                </td>


                                <!-- EMAIL -->

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $user['email'] ?? ''
                                    );
                                    ?>

                                </td>


                                <!-- MOBILE -->

                                <td>

                                    <?php
                                    echo htmlspecialchars(
                                        $user['mobile'] ?? ''
                                    );
                                    ?>

                                </td>


                                <!-- CREATED -->

                                <td>

                                    <?php

                                    if (!empty($user['created_at'])) {

                                        echo date(
                                            "d M Y, h:i A",
                                            strtotime($user['created_at'])
                                        );

                                    } else {

                                        echo "-";

                                    }

                                    ?>

                                </td>


                                <!-- ACTION -->

                                <td>

                                    <a
                                        href="user.php?delete=<?php echo (int) $user['id']; ?>"
                                        class="btn btn-sm btn-outline-danger"
                                        onclick="
                                            return confirm(
                                                'Are you sure you want to delete this user? This action cannot be undone.'
                                            );
                                        "
                                        title="Delete User"
                                    >

                                        <i class="fas fa-trash"></i>

                                    </a>

                                </td>

                            </tr>

                        <?php endwhile; ?>


                    <?php else: ?>

                        <tr>

                            <td
                                colspan="7"
                                class="text-center py-5"
                            >

                                <div class="text-muted">

                                    <i
                                        class="fas fa-user-slash fa-2x mb-3"
                                    ></i>

                                    <p class="mb-1">
                                        No users found.
                                    </p>

                                    <?php if ($search !== ""): ?>

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

    </div>


</div>


</body>

</html>

<?php

if (isset($stmt) && $stmt) {
    mysqli_stmt_close($stmt);
}

?>