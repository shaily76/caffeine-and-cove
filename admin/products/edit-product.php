<?php

/* =========================================================
   CAFFEINE & COVE
   ADMIN - EDIT PRODUCT
   ========================================================= */


/* =========================================================
   ADMIN AUTHENTICATION
   ========================================================= */

require_once "../admin_auth.php";


/* =========================================================
   DATABASE CONNECTION
   ========================================================= */

require_once "../../include/config.php";

/* =========================================================
   FORM VARIABLES
   ========================================================= */

$productId = 0;

$name = "";
$category = "";
$description = "";
$price = "";
$imageName = "";
$status = "active";

$error = "";
$success = "";


/* =========================================================
   GET PRODUCT ID
   ========================================================= */

if (isset($_GET["id"]) && ctype_digit($_GET["id"])) {

    $productId = (int)$_GET["id"];

} else {

    $error = "Invalid product ID.";

}


/* =========================================================
   LOAD EXISTING PRODUCT
   ========================================================= */

if ($error === "" && $productId > 0) {

    $sql = "
        SELECT
            id,
            name,
            category,
            description,
            price,
            image,
            status
        FROM products
        WHERE id = ?
        LIMIT 1
    ";

    $stmt = mysqli_prepare($link, $sql);

    if ($stmt === false) {

        $error =
            "Database error: " .
            mysqli_error($link);

    } else {

        mysqli_stmt_bind_param(
            $stmt,
            "i",
            $productId
        );

        mysqli_stmt_execute($stmt);

        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) === 1) {

            $product = mysqli_fetch_assoc($result);

            $name =
                (string)$product["name"];

            $category =
                (string)$product["category"];

            $description =
                (string)$product["description"];

            $price =
                (string)$product["price"];

            $imageName =
                (string)$product["image"];

            $status =
                (string)$product["status"];

        } else {

            $error = "Product not found.";

        }

        mysqli_stmt_close($stmt);

    }

}


/* =========================================================
   HANDLE UPDATE
   ========================================================= */

if (
    $error === "" &&
    $_SERVER["REQUEST_METHOD"] === "POST"
) {

    /* -----------------------------------------------------
       PRODUCT ID FROM FORM
    ----------------------------------------------------- */

    if (
        isset($_POST["product_id"]) &&
        ctype_digit($_POST["product_id"])
    ) {

        $productId =
            (int)$_POST["product_id"];

    } else {

        $error = "Invalid product ID.";

    }


    /* -----------------------------------------------------
       GET FORM VALUES
    ----------------------------------------------------- */

    $name =
        trim($_POST["name"] ?? "");

    $category =
        trim($_POST["category"] ?? "");

    $description =
        trim($_POST["description"] ?? "");

    $price =
        trim($_POST["price"] ?? "");

    $status =
        trim($_POST["status"] ?? "active");


    /* -----------------------------------------------------
       VALIDATION
    ----------------------------------------------------- */

    if ($error === "" && $name === "") {

        $error =
            "Please enter the product name.";

    } elseif (
        $error === "" &&
        !in_array(
            $category,
            ["coffee", "tea", "bites", "desserts"],
            true
        )
    ) {

        $error =
            "Please select a valid category.";

    } elseif (
        $error === "" &&
        $description === ""
    ) {

        $error =
            "Please enter the product description.";

    } elseif (
        $error === "" &&
        ($price === "" || !is_numeric($price))
    ) {

        $error =
            "Please enter a valid price.";

    } elseif (
        $error === "" &&
        (float)$price < 0
    ) {

        $error =
            "Price cannot be negative.";

    } elseif (
        $error === "" &&
        !in_array(
            $status,
            ["active", "inactive"],
            true
        )
    ) {

        $error =
            "Please select a valid status.";

    }


    /* -----------------------------------------------------
       FIND CURRENT IMAGE
    ----------------------------------------------------- */

    $oldImageName = $imageName;

    $newImageName = "";

    $newImagePath = "";


    /* -----------------------------------------------------
       IMAGE UPLOAD
    ----------------------------------------------------- */

    if (
        $error === "" &&
        isset($_FILES["image"]) &&
        $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
    ) {

        if (
            $_FILES["image"]["error"] !==
            UPLOAD_ERR_OK
        ) {

            $error =
                "There was an error uploading the image.";

        } else {

            $temporaryName =
                $_FILES["image"]["tmp_name"];

            $originalName =
                $_FILES["image"]["name"];

            $fileSize =
                (int)$_FILES["image"]["size"];

            $extension =
                strtolower(
                    pathinfo(
                        $originalName,
                        PATHINFO_EXTENSION
                    )
                );


            $allowedExtensions = [
                "jpg",
                "jpeg",
                "png",
                "webp"
            ];


            if (!in_array(
                $extension,
                $allowedExtensions,
                true
            )) {

                $error =
                    "Only JPG, JPEG, PNG and WEBP images are allowed.";

            } elseif (
                $fileSize > 5 * 1024 * 1024
            ) {

                $error =
                    "Image size must be less than 5 MB.";

            } elseif (
                getimagesize($temporaryName) === false
            ) {

                $error =
                    "The uploaded file is not a valid image.";

            } else {

                $newImageName =
                    time() .
                    "_" .
                    bin2hex(
                        random_bytes(4)
                    ) .
                    "." .
                    $extension;


                $uploadDirectory =
                    realpath(
                        __DIR__ . "/../../img"
                    );


                if ($uploadDirectory === false) {

                    $error =
                        "The website img folder could not be found.";

                } else {

                    $newImagePath =
                        $uploadDirectory .
                        DIRECTORY_SEPARATOR .
                        $newImageName;


                    if (!move_uploaded_file(
                        $temporaryName,
                        $newImagePath
                    )) {

                        $error =
                            "Failed to upload the new image.";

                    }

                }

            }

        }

    }


    /* -----------------------------------------------------
       UPDATE DATABASE
    ----------------------------------------------------- */

    if ($error === "") {

        /*
         * Keep the old image if no new image was selected.
         */

        $finalImageName =
            ($newImageName !== "")
                ? $newImageName
                : $oldImageName;


        $priceValue =
            (float)$price;


        $sql = "
            UPDATE products
            SET
                name = ?,
                category = ?,
                description = ?,
                price = ?,
                image = ?,
                status = ?
            WHERE id = ?
        ";


        $stmt =
            mysqli_prepare(
                $link,
                $sql
            );


        if ($stmt === false) {

            /* Remove newly uploaded image */

            if (
                $newImagePath !== "" &&
                file_exists($newImagePath)
            ) {

                unlink($newImagePath);

            }


            $error =
                "Database error: " .
                mysqli_error($link);

        } else {

            mysqli_stmt_bind_param(
                $stmt,
                "sssdssi",
                $name,
                $category,
                $description,
                $priceValue,
                $finalImageName,
                $status,
                $productId
            );


            if (mysqli_stmt_execute($stmt)) {

                /*
                 * Delete old image only after
                 * successful database update.
                 */

                if (
                    $newImageName !== "" &&
                    $oldImageName !== "" &&
                    $oldImageName !== $newImageName
                ) {

                    $oldImagePath =
                        realpath(
                            __DIR__ . "/../../img"
                        );


                    if ($oldImagePath !== false) {

                        $oldImagePath =
                            $oldImagePath .
                            DIRECTORY_SEPARATOR .
                            basename($oldImageName);


                        if (
                            file_exists($oldImagePath)
                        ) {

                            unlink($oldImagePath);

                        }

                    }

                }


                $imageName =
                    $finalImageName;

                $success =
                    "Product updated successfully.";

            } else {

                /*
                 * Database failed, remove new image.
                 */

                if (
                    $newImagePath !== "" &&
                    file_exists($newImagePath)
                ) {

                    unlink($newImagePath);

                }


                $error =
                    "Unable to update product: " .
                    mysqli_stmt_error($stmt);

            }


            mysqli_stmt_close($stmt);

        }

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
                        Edit Product
                    </h1>

                </div>


                <div class="col-sm-6">

                    <ol class="breadcrumb float-sm-right">

                        <li class="breadcrumb-item">

                            <a href="../dashboard.php">
                                Admin
                            </a>

                        </li>

                        <li class="breadcrumb-item">

                            <a href="products.php">
                                Products
                            </a>

                        </li>

                        <li class="breadcrumb-item active">

                            Edit Product

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

            <?php if ($success !== ""): ?>

                <div class="alert alert-success">

                    <i class="fas fa-check-circle mr-2"></i>

                    <?php
                    echo htmlspecialchars($success);
                    ?>

                    <a
                        href="products.php"
                        class="ml-2 font-weight-bold"
                    >
                        View Products
                    </a>

                </div>

            <?php endif; ?>


            <!-- =================================================
                 ERROR
            ================================================== -->

            <?php if ($error !== ""): ?>

                <div class="alert alert-danger">

                    <i class="fas fa-exclamation-circle mr-2"></i>

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php endif; ?>


            <?php if ($productId > 0 && $name !== ""): ?>


                <!-- =============================================
                     EDIT FORM
                ============================================== -->

                <div class="card">


                    <div class="card-header">

                        <h3 class="card-title">

                            <i class="fas fa-edit mr-2"></i>

                            Product Information

                        </h3>

                    </div>


                    <form
                        method="POST"
                        action=""
                        enctype="multipart/form-data"
                    >


                        <input
                            type="hidden"
                            name="product_id"
                            value="<?php
                            echo $productId;
                            ?>"
                        >


                        <div class="card-body">


                            <!-- PRODUCT NAME -->

                            <div class="form-group">

                                <label for="name">

                                    Product Name
                                    <span class="text-danger">*</span>

                                </label>

                                <input
                                    type="text"
                                    id="name"
                                    name="name"
                                    class="form-control"
                                    value="<?php
                                    echo htmlspecialchars($name);
                                    ?>"
                                    maxlength="150"
                                    required
                                >

                            </div>


                            <!-- CATEGORY -->

                            <div class="form-group">

                                <label for="category">

                                    Category
                                    <span class="text-danger">*</span>

                                </label>

                                <select
                                    id="category"
                                    name="category"
                                    class="form-control"
                                    required
                                >

                                    <option value="coffee"
                                        <?php
                                        echo $category === "coffee"
                                            ? "selected"
                                            : "";
                                        ?>
                                    >
                                        Coffee
                                    </option>


                                    <option value="tea"
                                        <?php
                                        echo $category === "tea"
                                            ? "selected"
                                            : "";
                                        ?>
                                    >
                                        Tea
                                    </option>


                                    <option value="bites"
                                        <?php
                                        echo $category === "bites"
                                            ? "selected"
                                            : "";
                                        ?>
                                    >
                                        Quick Bites
                                    </option>


                                    <option value="desserts"
                                        <?php
                                        echo $category === "desserts"
                                            ? "selected"
                                            : "";
                                        ?>
                                    >
                                        Desserts
                                    </option>

                                </select>

                            </div>


                            <!-- DESCRIPTION -->

                            <div class="form-group">

                                <label for="description">

                                    Description
                                    <span class="text-danger">*</span>

                                </label>

                                <textarea
                                    id="description"
                                    name="description"
                                    rows="5"
                                    class="form-control"
                                    maxlength="5000"
                                    required
                                ><?php
                                echo htmlspecialchars($description);
                                ?></textarea>

                            </div>


                            <!-- PRICE -->

                            <div class="form-group">

                                <label for="price">

                                    Price (₹)
                                    <span class="text-danger">*</span>

                                </label>

                                <input
                                    type="number"
                                    id="price"
                                    name="price"
                                    class="form-control"
                                    min="0"
                                    step="0.01"
                                    value="<?php
                                    echo htmlspecialchars($price);
                                    ?>"
                                    required
                                >

                            </div>


                            <!-- CURRENT IMAGE -->

                            <div class="form-group">

                                <label>
                                    Current Product Image
                                </label>

                                <div>

                                    <?php if ($imageName !== ""): ?>

                                        <img
                                            src="../../img/<?php
                                            echo htmlspecialchars(
                                                basename($imageName)
                                            );
                                            ?>"
                                            alt="<?php
                                            echo htmlspecialchars($name);
                                            ?>"
                                            style="
                                                width:150px;
                                                height:150px;
                                                object-fit:cover;
                                                border-radius:12px;
                                                border:1px solid #e5d5c8;
                                                padding:3px;
                                                background:#fff;
                                            "
                                        >

                                    <?php else: ?>

                                        <div
                                            style="
                                                width:150px;
                                                height:150px;
                                                border-radius:12px;
                                                background:#f5e8da;
                                                display:flex;
                                                align-items:center;
                                                justify-content:center;
                                            "
                                        >

                                            <i
                                                class="fas fa-coffee"
                                                style="
                                                    color:#7B4728;
                                                    font-size:42px;
                                                "
                                            ></i>

                                        </div>

                                    <?php endif; ?>

                                </div>

                            </div>


                            <!-- NEW IMAGE -->

                            <div class="form-group">

                                <label for="image">

                                    Replace Product Image

                                </label>

                                <div class="custom-file">

                                    <input
                                        type="file"
                                        id="image"
                                        name="image"
                                        class="custom-file-input"
                                        accept=".jpg,.jpeg,.png,.webp"
                                    >

                                    <label
                                        class="custom-file-label"
                                        for="image"
                                    >
                                        Choose new image
                                    </label>

                                </div>

                                <small class="form-text text-muted">

                                    Leave empty to keep the current image.
                                    JPG, JPEG, PNG or WEBP.
                                    Maximum 5 MB.

                                </small>

                            </div>


                            <!-- STATUS -->

                            <div class="form-group">

                                <label for="status">

                                    Status
                                    <span class="text-danger">*</span>

                                </label>

                                <select
                                    id="status"
                                    name="status"
                                    class="form-control"
                                    required
                                >

                                    <option value="active"
                                        <?php
                                        echo $status === "active"
                                            ? "selected"
                                            : "";
                                        ?>
                                    >
                                        Active
                                    </option>


                                    <option value="inactive"
                                        <?php
                                        echo $status === "inactive"
                                            ? "selected"
                                            : "";
                                        ?>
                                    >
                                        Inactive
                                    </option>

                                </select>

                            </div>


                        </div>


                        <!-- FORM FOOTER -->

                        <div class="card-footer">

                            <button
                                type="submit"
                                class="btn btn-coffee"
                            >

                                <i class="fas fa-save mr-2"></i>

                                Update Product

                            </button>


                            <a
                                href="products.php"
                                class="btn btn-secondary ml-2"
                            >

                                <i class="fas fa-arrow-left mr-2"></i>

                                Back to Products

                            </a>

                        </div>

                    </form>

                </div>


            <?php endif; ?>


        </div>

    </section>

</div>


<?php

/* =========================================================
   COMMON FOOTER
   ========================================================= */

include "../includes/footer.php";

?>