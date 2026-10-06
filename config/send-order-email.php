<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . "/../PHPMailer/src/Exception.php";
require_once __DIR__ . "/../PHPMailer/src/PHPMailer.php";
require_once __DIR__ . "/../PHPMailer/src/SMTP.php";


function sendJasirEmail(
    $toEmail,
    $toName,
    $subject,
    $htmlBody,
    $plainBody = ""
) {

    $config = require __DIR__ . "/email.php";

    $mail = new PHPMailer(true);

    try {

        /*
        |--------------------------------------------------------------------------
        | SMTP
        |--------------------------------------------------------------------------
        */

        $mail->isSMTP();

        $mail->Host =
            $config["host"];

        $mail->SMTPAuth =
            true;

        $mail->Username =
            $config["username"];

        $mail->Password =
            $config["password"];


        /*
        |--------------------------------------------------------------------------
        | Encryption
        |--------------------------------------------------------------------------
        */

        if (
            $config["encryption"] === "ssl"
        ) {

            $mail->SMTPSecure =
                PHPMailer::ENCRYPTION_SMTPS;

        } else {

            $mail->SMTPSecure =
                PHPMailer::ENCRYPTION_STARTTLS;
        }


        $mail->Port =
            (int)$config["port"];


        /*
        |--------------------------------------------------------------------------
        | Character Set
        |--------------------------------------------------------------------------
        */

        $mail->CharSet = "UTF-8";


        /*
        |--------------------------------------------------------------------------
        | FROM
        |--------------------------------------------------------------------------
        */

        $mail->setFrom(
            $config["from_email"],
            $config["from_name"]
        );


        /*
        |--------------------------------------------------------------------------
        | TO
        |--------------------------------------------------------------------------
        */

        $mail->addAddress(
            $toEmail,
            $toName
        );


        /*
        |--------------------------------------------------------------------------
        | BODY
        |--------------------------------------------------------------------------
        */

        $mail->isHTML(true);

        $mail->Subject =
            $subject;

        $mail->Body =
            $htmlBody;

        $mail->AltBody =
            $plainBody !== ""
                ? $plainBody
                : strip_tags($htmlBody);


        /*
        |--------------------------------------------------------------------------
        | SEND
        |--------------------------------------------------------------------------
        */

        $mail->send();


        return [
            "success" => true,
            "message" =>
                "Email sent successfully."
        ];

    } catch (Exception $e) {

        return [
            "success" => false,
            "message" =>
                $mail->ErrorInfo
        ];
    }
}