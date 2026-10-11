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

   HELPERS

========================================================= */



function h($value)

{

    return htmlspecialchars((string)$value, ENT_QUOTES, "UTF-8");

}



function orderItems($json)

{

    $items = json_decode((string)$json, true);

    return is_array($items) ? $items : [];

}



function normalisePhone($phone)

{

    return preg_replace("/[^0-9]/", "", (string)$phone);

}



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

        "Pending",

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

        $stmt = $conn->prepare(

            "UPDATE orders SET status = ? WHERE id = ?"

        );



        if (!$stmt) {

            $message = "Database error. Could not prepare the update.";

            $messageType = "error";

        } else {

            $stmt->bind_param("si", $status, $orderId);



            if ($stmt->execute() && $stmt->affected_rows >= 0) {

                $message = "Order #" . $orderId .

                    " status updated to " . $status . ".";

            } else {

                $message = "Could not update the order status.";

                $messageType = "error";

            }



            $stmt->close();

        }

    }

}



/* =========================================================

   GET ORDERS — LATEST FIRST

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

    $phoneKey = normalisePhone($order["customer_phone"] ?? "");



    if ($phoneKey === "") {

        $phoneKey = "unknown_" . $order["id"];

    }



    if (!isset($customers[$phoneKey])) {

        $customers[$phoneKey] = [

            "name" => $order["customer_name"] ?? "Customer",

            "phone" => $order["customer_phone"] ?? "",

            "orders" => []

        ];

    }



    $customers[$phoneKey]["orders"][] = $order;

}



/* =========================================================

   CUSTOMER SEARCH

   Search customer name, phone number or any order ID.

========================================================= */



$search = trim($_GET["search"] ?? "");

$filteredCustomers = [];



foreach ($customers as $key => $customer) {
    $matches = ($search === "");

    if (!$matches) {
        $searchLower = function_exists("mb_strtolower")
            ? mb_strtolower($search, "UTF-8")
            : strtolower($search);

        $name = function_exists("mb_strtolower")
            ? mb_strtolower((string)$customer["name"], "UTF-8")
            : strtolower((string)$customer["name"]);

        $phone = (string)$customer["phone"];

        // change the condition to check if the search term is in the name or phone number
        if (
            strpos($name, $searchLower) !== false ||
            strpos($phone, $search) !== false
        ) {
            $matches = true;
        }

        if (!$matches && ctype_digit($search)) {
            foreach ($customer["orders"] as $order) {
                if ((string)$order["id"] === $search) {
                    $matches = true;
                    break;
                }
            }
        }
    }

    if ($matches) {
        $filteredCustomers[$key] = $customer;
    }
}




/* =========================================================

   SUMMARY COUNTS

========================================================= */



$totalOrders = count($orders);

$totalCustomers = count($customers);

$pendingOrders = 0;



foreach ($orders as $order) {

    $currentStatus = $order["status"] ?? "Pending";



    // Count legacy New orders together with Pending orders.

    if ($currentStatus === "Pending" || $currentStatus === "New") {

        $pendingOrders++;

    }

}



?>

<!DOCTYPE html>

<html lang="en">

<head>

<meta charset="UTF-8">

<meta name="viewport" content="width=device-width, initial-scale=1.0">



<title>Orders | Jasir Fresh</title>



<style>

* {

    box-sizing: border-box;

}



:root {

    --green: #1f6b45;

    --dark-green: #253b30;

    --page: #f5f7f6;

    --border: #e2e8e4;

    --muted: #707a74;

}



body {

    margin: 0;

    padding: 24px 18px;

    background: var(--page);

    color: #202722;

    font-family: Arial, Helvetica, sans-serif;

}



.container {

    width: 100%;

    max-width: 1250px;

    margin: 0 auto;

}



.header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 16px;

    margin-bottom: 20px;

}



.header h1 {

    margin: 0 0 6px;

    font-size: 28px;

}



.subtitle {

    margin: 0;

    color: var(--muted);

    line-height: 1.5;

}



.back {

    flex-shrink: 0;

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

    grid-template-columns: repeat(3, minmax(0, 1fr));

    gap: 14px;

    margin-bottom: 24px;

}



.summary-card {

    min-width: 0;

    background: #fff;

    border: 1px solid var(--border);

    border-radius: 16px;

    box-shadow: 0 8px 24px rgba(22, 40, 30, .05);

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

    font-size: 21px;

}



/* SEARCH */



.search-panel {

    display: flex;

    align-items: center;

    gap: 10px;

    width: 100%;

    margin-bottom: 18px;

    padding: 14px;

    background: #fff;

    border: 1px solid var(--border);

    border-radius: 14px;

}



.search-input {

    flex: 1;

    min-width: 0;

    padding: 12px 13px;

    border: 1px solid #ccd7d0;

    border-radius: 9px;

    outline: none;

    font-size: 15px;

}



.search-input:focus {

    border-color: var(--green);

    box-shadow: 0 0 0 3px rgba(31, 107, 69, .1);

}



.search-button,

.clear-button {

    flex-shrink: 0;

    padding: 12px 15px;

    border: 0;

    border-radius: 9px;

    font-weight: 700;

    cursor: pointer;

    text-decoration: none;

    font-size: 14px;

}



.search-button {

    background: var(--green);

    color: #fff;

}



.clear-button {

    background: #edf1ee;

    color: #26382e;

}



.search-result-count {

    margin: 0 0 14px;

    color: var(--muted);

    font-size: 13px;

}



/* CUSTOMER CARD */



.customer-card {

    min-width: 0;

    background: #fff;

    border: 1px solid var(--border);

    border-radius: 16px;

    box-shadow: 0 8px 24px rgba(22, 40, 30, .05);

    padding: 20px;

    margin-bottom: 16px;

}



.customer-head {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 14px;

    border-bottom: 1px solid #edf1ee;

    padding-bottom: 15px;

    margin-bottom: 15px;

}



.customer-name {

    font-size: 20px;

    font-weight: 800;

    overflow-wrap: anywhere;

}



.customer-phone {

    display: inline-block;

    color: var(--green);

    text-decoration: none;

    margin-top: 5px;

    overflow-wrap: anywhere;

}



.frequency {

    flex-shrink: 0;

    background: #edf8f1;

    color: var(--green);

    border-radius: 999px;

    padding: 8px 12px;

    font-weight: 800;

    white-space: nowrap;

}



/* LATEST ORDER */



.latest-order {

    border: 1px solid #e4ebe7;

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

    overflow-wrap: anywhere;

}



.date {

    color: #7b857f;

    font-size: 13px;

    margin-top: 4px;

}



.status-badge {

    flex-shrink: 0;

    padding: 7px 10px;

    border-radius: 999px;

    background: #eef2ef;

    font-size: 12px;

    font-weight: 800;

    white-space: nowrap;

}



.status-pending,

.status-new {

    background: #fff3d9;

    color: #805900;

}



.status-confirmed {

    background: #e8f1ff;

    color: #2357a6;

}



.status-completed {

    background: #e5f7eb;

    color: #176b37;

}



.status-cancelled {

    background: #ffeded;

    color: #a42d2d;

}



.items-title {

    font-weight: 800;

    margin-bottom: 8px;

}



.item {

    display: flex;

    justify-content: space-between;

    align-items: flex-start;

    gap: 12px;

    padding: 8px 0;

    border-bottom: 1px solid #f0f2f1;

    font-size: 14px;

}



.item span {

    min-width: 0;

    overflow-wrap: anywhere;

}



.item strong {

    flex-shrink: 0;

    text-align: right;

}



.item:last-child {

    border-bottom: 0;

}



.no-items {

    color: var(--muted);

    font-size: 13px;

    padding: 8px 0;

}



/* HORIZONTAL ACTIONS */



.actions {

    display: flex;

    flex-wrap: wrap;

    align-items: center;

    gap: 8px;

    margin-top: 14px;

}



.details {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    flex-shrink: 0;

    text-decoration: none;

    background: var(--green);

    color: #fff;

    padding: 10px 14px;

    border-radius: 8px;

    font-weight: 700;

    font-size: 13px;

    white-space: nowrap;

}



.status-form {

    display: flex;

    align-items: center;

    gap: 8px;

    flex: 1 1 310px;

    min-width: 260px;

}



.status-form select {

    flex: 1;

    min-width: 0;

    border: 1px solid #ccd7d0;

    border-radius: 8px;

    padding: 10px;

    background: #fff;

    color: #202722;

}



.status-form button {

    flex-shrink: 0;

    border: 0;

    background: var(--dark-green);

    color: #fff;

    border-radius: 8px;

    padding: 10px 13px;

    font-weight: 700;

    cursor: pointer;

}



.history-button {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    margin-top: 12px;

    padding: 9px 13px;

    border-radius: 8px;

    background: #edf4f0;

    color: var(--green);

    text-decoration: none;

    font-weight: 700;

    font-size: 13px;

}



.empty {

    background: #fff;

    border: 1px dashed #ccd7d0;

    padding: 30px;

    border-radius: 14px;

    text-align: center;

    color: #718078;

}



/* MOBILE: KEEP SUMMARY AND ACTIONS SIDE-BY-SIDE */



@media (max-width: 800px) {

    body {

        padding: 16px 10px;

    }



    .header {

        align-items: center;

        gap: 10px;

    }



    .header h1 {

        font-size: 23px;

    }



    .subtitle {

        font-size: 13px;

    }



    .back {

        padding: 10px;

        font-size: 12px;

    }



    .summary {

        grid-template-columns: repeat(3, minmax(0, 1fr));

        gap: 8px;

    }



    .summary-card {

        padding: 12px 9px;

        border-radius: 12px;

    }



    .summary-card span {

        font-size: 11px;

        line-height: 1.35;

    }



    .summary-card strong {

        font-size: 22px;

    }



    .customer-card {

        padding: 12px;

        border-radius: 13px;

    }



    .customer-head {

        flex-direction: row;

        align-items: center;

        gap: 8px;

    }



    .customer-name {

        font-size: 17px;

    }



    .customer-phone {

        font-size: 13px;

    }



    .frequency {

        font-size: 11px;

        padding: 7px 9px;

    }



    .latest-order {

        padding: 12px;

    }



    .order-id {

        font-size: 14px;

    }



    .date {

        font-size: 11px;

    }



    .status-badge {

        font-size: 10px;

        padding: 6px 8px;

    }



    .actions {

        flex-wrap: nowrap;

        align-items: stretch;

        overflow-x: auto;

        padding-bottom: 5px;

    }



    .details {

        padding: 10px;

        font-size: 12px;

    }



    .status-form {

        flex: 0 0 265px;

        min-width: 265px;

    }



    .status-form select,

    .status-form button {

        font-size: 12px;

    }



    .history-button {

        font-size: 12px;

    }



    .search-panel {

        gap: 7px;

        padding: 10px;

    }



    .search-input {

        padding: 11px 9px;

        font-size: 13px;

    }



    .search-button,

    .clear-button {

        padding: 11px 9px;

        font-size: 12px;

    }

      .search-panel {
        display: flex !important;
        flex-direction: row !important; /* Forces items to stay side-by-side */
        align-items: center !important;
        gap: 6px !important;
        width: 100% !important;
        position: relative !important; /* Keeps the datalist locked perfectly underneath the input */
    }

      .search-input {
        flex-grow: 1 !important;
        width: 100% !important;
        padding: 10px !important;
        box-sizing: border-box !important;
        font-size: 15px !important;
    }

     .search-button, .clear-button {
        width: auto !important; /* Prevents stretching to full width */
        padding: 10px 14px !important;
        font-size: 14px !important;
        white-space: nowrap !important; /* Prevents text inside button from breaking into two lines */
    }

}



@media (max-width: 380px) {

    .header h1 {

        font-size: 21px;

    }



    .back {

        font-size: 11px;

        padding: 9px 7px;

    }



    .summary-card strong {

        font-size: 19px;

    }



    .customer-name {

        font-size: 15px;

    }



    .frequency {

        font-size: 10px;

        padding: 6px 7px;

    }



    .search-button,

    .clear-button {

        padding: 10px 7px;

        font-size: 11px;

    }

      .search-panel {
        display: flex !important;
        flex-direction: row !important; /* Forces items to stay side-by-side */
        align-items: center !important;
        gap: 6px !important;
        width: 100% !important;
        position: relative !important; /* Keeps the datalist locked perfectly underneath the input */
    }

      .search-input {
        flex-grow: 1 !important;
        width: 100% !important;
        padding: 10px !important;
        box-sizing: border-box !important;
        font-size: 15px !important;
    }

     .search-button, .clear-button {
        width: auto !important; /* Prevents stretching to full width */
        padding: 10px 14px !important;
        font-size: 14px !important;
        white-space: nowrap !important; /* Prevents text inside button from breaking into two lines */
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



        <a class="back" href="index.php">Back to Admin</a>

    </div>



    <?php if ($message !== ""): ?>

        <div class="message <?= $messageType === "error" ? "error" : "" ?>">

            <?= h($message) ?>

        </div>

    <?php endif; ?>



    <div class="summary">

        <div class="summary-card">

            <span>Total Orders</span>

            <strong><?= $totalOrders ?></strong>

        </div>



        <div class="summary-card">

            <span>Total Customers</span>

            <strong><?= $totalCustomers ?></strong>

        </div>



        <div class="summary-card">

            <span>Pending Orders</span>

            <strong><?= $pendingOrders ?></strong>

        </div>

    </div>



    <h2 class="section-title">Customer Order History</h2>


<form class="search-panel" method="GET" action="orders.php">
    <!-- 1. Added list="customer-list" and autocomplete="off" -->
    <input
        id="search-input"
        class="search-input"
        type="search"
        name="search"
        list="customer-list"
        value="<?= htmlspecialchars($search) ?>"
        placeholder="Search customer, phone or order ID..."
        aria-label="Search customer, phone or order ID"
        autocomplete="off"
    >

    <button class="search-button" type="submit">Search</button>

    <?php if ($search !== ""): ?>
        <a class="clear-button" href="orders.php">Clear</a>
    <?php endif; ?>

    <!-- 2. This HTML Datalist will loop through your existing $customers array and create auto-suggestions -->
    <datalist id="customer-list">
        <?php 
        if (isset($customers) && is_array($customers)) {
            foreach ($customers as $customer) {
                if (isset($customer['name'])) {
                    echo '<option value="' . htmlspecialchars($customer['name']) . '">';
                }
            }
        }
        ?>
    </datalist>
</form>



    <?php if ($search !== ""): ?>

        <p class="search-result-count">

            Showing <?= count($filteredCustomers) ?>

            customer<?= count($filteredCustomers) === 1 ? "" : "s" ?>

            matching "<?= h($search) ?>"

        </p>

    <?php endif; ?>



    <?php if (!$filteredCustomers): ?>

        <div class="empty">

            <?php if ($search !== ""): ?>

                No customers or orders matched your search.

                <br><br>

                Try another customer name, phone number or order ID.

            <?php else: ?>

                No orders yet.

            <?php endif; ?>

        </div>

    <?php endif; ?>



    <?php foreach ($filteredCustomers as $customer): ?>

        <?php

        // Orders were fetched by descending ID, so the first is the latest.

        $latestOrder = $customer["orders"][0];



        $latestStatus = $latestOrder["status"] ?? "Pending";



        $statusClass = "status-" . strtolower(

            preg_replace("/[^a-zA-Z0-9]+/", "-", $latestStatus)

        );



        $latestItems = orderItems($latestOrder["items_json"] ?? "");

        ?>



        <div class="customer-card">



            <div class="customer-head">

                <div>

                    <div class="customer-name">

                        <?= h($customer["name"]) ?>

                    </div>



                    <?php if (trim((string)$customer["phone"]) !== ""): ?>

                        <a

                            class="customer-phone"

                            href="tel:<?= h($customer["phone"]) ?>"

                        >

                            <?= h($customer["phone"]) ?>

                        </a>

                    <?php else: ?>

                        <span class="customer-phone">No phone number</span>

                    <?php endif; ?>

                </div>



                <div class="frequency">

                    <?= count($customer["orders"]) ?>

                    order<?= count($customer["orders"]) === 1 ? "" : "s" ?>

                </div>

            </div>



            <div class="latest-order">



                <div class="order-top">

                    <div>

                        <div class="order-id">

                            Latest Order #<?= (int)$latestOrder["id"] ?>

                        </div>



                        <div class="date">

                            <?= h($latestOrder["created_at"] ?? "") ?>

                        </div>

                    </div>



                    <span class="status-badge <?= h($statusClass) ?>">

                        <?= h($latestStatus) ?>

                    </span>

                </div>



                <div>

                    <div class="items-title">Products</div>



                    <?php if (!$latestItems): ?>

                        <div class="no-items">

                            No product details available for this order.

                        </div>

                    <?php else: ?>

                        <?php foreach ($latestItems as $item): ?>

                            <div class="item">

                                <span>

                                    <?= h($item["product_name"] ?? "Product") ?>

                                </span>



                                <strong>

                                    <?= h($item["quantity"] ?? 0) ?>

                                    <?= h($item["unit"] ?? "") ?>

                                </strong>

                            </div>

                        <?php endforeach; ?>

                    <?php endif; ?>

                </div>



                <div class="actions">



                    <a

                        class="details"

                        href="order-details.php?id=<?= (int)$latestOrder["id"] ?>"

                    >

                        View Latest Order

                    </a>



                    <form method="POST" class="status-form">

                        <input

                            type="hidden"

                            name="order_id"

                            value="<?= (int)$latestOrder["id"] ?>"

                        >



                        <select name="status" aria-label="Order status">

                            <?php

                            $statuses = [

                                "Pending",

                                "New",

                                "Confirmed",

                                "Completed",

                                "Cancelled"

                            ];

                            ?>



                            <?php foreach ($statuses as $statusOption): ?>

                                <option

                                    value="<?= h($statusOption) ?>"

                                    <?= $latestStatus === $statusOption ? "selected" : "" ?>

                                >

                                    <?= h($statusOption) ?>

                                </option>

                            <?php endforeach; ?>

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
<script>
// JavaScript code for live search suggestions
document.getElementById('search-input').addEventListener('input', function() {
    let query = this.value.trim();
    let suggestionsBox = document.getElementById('suggestions-box');

    if (query.length > 0) {
        suggestionsBox.innerHTML = '<div style="padding: 10px; color: #6b7280; font-size: 14px;">Loading...</div>';
        suggestionsBox.style.display = 'block';

        // Fetch matched names from the suggestions file
        fetch('search-suggestions.php?q=' + encodeURIComponent(query))
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                suggestionsBox.innerHTML = '';
                if (data && data.length > 0) {
                    data.forEach(item => {
                        let div = document.createElement('div');
                        div.innerHTML = item;
                        div.style.padding = '10px 15px';
                        div.style.cursor = 'pointer';
                        div.style.color = '#1f2937';
                        div.style.fontSize = '14px';
                        div.style.borderBottom = '1px solid #f3f4f6';
                        div.style.textAlign = 'left';
                        
                        // Add hover effect using JavaScript
                        div.addEventListener('mouseenter', function() {
                            this.style.background = '#f3f4f6';
                        });
                        div.addEventListener('mouseleave', function() {
                            this.style.background = '#ffffff';
                        });

                        // Handle suggestion item click
                        div.addEventListener('click', function() {
                            document.getElementById('search-input').value = this.innerText;
                            suggestionsBox.style.display = 'none';
                            document.getElementById('search-input').closest('form').submit();
                        });
                        
                        suggestionsBox.appendChild(div);
                    });
                } else {
                    suggestionsBox.innerHTML = '<div style="padding: 10px; color: #9ca3af; font-size: 14px;">No customers found</div>';
                }
            })
            .catch(error => {
                console.error('Error fetching suggestions:', error);
                suggestionsBox.style.display = 'none';
            });
    } else {
        suggestionsBox.style.display = 'none';
    }
});

// Hide dropdown list when clicking outside the input box
document.addEventListener('click', function(e) {
    if (e.target.id !== 'search-input') {
        document.getElementById('suggestions-box').style.display = 'none';
    }
});
</script>


</html>
