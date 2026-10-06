<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";


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
   ITEMS
========================================================= */

$items = [];

$originalItems =
    json_decode(
        $order["items_json"],
        true
    );

if (is_array($originalItems)) {
    $items = $originalItems;
}


/* =========================================================
   USE SAVED QUOTE IF AVAILABLE
========================================================= */

$quotedItems = [];

if (!empty($order["quoted_items_json"])) {

    $decoded =
        json_decode(
            $order["quoted_items_json"],
            true
        );

    if (is_array($decoded)) {
        $quotedItems = $decoded;
    }
}


$quoteSource =
    !empty($quotedItems)
        ? $quotedItems
        : $items;


/* =========================================================
   BUILD WHATSAPP MESSAGE
========================================================= */

$lines = [];

$lines[] =
    "ORDER #" .
    $orderId .
    " - PRICE CONFIRMATION";

$lines[] = "";

$lines[] =
    "Customer: " .
    $order["customer_name"];

$lines[] = "";

$lines[] =
    "Products:";


$total = 0;


foreach ($quoteSource as $item) {

    $name =
        $item["product_name"]
        ?? "Product";

    $quantity =
        (float)(
            $item["quantity"]
            ?? 0
        );

    $unit =
        $item["unit"]
        ?? "";

    $price =
        isset($item["price"])
            ? (float)$item["price"]
            : 0;

    $lineTotal =
        isset($item["line_total"])
            ? (float)$item["line_total"]
            : ($quantity * $price);


    /*
    |--------------------------------------------------------------------------
    | WITH PRICE
    |--------------------------------------------------------------------------
    */

    if ($price > 0) {

        $lines[] =
            $name .
            " - " .
            $quantity .
            " " .
            $unit .
            " - AED " .
            number_format(
                $price,
                2
            );

        $total +=
            $lineTotal;

    }


    /*
    |--------------------------------------------------------------------------
    | WITHOUT PRICE
    |--------------------------------------------------------------------------
    */

    else {

        $lines[] =
            $name .
            " - " .
            $quantity .
            " " .
            $unit;
    }
}


/* =========================================================
   TOTAL
========================================================= */

if ($total > 0) {

    $lines[] = "";

    $lines[] =
        "Total: AED " .
        number_format(
            $total,
            2
        );
}


$lines[] = "";

$lines[] =
    "Please confirm your order.";

$lines[] = "";

$lines[] =
    "Jasir Fresh";


$message =
    implode(
        "\n",
        $lines
    );


/* =========================================================
   PHONE NUMBER
========================================================= */

$phone =
    preg_replace(
        "/[^0-9]/",
        "",
        $order["customer_phone"]
    );


/*
|--------------------------------------------------------------------------
| Remove international 00
|--------------------------------------------------------------------------
*/

if (
    substr($phone, 0, 2) === "00"
) {

    $phone =
        substr($phone, 2);
}


/*
|--------------------------------------------------------------------------
| UAE LOCAL NUMBER
|
| 0554480623
| becomes
| 971554480623
|--------------------------------------------------------------------------
*/

if (
    preg_match(
        "/^0?5[0-9]{8}$/",
        $phone
    )
) {

    $phone =
        "971" .
        ltrim(
            $phone,
            "0"
        );
}


/* =========================================================
   VALIDATE PHONE
========================================================= */

if ($phone === "") {

    die(
        "Customer phone number is empty."
    );
}


/* =========================================================
   WHATSAPP URL
========================================================= */

$whatsappUrl =
    "https://wa.me/" .
    $phone .
    "?text=" .
    rawurlencode(
        $message
    );


/* =========================================================
   REDIRECT
========================================================= */

header(
    "Location: " .
    $whatsappUrl
);

exit;