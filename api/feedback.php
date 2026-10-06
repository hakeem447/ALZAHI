<?php

header(
    "Content-Type: application/json; charset=UTF-8"
);

require_once "../config/database.php";


/* =========================================================
   ONLY POST
========================================================= */

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    echo json_encode([
        "success" => false,
        "message" => "Invalid request method."
    ]);

    exit;
}


/* =========================================================
   GET JSON
========================================================= */

$rawData = file_get_contents("php://input");

$data = json_decode(
    $rawData,
    true
);


if (!is_array($data)) {

    echo json_encode([
        "success" => false,
        "message" => "Invalid JSON data."
    ]);

    exit;
}


/* =========================================================
   DATA
========================================================= */

$orderId =
    (int)($data["order_id"] ?? 0);

$customerName =
    trim($data["customer_name"] ?? "");

$customerPhone =
    trim($data["customer_phone"] ?? "");

$rating =
    (int)($data["rating"] ?? 0);

$comment =
    trim($data["comment"] ?? "");


/* =========================================================
   VALIDATE RATING
========================================================= */

if (
    $rating < 1 ||
    $rating > 5
) {

    echo json_encode([
        "success" => false,
        "message" => "Please select a rating."
    ]);

    exit;
}


/* =========================================================
   SAVE
========================================================= */

$stmt = $conn->prepare("
    INSERT INTO feedback
    (
        order_id,
        customer_name,
        customer_phone,
        rating,
        comment
    )
    VALUES (?, ?, ?, ?, ?)
");


$stmt->bind_param(
    "issis",
    $orderId,
    $customerName,
    $customerPhone,
    $rating,
    $comment
);


if ($stmt->execute()) {

    echo json_encode([
        "success" => true,
        "message" => "Feedback submitted successfully."
    ]);

} else {

    echo json_encode([
        "success" => false,
        "message" => "Unable to save feedback."
    ]);

}


$stmt->close();

$conn->close();

?>