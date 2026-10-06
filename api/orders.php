<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "../config/database.php";


/*
|--------------------------------------------------------------------------
| HELPER RESPONSE
|--------------------------------------------------------------------------
*/

function sendResponse($success, $message, $extra = [])
{
    echo json_encode(
        array_merge(
            [
                "success" => $success,
                "message" => $message
            ],
            $extra
        ),
        JSON_UNESCAPED_UNICODE
    );

    exit;
}


/*
|--------------------------------------------------------------------------
| ONLY POST REQUEST
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    sendResponse(
        false,
        "Invalid request method."
    );
}


/*
|--------------------------------------------------------------------------
| READ JSON DATA
|--------------------------------------------------------------------------
*/

$rawData = file_get_contents("php://input");

$data = json_decode($rawData, true);

if (!is_array($data)) {

    sendResponse(
        false,
        "Invalid order data."
    );
}


/*
|--------------------------------------------------------------------------
| CUSTOMER DETAILS
|--------------------------------------------------------------------------
*/

$customerName = trim(
    (string)($data["customer_name"] ?? "")
);

$customerPhone = trim(
    (string)($data["customer_phone"] ?? "")
);

$items = $data["items"] ?? [];


/*
|--------------------------------------------------------------------------
| VALIDATE CUSTOMER NAME
|--------------------------------------------------------------------------
*/

if ($customerName === "") {

    sendResponse(
        false,
        "Customer name is required."
    );
}


/*
|--------------------------------------------------------------------------
| VALIDATE CUSTOMER PHONE
|--------------------------------------------------------------------------
*/

if ($customerPhone === "") {

    sendResponse(
        false,
        "Customer phone is required."
    );
}


/*
|--------------------------------------------------------------------------
| VALIDATE CART
|--------------------------------------------------------------------------
*/

if (
    !is_array($items) ||
    count($items) === 0
) {

    sendResponse(
        false,
        "Your cart is empty."
    );
}


/*
|--------------------------------------------------------------------------
| START TRANSACTION
|--------------------------------------------------------------------------
*/

try {

    $conn->begin_transaction();


    /*
    |--------------------------------------------------------------------------
    | PREPARE VALIDATED ITEMS
    |--------------------------------------------------------------------------
    */

    $validatedItems = [];

    $totalItemCount = 0;


    /*
    |--------------------------------------------------------------------------
    | TEMPORARY STOCK TRACKING
    |--------------------------------------------------------------------------
    */

    $requestedStock = [];


    /*
    |--------------------------------------------------------------------------
    | FIRST PASS
    |
    | Validate frontend item structure.
    |--------------------------------------------------------------------------
    */

    foreach ($items as $item) {

        if (!is_array($item)) {

            throw new Exception(
                "Invalid cart item."
            );
        }


        /*
        |----------------------------------------------------------------------
        | PRODUCT ID
        |----------------------------------------------------------------------
        */

        $productId = (int)(
            $item["product_id"] ?? 0
        );


        /*
        |----------------------------------------------------------------------
        | QUANTITY
        |----------------------------------------------------------------------
        */

        $quantity = (int)(
            $item["quantity"] ?? 0
        );


        /*
        |----------------------------------------------------------------------
        | UNIT
        |----------------------------------------------------------------------
        */

        $unit = strtolower(
            trim(
                (string)(
                    $item["unit"] ?? ""
                )
            )
        );


        /*
        |----------------------------------------------------------------------
        | BASIC VALIDATION
        |----------------------------------------------------------------------
        */

        if ($productId <= 0) {

            throw new Exception(
                "Invalid product."
            );
        }


        if ($quantity <= 0) {

            throw new Exception(
                "Invalid quantity."
            );
        }


        /*
        |----------------------------------------------------------------------
        | UNIT MUST BE KG OR BOX
        |----------------------------------------------------------------------
        */

        if (
            $unit !== "kg" &&
            $unit !== "box"
        ) {

            throw new Exception(
                "Invalid product unit."
            );
        }


        /*
        |----------------------------------------------------------------------
        | TRACK PRODUCT STOCK
        |----------------------------------------------------------------------
        */

        if (
            !isset(
                $requestedStock[$productId]
            )
        ) {

            $requestedStock[$productId] = 0;
        }


        $requestedStock[$productId] += $quantity;
    }


    /*
    |--------------------------------------------------------------------------
    | SECOND PASS
    |
    | Check products directly from database.
    |--------------------------------------------------------------------------
    */

    foreach (
        $requestedStock
        as $productId => $requestedQuantity
    ) {

        $stmt = $conn->prepare("
            SELECT
                id,
                name,
                category,
                stock,
                status,
                available_kg,
                available_box,
                box_label

            FROM products

            WHERE id = ?

            FOR UPDATE
        ");


        if (!$stmt) {

            throw new Exception(
                "Unable to check product."
            );
        }


        $stmt->bind_param(
            "i",
            $productId
        );


        if (!$stmt->execute()) {

            $stmt->close();

            throw new Exception(
                "Unable to check product."
            );
        }


        $result = $stmt->get_result();


        if (
            !$result ||
            $result->num_rows === 0
        ) {

            $stmt->close();

            throw new Exception(
                "One of the selected products is no longer available."
            );
        }


        $product = $result->fetch_assoc();

        $stmt->close();


        /*
        |----------------------------------------------------------------------
        | CHECK ACTIVE STATUS
        |----------------------------------------------------------------------
        */

        if (
            (int)$product["status"] !== 1
        ) {

            throw new Exception(
                $product["name"] .
                " is currently unavailable."
            );
        }


        /*
        |----------------------------------------------------------------------
        | CHECK STOCK
        |----------------------------------------------------------------------
        */

        $currentStock =
            (int)$product["stock"];


        if (
            $currentStock <
            $requestedQuantity
        ) {

            throw new Exception(
                $product["name"] .
                " has only " .
                $currentStock .
                " available."
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | THIRD PASS
    |
    | Validate KG / BOX availability.
    |--------------------------------------------------------------------------
    */

    foreach ($items as $item) {

        $productId = (int)(
            $item["product_id"] ?? 0
        );

        $quantity = (int)(
            $item["quantity"] ?? 0
        );

        $unit = strtolower(
            trim(
                (string)(
                    $item["unit"] ?? ""
                )
            )
        );


        /*
        |----------------------------------------------------------------------
        | GET PRODUCT
        |----------------------------------------------------------------------
        */

        $stmt = $conn->prepare("
            SELECT
                id,
                name,
                category,
                stock,
                status,
                available_kg,
                available_box,
                box_label

            FROM products

            WHERE id = ?

            FOR UPDATE
        ");


        if (!$stmt) {

            throw new Exception(
                "Unable to validate product."
            );
        }


        $stmt->bind_param(
            "i",
            $productId
        );


        if (!$stmt->execute()) {

            $stmt->close();

            throw new Exception(
                "Unable to validate product."
            );
        }


        $result = $stmt->get_result();


        if (
            !$result ||
            $result->num_rows === 0
        ) {

            $stmt->close();

            throw new Exception(
                "Product is no longer available."
            );
        }


        $product = $result->fetch_assoc();

        $stmt->close();


        /*
        |----------------------------------------------------------------------
        | CHECK PRODUCT STATUS
        |----------------------------------------------------------------------
        */

        if (
            (int)$product["status"] !== 1
        ) {

            throw new Exception(
                $product["name"] .
                " is currently unavailable."
            );
        }


        /*
        |----------------------------------------------------------------------
        | CHECK KG
        |----------------------------------------------------------------------
        */

        if (
            $unit === "kg" &&
            (int)$product["available_kg"] !== 1
        ) {

            throw new Exception(
                $product["name"] .
                " is not available by KG."
            );
        }


        /*
        |----------------------------------------------------------------------
        | CHECK BOX
        |----------------------------------------------------------------------
        */

        if (
            $unit === "box" &&
            (int)$product["available_box"] !== 1
        ) {

            throw new Exception(
                $product["name"] .
                " is not available by Box."
            );
        }


        /*
        |----------------------------------------------------------------------
        | BOX LABEL
        |----------------------------------------------------------------------
        */

        $boxLabel = "";


        if ($unit === "box") {

            $boxLabel = trim(
                (string)(
                    $product["box_label"] ?? ""
                )
            );


            if ($boxLabel === "") {

                $boxLabel = "Box";
            }
        }


        /*
        |----------------------------------------------------------------------
        | SAVE ONLY DATABASE-TRUSTED PRODUCT DATA
        |----------------------------------------------------------------------
        */

        $validatedItems[] = [

            "product_id" =>
                (int)$product["id"],

            "product_name" =>
                $product["name"],

            "category" =>
                $product["category"],

            "unit" =>
                $unit,

            "box_label" =>
                $boxLabel,

            "quantity" =>
                $quantity
        ];


        $totalItemCount += $quantity;
    }


    /*
    |--------------------------------------------------------------------------
    | REDUCE STOCK
    |--------------------------------------------------------------------------
    */

    foreach (
        $requestedStock
        as $productId => $requestedQuantity
    ) {

        $stmt = $conn->prepare("
            UPDATE products

            SET
                stock = stock - ?,

                status = CASE

                    WHEN stock - ? <= 0
                    THEN 0

                    ELSE status

                END

            WHERE id = ?
        ");


        if (!$stmt) {

            throw new Exception(
                "Unable to update stock."
            );
        }


        $stmt->bind_param(
            "iii",
            $requestedQuantity,
            $requestedQuantity,
            $productId
        );


        if (!$stmt->execute()) {

            $stmt->close();

            throw new Exception(
                "Unable to update product stock."
            );
        }


        $stmt->close();
    }


    /*
    |--------------------------------------------------------------------------
    | CREATE ORDER JSON
    |--------------------------------------------------------------------------
    */

    $itemsJson = json_encode(
        $validatedItems,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );


    if ($itemsJson === false) {

        throw new Exception(
            "Unable to prepare order items."
        );
    }


    /*
    |--------------------------------------------------------------------------
    | ORDER STATUS
    |--------------------------------------------------------------------------
    */

    $status = "New";


    /*
    |--------------------------------------------------------------------------
    | SAVE ORDER
    |--------------------------------------------------------------------------
    */

    $stmt = $conn->prepare("
        INSERT INTO orders
        (
            customer_name,
            customer_phone,
            items_json,
            item_count,
            status
        )

        VALUES
        (?, ?, ?, ?, ?)
    ");


    if (!$stmt) {

        throw new Exception(
            "Unable to save order."
        );
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

        throw new Exception(
            "Unable to save order."
        );
    }


    /*
    |--------------------------------------------------------------------------
    | GET ORDER ID
    |--------------------------------------------------------------------------
    */

    $orderId =
        (int)$stmt->insert_id;


    $stmt->close();


    /*
    |--------------------------------------------------------------------------
    | COMMIT
    |--------------------------------------------------------------------------
    */

    $conn->commit();


    /*
    |--------------------------------------------------------------------------
    | SUCCESS
    |--------------------------------------------------------------------------
    */

    sendResponse(
        true,
        "Order saved successfully.",
        [
            "order_id" => $orderId
        ]
    );


} catch (Throwable $e) {

    /*
    |--------------------------------------------------------------------------
    | ROLLBACK
    |--------------------------------------------------------------------------
    */

    if (
        isset($conn) &&
        $conn->errno === 0
    ) {
        // Transaction state is handled below.
    }

    try {
        $conn->rollback();
    } catch (Throwable $rollbackError) {
        // Ignore rollback failure.
    }


    /*
    |--------------------------------------------------------------------------
    | ERROR
    |--------------------------------------------------------------------------
    */

    sendResponse(
        false,
        $e->getMessage()
    );
}


/*
|--------------------------------------------------------------------------
| CLOSE DATABASE
|--------------------------------------------------------------------------
*/

$conn->close();

?>