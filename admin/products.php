<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";


$sql = "
    SELECT
        id,
        name,
        category,
        subcategory,
        image,
        price,
        unit,
        stock,
        description,
        status,
        available_kg,
        available_box,
        box_label,
        created_at
    FROM products
    ORDER BY id DESC
";


$result =
    $conn->query($sql);


$products = [];


if ($result) {

    while ($row = $result->fetch_assoc()) {

        $products[] = $row;
    }
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

<title>
    Products | Fresh Harvest Admin
</title>


<style>

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    font-family:
        Arial,
        sans-serif;

    background:
        #f4f7f5;

    color:
        #17382c;
}


.topbar {

    background:
        #123b2d;

    color:
        white;

    padding:
        18px 30px;

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;
}


.brand {

    font-size:
        21px;

    font-weight:
        700;
}


.topbar a {

    color:
        white;

    text-decoration:
        none;

    font-weight:
        600;
}


.container {

    max-width:
        1200px;

    margin:
        35px auto;

    padding:
        0 20px;
}


.page-header {

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    gap:
        20px;

    margin-bottom:
        25px;
}


.page-header h1 {

    margin:
        0 0 6px;
}


.add-btn {

    display:
        inline-block;

    padding:
        12px 18px;

    background:
        #1f9d68;

    color:
        white;

    text-decoration:
        none;

    border-radius:
        10px;

    font-weight:
        700;
}


.products-grid {

    display:
        grid;

    grid-template-columns:
        repeat(3, 1fr);

    gap:
        20px;
}


.product-card {

    background:
        white;

    border-radius:
        18px;

    overflow:
        hidden;

    box-shadow:
        0 8px 25px
        rgba(0,0,0,0.07);
}


.product-image {

    width:
        100%;

    height:
        220px;

    object-fit:
        cover;

    display:
        block;

    background:
        #eef2ef;
}


.no-image {

    width:
        100%;

    height:
        220px;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        #eef2ef;

    color:
        #7a8880;
}


.product-content {

    padding:
        18px;
}


.product-content h3 {

    margin:
        0 0 8px;

    font-size:
        20px;
}


.category {

    display:
        inline-block;

    background:
        #e8f5ee;

    color:
        #176b48;

    padding:
        5px 9px;

    border-radius:
        6px;

    font-size:
        12px;

    font-weight:
        700;

    margin-bottom:
        10px;
}


.info {

    margin:
        7px 0;

    color:
        #65736b;

    font-size:
        14px;
}


.units {

    display:
        flex;

    flex-wrap:
        wrap;

    gap:
        7px;

    margin:
        12px 0;
}


.unit-badge {

    padding:
        6px 9px;

    border-radius:
        7px;

    background:
        #f1f5f3;

    font-size:
        12px;

    font-weight:
        700;
}


.actions {

    display:
        flex;

    gap:
        8px;

    margin-top:
        15px;
}


.actions a,
.actions button {

    flex:
        1;

    padding:
        10px;

    border:
        none;

    border-radius:
        8px;

    text-align:
        center;

    text-decoration:
        none;

    font-size:
        13px;

    font-weight:
        700;

    cursor:
        pointer;
}


.edit {

    background:
        #1f6b45;

    color:
        white;
}


.delete {

    background:
        #fff0f0;

    color:
        #c62828;
}


.status {

    margin-top:
        10px;

    font-size:
        13px;

    font-weight:
        700;
}


.active {
    color:
        #18834f;
}


.inactive {
    color:
        #c62828;
}


.empty {

    background:
        white;

    padding:
        50px;

    text-align:
        center;

    border-radius:
        18px;

    color:
        #718078;
}


@media(max-width:900px) {

    .products-grid {

        grid-template-columns:
            repeat(2, 1fr);
    }

}


@media(max-width:600px) {

    .products-grid {

        grid-template-columns:
            1fr;
    }


    .page-header {

        flex-direction:
            column;

        align-items:
            flex-start;
    }


    .topbar {

        padding:
            16px 18px;
    }

}

</style>

</head>


<body>


<header class="topbar">

<div class="brand">
    FRESH HARVEST ADMIN
</div>


<a href="dashboard.php">
    Dashboard
</a>

</header>



<main class="container">


<div class="page-header">

<div>

<h1>
    Products
</h1>

<p>
    Manage products, images, units and stock.
</p>

</div>


<a
    href="add-product.php"
    class="add-btn"
>
    + Add Product
</a>

</div>



<?php if (empty($products)): ?>

<div class="empty">

    No products found.

</div>

<?php else: ?>


<div class="products-grid">


<?php foreach ($products as $product): ?>


<div class="product-card">


<?php if (!empty($product["image"])): ?>

<img
    class="product-image"
    src="../<?= htmlspecialchars($product["image"]) ?>"
    alt="<?= htmlspecialchars($product["name"]) ?>"
    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';"
>


<div
    class="no-image"
    style="display:none;"
>
    Image not found
</div>

<?php else: ?>

<div class="no-image">
    No Image
</div>

<?php endif; ?>



<div class="product-content">


<span class="category">

<?= htmlspecialchars(
    ucfirst($product["category"])
) ?>

</span>


<h3>

<?= htmlspecialchars(
    $product["name"]
) ?>

</h3>


<div class="info">

Sub Category:
<?= htmlspecialchars(
    $product["subcategory"] ?? "-"
) ?>

</div>


<div class="info">

Price:
AED
<?= number_format(
    (float)$product["price"],
    2
) ?>

</div>


<div class="info">

Stock:
<?= (int)$product["stock"] ?>

</div>



<div class="units">


<?php if (
    (int)$product["available_kg"] === 1
): ?>

<span class="unit-badge">
    KG
</span>

<?php endif; ?>


<?php if (
    (int)$product["available_box"] === 1
): ?>

<span class="unit-badge">

Box:
<?= htmlspecialchars(
    $product["box_label"] ?? "Box"
) ?>

</span>

<?php endif; ?>


</div>



<div
    class="status
    <?= ((int)$product["status"] === 1)
        ? "active"
        : "inactive" ?>"
>

<?= ((int)$product["status"] === 1)
    ? "Active"
    : "Inactive" ?>

</div>



<div class="actions">


<a
    class="edit"
    href="edit-product.php?id=<?= (int)$product["id"] ?>"
>
    Edit
</a>


<a
    class="delete"
    href="delete-product.php?id=<?= (int)$product["id"] ?>"
    onclick="return confirm('Delete this product?');"
>
    Delete
</a>


</div>


</div>


</div>


<?php endforeach; ?>


</div>


<?php endif; ?>


</main>


</body>

</html>