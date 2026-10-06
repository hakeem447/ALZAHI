<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";


$id = isset($_GET["id"])
    ? (int)$_GET["id"]
    : 0;


if ($id <= 0) {
    die("Invalid product ID.");
}


$message = "";
$messageType = "";


/* =========================
   GET PRODUCT
========================= */

$stmt = $conn->prepare("
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
        box_label
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
    die("Product not found.");
}


/* =========================
   UPDATE
========================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $name =
        trim($_POST["name"] ?? "");

    $category =
        trim($_POST["category"] ?? "");

    $subcategory =
        trim($_POST["subcategory"] ?? "");

    $price =
        trim($_POST["price"] ?? "");

    $stock =
        trim($_POST["stock"] ?? "");

    $description =
        trim($_POST["description"] ?? "");


    $availableKg =
        isset($_POST["available_kg"])
        ? 1
        : 0;


    $availableBox =
        isset($_POST["available_box"])
        ? 1
        : 0;


    $boxLabel =
        trim($_POST["box_label"] ?? "");


    $status =
        isset($_POST["status"])
        ? 1
        : 0;


    /* =========================
       VALIDATION
    ========================== */

    if ($name === "" || $category === "") {

        $message =
            "Product name and category are required.";

        $messageType =
            "error";

    } elseif (
        $availableKg === 0 &&
        $availableBox === 0
    ) {

        $message =
            "Please select KG or Box.";

        $messageType =
            "error";
    }


    if (
        $availableBox === 1 &&
        $boxLabel === ""
    ) {

        $boxLabel =
            "Box";
    }


    $priceValue =
        ($price === "")
        ? 0
        : (float)$price;


    $stockValue =
        ($stock === "")
        ? 0
        : (int)$stock;


    /*
     * Unit compatibility
     */

    if (
        $availableKg === 1 &&
        $availableBox === 1
    ) {

        $unit = "both";

    } elseif ($availableKg === 1) {

        $unit = "kg";

    } else {

        $unit = "box";
    }


    /*
     * If stock is zero,
     * automatically disable product.
     */

    if ($stockValue <= 0) {
        $status = 0;
    }


    /* =========================
       IMAGE
    ========================== */

    $imagePath =
        $product["image"];


    if (
        $messageType !== "error" &&
        isset($_FILES["image"]) &&
        $_FILES["image"]["error"] === UPLOAD_ERR_OK
    ) {

        $file =
            $_FILES["image"];


        $allowedTypes = [
            "image/jpeg",
            "image/png",
            "image/webp"
        ];


        if (
            !in_array(
                $file["type"],
                $allowedTypes
            )
        ) {

            $message =
                "Only JPG, PNG and WEBP images are allowed.";

            $messageType =
                "error";

        } elseif (
            $file["size"] >
            5 * 1024 * 1024
        ) {

            $message =
                "Image size must be below 5MB.";

            $messageType =
                "error";

        } else {

            $extension =
                strtolower(
                    pathinfo(
                        $file["name"],
                        PATHINFO_EXTENSION
                    )
                );


            $newFileName =
                time() .
                "_" .
                uniqid() .
                "." .
                $extension;


            $uploadDirectory =
                "../uploads/products/";


            if (
                !is_dir(
                    $uploadDirectory
                )
            ) {

                mkdir(
                    $uploadDirectory,
                    0777,
                    true
                );
            }


            $targetFile =
                $uploadDirectory .
                $newFileName;


            if (
                move_uploaded_file(
                    $file["tmp_name"],
                    $targetFile
                )
            ) {

                $imagePath =
                    "uploads/products/" .
                    $newFileName;

            } else {

                $message =
                    "Image upload failed.";

                $messageType =
                    "error";
            }
        }
    }


    /* =========================
       SAVE
    ========================== */

    if ($messageType !== "error") {

        $sql = "
            UPDATE products
            SET
                name = ?,
                category = ?,
                subcategory = ?,
                image = ?,
                price = ?,
                unit = ?,
                stock = ?,
                description = ?,
                status = ?,
                available_kg = ?,
                available_box = ?,
                box_label = ?
            WHERE id = ?
        ";


        $stmt =
            $conn->prepare($sql);


        if (!$stmt) {

            $message =
                "Database error: " .
                $conn->error;

            $messageType =
                "error";

        } else {

            $stmt->bind_param(
                "ssssdsisiiisi",
                $name,
                $category,
                $subcategory,
                $imagePath,
                $priceValue,
                $unit,
                $stockValue,
                $description,
                $status,
                $availableKg,
                $availableBox,
                $boxLabel,
                $id
            );


            if ($stmt->execute()) {

                $message =
                    "Product updated successfully.";

                $messageType =
                    "success";


                /*
                 * Reload updated product
                 */

                $stmt->close();


                $stmt =
                    $conn->prepare("
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
                            box_label
                        FROM products
                        WHERE id = ?
                    ");


                $stmt->bind_param(
                    "i",
                    $id
                );


                $stmt->execute();


                $product =
                    $stmt
                    ->get_result()
                    ->fetch_assoc();

            } else {

                $message =
                    "Database error: " .
                    $stmt->error;

                $messageType =
                    "error";
            }


            $stmt->close();
        }
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
    Edit Product | Fresh Harvest
</title>


<style>

* {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}


body {

    font-family:
        Arial,
        sans-serif;

    background:
        #f5f7f6;

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


.back {

    color:
        white;

    text-decoration:
        none;

    font-weight:
        600;
}


.container {

    max-width:
        900px;

    margin:
        35px auto;

    padding:
        0 20px;
}


.form-box {

    background:
        white;

    padding:
        30px;

    border-radius:
        20px;

    box-shadow:
        0 10px 35px
        rgba(0,0,0,0.07);
}


h1 {

    margin-bottom:
        25px;
}


.form-grid {

    display:
        grid;

    grid-template-columns:
        1fr 1fr;

    gap:
        20px;
}


.form-group {

    display:
        flex;

    flex-direction:
        column;
}


.full {

    grid-column:
        1 / -1;
}


label {

    margin-bottom:
        8px;

    font-weight:
        600;
}


input,
select,
textarea {

    width:
        100%;

    padding:
        13px 14px;

    border:
        1px solid #ddd;

    border-radius:
        10px;

    font-size:
        15px;

    outline:
        none;
}


input:focus,
select:focus,
textarea:focus {

    border-color:
        #1f9d68;
}


textarea {

    min-height:
        110px;

    resize:
        vertical;
}


/* UNIT */

.unit-options {

    display:
        grid;

    grid-template-columns:
        1fr 1fr;

    gap:
        12px;

    margin-bottom:
        15px;
}


.unit-option {

    display:
        flex;

    align-items:
        center;

    gap:
        10px;

    padding:
        15px;

    border:
        1px solid #dce5df;

    border-radius:
        12px;

    background:
        #fafcfb;

    cursor:
        pointer;

    font-weight:
        700;
}


.unit-option input {

    width:
        18px;

    height:
        18px;

    accent-color:
        #1f9d68;
}


.box-name-field {

    margin-bottom:
        18px;
}


.box-name-field input {

    margin-top:
        2px;
}


/* CURRENT IMAGE */

.current-image {

    width:
        130px;

    height:
        100px;

    object-fit:
        cover;

    border-radius:
        12px;

    border:
        1px solid #ddd;

    margin-bottom:
        12px;
}


/* STATUS */

.status-box {

    padding:
        15px;

    border:
        1px solid #dce5df;

    border-radius:
        12px;

    background:
        #fafcfb;
}


.status-box label {

    display:
        flex;

    align-items:
        center;

    gap:
        10px;

    margin:
        0;

}


.status-box input {

    width:
        18px;

    height:
        18px;

    accent-color:
        #1f9d68;
}


button {

    margin-top:
        25px;

    width:
        100%;

    padding:
        15px;

    border:
        none;

    border-radius:
        10px;

    background:
        #1f9d68;

    color:
        white;

    font-size:
        16px;

    font-weight:
        700;

    cursor:
        pointer;
}


button:hover {

    background:
        #167b51;
}


.message {

    padding:
        13px;

    border-radius:
        10px;

    margin-bottom:
        20px;

    font-weight:
        600;
}


.success {

    background:
        #e9f8ef;

    color:
        #137342;
}


.error {

    background:
        #fff0f0;

    color:
        #c62828;
}


@media(max-width:650px) {

    .form-grid {

        grid-template-columns:
            1fr;
    }


    .full {

        grid-column:
            auto;
    }


    .unit-options {

        grid-template-columns:
            1fr;
    }


    .form-box {

        padding:
            20px;
    }

}

</style>

</head>


<body>


<header class="topbar">

<div class="brand">
    FRESH HARVEST
</div>


<a
    class="back"
    href="products.php"
>
    Back to Products
</a>

</header>



<main class="container">


<div class="form-box">


<h1>
    Edit Product
</h1>



<?php if ($message !== ""): ?>

<div class="message <?= htmlspecialchars($messageType) ?>">

<?= htmlspecialchars($message) ?>

</div>

<?php endif; ?>



<form
    method="POST"
    enctype="multipart/form-data"
>


<div class="form-grid">


<!-- NAME -->

<div class="form-group">

<label>
    Product Name
</label>

<input
    type="text"
    name="name"
    value="<?= htmlspecialchars($product["name"]) ?>"
    required
>

</div>



<!-- CATEGORY -->

<div class="form-group">

<label>
    Main Category
</label>

<select
    name="category"
    required
>

<option
    value="fruits"
    <?= $product["category"] === "fruits"
        ? "selected"
        : "" ?>
>
    Fruits
</option>

<option
    value="vegetables"
    <?= $product["category"] === "vegetables"
        ? "selected"
        : "" ?>
>
    Vegetables
</option>

<option
    value="frozen"
    <?= $product["category"] === "frozen"
        ? "selected"
        : "" ?>
>
    Frozen
</option>

<option
    value="other"
    <?= $product["category"] === "other"
        ? "selected"
        : "" ?>
>
    Other
</option>

</select>

</div>



<!-- SUBCATEGORY -->

<div class="form-group">

<label>
    Sub Category
</label>

<input
    type="text"
    name="subcategory"
    value="<?= htmlspecialchars($product["subcategory"] ?? "") ?>"
>

</div>



<!-- UNIT -->

<div class="form-group">

<label>
    Available Unit
</label>


<div class="unit-options">


<label class="unit-option">

<input
    type="checkbox"
    name="available_kg"
    value="1"
    <?= ((int)$product["available_kg"] === 1)
        ? "checked"
        : "" ?>
>

<span>
    Available by KG
</span>

</label>



<label class="unit-option">

<input
    type="checkbox"
    name="available_box"
    value="1"
    <?= ((int)$product["available_box"] === 1)
        ? "checked"
        : "" ?>
>

<span>
    Available by Box
</span>

</label>


</div>



<div class="box-name-field">

<label for="box_label">
    Box Name
</label>

<input
    type="text"
    name="box_label"
    id="box_label"
    value="<?= htmlspecialchars($product["box_label"] ?? "Box") ?>"
    placeholder="Example: 5kg Box / 10 Pieces Box"
>

</div>


</div>



<!-- PRICE -->

<div class="form-group">

<label>
    Price
</label>

<input
    type="number"
    name="price"
    step="0.01"
    min="0"
    value="<?= htmlspecialchars($product["price"]) ?>"
>

</div>



<!-- STOCK -->

<div class="form-group">

<label>
    Stock
</label>

<input
    type="number"
    name="stock"
    min="0"
    value="<?= (int)$product["stock"] ?>"
>

</div>



<!-- IMAGE -->

<div class="form-group full">

<label>
    Current Product Image
</label>


<?php if (!empty($product["image"])): ?>

<img
    src="../<?= htmlspecialchars($product["image"]) ?>"
    class="current-image"
    alt="Product image"
>

<?php endif; ?>


<label>
    Change Product Image
</label>

<input
    type="file"
    name="image"
    accept=".jpg,.jpeg,.png,.webp"
>

</div>



<!-- DESCRIPTION -->

<div class="form-group full">

<label>
    Description
</label>

<textarea
    name="description"
><?= htmlspecialchars($product["description"] ?? "") ?></textarea>

</div>



<!-- STATUS -->

<div class="form-group full">

<div class="status-box">

<label>

<input
    type="checkbox"
    name="status"
    value="1"
    <?= ((int)$product["status"] === 1)
        ? "checked"
        : "" ?>
>

<span>
    Product Active
</span>

</label>

</div>

</div>



</div>


<button type="submit">

    Update Product

</button>


</form>


</div>


</main>


</body>

</html>