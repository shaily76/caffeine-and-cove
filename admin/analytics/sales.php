<?php

/* =========================================================
   CAFFEINE & COVE
   ADMIN - SALES ANALYTICS
   ========================================================= */
require_once "../admin_auth.php";

/* =========================================================
   DATABASE CONNECTION
   ========================================================= */

require_once "../../include/config.php";


/* =========================================================
   FILTER
   ========================================================= */

$period = $_GET["period"] ?? "30";


$allowedPeriods = [
    "7",
    "30",
    "90",
    "365"
];


if (!in_array(
    $period,
    $allowedPeriods,
    true
)) {

    $period = "30";

}


$days = (int)$period;


/* =========================================================
   DATE RANGE
   ========================================================= */

$startDate = date(
    "Y-m-d",
    strtotime("-" . ($days - 1) . " days")
);

$endDate = date("Y-m-d");


/* =========================================================
   TOTAL SALES
   ========================================================= */

$totalSales = 0;

$sql = "
    SELECT
        COALESCE(SUM(total), 0) AS total_sales
    FROM orders
    WHERE
        status = 'completed'
        AND DATE(created_at) BETWEEN ? AND ?
";


$stmt = mysqli_prepare(
    $link,
    $sql
);


if ($stmt !== false) {

    mysqli_stmt_bind_param(
        $stmt,
        "ss",
        $startDate,
        $endDate
    );

    mysqli_stmt_execute($stmt);

    $result =
        mysqli_stmt_get_result($stmt);

    if ($result) {

        $row =
            mysqli_fetch_assoc($result);

        $totalSales =
            (float)$row["total_sales"];

    }

    mysqli_stmt_close($stmt);

}


/* =========================================================
   TOTAL ORDERS
   ========================================================= */

$totalOrders = 0;

$sql = "
    SELECT
        COUNT(*) AS total_orders
    FROM orders
    WHERE
        DATE(created_at) BETWEEN ? AND ?
";


$stmt = mysqli_prepare(
    $link,
    $sql
);


if ($stmt !== false) {

    mysqli_stmt_bind_param(
        $stmt,
        "ss",
        $startDate,
        $endDate
    );

    mysqli_stmt_execute($stmt);

    $result =
        mysqli_stmt_get_result($stmt);

    if ($result) {

        $row =
            mysqli_fetch_assoc($result);

        $totalOrders =
            (int)$row["total_orders"];

    }

    mysqli_stmt_close($stmt);

}


/* =========================================================
   COMPLETED ORDERS
   ========================================================= */

$completedOrders = 0;

$sql = "
    SELECT
        COUNT(*) AS completed_orders
    FROM orders
    WHERE
        status = 'completed'
        AND DATE(created_at) BETWEEN ? AND ?
";


$stmt = mysqli_prepare(
    $link,
    $sql
);


if ($stmt !== false) {

    mysqli_stmt_bind_param(
        $stmt,
        "ss",
        $startDate,
        $endDate
    );

    mysqli_stmt_execute($stmt);

    $result =
        mysqli_stmt_get_result($stmt);

    if ($result) {

        $row =
            mysqli_fetch_assoc($result);

        $completedOrders =
            (int)$row["completed_orders"];

    }

    mysqli_stmt_close($stmt);

}


/* =========================================================
   AVERAGE ORDER VALUE
   ========================================================= */

$averageOrderValue = 0;

if ($completedOrders > 0) {

    $averageOrderValue =
        $totalSales /
        $completedOrders;

}


/* =========================================================
   DAILY SALES
   ========================================================= */

$dailySales = [];

for ($i = $days - 1; $i >= 0; $i--) {

    $dateKey =
        date(
            "Y-m-d",
            strtotime(
                "-" . $i . " days"
            )
        );

    $dailySales[$dateKey] = 0;

}


$sql = "
    SELECT
        DATE(created_at) AS sale_date,
        COALESCE(SUM(total), 0) AS daily_total
    FROM orders
    WHERE
        status = 'completed'
        AND DATE(created_at) BETWEEN ? AND ?
    GROUP BY DATE(created_at)
    ORDER BY DATE(created_at)
";


$stmt = mysqli_prepare(
    $link,
    $sql
);


if ($stmt !== false) {

    mysqli_stmt_bind_param(
        $stmt,
        "ss",
        $startDate,
        $endDate
    );

    mysqli_stmt_execute($stmt);

    $result =
        mysqli_stmt_get_result($stmt);

    if ($result) {

        while (
            $row =
            mysqli_fetch_assoc($result)
        ) {

            $saleDate =
                $row["sale_date"];

            if (
                isset(
                    $dailySales[$saleDate]
                )
            ) {

                $dailySales[$saleDate] =
                    (float)$row["daily_total"];

            }

        }

    }

    mysqli_stmt_close($stmt);

}


/* =========================================================
   TOP SELLING PRODUCTS
   ========================================================= */

$topProducts = null;

$sql = "
    SELECT
        product_name,
        SUM(quantity) AS total_quantity,
        SUM(item_total) AS total_sales
    FROM order_details od
    INNER JOIN orders o
        ON od.order_id = o.id
    WHERE
        o.status = 'completed'
        AND DATE(o.created_at) BETWEEN ? AND ?
    GROUP BY
        product_id,
        product_name
    ORDER BY
        total_quantity DESC,
        total_sales DESC
    LIMIT 10
";


$stmt = mysqli_prepare(
    $link,
    $sql
);


if ($stmt !== false) {

    mysqli_stmt_bind_param(
        $stmt,
        "ss",
        $startDate,
        $endDate
    );

    mysqli_stmt_execute($stmt);

    $topProducts =
        mysqli_stmt_get_result($stmt);

}


/* =========================================================
   ORDER STATUS SUMMARY
   ========================================================= */

$statusSummary = [];

$sql = "
    SELECT
        status,
        COUNT(*) AS status_count
    FROM orders
    WHERE
        DATE(created_at) BETWEEN ? AND ?
    GROUP BY status
    ORDER BY status_count DESC
";


$stmt = mysqli_prepare(
    $link,
    $sql
);


if ($stmt !== false) {

    mysqli_stmt_bind_param(
        $stmt,
        "ss",
        $startDate,
        $endDate
    );

    mysqli_stmt_execute($stmt);

    $result =
        mysqli_stmt_get_result($stmt);

    if ($result) {

        while (
            $row =
            mysqli_fetch_assoc($result)
        ) {

            $statusSummary[] =
                $row;

        }

    }

    mysqli_stmt_close($stmt);

}


/* =========================================================
   PREPARE CHART DATA
   ========================================================= */

$chartLabels = [];

$chartValues = [];


foreach (
    $dailySales
    as $date => $value
) {

    $chartLabels[] =
        date(
            "d M",
            strtotime($date)
        );

    $chartValues[] =
        round(
            $value,
            2
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
                            class="fas fa-chart-line mr-2"
                            style="color:#7B4728;"
                        ></i>

                        Sales Analytics

                    </h1>

                </div>


                <div class="col-sm-6">

                    <ol class="breadcrumb float-sm-right">

                        <li class="breadcrumb-item">

                            <a href="../dashboard.php">
                                Dashboard
                            </a>

                        </li>

                        <li class="breadcrumb-item active">

                            Sales Analytics

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
                 FILTER
            ================================================== -->

            <div class="card">

                <div class="card-body">

                    <div
                        class="d-flex align-items-center justify-content-between flex-wrap"
                    >


                        <div>

                            <strong
                                style="
                                    color:#4A2C1D;
                                "
                            >

                                Sales Period

                            </strong>

                            <div
                                style="
                                    color:#8A7468;
                                    font-size:13px;
                                "
                            >

                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime($startDate)
                                );
                                ?>

                                -
                                
                                <?php
                                echo date(
                                    "d M Y",
                                    strtotime($endDate)
                                );
                                ?>

                            </div>

                        </div>


                        <div class="mt-2 mt-md-0">

                            <a
                                href="sales.php?period=7"
                                class="btn btn-sm
                                <?php
                                echo $period === "7"
                                    ? "btn-coffee"
                                    : "btn-light";
                                ?>"
                            >

                                7 Days

                            </a>


                            <a
                                href="sales.php?period=30"
                                class="btn btn-sm
                                <?php
                                echo $period === "30"
                                    ? "btn-coffee"
                                    : "btn-light";
                                ?>"
                            >

                                30 Days

                            </a>


                            <a
                                href="sales.php?period=90"
                                class="btn btn-sm
                                <?php
                                echo $period === "90"
                                    ? "btn-coffee"
                                    : "btn-light";
                                ?>"
                            >

                                90 Days

                            </a>


                            <a
                                href="sales.php?period=365"
                                class="btn btn-sm
                                <?php
                                echo $period === "365"
                                    ? "btn-coffee"
                                    : "btn-light";
                                ?>"
                            >

                                1 Year

                            </a>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 SUMMARY CARDS
            ================================================== -->

            <div class="row">


                <!-- TOTAL SALES -->

                <div class="col-lg-3 col-md-6">

                    <div class="small-box bg-coffee">

                        <div class="inner">

                            <h3>

                                ₹<?php
                                echo number_format(
                                    $totalSales,
                                    2
                                );
                                ?>

                            </h3>

                            <p>
                                Total Sales
                            </p>

                        </div>

                        <div class="icon">

                            <i class="fas fa-rupee-sign"></i>

                        </div>

                    </div>

                </div>


                <!-- TOTAL ORDERS -->

                <div class="col-lg-3 col-md-6">

                    <div class="small-box bg-gold">

                        <div class="inner">

                            <h3>

                                <?php
                                echo $totalOrders;
                                ?>

                            </h3>

                            <p>
                                Total Orders
                            </p>

                        </div>

                        <div class="icon">

                            <i class="fas fa-shopping-cart"></i>

                        </div>

                    </div>

                </div>


                <!-- COMPLETED -->

                <div class="col-lg-3 col-md-6">

                    <div class="small-box bg-dark-coffee">

                        <div class="inner">

                            <h3>

                                <?php
                                echo $completedOrders;
                                ?>

                            </h3>

                            <p>
                                Completed Orders
                            </p>

                        </div>

                        <div class="icon">

                            <i class="fas fa-check-circle"></i>

                        </div>

                    </div>

                </div>


                <!-- AVERAGE ORDER -->

                <div class="col-lg-3 col-md-6">

                    <div class="small-box bg-coffee">

                        <div class="inner">

                            <h3>

                                ₹<?php
                                echo number_format(
                                    $averageOrderValue,
                                    2
                                );
                                ?>

                            </h3>

                            <p>
                                Average Order Value
                            </p>

                        </div>

                        <div class="icon">

                            <i class="fas fa-chart-bar"></i>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =================================================
                 SALES CHART
            ================================================== -->

            <div class="card">

                <div class="card-header">

                    <h3 class="card-title">

                        <i
                            class="fas fa-chart-area mr-2"
                        ></i>

                        Sales Trend

                    </h3>

                </div>


                <div class="card-body">

                    <canvas
                        id="salesChart"
                        style="
                            min-height:300px;
                            height:300px;
                        "
                    ></canvas>

                </div>

            </div>


            <div class="row">


                <!-- =================================================
                     TOP PRODUCTS
                ================================================== -->

                <div class="col-md-8">

                    <div class="card">

                        <div class="card-header">

                            <h3 class="card-title">

                                <i
                                    class="fas fa-trophy mr-2"
                                ></i>

                                Top Selling Products

                            </h3>

                        </div>


                        <div class="card-body p-0">

                            <div class="table-responsive">

                                <table
                                    class="table table-hover mb-0"
                                >

                                    <thead>

                                        <tr>

                                            <th>
                                                #
                                            </th>

                                            <th>
                                                Product
                                            </th>

                                            <th>
                                                Quantity Sold
                                            </th>

                                            <th>
                                                Sales
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>


                                    <?php if (
                                        $topProducts !== null &&
                                        mysqli_num_rows(
                                            $topProducts
                                        ) > 0
                                    ): ?>


                                        <?php

                                        $rank = 1;

                                        ?>


                                        <?php while (
                                            $product =
                                            mysqli_fetch_assoc(
                                                $topProducts
                                            )
                                        ): ?>


                                            <tr>


                                                <td>

                                                    <strong
                                                        style="
                                                            color:#D8A15B;
                                                        "
                                                    >

                                                        <?php
                                                        echo $rank;
                                                        ?>

                                                    </strong>

                                                </td>


                                                <td>

                                                    <strong
                                                        style="
                                                            color:#4A2C1D;
                                                        "
                                                    >

                                                        <?php
                                                        echo htmlspecialchars(
                                                            $product[
                                                                "product_name"
                                                            ]
                                                        );
                                                        ?>

                                                    </strong>

                                                </td>


                                                <td>

                                                    <?php
                                                    echo (int)$product[
                                                        "total_quantity"
                                                    ];
                                                    ?>

                                                </td>


                                                <td>

                                                    <strong
                                                        style="
                                                            color:#7B4728;
                                                        "
                                                    >

                                                        ₹<?php
                                                        echo number_format(
                                                            (float)$product[
                                                                "total_sales"
                                                            ],
                                                            2
                                                        );
                                                        ?>

                                                    </strong>

                                                </td>

                                            </tr>


                                            <?php

                                            $rank++;

                                            ?>


                                        <?php endwhile; ?>


                                    <?php else: ?>


                                        <tr>

                                            <td
                                                colspan="4"
                                                class="text-center"
                                                style="
                                                    padding:50px;
                                                    color:#8A7468;
                                                "
                                            >

                                                No completed sales
                                                found for this period.

                                            </td>

                                        </tr>


                                    <?php endif; ?>


                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     ORDER STATUS
                ================================================== -->

                <div class="col-md-4">

                    <div class="card">

                        <div class="card-header">

                            <h3 class="card-title">

                                <i
                                    class="fas fa-tasks mr-2"
                                ></i>

                                Order Status

                            </h3>

                        </div>


                        <div class="card-body">


                        <?php if (
                            count($statusSummary) > 0
                        ): ?>


                            <?php foreach (
                                $statusSummary
                                as $status
                            ): ?>


                                <?php

                                $statusName =
                                    strtolower(
                                        $status["status"]
                                    );


                                switch ($statusName) {

                                    case "pending":
                                        $badge =
                                            "badge-warning";
                                        break;

                                    case "confirmed":
                                        $badge =
                                            "badge-gold";
                                        break;

                                    case "preparing":
                                        $badge =
                                            "badge-coffee";
                                        break;

                                    case "ready":
                                        $badge =
                                            "badge-success";
                                        break;

                                    case "completed":
                                        $badge =
                                            "badge-success";
                                        break;

                                    case "cancelled":
                                        $badge =
                                            "badge-danger";
                                        break;

                                    default:
                                        $badge =
                                            "badge-secondary";
                                        break;

                                }

                                ?>


                                <div
                                    class="d-flex justify-content-between
                                    align-items-center mb-3"
                                >

                                    <span>

                                        <span
                                            class="badge <?php
                                            echo $badge;
                                            ?>"
                                        >

                                            <?php
                                            echo htmlspecialchars(
                                                ucfirst(
                                                    $statusName
                                                )
                                            );
                                            ?>

                                        </span>

                                    </span>


                                    <strong
                                        style="
                                            color:#4A2C1D;
                                        "
                                    >

                                        <?php
                                        echo (int)$status[
                                            "status_count"
                                        ];
                                        ?>

                                    </strong>

                                </div>


                            <?php endforeach; ?>


                        <?php else: ?>


                            <p
                                style="
                                    color:#8A7468;
                                "
                            >

                                No orders found.

                            </p>


                        <?php endif; ?>


                        </div>

                    </div>

                </div>

            </div>


        </div>

    </section>

</div>


<!-- =========================================================
     CHART.JS
========================================================= -->

<script src="../assests/plugins/chart.js/Chart.min.js"></script>


<script>

const salesLabels =
    <?php echo json_encode($chartLabels); ?>;

const salesValues =
    <?php echo json_encode($chartValues); ?>;


const salesCanvas =
    document.getElementById("salesChart");


if (salesCanvas) {

    new Chart(
        salesCanvas.getContext("2d"),
        {
            type: "line",

            data: {

                labels: salesLabels,

                datasets: [

                    {
                        label: "Sales (₹)",

                        data: salesValues,

                        borderWidth: 3,

                        pointRadius: 3,

                        fill: false
                    }

                ]

            },

            options: {

                responsive: true,

                maintainAspectRatio: false,

                legend: {

                    display: true

                },

                scales: {

                    yAxes: [

                        {

                            ticks: {

                                beginAtZero: true,

                                callback: function(value) {

                                    return "₹" +
                                        value;

                                }

                            }

                        }

                    ]

                }

            }

        }
    );

}

</script>


<?php

/* =========================================================
   COMMON FOOTER
========================================================= */

include "../includes/footer.php";

?>