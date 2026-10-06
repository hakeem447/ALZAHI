<?php

require_once __DIR__ . "/send-order-email.php";

$result = sendJasirEmail(

    "abudlhakeem1998@gmail.com",

    "Jasir Fresh",

    "Jasir Fresh SMTP Test",

    "
    <h2>SMTP Test Successful</h2>

    <p>
        This email was sent using Gmail SMTP
        and PHPMailer.
    </p>

    <p>
        Jasir Fresh website email system is working.
    </p>
    ",

    "SMTP Test Successful.
    Jasir Fresh email system is working."
);


echo "<pre>";

print_r($result);

echo "</pre>";