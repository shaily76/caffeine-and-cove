<?php

/* =========================================================
   CAFFEINE & COVE
   EMAIL SYSTEM - BREVO SMTP
   XAMPP + RENDER SAFE VERSION
========================================================= */

use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;


/* =========================================================
   LOAD PHPMailer
========================================================= */

require_once __DIR__ . "/../vendor/autoload.php";


/* =========================================================
   BREVO SMTP SETTINGS
========================================================= */

/*
 * BREVO SMTP USERNAME
 */

$brevoUsername =
    "b6e0fd001@smtp-brevo.com";


/*
 * BREVO SMTP KEY
 *
 * IMPORTANT:
 * Do NOT put the actual SMTP key here.
 *
 * It will be added later through
 * Render Environment Variables.
 */

$brevoPassword =
    getenv("BREVO_SMTP_KEY");


/* =========================================================
   SEND EMAIL
========================================================= */

function sendCoveEmail(
    string $toEmail,
    string $toName,
    string $subject,
    string $htmlBody
): bool {

    global $brevoUsername;
    global $brevoPassword;

    $mail = new PHPMailer(true);


    try {

        /* =================================================
           SMTP SETTINGS
        ================================================== */

        $mail->isSMTP();

        $mail->Host =
            "smtp-relay.brevo.com";

        $mail->SMTPAuth =
            true;

        $mail->Username =
            $brevoUsername;

        $mail->Password =
            $brevoPassword;

        $mail->SMTPSecure =
            PHPMailer::ENCRYPTION_STARTTLS;

        $mail->Port =
            587;


        /* =================================================
           SENDER
        ================================================== */

        $mail->setFrom(
            "caffeine.coveee@gmail.com",
            "Caffeine & Cove"
        );


        /* =================================================
           CUSTOMER
        ================================================== */

        $mail->addAddress(
            $toEmail,
            $toName
        );


        /* =================================================
           EMAIL CONTENT
        ================================================== */

        $mail->isHTML(true);

        $mail->CharSet =
            "UTF-8";

        $mail->Subject =
            $subject;

        $mail->Body =
            $htmlBody;


        /* =================================================
           PLAIN TEXT VERSION
        ================================================== */

        $mail->AltBody =
            strip_tags($htmlBody);


        /* =================================================
           SEND
        ================================================== */

        return $mail->send();


    } catch (Exception $e) {

        error_log(
            "Caffeine & Cove mail error: "
            . $mail->ErrorInfo
        );

        return false;
    }

}

?>