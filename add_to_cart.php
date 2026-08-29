<?php

/* =========================================================
   CAFFEINE & COVE
   ADD PRODUCT TO CART
========================================================= */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

header("Content-Type: application/json; charset=UTF-8");


/* =========================================================
   LOGIN CHECK
========================================================= */

if (
    !isset($_SESSION["logged_in"]) ||
    $_SESSION["logged_in"] !== true
) {

    echo json_encode([
        "success" => false,
        "login_required" => true,
        "message" => "Please login to add items to your order."
    ]);

    exit;
}


/* =========================================================
   POST ONLY
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] !== "POST"
) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request."
    ]);

    exit;
}


/* =========================================================
   DATABASE
========================================================= */

require_once __DIR__ . "/include/config.php";


/* =========================================================
   CSRF TOKEN
========================================================= */

if (
    empty($_SESSION["cart_csrf"])
) {

    $_SESSION["cart_csrf"] =
        bin2hex(
            random_bytes(32)
        );

}


$csrfToken =
    $_POST["csrf_token"] ?? "";


/*
 * CSRF is required when the menu sends it.
 *
 * If your existing menu does not yet send a token,
 * this temporarily allows the request so your
 * existing Add to Cart buttons continue working.
 *
 * We will connect the menu token in the next step.
 */

if (
    $csrfToken !== ""
) {

    if (
        !hash_equals(
            $_SESSION["cart_csrf"],
            $csrfToken
        )
    ) {

        echo json_encode([
            "success" => false,
            "message" => "Security validation failed. Please refresh the page."
        ]);

        exit;
    }

}


/* =========================================================
   PRODUCT ID
========================================================= */

$product_id =
    filter_input(
        INPUT_POST,
        "product_id",
        FILTER_VALIDATE_INT
    );


if (
    $product_id === false ||
    $product_id === null ||
    $product_id <= 0
) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid product."
    ]);

    exit;
}


$product_id =
    (int)$product_id;


/* =========================================================
   GET REAL PRODUCT FROM DATABASE
========================================================= */

$sql = "
    SELECT
        id,
        name,
        description,
        price,
        image,
        status
    FROM products
    WHERE id = ?
    LIMIT 1
";


$stmt =
    mysqli_prepare(
        $link,
        $sql
    );


if (!$stmt) {

    error_log(
        "Add to cart prepare failed: " .
        mysqli_error($link)
    );

    echo json_encode([
        "success" => false,
        "message" => "Unable to process product."
    ]);

    exit;
}


mysqli_stmt_bind_param(
    $stmt,
    "i",
    $product_id
);


if (
    !mysqli_stmt_execute(
        $stmt
    )
) {

    error_log(
        "Add to cart execute failed: " .
        mysqli_stmt_error($stmt)
    );

    mysqli_stmt_close(
        $stmt
    );

    echo json_encode([
        "success" => false,
        "message" => "Unable to process product."
    ]);

    exit;
}


$result =
    mysqli_stmt_get_result(
        $stmt
    );


if (!$result) {

    mysqli_stmt_close(
        $stmt
    );

    echo json_encode([
        "success" => false,
        "message" => "Unable to find product."
    ]);

    exit;
}


$product =
    mysqli_fetch_assoc(
        $result
    );


mysqli_stmt_close(
    $stmt
);


/* =========================================================
   PRODUCT EXISTS
========================================================= */

if (!$product) {

    echo json_encode([
        "success" => false,
        "message" => "Product not found."
    ]);

    exit;
}


/* =========================================================
   PRODUCT STATUS
========================================================= */

$productStatus =
    strtolower(
        trim(
            (string)(
                $product["status"] ?? ""
            )
        )
    );


if (
    $productStatus !== "active"
) {

    echo json_encode([
        "success" => false,
        "message" => "This product is currently unavailable."
    ]);

    exit;
}


/* =========================================================
   TRUSTED PRODUCT DATA
========================================================= */

$product_id =
    (int)$product["id"];


$product_name =
    trim(
        (string)$product["name"]
    );


$product_price =
    (float)$product["price"];


$product_image =
    basename(
        trim(
            (string)(
                $product["image"] ?? ""
            )
        )
    );


$product_description =
    trim(
        (string)(
            $product["description"] ?? ""
        )
    );


/* =========================================================
   PRODUCT VALIDATION
========================================================= */

if (
    $product_name === ""
) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid product name."
    ]);

    exit;
}


if (
    $product_price <= 0
) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid product price."
    ]);

    exit;
}


/* =========================================================
   IMAGE FALLBACK
========================================================= */

if (
    $product_image === ""
) {

    $product_image =
        "default-coffee.jpg";

}


/* =========================================================
   DESCRIPTION FALLBACK
========================================================= */

if (
    $product_description === ""
) {

    $product_description =
        "Freshly prepared at Caffeine & Cove.";

}


/* =========================================================
   CREATE CART
========================================================= */

if (
    !isset($_SESSION["cart"]) ||
    !is_array($_SESSION["cart"])
) {

    $_SESSION["cart"] = [];

}


/* =========================================================
   CART KEY
========================================================= */

$cartKey =
    "product_" .
    $product_id;


/* =========================================================
   CURRENT QUANTITY
========================================================= */

$currentQuantity = 0;


if (
    isset(
        $_SESSION["cart"][$cartKey]
    ) &&
    is_array(
        $_SESSION["cart"][$cartKey]
    )
) {

    $currentQuantity =
        isset(
            $_SESSION["cart"][$cartKey]["quantity"]
        )
            ? (int)
                $_SESSION["cart"][$cartKey]["quantity"]
            : 0;

}


/* =========================================================
   QUANTITY VALIDATION
========================================================= */

if (
    $currentQuantity < 0
) {

    $currentQuantity = 0;

}


if (
    $currentQuantity >= 20
) {

    echo json_encode([
        "success" => false,
        "message" => "Maximum quantity reached for this item.",
        "cart_count" => 20
    ]);

    exit;
}


/* =========================================================
   NEW QUANTITY
========================================================= */

$newQuantity =
    $currentQuantity + 1;


if (
    $newQuantity > 20
) {

    $newQuantity = 20;

}


/* =========================================================
   SAVE CART ITEM
========================================================= */

$_SESSION["cart"][$cartKey] = [

    "product_id" =>
        $product_id,

    "name" =>
        $product_name,

    "price" =>
        $product_price,

    "quantity" =>
        $newQuantity,

    "image" =>
        $product_image,

    "description" =>
        $product_description

];


/* =========================================================
   CART COUNT
========================================================= */

$cart_count = 0;


foreach (
    $_SESSION["cart"]
    as $item
) {

    if (
        !is_array($item)
    ) {

        continue;

    }


    $quantity =
        isset($item["quantity"])
            ? (int)$item["quantity"]
            : 0;


    if (
        $quantity < 1
    ) {

        continue;

    }


    if (
        $quantity > 20
    ) {

        $quantity = 20;

    }


    $cart_count +=
        $quantity;

}


/* =========================================================
   RESPONSE
========================================================= */

echo json_encode([

    "success" =>
        true,

    "message" =>
        $product_name .
        " added to cart.",

    "cart_count" =>
        $cart_count

]);

exit;

?>