<?php

header("Content-Type: application/json; charset=UTF-8");


/*
|--------------------------------------------------------------------------
| PHPMailer
|--------------------------------------------------------------------------
*/

require_once "../PHPMailer/src/Exception.php";
require_once "../PHPMailer/src/PHPMailer.php";
require_once "../PHPMailer/src/SMTP.php";


/*
|--------------------------------------------------------------------------
| DATABASE
|--------------------------------------------------------------------------
*/

require_once "../config/database.php";


use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


/*
|--------------------------------------------------------------------------
| EMAIL CONFIG
|--------------------------------------------------------------------------
*/

$config =
    require_once "../config/email.php";


/*
|--------------------------------------------------------------------------
| READ JSON
|--------------------------------------------------------------------------
*/

$input = json_decode(
    file_get_contents("php://input"),
    true
);


if (!is_array($input)) {

    echo json_encode(
        [
            "success" => false,
            "message" => "Invalid email request."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| ORDER ID
|--------------------------------------------------------------------------
*/

$orderId = (int)(
    $input["orderId"] ??
    $input["order_id"] ??
    0
);


if ($orderId <= 0) {

    echo json_encode(
        [
            "success" => false,
            "message" => "Invalid order ID."
        ],
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| GET ORDER FROM DATABASE
|--------------------------------------------------------------------------
*/

$stmt = $conn->prepare("
    SELECT
        id,
        customer_name,
        customer_phone,
        items_json,
        item_count,
        status

    FROM orders

    WHERE id = ?

    LIMIT 1
");


if (!$stmt) {

    echo json_encode(
        [
            "success" => false,
            "message" => "Unable to find order."
        ],
        JSON_UNESCAPED_UNICODE
    );

    $conn->close();

    exit;
}


$stmt->bind_param(
    "i",
    $orderId
);


if (!$stmt->execute()) {

    $stmt->close();

    echo json_encode(
        [
            "success" => false,
            "message" => "Unable to load order."
        ],
        JSON_UNESCAPED_UNICODE
    );

    $conn->close();

    exit;
}


$result = $stmt->get_result();


if (
    !$result ||
    $result->num_rows === 0
) {

    $stmt->close();

    echo json_encode(
        [
            "success" => false,
            "message" => "Order not found."
        ],
        JSON_UNESCAPED_UNICODE
    );

    $conn->close();

    exit;
}


$order = $result->fetch_assoc();

$stmt->close();


/*
|--------------------------------------------------------------------------
| ORDER DATA
|--------------------------------------------------------------------------
*/

$name =
    trim(
        (string)$order["customer_name"]
    );


$phone =
    trim(
        (string)$order["customer_phone"]
    );


$items = json_decode(
    $order["items_json"],
    true
);


if (
    !is_array($items) ||
    empty($items)
) {

    echo json_encode(
        [
            "success" => false,
            "message" => "Order items are invalid."
        ],
        JSON_UNESCAPED_UNICODE
    );

    $conn->close();

    exit;
}


/*
|--------------------------------------------------------------------------
| CREATE MAIL
|--------------------------------------------------------------------------
*/

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

    $mail->SMTPAuth = true;

    $mail->Username =
        $config["username"];

    $mail->Password =
        $config["password"];

    $mail->SMTPSecure =
        PHPMailer::ENCRYPTION_STARTTLS;

    $mail->Port =
        (int)$config["port"];

    $mail->CharSet =
        "UTF-8";


    /*
    |--------------------------------------------------------------------------
    | FROM / TO
    |--------------------------------------------------------------------------
    */

    $mail->setFrom(
        $config["from_email"],
        $config["from_name"]
    );


    $mail->addAddress(
        $config["to_email"],
        $config["to_name"]
    );


    /*
    |--------------------------------------------------------------------------
    | EMAIL CONTENT
    |--------------------------------------------------------------------------
    */

    $mail->isHTML(true);


    $mail->Subject =
        "New Fresh Harvest Order #" .
        $orderId;


    $html =
        "<h2>New Fresh Harvest Order</h2>";


    $html .=
        "<p><strong>Order ID:</strong> #" .
        htmlspecialchars(
            (string)$orderId,
            ENT_QUOTES,
            "UTF-8"
        ) .
        "</p>";


    $html .=
        "<p><strong>Customer:</strong> " .
        htmlspecialchars(
            $name,
            ENT_QUOTES,
            "UTF-8"
        ) .
        "</p>";


    $html .=
        "<p><strong>Phone:</strong> " .
        htmlspecialchars(
            $phone,
            ENT_QUOTES,
            "UTF-8"
        ) .
        "</p>";


    $html .=
        "<h3>Products</h3>";


    $html .=
        "<table border='1' cellpadding='8' cellspacing='0' style='border-collapse:collapse;width:100%;'>";


    $html .=
        "<tr>
            <th>Product</th>
            <th>Quantity</th>
            <th>Unit</th>
            <th>Box Label</th>
        </tr>";


    foreach ($items as $item) {

        $productName =
            (string)(
                $item["product_name"] ??
                "Product"
            );


        $quantity =
            (string)(
                $item["quantity"] ??
                "0"
            );


        $unit =
            (string)(
                $item["unit"] ??
                ""
            );


        $boxLabel =
            (string)(
                $item["box_label"] ??
                ""
            );


        $html .=
            "<tr>";

        $html .=
            "<td>" .
            htmlspecialchars(
                $productName,
                ENT_QUOTES,
                "UTF-8"
            ) .
            "</td>";


        $html .=
            "<td>" .
            htmlspecialchars(
                $quantity,
                ENT_QUOTES,
                "UTF-8"
            ) .
            "</td>";


        $html .=
            "<td>" .
            htmlspecialchars(
                strtoupper($unit),
                ENT_QUOTES,
                "UTF-8"
            ) .
            "</td>";


        $html .=
            "<td>" .
            htmlspecialchars(
                $boxLabel,
                ENT_QUOTES,
                "UTF-8"
            ) .
            "</td>";


        $html .=
            "</tr>";
    }


    $html .=
        "</table>";


    $html .=
        "<p><strong>Total Items:</strong> " .
        (int)$order["item_count"] .
        "</p>";


    $html .=
        "<p>Please confirm product availability and final pricing.</p>";


    /*
    |--------------------------------------------------------------------------
    | EMAIL BODY
    |--------------------------------------------------------------------------
    */

    $mail->Body =
        $html;


    $mail->AltBody =
        strip_tags(
            str_replace(
                [
                    "<br>",
                    "</p>",
                    "</li>",
                    "</h3>",
                    "</ul>",
                    "</tr>"
                ],
                [
                    "\n",
                    "\n",
                    "\n",
                    "\n",
                    "\n",
                    "\n"
                ],
                $html
            )
        );


    /*
    |--------------------------------------------------------------------------
    | SEND
    |--------------------------------------------------------------------------
    */

    $mail->send();


    /*
    |--------------------------------------------------------------------------
    | SUCCESS
    |--------------------------------------------------------------------------
    */

    echo json_encode(
        [
            "success" => true,
            "message" =>
                "Order email sent successfully.",
            "order_id" =>
                $orderId
        ],
        JSON_UNESCAPED_UNICODE
    );


} catch (Exception $e) {

    echo json_encode(
        [
            "success" => false,
            "message" =>
                "Email sending failed."
        ],
        JSON_UNESCAPED_UNICODE
    );
}


$conn->close();

?>