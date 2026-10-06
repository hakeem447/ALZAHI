<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";


/* =========================================================
   HELPERS
========================================================= */

function h($value)
{
    return htmlspecialchars(
        (string)$value,
        ENT_QUOTES,
        "UTF-8"
    );
}


function getItems($json)
{
    $items = json_decode(
        $json,
        true
    );

    return is_array($items)
        ? $items
        : [];
}


/* =========================================================
   ORDER ID
========================================================= */

$orderId =
    (int)($_GET["id"] ?? $_POST["order_id"] ?? 0);


if ($orderId <= 0) {

    die("Invalid order ID.");
}


/* =========================================================
   SAVE QUOTE
========================================================= */

$message = "";
$messageType = "success";

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["save_quote"])
) {

    $prices =
        $_POST["price"] ?? [];

    $originalItems = [];


    $stmt = $conn->prepare("
        SELECT items_json
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

    $orderRow =
        $result->fetch_assoc();

    $stmt->close();


    if (!$orderRow) {

        die("Order not found.");
    }


    $originalItems =
        getItems(
            $orderRow["items_json"]
        );


    $quotedItems = [];

    $total = 0;


    foreach (
        $originalItems
        as $index => $item
    ) {

        $quantity =
            (float)(
                $item["quantity"]
                ?? 0
            );

        $unit =
            $item["unit"]
            ?? "";

        $productName =
            $item["product_name"]
            ?? "Product";


        $priceValue =
            $prices[$index]
            ?? "";


        $priceValue =
            trim(
                str_replace(
                    ",",
                    "",
                    (string)$priceValue
                )
            );


        $price =
            $priceValue === ""
                ? 0
                : (float)$priceValue;


        $lineTotal =
            $quantity * $price;


        $total +=
            $lineTotal;


        $quotedItems[] = [

            "product_id" =>
                (int)(
                    $item["product_id"]
                    ?? 0
                ),

            "product_name" =>
                $productName,

            "category" =>
                $item["category"]
                ?? "",

            "unit" =>
                $unit,

            "quantity" =>
                $quantity,

            "price" =>
                $price,

            "line_total" =>
                $lineTotal
        ];
    }


    $quotedJson =
        json_encode(
            $quotedItems,
            JSON_UNESCAPED_UNICODE
        );


    $stmt =
        $conn->prepare("
            UPDATE orders
            SET
                quoted_items_json = ?,
                quoted_total = ?,
                quote_sent_at = NULL
            WHERE id = ?
        ");


    if (!$stmt) {

        $message =
            "Database error: " .
            $conn->error;

        $messageType =
            "error";

    } else {

        $stmt->bind_param(
            "sdi",
            $quotedJson,
            $total,
            $orderId
        );


        if ($stmt->execute()) {

            $message =
                "Prices saved successfully.";

        } else {

            $message =
                "Could not save prices: " .
                $stmt->error;

            $messageType =
                "error";
        }

        $stmt->close();
    }
}


/* =========================================================
   GET CURRENT ORDER
========================================================= */

$stmt = $conn->prepare("
    SELECT
        id,
        customer_name,
        customer_phone,
        items_json,
        item_count,
        status,
        created_at,
        quoted_items_json,
        quoted_total,
        quote_sent_at
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
   ORIGINAL ITEMS
========================================================= */

$items =
    getItems(
        $order["items_json"]
    );


/* =========================================================
   EXISTING QUOTE
========================================================= */

$quotedItems = [];

if (
    !empty(
        $order["quoted_items_json"]
    )
) {

    $decoded =
        json_decode(
            $order["quoted_items_json"],
            true
        );

    if (is_array($decoded)) {

        $quotedItems =
            $decoded;
    }
}


/* =========================================================
   CUSTOMER HISTORY
========================================================= */

$history = [];

$stmt = $conn->prepare("
    SELECT
        id,
        customer_name,
        customer_phone,
        items_json,
        status,
        created_at,
        quoted_total
    FROM orders
    WHERE
        customer_phone = ?
    ORDER BY id DESC
");

$stmt->bind_param(
    "s",
    $order["customer_phone"]
);

$stmt->execute();

$historyResult =
    $stmt->get_result();

while (
    $row =
        $historyResult->fetch_assoc()
) {

    $history[] =
        $row;
}

$stmt->close();


/* =========================================================
   BUILD WHATSAPP QUOTE
========================================================= */

/*$whatsappLines = [];

$whatsappLines[] =
    "ORDER #" .
    $orderId .
    " - PRICE CONFIRMATION";

$whatsappLines[] = "";

$whatsappLines[] =
    "Customer: " .
    $order["customer_name"];

$whatsappLines[] = "";

$whatsappLines[] =
    "Products:";

$whatsappTotal = 0;


$quoteSource =
    !empty($quotedItems)
        ? $quotedItems
        : $items;


foreach (
    $quoteSource
    as $item
) {

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
            : (
                $quantity * $price
            );


    if ($price > 0) {

        $whatsappLines[] =
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

        $whatsappTotal +=
            $lineTotal;

    } else {

        $whatsappLines[] =
            $name .
            " - " .
            $quantity .
            " " .
            $unit;
    }
}


if ($whatsappTotal > 0) {

    $whatsappLines[] = "";

    $whatsappLines[] =
        "Total: AED " .
        number_format(
            $whatsappTotal,
            2
        );
}


$whatsappLines[] = "";

$whatsappLines[] =
    "Please confirm your order.";

$whatsappLines[] = "";

$whatsappLines[] =
    "Jasir Fresh";


$whatsappMessage =
    implode(
        "\n",
        $whatsappLines
    );


$whatsappUrl =
    "https://wa.me/" .
    preg_replace(
        "/[^0-9]/",
        "",
        $order["customer_phone"]
    ) .
    "?text=" .
    rawurlencode(
        $whatsappMessage
    ); */

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>
    Order #<?= (int)$orderId ?>
    | Jasir Fresh
</title>

<style>

* {
    box-sizing: border-box;
}

body {

    margin: 0;

    padding: 28px 18px;

    background: #f5f7f6;

    color: #202722;

    font-family:
        Arial,
        Helvetica,
        sans-serif;
}

.container {

    max-width: 1100px;

    margin: auto;
}

.header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 15px;

    margin-bottom: 20px;
}

.header h1 {

    margin: 0 0 5px;
}

.subtitle {

    margin: 0;

    color: #707a74;
}

.back {

    background: #e9eeeb;

    color: #1d3026;

    text-decoration: none;

    padding: 11px 16px;

    border-radius: 9px;

    font-weight: 700;
}

.message {

    padding: 13px 16px;

    border-radius: 10px;

    margin-bottom: 18px;

    background: #eaf8ee;

    color: #176b37;
}

.message.error {

    background: #fff0f0;

    color: #a42d2d;
}

.card {

    background: #fff;

    border:
        1px solid #e2e8e4;

    border-radius: 16px;

    padding: 20px;

    margin-bottom: 18px;

    box-shadow:
        0 8px 24px
        rgba(22,40,30,.05);
}

.customer-info {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 15px;
}

.info-box {

    background: #f7faf8;

    border-radius: 10px;

    padding: 13px;
}

.info-label {

    color: #78837c;

    font-size: 12px;

    margin-bottom: 5px;
}

.info-value {

    font-weight: 800;
}

.items-title {

    font-size: 20px;

    font-weight: 800;

    margin-bottom: 15px;
}

.price-table {

    width: 100%;

    border-collapse: collapse;
}

.price-table th {

    text-align: left;

    background: #f3f7f4;

    padding: 12px;

    font-size: 13px;

    color: #526158;
}

.price-table td {

    padding: 12px;

    border-bottom:
        1px solid #edf1ee;
}

.price-input {

    width: 130px;

    padding: 10px 11px;

    border:
        1px solid #ccd7d0;

    border-radius: 8px;

    font-size: 14px;
}

.line-total {

    font-weight: 800;

    white-space: nowrap;
}

.total-box {

    display: flex;

    justify-content: flex-end;

    align-items: center;

    gap: 18px;

    margin-top: 18px;

    font-size: 20px;

    font-weight: 800;
}

.total-value {

    color: #1f6b45;
}

.quote-actions {

    display: flex;

    flex-wrap: wrap;

    gap: 10px;

    margin-top: 20px;
}

.save-button,
.whatsapp-button,
.email-button {

    border: 0;

    border-radius: 9px;

    padding: 12px 17px;

    font-weight: 800;

    cursor: pointer;

    text-decoration: none;

    display: inline-block;
}

.save-button {

    background: #253b30;

    color: #fff;
}

.whatsapp-button {

    background: #1f9d68;

    color: #fff;
}

.email-button {

    background: #e9eeeb;

    color: #1d3026;
}

.history-title {

    font-size: 20px;

    font-weight: 800;

    margin-bottom: 14px;
}

.history-item {

    border:
        1px solid #e4ebe7;

    border-radius: 12px;

    padding: 14px;

    margin-bottom: 10px;

    background: #fbfcfb;
}

.history-top {

    display: flex;

    justify-content: space-between;

    gap: 10px;

    align-items: center;
}

.history-order {

    font-weight: 800;
}

.history-date {

    font-size: 12px;

    color: #78837c;

    margin-top: 3px;
}

.history-products {

    margin-top: 10px;

    color: #445149;

    font-size: 14px;

    line-height: 1.6;
}

.history-link {

    display: inline-block;

    margin-top: 10px;

    color: #1f6b45;

    font-weight: 700;

    text-decoration: none;
}

.status {

    background: #eef2ef;

    border-radius: 999px;

    padding: 6px 10px;

    font-size: 12px;

    font-weight: 800;
}

@media(max-width: 700px) {

    .header {

        align-items: flex-start;

        flex-direction: column;
    }

    .customer-info {

        grid-template-columns: 1fr;
    }

    .price-table {

        display: block;

        overflow-x: auto;
    }

    .price-table {

        min-width: 650px;
    }

    .card {

        overflow-x: auto;
    }

    .quote-actions {

        flex-direction: column;
    }

    .save-button,
    .whatsapp-button,
    .email-button {

        text-align: center;

        width: 100%;
    }
}

</style>

</head>

<body>

<div class="container">


<div class="header">

    <div>

        <h1>
            Order #<?= (int)$orderId ?>
        </h1>

        <p class="subtitle">
            Review products, add prices and reply to the customer.
        </p>

    </div>


    <a
        href="orders.php"
        class="back"
    >
        Back to Orders
    </a>

</div>


<?php if ($message !== ""): ?>

<div
    class="message
    <?= $messageType === "error"
        ? "error"
        : "" ?>"
>
    <?= h($message) ?>
</div>

<?php endif; ?>


<!-- CUSTOMER -->

<div class="card">

    <div class="customer-info">

        <div class="info-box">

            <div class="info-label">
                Customer
            </div>

            <div class="info-value">
                <?= h(
                    $order["customer_name"]
                ) ?>
            </div>

        </div>


        <div class="info-box">

            <div class="info-label">
                Phone
            </div>

            <div class="info-value">

                <a
                    href="tel:<?= h($order["customer_phone"]) ?>"
                    style="
                        color:#1f6b45;
                        text-decoration:none;
                    "
                >
                    <?= h(
                        $order["customer_phone"]
                    ) ?>
                </a>

            </div>

        </div>


        <div class="info-box">

            <div class="info-label">
                Order Date
            </div>

            <div class="info-value">
                <?= h(
                    $order["created_at"]
                ) ?>
            </div>

        </div>

    </div>

</div>


<!-- PRICE QUOTE -->

<div class="card">

    <div class="items-title">
        Order Items & Price
    </div>


    <form method="POST">

        <input
            type="hidden"
            name="order_id"
            value="<?= (int)$orderId ?>"
        >


        <table class="price-table">

            <thead>

                <tr>

                    <th>
                        Product
                    </th>

                    <th>
                        Quantity
                    </th>

                    <th>
                        Price / Unit
                    </th>

                    <th>
                        Total
                    </th>

                </tr>

            </thead>


            <tbody>

            <?php foreach (
                $items
                as $index => $item
            ): ?>

            <?php

            $existingPrice = 0;

            if (
                isset(
                    $quotedItems[$index]["price"]
                )
            ) {

                $existingPrice =
                    (float)
                    $quotedItems[$index]["price"];

            } elseif (
                isset($item["price"])
            ) {

                $existingPrice =
                    (float)$item["price"];
            }


            $quantity =
                (float)(
                    $item["quantity"]
                    ?? 0
                );

            $lineTotal =
                $quantity *
                $existingPrice;

            ?>

            <tr>

                <td>
                    <strong>
                        <?= h(
                            $item["product_name"]
                            ?? "Product"
                        ) ?>
                    </strong>
                </td>


                <td>

                    <?= $quantity ?>

                    <?= h(
                        $item["unit"]
                        ?? ""
                    ) ?>

                </td>


                <td>

                    <input
                        type="number"
                        name="price[<?= $index ?>]"
                        class="price-input"
                        value="<?= $existingPrice > 0
                            ? number_format(
                                $existingPrice,
                                2,
                                ".",
                                ""
                            )
                            : "" ?>"
                        min="0"
                        step="0.01"
                        placeholder="AED"
                    >

                </td>


                <td>

                    <span class="line-total">
                        AED
                        <?= number_format(
                            $lineTotal,
                            2
                        ) ?>
                    </span>

                </td>

            </tr>

            <?php endforeach; ?>

            </tbody>

        </table>


        <div class="total-box">

            <span>
                Total:
            </span>

            <span
                class="total-value"
                id="quoteTotal"
            >
                AED
                <?= number_format(
                    (float)$order["quoted_total"],
                    2
                ) ?>
            </span>

        </div>


        <div class="quote-actions">

            <button
                type="submit"
                name="save_quote"
                value="1"
                class="save-button"
            >
                Save Prices
            </button>


           <a
    href="whatsapp-quote.php?id=<?= (int)$orderId ?>"
    target="_blank"
    class="whatsapp-button"
>
    Reply on WhatsApp
</a>


            <a
                href="mailto:<?= h(
                    "Jasirmusthafa1@gmail.com"
                ) ?>?subject=Order%20%23<?= (int)$orderId ?>%20Price%20Confirmation&body=<?= rawurlencode(
                    $whatsappMessage
                ) ?>"
                class="email-button"
            >
                Send by Email
            </a>

        </div>

    </form>

</div>


<!-- CUSTOMER HISTORY -->

<div
    class="card"
    id="customer-history"
>

    <div class="history-title">
        Customer Order History
    </div>


    <?php foreach (
        $history
        as $historyOrder
    ): ?>

    <div class="history-item">

        <div class="history-top">

            <div>

                <div class="history-order">

                    Order #
                    <?= (int)$historyOrder["id"] ?>

                </div>

                <div class="history-date">

                    <?= h(
                        $historyOrder["created_at"]
                    ) ?>

                </div>

            </div>


            <span class="status">

                <?= h(
                    $historyOrder["status"]
                ) ?>

            </span>

        </div>


        <div class="history-products">

            <?php

            $historyItems =
                getItems(
                    $historyOrder["items_json"]
                );

            $historyNames = [];

            foreach (
                $historyItems
                as $historyItem
            ) {

                $historyNames[] =
                    (
                        $historyItem["product_name"]
                        ?? "Product"
                    ) .
                    " — " .
                    (
                        $historyItem["quantity"]
                        ?? 0
                    ) .
                    " " .
                    (
                        $historyItem["unit"]
                        ?? ""
                    );
            }

            echo h(
                implode(
                    " | ",
                    $historyNames
                )
            );

            ?>

        </div>


        <a
            class="history-link"
            href="order-details.php?id=<?= (int)$historyOrder["id"] ?>"
        >
            View Order Details
        </a>

    </div>

    <?php endforeach; ?>

</div>


</div>

</body>

</html>