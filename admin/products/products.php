<?php

/* =========================================================
   CAFFEINE & COVE
   ADMIN - PRODUCTS MANAGEMENT
   ========================================================= */
require_once "../admin_auth.php";

/* =========================================================
   DATABASE CONNECTION
   ========================================================= */

require_once "../../include/config.php";


/* =========================================================
   VARIABLES
   ========================================================= */

$success = "";
$error = "";


/* =========================================================
   SUCCESS / ERROR MESSAGES
   ========================================================= */

if (isset($_GET["success"])) {

    switch ($_GET["success"]) {

        case "deleted":
            $success = "Product deleted successfully.";
            break;

        default:
            $success = "";
            break;
    }
}


if (isset($_GET["error"])) {

    switch ($_GET["error"]) {

        case "invalid_id":
            $error = "Invalid product ID.";
            break;

        case "not_found":
            $error = "Product was not found.";
            break;

        case "delete_failed":
            $error = "Unable to delete the product.";
            break;

        case "database":
            $error = "A database error occurred.";
            break;

        default:
            $error = "Something went wrong.";
            break;
    }
}


/* =========================================================
   GET PRODUCTS
   ========================================================= */

$sql = "
    SELECT
        id,
        name,
        category,
        description,
        price,
        image,
        status,
        created_at,
        updated_at
    FROM products
    ORDER BY id DESC
";


$result = mysqli_query($link, $sql);


/* =========================================================
   DATABASE ERROR
   ========================================================= */

if ($result === false) {

    $error =
        "Unable to load products: " .
        mysqli_error($link);

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


                <!-- TITLE -->

                <div class="col-sm-6">

                    <h1 class="m-0">

                        <i class="fas fa-coffee mr-2"
                           style="color:#7B4728;"></i>

                        Products

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

                            Products

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
                 SUCCESS MESSAGE
            ================================================== -->

            <?php if ($success !== ""): ?>

                <div class="alert alert-success">

                    <i class="fas fa-check-circle mr-2"></i>

                    <?php
                    echo htmlspecialchars($success);
                    ?>

                    <button
                        type="button"
                        class="close"
                        data-dismiss="alert"
                        aria-label="Close"
                    >

                        <span aria-hidden="true">
                            &times;
                        </span>

                    </button>

                </div>

            <?php endif; ?>


            <!-- =================================================
                 ERROR MESSAGE
            ================================================== -->

            <?php if ($error !== ""): ?>

                <div class="alert alert-danger">

                    <i class="fas fa-exclamation-circle mr-2"></i>

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                    <button
                        type="button"
                        class="close"
                        data-dismiss="alert"
                        aria-label="Close"
                    >

                        <span aria-hidden="true">
                            &times;
                        </span>

                    </button>

                </div>

            <?php endif; ?>


            <!-- =================================================
                 TOP ACTION ROW
            ================================================== -->

            <div class="row mb-3">


                <!-- DESCRIPTION -->

                <div class="col-md-8">

                    <p
                        class="mb-0"
                        style="
                            color:#806858;
                            padding-top:8px;
                        "
                    >

                        Manage the products displayed on the
                        Caffeine & Cove customer menu.

                    </p>

                </div>


                <!-- ADD PRODUCT -->

                <div class="col-md-4 text-md-right">

                    <a
                        href="add-product.php"
                        class="btn btn-coffee"
                    >

                        <i class="fas fa-plus mr-2"></i>

                        Add Product

                    </a>

                </div>

            </div>


            <!-- =================================================
                 PRODUCTS CARD
            ================================================== -->

            <div class="card">


                <!-- CARD HEADER -->

                <div class="card-header">


                    <h3 class="card-title">

                        <i class="fas fa-list mr-2"></i>

                        All Products

                    </h3>


                    <div class="card-tools">


                        <span class="badge badge-gold">

                            <?php

                            if ($result !== false) {

                                echo mysqli_num_rows($result);

                            } else {

                                echo "0";

                            }

                            ?>

                            Products

                        </span>


                    </div>

                </div>


                <!-- CARD BODY -->

                <div class="card-body p-0">


                    <div class="table-responsive">


                        <table
                            class="table table-hover mb-0"
                        >


                            <!-- =================================================
                                 TABLE HEADER
                            ================================================== -->

                            <thead>

                                <tr>

                                    <th style="width:70px;">
                                        ID
                                    </th>

                                    <th style="min-width:300px;">
                                        Product
                                    </th>

                                    <th style="width:150px;">
                                        Category
                                    </th>

                                    <th style="width:120px;">
                                        Price
                                    </th>

                                    <th style="width:120px;">
                                        Status
                                    </th>

                                    <th
                                        style="width:120px;"
                                        class="text-center"
                                    >
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <!-- =================================================
                                 TABLE BODY
                            ================================================== -->

                            <tbody>


                            <?php if (
                                $result !== false &&
                                mysqli_num_rows($result) > 0
                            ): ?>


                                <?php while (
                                    $product =
                                    mysqli_fetch_assoc($result)
                                ): ?>


                                    <?php

                                    /* -------------------------------------
                                       PRODUCT VALUES
                                    -------------------------------------- */

                                    $productId =
                                        (int)$product["id"];

                                    $productName =
                                        (string)$product["name"];

                                    $productCategory =
                                        strtolower(
                                            trim(
                                                (string)$product[
                                                    "category"
                                                ]
                                            )
                                        );

                                    $productDescription =
                                        (string)$product[
                                            "description"
                                        ];

                                    $productPrice =
                                        (float)$product["price"];

                                    $productImage =
                                        trim(
                                            (string)$product["image"]
                                        );

                                    $productStatus =
                                        strtolower(
                                            trim(
                                                (string)$product[
                                                    "status"
                                                ]
                                            )
                                        );


                                    /* -------------------------------------
                                       CATEGORY DISPLAY NAME
                                    -------------------------------------- */

                                    switch ($productCategory) {

                                        case "coffee":
                                            $categoryLabel =
                                                "Coffee";
                                            break;

                                        case "tea":
                                            $categoryLabel =
                                                "Tea";
                                            break;

                                        case "bites":
                                            $categoryLabel =
                                                "Quick Bites";
                                            break;

                                        case "desserts":
                                            $categoryLabel =
                                                "Desserts";
                                            break;

                                        default:
                                            $categoryLabel =
                                                ucfirst(
                                                    $productCategory
                                                );
                                            break;
                                    }


                                    /* -------------------------------------
                                       SHORT DESCRIPTION
                                    -------------------------------------- */

                                    $shortDescription =
                                        $productDescription;

                                    if (
                                        strlen(
                                            $shortDescription
                                        ) > 75
                                    ) {

                                        $shortDescription =
                                            substr(
                                                $shortDescription,
                                                0,
                                                75
                                            ) . "...";

                                    }


                                    /* -------------------------------------
                                       IMAGE URL
                                    -------------------------------------- */

                                    $imageUrl = "";

                                    if ($productImage !== "") {

                                        $imageUrl =
                                            "../../img/" .
                                            basename(
                                                $productImage
                                            );

                                    }

                                    ?>


                                    <tr>


                                        <!-- =================================
                                             ID
                                        ================================== -->

                                        <td>

                                            <span
                                                style="
                                                    color:#806858;
                                                    font-weight:600;
                                                "
                                            >

                                                #<?php
                                                echo $productId;
                                                ?>

                                            </span>

                                        </td>


                                        <!-- =================================
                                             PRODUCT
                                        ================================== -->

                                        <td>

                                            <div
                                                class="d-flex align-items-center"
                                            >


                                                <!-- IMAGE -->

                                                <?php if (
                                                    $imageUrl !== ""
                                                ): ?>

                                                    <img
                                                        src="<?php
                                                        echo htmlspecialchars(
                                                            $imageUrl
                                                        );
                                                        ?>"
                                                        alt="<?php
                                                        echo htmlspecialchars(
                                                            $productName
                                                        );
                                                        ?>"
                                                        style="
                                                            width:60px;
                                                            height:60px;
                                                            object-fit:cover;
                                                            border-radius:10px;
                                                            border:1px solid #E5D5C8;
                                                            margin-right:14px;
                                                            background:#F5E8DA;
                                                        "
                                                    >

                                                <?php else: ?>

                                                    <div
                                                        style="
                                                            width:60px;
                                                            height:60px;
                                                            border-radius:10px;
                                                            background:#F5E8DA;
                                                            display:flex;
                                                            align-items:center;
                                                            justify-content:center;
                                                            margin-right:14px;
                                                        "
                                                    >

                                                        <i
                                                            class="fas fa-coffee"
                                                            style="
                                                                color:#7B4728;
                                                                font-size:22px;
                                                            "
                                                        ></i>

                                                    </div>

                                                <?php endif; ?>


                                                <!-- NAME + DESCRIPTION -->

                                                <div>

                                                    <div
                                                        style="
                                                            color:#4A2C1D;
                                                            font-weight:600;
                                                            font-size:15px;
                                                            margin-bottom:3px;
                                                        "
                                                    >

                                                        <?php
                                                        echo htmlspecialchars(
                                                            $productName
                                                        );
                                                        ?>

                                                    </div>


                                                    <div
                                                        style="
                                                            color:#8A7468;
                                                            font-size:12px;
                                                            line-height:1.4;
                                                        "
                                                    >

                                                        <?php
                                                        echo htmlspecialchars(
                                                            $shortDescription
                                                        );
                                                        ?>

                                                    </div>

                                                </div>

                                            </div>

                                        </td>


                                        <!-- =================================
                                             CATEGORY
                                        ================================== -->

                                        <td>

                                            <span
                                                style="
                                                    display:inline-block;
                                                    background:#F5E8DA;
                                                    color:#7B4728;
                                                    padding:6px 11px;
                                                    border-radius:20px;
                                                    font-size:12px;
                                                    font-weight:600;
                                                "
                                            >

                                                <?php
                                                echo htmlspecialchars(
                                                    $categoryLabel
                                                );
                                                ?>

                                            </span>

                                        </td>


                                        <!-- =================================
                                             PRICE
                                        ================================== -->

                                        <td>

                                            <strong
                                                style="
                                                    color:#7B4728;
                                                    font-size:15px;
                                                "
                                            >

                                                ₹<?php
                                                echo number_format(
                                                    $productPrice,
                                                    2
                                                );
                                                ?>

                                            </strong>

                                        </td>


                                        <!-- =================================
                                             STATUS
                                        ================================== -->

                                        <td>


                                            <?php if (
                                                $productStatus === "active"
                                            ): ?>

                                                <span
                                                    class="badge badge-success"
                                                    style="
                                                        padding:7px 10px;
                                                    "
                                                >

                                                    <i
                                                        class="fas fa-check-circle mr-1"
                                                    ></i>

                                                    Active

                                                </span>

                                            <?php else: ?>

                                                <span
                                                    class="badge badge-danger"
                                                    style="
                                                        padding:7px 10px;
                                                    "
                                                >

                                                    <i
                                                        class="fas fa-times-circle mr-1"
                                                    ></i>

                                                    Inactive

                                                </span>

                                            <?php endif; ?>


                                        </td>


                                        <!-- =================================
                                             ACTIONS
                                        ================================== -->

                                        <td>

                                            <div
                                                class="d-flex justify-content-center"
                                                style="gap:6px;"
                                            >


                                                <!-- EDIT -->

                                                <a
                                                    href="edit-product.php?id=<?php
                                                    echo $productId;
                                                    ?>"
                                                    class="btn btn-sm btn-gold"
                                                    title="Edit Product"
                                                >

                                                    <i
                                                        class="fas fa-edit"
                                                    ></i>

                                                </a>


                                                <!-- DELETE -->

                                                <a
                                                    href="delete-product.php?id=<?php
                                                    echo $productId;
                                                    ?>"
                                                    class="btn btn-sm btn-danger"
                                                    title="Delete Product"
                                                    onclick="return confirm('Are you sure you want to delete this product?');"
                                                >

                                                    <i
                                                        class="fas fa-trash"
                                                    ></i>

                                                </a>

                                            </div>

                                        </td>


                                    </tr>


                                <?php endwhile; ?>


                            <?php else: ?>


                                <!-- =================================================
                                     NO PRODUCTS
                                ================================================== -->

                                <tr>

                                    <td
                                        colspan="6"
                                        class="text-center"
                                        style="padding:70px 20px;"
                                    >


                                        <div>

                                            <i
                                                class="fas fa-coffee"
                                                style="
                                                    color:#D8A15B;
                                                    font-size:48px;
                                                    margin-bottom:15px;
                                                "
                                            ></i>


                                            <h4
                                                style="
                                                    color:#4A2C1D;
                                                    font-weight:600;
                                                "
                                            >

                                                No Products Found

                                            </h4>


                                            <p
                                                style="
                                                    color:#8A7468;
                                                    margin-bottom:20px;
                                                "
                                            >

                                                There are currently no
                                                products in your database.

                                            </p>


                                            <a
                                                href="add-product.php"
                                                class="btn btn-coffee"
                                            >

                                                <i
                                                    class="fas fa-plus mr-2"
                                                ></i>

                                                Add Product

                                            </a>

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

                <?php if (
                    $result !== false &&
                    mysqli_num_rows($result) > 0
                ): ?>

                    <div class="card-footer">

                        <div
                            class="text-muted"
                            style="font-size:13px;"
                        >

                            Showing
                            <strong>
                                <?php
                                echo mysqli_num_rows($result);
                                ?>
                            </strong>
                            product(s)

                        </div>

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