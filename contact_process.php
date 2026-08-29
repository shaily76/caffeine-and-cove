<?php

session_start();


// =========================================================
// DATABASE CONNECTION
// =========================================================

$conn = mysqli_connect(
    "localhost",
    "root",
    "",
    "caffeine_cove"
);


// Check connection

if (!$conn) {

    die("Database connection failed: " . mysqli_connect_error());

}


// =========================================================
// GET FORM DATA
// =========================================================

$name = trim($_POST['name'] ?? '');

$email = trim($_POST['email'] ?? '');

$subject = trim($_POST['subject'] ?? '');

$message = trim($_POST['message'] ?? '');


// =========================================================
// VALIDATION
// =========================================================

if ($name === '' || $email === '' || $subject === '' || $message === '') {

    echo "<script>
            alert('Please fill in all fields.');
            window.history.back();
          </script>";

    exit;
}


if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

    echo "<script>
            alert('Please enter a valid email address.');
            window.history.back();
          </script>";

    exit;
}


// =========================================================
// INSERT MESSAGE
// =========================================================

$sql = "INSERT INTO contact_messages
        (name, email, subject, message)
        VALUES (?, ?, ?, ?)";


$stmt = mysqli_prepare($conn, $sql);


if (!$stmt) {

    die("Something went wrong. Please try again.");

}


mysqli_stmt_bind_param(
    $stmt,
    "ssss",
    $name,
    $email,
    $subject,
    $message
);


if (mysqli_stmt_execute($stmt)) {

    echo "<script>

            alert('Thank you! Your message has been sent successfully.');

            window.location.href = 'contact.php';

          </script>";

} else {

    echo "<script>

            alert('Unable to send your message. Please try again.');

            window.history.back();

          </script>";

}


// =========================================================
// CLOSE
// =========================================================

mysqli_stmt_close($stmt);

mysqli_close($conn);

?>