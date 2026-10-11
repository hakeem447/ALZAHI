
<?php
header("Content-Type: application/json; charset=UTF-8");

require_once __DIR__ . "/../config/database.php";

function sendResponse($success, $message, $extra = [])
{
    echo json_encode(
        array_merge([
            "success" => $success,
            "message" => $message
        ], $extra),
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );
    exit;
}

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    sendResponse(false, "Invalid request method. Please submit an order using the website.");
}

$rawData = file_get_contents("php://input");
$data = json_decode($rawData, true);

if (!is_array($data)) {
    sendResponse(false, "Invalid order data.");
}

$customerName = trim((string)($data["customer_name"] ?? ""));
$customerPhone = trim((string)($data["customer_phone"] ?? ""));
$items = $data["items"] ?? [];

if ($customerName === "") {
    sendResponse(false, "Customer name is required.");
}

if ($customerPhone === "") {
    sendResponse(false, "Customer phone is required.");
}

if (!is_array($items) || count($items) === 0) {
    sendResponse(false, "Your cart is empty.");
}

try {
    $conn->begin_transaction();

    $validatedItems = [];
    $totalItemCount = 0;
    $requestedStock = [];

    // Validate each cart item.
    foreach ($items as $item) {
        if (!is_array($item)) {
            throw new Exception("Invalid cart item.");
        }

        $productId = (int)($item["product_id"] ?? 0);
        $quantity = (int)($item["quantity"] ?? 0);
        $unit = strtolower(trim((string)($item["unit"] ?? "")));

        if ($productId <= 0 || $quantity <= 0) {
            throw new Exception("Please check the product and quantity.");
        }

        if (!in_array($unit, ["kg", "box"], true)) {
            throw new Exception("Invalid product unit.");
        }

        if (!isset($requestedStock[$productId])) {
            $requestedStock[$productId] = 0;
        }

        $requestedStock[$productId] += $quantity;
    }

    // Lock and validate products.
    foreach ($requestedStock as $productId => $requestedQuantity) {
        $stmt = $conn->prepare("
            SELECT
                id, name, category, stock, status,
                available_kg, available_box, box_label
            FROM products
            WHERE id = ?
            FOR UPDATE
        ");

        if (!$stmt) {
            throw new Exception("Unable to check product details.");
        }

        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        $stmt->close();

        if (!$product || (int)$product["status"] !== 1) {
            throw new Exception("A selected product is currently unavailable.");
        }

        if ((int)$product["stock"] < $requestedQuantity) {
            throw new Exception(
                $product["name"] . " does not have enough stock available."
            );
        }
    }

    // Build order details using trusted database product names and settings.
    foreach ($items as $item) {
        $productId = (int)$item["product_id"];
        $quantity = (int)$item["quantity"];
        $unit = strtolower(trim((string)$item["unit"]));

        $stmt = $conn->prepare("
            SELECT
                id, name, category, status,
                available_kg, available_box, box_label
            FROM products
            WHERE id = ?
            FOR UPDATE
        ");

        if (!$stmt) {
            throw new Exception("Unable to validate product details.");
        }

        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $product = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$product || (int)$product["status"] !== 1) {
            throw new Exception("A selected product is no longer available.");
        }

        if ($unit === "kg" && (int)$product["available_kg"] !== 1) {
            throw new Exception($product["name"] . " is not available by KG.");
        }

        if ($unit === "box" && (int)$product["available_box"] !== 1) {
            throw new Exception($product["name"] . " is not available by Box.");
        }

        $boxLabel = $unit === "box"
            ? trim((string)($product["box_label"] ?? "Box"))
            : "";

        if ($boxLabel === "") {
            $boxLabel = "Box";
        }

        $validatedItems[] = [
            "product_id" => (int)$product["id"],
            "product_name" => $product["name"],
            "category" => $product["category"],
            "unit" => $unit,
            "box_label" => $boxLabel,
            "quantity" => $quantity,
            "availability" => "Pending"
        ];

        $totalItemCount += $quantity;
    }

    $itemsJson = json_encode(
        $validatedItems,
        JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
    );

    if ($itemsJson === false) {
        throw new Exception("Unable to prepare order items.");
    }

    // All new orders start as Pending.
    $status = "Pending";

    $stmt = $conn->prepare("
        INSERT INTO orders
            (customer_name, customer_phone, items_json, item_count, status)
        VALUES (?, ?, ?, ?, ?)
    ");

    if (!$stmt) {
        throw new Exception("Unable to prepare the order.");
    }

    $stmt->bind_param(
        "sssis",
        $customerName,
        $customerPhone,
        $itemsJson,
        $totalItemCount,
        $status
    );

    if (!$stmt->execute()) {
        $stmt->close();
        throw new Exception("Unable to save the order.");
    }

    $orderId = (int)$stmt->insert_id;
    $stmt->close();

    $conn->commit();

    sendResponse(true, "Order submitted successfully. Status: Pending.", [
        "order_id" => $orderId,
        "status" => "Pending"
    ]);

} catch (Throwable $e) {
    try {
        $conn->rollback();
    } catch (Throwable $ignored) {
        // No further action required.
    }

    error_log("Jasir Fresh order API: " . $e->getMessage());

    sendResponse(false, $e->getMessage());
}
