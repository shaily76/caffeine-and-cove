<?php

/* =========================================================
   CAFFEINE & COVE
   USER REGISTRATION PROCESS
   Local XAMPP + Vercel / TiDB Cloud
========================================================= */

session_start();

require_once "include/config.php";


/* =========================================================
   ONLY POST REQUESTS
========================================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: register.php");
    exit;

}


/* =========================================================
   GET FORM DATA
========================================================= */

$full_name = trim($_POST["full_name"] ?? "");
$username  = trim($_POST["username"] ?? "");
$email     = trim($_POST["email"] ?? "");
$mobile    = trim($_POST["mobile"] ?? "");

$password = $_POST["password"] ?? "";
$confirm_password = $_POST["confirm_password"] ?? "";


/* =========================================================
   BASIC VALIDATION
========================================================= */

if (
    $full_name === "" ||
    $username === "" ||
    $email === "" ||
    $mobile === "" ||
    $password === "" ||
    $confirm_password === ""
) {

    $_SESSION["register_error"] =
        "Please fill in all required fields.";

    header("Location: register.php");
    exit;

}


/* =========================================================
   EMAIL VALIDATION
========================================================= */

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    $_SESSION["register_error"] =
        "Please enter a valid email address.";

    header("Location: register.php");
    exit;

}


/* =========================================================
   MOBILE VALIDATION
========================================================= */

$mobile_clean = preg_replace(
    "/[^0-9+]/",
    "",
    $mobile
);

$mobile_digits = preg_replace(
    "/[^0-9]/",
    "",
    $mobile_clean
);

if (strlen($mobile_digits) < 10) {

    $_SESSION["register_error"] =
        "Please enter a valid mobile number.";

    header("Location: register.php");
    exit;

}


/* =========================================================
   PASSWORD VALIDATION
========================================================= */

if (strlen($password) < 6) {

    $_SESSION["register_error"] =
        "Password must contain at least 6 characters.";

    header("Location: register.php");
    exit;

}


if ($password !== $confirm_password) {

    $_SESSION["register_error"] =
        "Passwords do not match.";

    header("Location: register.php");
    exit;

}


/* =========================================================
   CHECK USERNAME
========================================================= */

$sql = "
    SELECT id
    FROM users
    WHERE username = ?
    LIMIT 1
";

$stmt = mysqli_prepare($link, $sql);

if (!$stmt) {

    $_SESSION["register_error"] =
        "Unable to process registration.";

    header("Location: register.php");
    exit;

}

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $username
);

mysqli_stmt_execute($stmt);

mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {

    mysqli_stmt_close($stmt);

    $_SESSION["register_error"] =
        "Username already exists.";

    header("Location: register.php");
    exit;

}

mysqli_stmt_close($stmt);


/* =========================================================
   CHECK EMAIL
========================================================= */

$sql = "
    SELECT id
    FROM users
    WHERE email = ?
    LIMIT 1
";

$stmt = mysqli_prepare($link, $sql);

if (!$stmt) {

    $_SESSION["register_error"] =
        "Unable to process registration.";

    header("Location: register.php");
    exit;

}

mysqli_stmt_bind_param(
    $stmt,
    "s",
    $email
);

mysqli_stmt_execute($stmt);

mysqli_stmt_store_result($stmt);

if (mysqli_stmt_num_rows($stmt) > 0) {

    mysqli_stmt_close($stmt);

    $_SESSION["register_error"] =
        "Email address is already registered.";

    header("Location: register.php");
    exit;

}

mysqli_stmt_close($stmt);


/* =========================================================
   HASH PASSWORD
========================================================= */

$hashed_password = password_hash(
    $password,
    PASSWORD_DEFAULT
);


/* =========================================================
   GENERATE NEXT USER ID
========================================================= */

$sql = "
    SELECT COALESCE(MAX(id), 0) + 1 AS next_id
    FROM users
";

$result = mysqli_query(
    $link,
    $sql
);

if ($result === false) {

    $_SESSION["register_error"] =
        "Unable to create user ID.";

    header("Location: register.php");
    exit;

}

$row = mysqli_fetch_assoc($result);

$new_user_id = (int) $row["next_id"];


/* =========================================================
   INSERT USER
========================================================= */

$sql = "
    INSERT INTO users
    (
        id,
        full_name,
        username,
        email,
        mobile,
        password
    )
    VALUES
    (?, ?, ?, ?, ?, ?)
";

$stmt = mysqli_prepare(
    $link,
    $sql
);

if (!$stmt) {

    $_SESSION["register_error"] =
        "Registration failed. Please try again.";

    header("Location: register.php");
    exit;

}


/* =========================================================
   BIND VALUES
========================================================= */

mysqli_stmt_bind_param(
    $stmt,
    "isssss",
    $new_user_id,
    $full_name,
    $username,
    $email,
    $mobile_clean,
    $hashed_password
);


/* =========================================================
   EXECUTE
========================================================= */

if (mysqli_stmt_execute($stmt)) {

    mysqli_stmt_close($stmt);

    $_SESSION["register_success"] =
        "Account created successfully. Please login.";

    header("Location: login.php");
    exit;

}


/* =========================================================
   INSERT ERROR
========================================================= */

$error_message = mysqli_stmt_error($stmt);

mysqli_stmt_close($stmt);

$_SESSION["register_error"] =
    "Registration failed: " . $error_message;

header("Location: register.php");
exit;

?>
