<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";


/* =========================================================
   GET PRODUCT ID
========================================================= */

$id = isset($_GET["id"])
    ? (int)$_GET["id"]
    : 0;


if ($id <= 0) {

    header("Location: products.php");

    exit;
}


/* =========================================================
   GET IMAGE
========================================================= */

$stmt = $conn->prepare("
    SELECT image
    FROM products
    WHERE id = ?
    LIMIT 1
");

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$result = $stmt->get_result();

$product = $result->fetch_assoc();

$stmt->close();


if (!$product) {

    header("Location: products.php");

    exit;
}


/* =========================================================
   DELETE PRODUCT
========================================================= */

$stmt = $conn->prepare("
    DELETE FROM products
    WHERE id = ?
");

$stmt->bind_param(
    "i",
    $id
);

$stmt->execute();

$stmt->close();


/* =========================================================
   DELETE IMAGE FILE
========================================================= */

$imagePath = $product["image"] ?? "";


if (
    $imagePath !== "" &&
    strpos($imagePath, "uploads/products/") === 0
) {

    $fullPath =
        dirname(__DIR__) .
        "/" .
        $imagePath;


    if (is_file($fullPath)) {

        unlink($fullPath);

    }
}


header("Location: products.php");

exit;

?>