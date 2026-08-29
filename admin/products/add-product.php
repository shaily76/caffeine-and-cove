<?php

/* =========================================================
   CAFFEINE & COVE
   ADMIN - ADD PRODUCT
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

$name = "";
$category = "";
$description = "";
$price = "";
$status = "active";

$error = "";
$success = "";


/* =========================================================
   HANDLE FORM SUBMISSION
   ========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /* -----------------------------------------------------
       GET FORM DATA
    ----------------------------------------------------- */

    $name = trim($_POST["name"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $description = trim($_POST["description"] ?? "");
    $price = trim($_POST["price"] ?? "");
    $status = trim($_POST["status"] ?? "active");


    /* -----------------------------------------------------
       VALIDATION
    ----------------------------------------------------- */

    if ($name === "") {

        $error = "Please enter the product name.";

    } elseif (!in_array(
        $category,
        ["coffee", "tea", "bites", "desserts"],
        true
    )) {

        $error = "Please select a valid category.";

    } elseif ($description === "") {

        $error = "Please enter the product description.";

    } elseif ($price === "" || !is_numeric($price)) {

        $error = "Please enter a valid price.";

    } elseif ((float)$price < 0) {

        $error = "Price cannot be negative.";

    } elseif (!in_array(
        $status,
        ["active", "inactive"],
        true
    )) {

        $error = "Please select a valid status.";

    }


    /* -----------------------------------------------------
       IMAGE VARIABLES
    ----------------------------------------------------- */

    $imageName = "";


    /* -----------------------------------------------------
       IMAGE UPLOAD
    ----------------------------------------------------- */

    if (
        $error === "" &&
        isset($_FILES["image"]) &&
        $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
    ) {

        if ($_FILES["image"]["error"] !== UPLOAD_ERR_OK) {

            $error = "There was an error uploading the image.";

        } else {

            $originalName =
                $_FILES["image"]["name"];

            $temporaryName =
                $_FILES["image"]["tmp_name"];

            $fileSize =
                (int)$_FILES["image"]["size"];

            $extension =
                strtolower(
                    pathinfo(
                        $originalName,
                        PATHINFO_EXTENSION
                    )
                );


            /* Allowed extensions */

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

            } elseif ($fileSize > 5 * 1024 * 1024) {

                $error =
                    "Image size must be less than 5 MB.";

            } else {

                /* -----------------------------------------
                   CHECK ACTUAL IMAGE
                ----------------------------------------- */

                $imageInfo =
                    getimagesize($temporaryName);


                if ($imageInfo === false) {

                    $error =
                        "The uploaded file is not a valid image.";

                } else {

                    /* -------------------------------------
                       CREATE UNIQUE FILE NAME
                    ------------------------------------- */

                    $imageName =
                        time() .
                        "_" .
                        bin2hex(
                            random_bytes(4)
                        ) .
                        "." .
                        $extension;


                    /* -------------------------------------
                       EXISTING WEBSITE IMAGE FOLDER

                       admin/products/
                       ../../img/
                    ------------------------------------- */

                    $uploadDirectory =
                        realpath(
                            __DIR__ . "/../../img"
                        );


                    if ($uploadDirectory === false) {

                        $error =
                            "The website img folder could not be found.";

                    } else {

                        $destination =
                            $uploadDirectory .
                            DIRECTORY_SEPARATOR .
                            $imageName;


                        if (!move_uploaded_file(
                            $temporaryName,
                            $destination
                        )) {

                            $error =
                                "Failed to upload the product image.";

                        }

                    }

                }

            }

        }

    }


    /* -----------------------------------------------------
       INSERT PRODUCT INTO DATABASE
    ----------------------------------------------------- */

    if ($error === "") {

        $priceValue = (float)$price;


        $sql = "
            INSERT INTO products
            (
                name,
                category,
                description,
                price,
                image,
                status
            )
            VALUES
            (?, ?, ?, ?, ?, ?)
        ";


        $stmt = mysqli_prepare(
            $link,
            $sql
        );


        if ($stmt === false) {

            /* Remove uploaded image if query preparation fails */

            if (
                $imageName !== "" &&
                isset($destination) &&
                file_exists($destination)
            ) {

                unlink($destination);

            }


            $error =
                "Database error: " .
                mysqli_error($link);

        } else {

            /*
             * s = string
             * s = string
             * s = string
             * d = decimal/double
             * s = string
             * s = string
             */

            mysqli_stmt_bind_param(
                $stmt,
                "sssdss",
                $name,
                $category,
                $description,
                $priceValue,
                $imageName,
                $status
            );


            if (mysqli_stmt_execute($stmt)) {

                $success =
                    "Product added successfully.";


                /* -----------------------------------------
                   RESET FORM
                ----------------------------------------- */

                $name = "";
                $category = "";
                $description = "";
                $price = "";
                $status = "active";


            } else {

                /* Remove image if database insert fails */

                if (
                    $imageName !== "" &&
                    isset($destination) &&
                    file_exists($destination)
                ) {

                    unlink($destination);

                }


                $error =
                    "Unable to add product: " .
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
                        Add Product
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

                            Products

                        </li>

                        <li class="breadcrumb-item active">

                            Add Product

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
                 SUCCESS MESSAGE
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
                 ERROR MESSAGE
                 ================================================== -->

            <?php if ($error !== ""): ?>

                <div class="alert alert-danger">

                    <i class="fas fa-exclamation-circle mr-2"></i>

                    <?php
                    echo htmlspecialchars($error);
                    ?>

                </div>

            <?php endif; ?>


            <!-- =================================================
                 PRODUCT FORM
                 ================================================== -->

            <div class="card">


                <!-- CARD HEADER -->

                <div class="card-header">

                    <h3 class="card-title">

                        <i class="fas fa-coffee mr-2"></i>

                        Product Information

                    </h3>

                </div>


                <!-- FORM -->

                <form
                    method="POST"
                    action=""
                    enctype="multipart/form-data"
                >


                    <div class="card-body">


                        <!-- =====================================
                             PRODUCT NAME
                        ====================================== -->

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
                                placeholder="Enter product name"
                                value="<?php
                                echo htmlspecialchars($name);
                                ?>"
                                maxlength="150"
                                required
                            >

                        </div>


                        <!-- =====================================
                             CATEGORY
                        ====================================== -->

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

                                <option value="">
                                    Select Category
                                </option>


                                <option
                                    value="coffee"
                                    <?php
                                    echo (
                                        $category === "coffee"
                                    )
                                        ? "selected"
                                        : "";
                                    ?>
                                >
                                    Coffee
                                </option>


                                <option
                                    value="tea"
                                    <?php
                                    echo (
                                        $category === "tea"
                                    )
                                        ? "selected"
                                        : "";
                                    ?>
                                >
                                    Tea
                                </option>


                                <option
                                    value="bites"
                                    <?php
                                    echo (
                                        $category === "bites"
                                    )
                                        ? "selected"
                                        : "";
                                    ?>
                                >
                                    Quick Bites
                                </option>


                                <option
                                    value="desserts"
                                    <?php
                                    echo (
                                        $category === "desserts"
                                    )
                                        ? "selected"
                                        : "";
                                    ?>
                                >
                                    Desserts
                                </option>

                            </select>

                        </div>


                        <!-- =====================================
                             DESCRIPTION
                        ====================================== -->

                        <div class="form-group">

                            <label for="description">

                                Description
                                <span class="text-danger">*</span>

                            </label>

                            <textarea
                                id="description"
                                name="description"
                                class="form-control"
                                rows="5"
                                maxlength="5000"
                                placeholder="Enter product description"
                                required
                            ><?php
                            echo htmlspecialchars($description);
                            ?></textarea>

                        </div>


                        <!-- =====================================
                             PRICE
                        ====================================== -->

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
                                placeholder="Enter price"
                                value="<?php
                                echo htmlspecialchars($price);
                                ?>"
                                min="0"
                                step="0.01"
                                required
                            >

                        </div>


                        <!-- =====================================
                             IMAGE
                        ====================================== -->

                        <div class="form-group">

                            <label for="image">

                                Product Image
                                <span class="text-danger">*</span>

                            </label>

                            <div class="custom-file">

                                <input
                                    type="file"
                                    id="image"
                                    name="image"
                                    class="custom-file-input"
                                    accept=".jpg,.jpeg,.png,.webp"
                                    required
                                >

                                <label
                                    class="custom-file-label"
                                    for="image"
                                >
                                    Choose product image
                                </label>

                            </div>

                            <small class="form-text text-muted">

                                JPG, JPEG, PNG or WEBP.
                                Maximum 5 MB.

                            </small>

                        </div>


                        <!-- =====================================
                             STATUS
                        ====================================== -->

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

                                <option
                                    value="active"
                                    <?php
                                    echo (
                                        $status === "active"
                                    )
                                        ? "selected"
                                        : "";
                                    ?>
                                >
                                    Active
                                </option>


                                <option
                                    value="inactive"
                                    <?php
                                    echo (
                                        $status === "inactive"
                                    )
                                        ? "selected"
                                        : "";
                                    ?>
                                >
                                    Inactive
                                </option>

                            </select>

                        </div>


                    </div>


                    <!-- =================================================
                         FORM FOOTER
                    ================================================== -->

                    <div class="card-footer">


                        <button
                            type="submit"
                            class="btn btn-coffee"
                        >

                            <i class="fas fa-save mr-2"></i>

                            Add Product

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

        </div>

    </section>

</div>


<?php

/* =========================================================
   COMMON FOOTER
   ========================================================= */

include "../includes/footer.php";

?>