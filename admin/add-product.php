<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";

$message = "";
$messageType = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    /* =========================
       BASIC PRODUCT DETAILS
    ========================== */

    $name = trim($_POST["name"] ?? "");
    $category = trim($_POST["category"] ?? "");
    $subcategory = trim($_POST["subcategory"] ?? "");

    $price = trim($_POST["price"] ?? "");
    $stock = trim($_POST["stock"] ?? "");

    $description = trim($_POST["description"] ?? "");


    /* =========================
       KG / BOX AVAILABILITY
    ========================== */

    $availableKg = isset($_POST["available_kg"]) ? 1 : 0;

    $availableBox = isset($_POST["available_box"]) ? 1 : 0;

    $boxLabel = trim($_POST["box_label"] ?? "");


    /*
     * At least one unit must be selected.
     */

    if ($availableKg === 0 && $availableBox === 0) {

        $message = "Please select at least KG or Box.";
        $messageType = "error";

    }


    /*
     * If Box is selected but no box name is entered,
     * use Box as the default name.
     */

    if ($availableBox === 1 && $boxLabel === "") {
        $boxLabel = "Box";
    }


    /* =========================
       VALIDATE BASIC DETAILS
    ========================== */

    if (
        $messageType !== "error" &&
        ($name === "" || $category === "")
    ) {

        $message = "Product name and category are required.";
        $messageType = "error";

    }


    /* =========================
       IMAGE UPLOAD
    ========================== */

    $imagePath = "";

    if (
        $messageType !== "error" &&
        isset($_FILES["image"]) &&
        $_FILES["image"]["error"] === UPLOAD_ERR_OK
    ) {

        $file = $_FILES["image"];

        $allowedTypes = [
            "image/jpeg",
            "image/png",
            "image/webp"
        ];


        if (!in_array($file["type"], $allowedTypes)) {

            $message = "Only JPG, PNG and WEBP images are allowed.";
            $messageType = "error";

        } elseif ($file["size"] > 5 * 1024 * 1024) {

            $message = "Image size must be below 5MB.";
            $messageType = "error";

        } else {

            $extension = strtolower(
                pathinfo($file["name"], PATHINFO_EXTENSION)
            );


            $newFileName =
                time() . "_" .
                uniqid() . "." .
                $extension;


            $uploadDirectory = "../uploads/products/";


            if (!is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, 0777, true);
            }


            $targetFile =
                $uploadDirectory . $newFileName;


            if (
                move_uploaded_file(
                    $file["tmp_name"],
                    $targetFile
                )
            ) {

                $imagePath =
                    "uploads/products/" . $newFileName;

            } else {

                $message = "Image upload failed.";
                $messageType = "error";
            }
        }
    }


    /* =========================
       SAVE PRODUCT
    ========================== */

    if ($messageType !== "error") {

        $priceValue =
            ($price === "")
            ? 0
            : (float)$price;


        $stockValue =
            ($stock === "")
            ? 0
            : (int)$stock;


        /*
         * Keep the old "unit" column working.
         *
         * KG only  = kg
         * Box only = box
         * Both     = both
         */

        if ($availableKg === 1 && $availableBox === 1) {

            $unit = "both";

        } elseif ($availableKg === 1) {

            $unit = "kg";

        } else {

            $unit = "box";
        }


        /*
         * Insert product into database.
         *
         * New fields:
         * available_kg
         * available_box
         * box_label
         */

        $sql = "
            INSERT INTO products
            (
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
            )
            VALUES
            (
                ?, ?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?
            )
        ";


        $stmt = $conn->prepare($sql);


        if (!$stmt) {

            $message =
                "Database error: " . $conn->error;

            $messageType = "error";

        } else {

            /*
             * s = string
             * d = decimal
             * i = integer
             */

            $stmt->bind_param(
                "ssssdsisiis",
                $name,
                $category,
                $subcategory,
                $imagePath,
                $priceValue,
                $unit,
                $stockValue,
                $description,
                $availableKg,
                $availableBox,
                $boxLabel
            );


            if ($stmt->execute()) {

                $message =
                    "Product added successfully!";

                $messageType = "success";

                $_POST = [];

            } else {

                $message =
                    "Database error: " . $stmt->error;

                $messageType = "error";
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

<title>Add Product | Fresh Harvest</title>


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


/* =========================
   TOP BAR
========================= */

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

    font-size:
        14px;

    font-weight:
        600;
}


/* =========================
   CONTAINER
========================= */

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
        0 10px 35px rgba(0,0,0,0.07);
}


h1 {

    margin-bottom:
        25px;

    font-size:
        28px;
}


/* =========================
   FORM GRID
========================= */

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

    background:
        white;
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


/* =========================
   KG / BOX
========================= */

.unit-options {

    display:
        grid;

    grid-template-columns:
        repeat(2, 1fr);

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

    transition:
        0.2s;
}


.unit-option:hover {

    border-color:
        #1f9d68;

    background:
        #f1faf5;
}


.unit-option input {

    width:
        18px;

    height:
        18px;

    accent-color:
        #1f9d68;

    cursor:
        pointer;
}


.box-name-field {

    margin-top:
        5px;

    margin-bottom:
        18px;
}


.box-name-field label {

    display:
        block;

    margin-bottom:
        7px;

    font-weight:
        700;
}


.box-name-field input {

    width:
        100%;

    padding:
        12px;

    border:
        1px solid #d7dfda;

    border-radius:
        8px;
}


/* =========================
   BUTTON
========================= */

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

    transition:
        0.2s;
}


button:hover {

    background:
        #167b51;
}


/* =========================
   MESSAGE
========================= */

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


/* =========================
   MOBILE
========================= */

@media(max-width:650px) {

    .form-grid {

        grid-template-columns:
            1fr;
    }


    .full {

        grid-column:
            auto;
    }


    .form-box {

        padding:
            20px;
    }


    .unit-options {

        grid-template-columns:
            1fr;
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
        FRESH HARVEST
    </div>


    <a
        class="back"
        href="dashboard.php"
    >
        Dashboard
    </a>

</header>



<main class="container">


<div class="form-box">


<h1>
    Add New Product
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



<!-- PRODUCT NAME -->

<div class="form-group">

<label>
    Product Name
</label>

<input
    type="text"
    name="name"
    placeholder="Example: Fresh Mango"
    required
>

</div>



<!-- MAIN CATEGORY -->

<div class="form-group">

<label>
    Main Category
</label>

<select
    name="category"
    required
>

<option value="">
    Select Category
</option>

<option value="fruits">
    Fruits
</option>

<option value="vegetables">
    Vegetables
</option>

<option value="frozen">
    Frozen
</option>

<option value="other">
    Other
</option>

</select>

</div>



<!-- SUB CATEGORY -->

<div class="form-group">

<label>
    Sub Category
</label>

<input
    type="text"
    name="subcategory"
    placeholder="Example: Mango"
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
    checked
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
>

<span>
    Available by Box
</span>

</label>


</div>



<div class="box-name-field" id="boxNameField">

<label
    for="box_label"
>
    Box Name
</label>

<input
    type="text"
    name="box_label"
    id="box_label"
    placeholder="Example: 5kg Box / 10 Pieces Box"
    value="Box"
>

</div>


</div>



<!-- PRICE -->

<div class="form-group">

<label>
    Price
</label>

<input
    type="text"
    name="price"
    step="0.01"
   
    placeholder="Example: 10.00"
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
    placeholder="Example: 50"
>

</div>



<!-- PRODUCT IMAGE -->

<div class="form-group full">

<label>
    Product Image
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
    placeholder="Product description..."
></textarea>

</div>



</div>



<button type="submit">

    Save Product

</button>


</form>


</div>


</main>
<script>
const kgCheckbox = document.querySelector('input[name="available_kg"]');
const boxCheckbox = document.querySelector('input[name="available_box"]');
const boxNameField = document.getElementById('boxNameField');

function updateBoxField() {
    if (boxCheckbox.checked) {
        boxNameField.style.display = "block";
    } else {
        boxNameField.style.display = "none";
    }
}

kgCheckbox.addEventListener("change", updateBoxField);
boxCheckbox.addEventListener("change", updateBoxField);

updateBoxField();
</script>


</body>

</html>