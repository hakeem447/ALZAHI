<?php
session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";

$message = "";
$messageType = "success";

/* =========================================================
   UPDATE ORDER STATUS
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["update_status"])
) {
    $orderId = (int)($_POST["order_id"] ?? 0);
    $status = trim($_POST["status"] ?? "");

    $allowedStatuses = [
        "New",
        "Confirmed",
        "Completed",
        "Cancelled"
    ];

    if ($orderId <= 0) {

        $message = "Invalid order ID.";
        $messageType = "error";

    } elseif (!in_array($status, $allowedStatuses, true)) {

        $message = "Invalid status.";
        $messageType = "error";

    } else {

        $stmt = $conn->prepare("
            UPDATE orders
            SET status = ?
            WHERE id = ?
        ");

        if (!$stmt) {

            $message = "Database error: " . $conn->error;
            $messageType = "error";

        } else {

            $stmt->bind_param(
                "si",
                $status,
                $orderId
            );

            if ($stmt->execute()) {

                $message =
                    "Order #" .
                    $orderId .
                    " status updated to " .
                    $status .
                    ".";

            } else {

                $message =
                    "Could not update order: " .
                    $stmt->error;

                $messageType = "error";
            }

            $stmt->close();
        }
    }
}


/* =========================================================
   GET ORDERS
========================================================= */

$orders = [];

$result = $conn->query("
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
    ORDER BY id DESC
");

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $orders[] = $row;
    }
}


/* =========================================================
   GROUP ORDERS BY CUSTOMER PHONE
========================================================= */

$customers = [];

foreach ($orders as $order) {

    $phoneKey = preg_replace(
        "/[^0-9]/",
        "",
        (string)$order["customer_phone"]
    );

    if ($phoneKey === "") {

        $phoneKey =
            "unknown_" .
            $order["id"];
    }

    if (!isset($customers[$phoneKey])) {

        $customers[$phoneKey] = [
            "name" =>
                $order["customer_name"],

            "phone" =>
                $order["customer_phone"],

            "orders" => []
        ];
    }

    $customers[$phoneKey]["orders"][] =
        $order;
}


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


function orderItems($json)
{
    $items = json_decode(
        $json,
        true
    );

    return is_array($items)
        ? $items
        : [];
}

?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta
    name="viewport"
    content="width=device-width, initial-scale=1.0"
>

<title>Orders | Jasir Fresh</title>

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

    max-width: 1250px;

    margin: auto;
}

.header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 16px;

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

.summary {

    display: grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap: 14px;

    margin-bottom: 24px;
}

.summary-card {

    background: #fff;

    border: 1px solid #e2e8e4;

    border-radius: 16px;

    box-shadow:
        0 8px 24px
        rgba(22,40,30,.05);

    padding: 18px;
}

.summary-card span {

    display: block;

    color: #748078;

    font-size: 13px;

    margin-bottom: 6px;
}

.summary-card strong {

    font-size: 27px;
}

.section-title {

    margin: 28px 0 12px;
}

.customer-card {

    background: #fff;

    border: 1px solid #e2e8e4;

    border-radius: 16px;

    box-shadow:
        0 8px 24px
        rgba(22,40,30,.05);

    padding: 20px;

    margin-bottom: 16px;
}

.customer-head {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 14px;

    border-bottom:
        1px solid #edf1ee;

    padding-bottom: 15px;

    margin-bottom: 15px;
}

.customer-name {

    font-size: 20px;

    font-weight: 800;
}

.customer-phone {

    display: block;

    color: #1f6b45;

    text-decoration: none;

    margin-top: 5px;
}

.frequency {

    background: #edf8f1;

    color: #1f6b45;

    border-radius: 999px;

    padding: 8px 12px;

    font-weight: 800;

    white-space: nowrap;
}


/* ONLY LATEST ORDER ON MAIN PAGE */

.latest-order {

    border:
        1px solid #e4ebe7;

    border-radius: 14px;

    padding: 16px;

    background: #fbfcfb;
}

.order-top {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 10px;

    margin-bottom: 12px;
}

.order-id {

    font-weight: 800;
}

.date {

    color: #7b857f;

    font-size: 13px;

    margin-top: 3px;
}

.status-badge {

    padding: 6px 10px;

    border-radius: 999px;

    background: #eef2ef;

    font-size: 12px;

    font-weight: 800;
}

.items-title {

    font-weight: 800;

    margin-bottom: 8px;
}

.item {

    display: flex;

    justify-content: space-between;

    gap: 10px;

    padding: 7px 0;

    border-bottom:
        1px solid #f0f2f1;

    font-size: 14px;
}

.item:last-child {

    border-bottom: 0;
}

.actions {

    display: flex;

    flex-wrap: wrap;

    gap: 8px;

    margin-top: 14px;
}

.details {

    display: inline-block;

    text-decoration: none;

    background: #1f6b45;

    color: #fff;

    padding: 10px 14px;

    border-radius: 8px;

    font-weight: 700;

    font-size: 13px;
}

.status-form {

    display: flex;

    gap: 8px;

    flex: 1;

    min-width: 280px;
}

.status-form select {

    flex: 1;

    min-width: 0;

    border:
        1px solid #ccd7d0;

    border-radius: 8px;

    padding: 9px;

    background: #fff;
}

.status-form button {

    border: 0;

    background: #253b30;

    color: #fff;

    border-radius: 8px;

    padding: 9px 12px;

    font-weight: 700;

    cursor: pointer;
}

.history-button {

    display: inline-block;

    margin-top: 12px;

    padding: 9px 13px;

    border-radius: 8px;

    background: #edf4f0;

    color: #1f6b45;

    text-decoration: none;

    font-weight: 700;

    font-size: 13px;
}

.empty {

    background: #fff;

    border:
        1px dashed #ccd7d0;

    padding: 30px;

    border-radius: 14px;

    text-align: center;

    color: #718078;
}

@media (max-width: 800px) {

    .summary {

        grid-template-columns: 1fr;
    }

    .header,
    .customer-head {

        align-items: flex-start;

        flex-direction: column;
    }

    .status-form {

        min-width: 100%;
    }
}

</style>

</head>

<body>

<div class="container">


<div class="header">

    <div>

        <h1>Orders</h1>

        <p class="subtitle">
            Customer-wise order history and order status management.
        </p>

    </div>

    <a
        class="back"
        href="index.php"
    >
        Back to Admin
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


<div class="summary">

    <div class="summary-card">

        <span>
            Total Orders
        </span>

        <strong>
            <?= count($orders) ?>
        </strong>

    </div>


    <div class="summary-card">

        <span>
            Total Customers
        </span>

        <strong>
            <?= count($customers) ?>
        </strong>

    </div>


    <div class="summary-card">

        <span>
            New Orders
        </span>

        <strong>
            <?= count(
                array_filter(
                    $orders,
                    fn($o) =>
                        ($o["status"] ?? "New")
                        === "New"
                )
            ) ?>
        </strong>

    </div>

</div>


<h2 class="section-title">
    Customer Order History
</h2>


<?php if (!$customers): ?>

<div class="empty">
    No orders yet.
</div>

<?php endif; ?>


<?php foreach ($customers as $customer): ?>

<?php

$latestOrder =
    $customer["orders"][0];

?>

<div class="customer-card">


    <div class="customer-head">

        <div>

            <div class="customer-name">
                <?= h($customer["name"]) ?>
            </div>

            <a
                class="customer-phone"
                href="tel:<?= h($customer["phone"]) ?>"
            >
                <?= h($customer["phone"]) ?>
            </a>

        </div>


        <div class="frequency">

            <?= count($customer["orders"]) ?>

            order<?= count($customer["orders"]) === 1
                ? ""
                : "s" ?>

        </div>

    </div>


    <!-- LATEST ORDER ONLY -->

    <div class="latest-order">

        <div class="order-top">

            <div>

                <div class="order-id">
                    Latest Order
                    #<?= (int)$latestOrder["id"] ?>
                </div>

                <div class="date">
                    <?= h(
                        $latestOrder["created_at"]
                    ) ?>
                </div>

            </div>


            <span class="status-badge">

                <?= h(
                    $latestOrder["status"]
                ) ?>

            </span>

        </div>


        <div>

            <div class="items-title">
                Products
            </div>


            <?php
            foreach (
                orderItems(
                    $latestOrder["items_json"]
                )
                as $item
            ):
            ?>

            <div class="item">

                <span>
                    <?= h(
                        $item["product_name"]
                        ?? "Product"
                    ) ?>
                </span>

                <strong>

                    <?= (int)(
                        $item["quantity"]
                        ?? 0
                    ) ?>

                    <?= h(
                        $item["unit"]
                        ?? ""
                    ) ?>

                </strong>

            </div>

            <?php endforeach; ?>

        </div>


        <div class="actions">

            <a
                class="details"
                href="order-details.php?id=<?= (int)$latestOrder["id"] ?>"
            >
                View Latest Order
            </a>


            <form
                method="POST"
                class="status-form"
            >

                <input
                    type="hidden"
                    name="order_id"
                    value="<?= (int)$latestOrder["id"] ?>"
                >


                <select name="status">

                    <option
                        value="New"
                        <?= $latestOrder["status"] === "New"
                            ? "selected"
                            : "" ?>
                    >
                        New
                    </option>

                    <option
                        value="Confirmed"
                        <?= $latestOrder["status"] === "Confirmed"
                            ? "selected"
                            : "" ?>
                    >
                        Confirmed
                    </option>

                    <option
                        value="Completed"
                        <?= $latestOrder["status"] === "Completed"
                            ? "selected"
                            : "" ?>
                    >
                        Completed
                    </option>

                    <option
                        value="Cancelled"
                        <?= $latestOrder["status"] === "Cancelled"
                            ? "selected"
                            : "" ?>
                    >
                        Cancelled
                    </option>

                </select>


                <button
                    type="submit"
                    name="update_status"
                    value="1"
                >
                    Update
                </button>

            </form>

        </div>


        <a
            class="history-button"
            href="order-details.php?id=<?= (int)$latestOrder["id"] ?>#customer-history"
        >
            View Customer Order History
        </a>

    </div>

</div>

<?php endforeach; ?>


</div>

</body>

</html>