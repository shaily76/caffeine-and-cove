<?php

/* =========================================================
   CAFFEINE & COVE
   USER LOGIN PROCESS
========================================================= */

session_start();

require_once "include/config.php";


/* =========================================================
   ONLY POST REQUESTS
========================================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: login.php");

    exit;

}


/* =========================================================
   GET FORM DATA
========================================================= */

$login_input = trim($_POST["username"] ?? "");

$password = $_POST["password"] ?? "";

$remember = isset($_POST["remember"]);


/* =========================================================
   VALIDATION
========================================================= */

if ($login_input === "" || $password === "") {

    $_SESSION["login_error"] =
        "Please enter your username/email and password.";

    header("Location: login.php");

    exit;

}


/* =========================================================
   FIND USER
   Username OR Email
========================================================= */

$sql = "
    SELECT
        id,
        full_name,
        username,
        email,
        mobile,
        password
    FROM users
    WHERE username = ?
       OR email = ?
    LIMIT 1
";


$stmt = mysqli_prepare($link, $sql);


if (!$stmt) {

    $_SESSION["login_error"] =
        "Unable to process login. Please try again.";

    header("Location: login.php");

    exit;

}


mysqli_stmt_bind_param(
    $stmt,
    "ss",
    $login_input,
    $login_input
);


mysqli_stmt_execute($stmt);


$result = mysqli_stmt_get_result($stmt);


if (!$result || mysqli_num_rows($result) === 0) {

    mysqli_stmt_close($stmt);

    $_SESSION["login_error"] =
        "Invalid username/email or password.";

    header("Location: login.php");

    exit;

}


$user = mysqli_fetch_assoc($result);


mysqli_stmt_close($stmt);


/* =========================================================
   VERIFY PASSWORD
========================================================= */

if (!password_verify($password, $user["password"])) {

    $_SESSION["login_error"] =
        "Invalid username/email or password.";

    header("Location: login.php");

    exit;

}


/* =========================================================
   LOGIN SUCCESS
========================================================= */

session_regenerate_id(true);


/* =========================================================
   SAVE USER SESSION
========================================================= */

$_SESSION["user_id"] = (int) $user["id"];

$_SESSION["user_name"] = $user["full_name"];

$_SESSION["username"] = $user["username"];

$_SESSION["user_email"] = $user["email"];

$_SESSION["user_mobile"] = $user["mobile"];

$_SESSION["logged_in"] = true;


/* =========================================================
   REMEMBER ME
========================================================= */

if ($remember) {

    /*
     * For now we only keep the login active
     * through the PHP session.
     *
     * A secure persistent remember-token system
     * can be added later.
     */

    $_SESSION["remember_me"] = true;

} else {

    $_SESSION["remember_me"] = false;

}


/* =========================================================
   SUCCESS MESSAGE
========================================================= */

$_SESSION["login_success"] =
    "Welcome back, " . $user["full_name"] . "!";


/* =========================================================
   REDIRECT
========================================================= */

header("Location: index.php");

exit;

?>