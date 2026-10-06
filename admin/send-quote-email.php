<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";
require_once "../config/send-order-email.php";


/* =========================================================
   ORDER ID
========================================================= */

$orderId =
    (int)($_GET["id"] ?? 0);


if ($orderId <= 0) {

    die("Invalid order ID.");
}


/* =========================================================
   GET ORDER
========================================================= */

$stmt = $conn->prepare("
    SELECT
        id,
        customer_name,
        customer_phone,
        items_json,
        quoted_items_json,
        quoted_total
    FROM orders
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $orderId
);

$stmt->execute();

$result =
    $stmt->get_result();

$order =
    $result->fetch_assoc();

$stmt->close();


if (!$order) {

    die("Order not found.");
}


/* =========================================================
   GET QUOTE
========================================================= */

$items = [];

if (!empty($order["quoted_items_json"])) {

    $decoded =
        json_decode(
            $order["quoted_items_json"],
            true
        );

    if (is_array($decoded)) {

        $items = $decoded;
    }
}


/*
|--------------------------------------------------------------------------
| If no quote saved, use original order
|--------------------------------------------------------------------------
*/

if (empty($items)) {

    $decoded =
        json_decode(
            $order["items_json"],
            true
        );

    if (is_array($decoded)) {

        $items = $decoded;
    }
}


/* =========================================================
   BUILD EMAIL
========================================================= */

$total = 0;

$htmlItems = "";

$plainItems = "";


foreach ($items as $item) {

    $name =
        htmlspecialchars(
            $item["product_name"]
            ?? "Product",
            ENT_QUOTES,
            "UTF-8"
        );

    $quantity =
        (float)(
            $item["quantity"]
            ?? 0
        );

    $unit =
        htmlspecialchars(
            $item["unit"]
            ?? "",
            ENT_QUOTES,
            "UTF-8"
        );

    $price =
        isset($item["price"])
            ? (float)$item["price"]
            : 0;

    $lineTotal =
        isset($item["line_total"])
            ? (float)$item["line_total"]
            : ($quantity * $price);


    if ($price > 0) {

        $htmlItems .= "
            <tr>
                <td style='padding:8px;border-bottom:1px solid #ddd;'>
                    {$name}
                </td>

                <td style='padding:8px;border-bottom:1px solid #ddd;'>
                    {$quantity} {$unit}
                </td>

                <td style='padding:8px;border-bottom:1px solid #ddd;'>
                    AED " .
                    number_format(
                        $price,
                        2
                    ) .
                "
                </td>

                <td style='padding:8px;border-bottom:1px solid #ddd;'>
                    AED " .
                    number_format(
                        $lineTotal,
                        2
                    ) .
                "
                </td>
            </tr>
        ";

        $total +=
            $lineTotal;


        $plainItems .=
            ($item["product_name"] ?? "Product") .
            " - " .
            $quantity .
            " " .
            ($item["unit"] ?? "") .
            " - AED " .
            number_format(
                $price,
                2
            ) .
            "\n";

    } else {

        $htmlItems .= "
            <tr>
                <td style='padding:8px;border-bottom:1px solid #ddd;'>
                    {$name}
                </td>

                <td style='padding:8px;border-bottom:1px solid #ddd;'>
                    {$quantity} {$unit}
                </td>

                <td style='padding:8px;border-bottom:1px solid #ddd;'>
                    -
                </td>

                <td style='padding:8px;border-bottom:1px solid #ddd;'>
                    -
                </td>
            </tr>
        ";


        $plainItems .=
            ($item["product_name"] ?? "Product") .
            " - " .
            $quantity .
            " " .
            ($item["unit"] ?? "") .
            "\n";
    }
}


/* =========================================================
   HTML EMAIL
========================================================= */

$htmlBody = "

<div style='font-family:Arial,Helvetica,sans-serif;'>

    <h2>
        Jasir Fresh
    </h2>

    <h3>
        Order #{$orderId} - Price Confirmation
    </h3>

    <p>
        <strong>Customer:</strong>
        " .
        htmlspecialchars(
            $order["customer_name"],
            ENT_QUOTES,
            "UTF-8"
        ) .
    "
    </p>

    <p>
        <strong>Phone:</strong>
        " .
        htmlspecialchars(
            $order["customer_phone"],
            ENT_QUOTES,
            "UTF-8"
        ) .
    "
    </p>

    <table
        cellpadding='0'
        cellspacing='0'
        style='
            width:100%;
            border-collapse:collapse;
            border:1px solid #ddd;
        '
    >

        <thead>

            <tr style='background:#f3f7f4;'>

                <th style='padding:10px;text-align:left;'>
                    Product
                </th>

                <th style='padding:10px;text-align:left;'>
                    Quantity
                </th>

                <th style='padding:10px;text-align:left;'>
                    Price
                </th>

                <th style='padding:10px;text-align:left;'>
                    Total
                </th>

            </tr>

        </thead>

        <tbody>

            {$htmlItems}

        </tbody>

    </table>
";


if ($total > 0) {

    $htmlBody .= "

        <h3>
            Total: AED " .
            number_format(
                $total,
                2
            ) .
        "
        </h3>
    ";
}


$htmlBody .= "

    <p>
        Please confirm your order.
    </p>

    <p>
        Jasir Fresh
    </p>

</div>
";


/* =========================================================
   PLAIN TEXT
========================================================= */

$plainBody =
    "ORDER #" .
    $orderId .
    " - PRICE CONFIRMATION\n\n" .

    "Customer: " .
    $order["customer_name"] .
    "\n\n" .

    "Products:\n\n" .

    $plainItems;


if ($total > 0) {

    $plainBody .=
        "\nTotal: AED " .
        number_format(
            $total,
            2
        );
}


$plainBody .=
    "\n\nPlease confirm your order.\n\n" .
    "Jasir Fresh";


/* =========================================================
   SEND EMAIL
========================================================= */

$config =
    require "../config/email.php";


$result =
    sendJasirEmail(

        $config["admin_email"],

        $config["admin_name"],

        "Order #{$orderId} - Price Confirmation",

        $htmlBody,

        $plainBody
    );


/* =========================================================
   UPDATE SENT TIME
========================================================= */

if ($result["success"]) {

    $stmt =
        $conn->prepare("
            UPDATE orders
            SET quote_sent_at = NOW()
            WHERE id = ?
        ");

    $stmt->bind_param(
        "i",
        $orderId
    );

    $stmt->execute();

    $stmt->close();
}


/* =========================================================
   RESULT
========================================================= */

if ($result["success"]) {

    echo "
    <script>

        alert('Email sent successfully.');

        window.location.href =
            'order-details.php?id={$orderId}';

    </script>
    ";

} else {

    echo "
    <script>

        alert(
            'Email failed: " .
            addslashes(
                $result["message"]
            ) .
            "'
        );

        window.location.href =
            'order-details.php?id={$orderId}';

    </script>
    ";
}

exit;