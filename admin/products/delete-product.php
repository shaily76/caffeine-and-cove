<?php

/* =========================================================
   CAFFEINE & COVE
   ADMIN - DELETE PRODUCT
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
   GET PRODUCT ID
   ========================================================= */

$productId = 0;

if (
    isset($_GET["id"]) &&
    ctype_digit($_GET["id"])
) {

    $productId = (int)$_GET["id"];

}


/* =========================================================
   VALIDATE ID
   ========================================================= */

if ($productId <= 0) {

    header("Location: products.php?error=invalid_id");
    exit;

}


/* =========================================================
   GET PRODUCT IMAGE
   ========================================================= */

$imageName = "";

$sql = "
    SELECT image
    FROM products
    WHERE id = ?
    LIMIT 1
";

$stmt = mysqli_prepare($link, $sql);


if ($stmt === false) {

    header("Location: products.php?error=database");
    exit;

}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $productId
);


mysqli_stmt_execute($stmt);


$result =
    mysqli_stmt_get_result($stmt);


if (
    !$result ||
    mysqli_num_rows($result) !== 1
) {

    mysqli_stmt_close($stmt);

    header("Location: products.php?error=not_found");
    exit;

}


$product =
    mysqli_fetch_assoc($result);


$imageName =
    (string)$product["image"];


mysqli_stmt_close($stmt);


/* =========================================================
   DELETE PRODUCT
   ========================================================= */

$sql = "
    DELETE FROM products
    WHERE id = ?
";

$stmt = mysqli_prepare($link, $sql);


if ($stmt === false) {

    header("Location: products.php?error=database");
    exit;

}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $productId
);


if (mysqli_stmt_execute($stmt)) {

    /*
     * Delete image only after
     * successful database deletion.
     */

    if ($imageName !== "") {

        $imageDirectory =
            realpath(
                __DIR__ . "/../../img"
            );


        if ($imageDirectory !== false) {

            $imagePath =
                $imageDirectory .
                DIRECTORY_SEPARATOR .
                basename($imageName);


            if (file_exists($imagePath)) {

                unlink($imagePath);

            }

        }

    }


    mysqli_stmt_close($stmt);


    header("Location: products.php?success=deleted");
    exit;

}


/* =========================================================
   DELETE FAILED
   ========================================================= */

mysqli_stmt_close($stmt);

header("Location: products.php?error=delete_failed");
exit;

?>