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
   GET CURRENT PRODUCT STATUS
========================================================= */

$stmt = $conn->prepare("
    SELECT
        status,
        stock
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
   TOGGLE STATUS
========================================================= */

$currentStatus = (int)$product["status"];

$stock = (int)$product["stock"];


/*
   Stock 0 என்றால் activate செய்யக்கூடாது.
*/

if ($currentStatus === 1) {

    $newStatus = 0;

} else {

    if ($stock <= 0) {

        header("Location: products.php");
        exit;

    }

    $newStatus = 1;
}


/* =========================================================
   UPDATE
========================================================= */

$stmt = $conn->prepare("
    UPDATE products
    SET status = ?
    WHERE id = ?
");

$stmt->bind_param(
    "ii",
    $newStatus,
    $id
);

$stmt->execute();

$stmt->close();


header("Location: products.php");

exit;

?>