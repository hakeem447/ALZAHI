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
   IMAGE UPLOAD FUNCTION
========================================================= */

function uploadProductImage($file)
{

    if (
        !isset($file) ||
        $file["error"] === UPLOAD_ERR_NO_FILE
    ) {
        return null;
    }


    if (
        $file["error"] !== UPLOAD_ERR_OK
    ) {
        return null;
    }


    $allowedTypes = [

        "image/jpeg" => "jpg",
        "image/png"  => "png",
        "image/webp" => "webp"

    ];


    $mimeType =
        mime_content_type(
            $file["tmp_name"]
        );


    if (
        !isset(
            $allowedTypes[$mimeType]
        )
    ) {
        return null;
    }


    /* Maximum 5MB */

    if (
        $file["size"] >
        5 * 1024 * 1024
    ) {
        return null;
    }


    $uploadDir =
        "../uploads/products/";


    if (
        !is_dir($uploadDir)
    ) {

        mkdir(
            $uploadDir,
            0777,
            true
        );

    }


    $extension =
        $allowedTypes[$mimeType];


    $fileName =
        "product_" .
        time() .
        "_" .
        bin2hex(
            random_bytes(5)
        ) .
        "." .
        $extension;


    $target =
        $uploadDir .
        $fileName;


    if (
        move_uploaded_file(
            $file["tmp_name"],
            $target
        )
    ) {

        return
            "uploads/products/" .
            $fileName;

    }


    return null;
}


/* =========================================================
   CHANGE PRODUCT IMAGE
========================================================= */

if (
    $_SERVER["REQUEST_METHOD"] === "POST" &&
    isset($_POST["product_id"])
) {

    $productId =
        (int)$_POST["product_id"];


    if ($productId <= 0) {

        $message =
            "Invalid product.";

        $messageType =
            "error";

    }

    else {

        $stmt =
            $conn->prepare("
                SELECT image
                FROM products
                WHERE id = ?
                LIMIT 1
            ");


        $stmt->bind_param(
            "i",
            $productId
        );


        $stmt->execute();


        $result =
            $stmt->get_result();


        $product =
            $result->fetch_assoc();


        $stmt->close();


        if (!$product) {

            $message =
                "Product not found.";

            $messageType =
                "error";

        }

        else {

            $newImage =
                uploadProductImage(
                    $_FILES["product_image"] ?? null
                );


            if (!$newImage) {

                $message =
                    "Please select a valid JPG, PNG or WEBP image under 5MB.";

                $messageType =
                    "error";

            }

            else {

                $update =
                    $conn->prepare("
                        UPDATE products
                        SET image = ?
                        WHERE id = ?
                    ");


                $update->bind_param(
                    "si",
                    $newImage,
                    $productId
                );


                if (
                    $update->execute()
                ) {

                    $message =
                        "Product image updated successfully.";

                    $messageType =
                        "success";

                }

                else {

                    $message =
                        "Unable to update product image.";

                    $messageType =
                        "error";

                }


                $update->close();

            }

        }

    }

}


/* =========================================================
   GET ALL PRODUCTS
========================================================= */

$result =
    $conn->query("
        SELECT
            id,
            name,
            category,
            subcategory,
            image,
            stock,
            status
        FROM products
        ORDER BY id ASC
    ");


$products = [];


if ($result) {

    while (
        $row =
        $result->fetch_assoc()
    ) {

        $products[] =
            $row;

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
    Product Images
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
        #1e2a23;

}


.page {

    width:
        min(1400px, calc(100% - 30px));

    margin:
        0 auto;

    padding:
        30px 0 60px;

}


/* =========================================================
   HEADER
========================================================= */

.topbar {

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    gap:
        15px;

    margin-bottom:
        25px;

}


.topbar h1 {

    margin:
        0;

    font-size:
        28px;

}


.back-btn {

    display:
        inline-block;

    padding:
        11px 18px;

    background:
        #1f6b45;

    color:
        #fff;

    text-decoration:
        none;

    border-radius:
        8px;

    font-weight:
        700;

}


/* =========================================================
   MESSAGE
========================================================= */

.message {

    padding:
        14px 18px;

    border-radius:
        10px;

    margin-bottom:
        20px;

    font-weight:
        700;

}


.message.success {

    background:
        #e6f6eb;

    color:
        #176b3d;

}


.message.error {

    background:
        #fdeaea;

    color:
        #a32828;

}


/* =========================================================
   INFO
========================================================= */

.info-box {

    background:
        #fff;

    border-radius:
        14px;

    padding:
        18px 20px;

    margin-bottom:
        25px;

    box-shadow:
        0 6px 22px rgba(
            0,
            0,
            0,
            .05
        );

}


.info-box strong {

    color:
        #1f6b45;

}


/* =========================================================
   PRODUCT GRID
========================================================= */

.product-grid {

    display:
        grid;

    grid-template-columns:
        repeat(
            4,
            minmax(
                0,
                1fr
            )
        );

    gap:
        20px;

}


/* =========================================================
   PRODUCT CARD
========================================================= */

.product-card {

    background:
        #fff;

    border:
        1px solid #e3e9e5;

    border-radius:
        15px;

    overflow:
        hidden;

    box-shadow:
        0 7px 24px rgba(
            0,
            0,
            0,
            .05
        );

}


/* =========================================================
   IMAGE
========================================================= */

.product-preview {

    width:
        100%;

    aspect-ratio:
        1 / 0.82;

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

    aspect-ratio:
        1 / 0.82;

    display:
        flex;

    align-items:
        center;

    justify-content:
        center;

    background:
        #eef2ef;

    color:
        #7b857e;

    font-size:
        14px;

    font-weight:
        700;

}


/* =========================================================
   CONTENT
========================================================= */

.product-content {

    padding:
        16px;

}


.product-content h3 {

    margin:
        0 0 7px;

    font-size:
        18px;

}


.category {

    font-size:
        12px;

    font-weight:
        800;

    color:
        #1f8b5c;

    text-transform:
        uppercase;

    letter-spacing:
        .5px;

}


.subcategory {

    color:
        #707a73;

    font-size:
        13px;

    margin-bottom:
        12px;

}


.status {

    display:
        inline-block;

    padding:
        5px 9px;

    border-radius:
        20px;

    font-size:
        11px;

    font-weight:
        800;

    margin-bottom:
        12px;

}


.status.active {

    background:
        #e4f6e9;

    color:
        #17703e;

}


.status.inactive {

    background:
        #f8e5e5;

    color:
        #a12626;

}


/* =========================================================
   FORM
========================================================= */

.image-form {

    display:
        flex;

    flex-direction:
        column;

    gap:
        8px;

}


.image-form input[type="file"] {

    width:
        100%;

    border:
        1px solid #d5ddd7;

    border-radius:
        8px;

    padding:
        8px;

    background:
        #fff;

}


.change-btn {

    border:
        0;

    background:
        #1f6b45;

    color:
        #fff;

    padding:
        11px;

    border-radius:
        8px;

    font-weight:
        800;

    cursor:
        pointer;

}


.change-btn:hover {

    background:
        #175637;

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 1000px) {

    .product-grid {

        grid-template-columns:
            repeat(
                3,
                minmax(
                    0,
                    1fr
                )
            );

    }

}


@media (max-width: 700px) {

    .product-grid {

        grid-template-columns:
            repeat(
                2,
                minmax(
                    0,
                    1fr
                )
            );

        gap:
            12px;

    }


    .product-content {

        padding:
            12px;

    }


    .product-content h3 {

        font-size:
            15px;

    }


    .topbar {

        align-items:
            flex-start;

        flex-direction:
            column;

    }

}


@media (max-width: 420px) {

    .product-grid {

        grid-template-columns:
            1fr 1fr;

    }


    .product-preview,
    .no-image {

        aspect-ratio:
            1 / .85;

    }

}

</style>

</head>


<body>


<div class="page">


    <!-- HEADER -->

    <div class="topbar">

        <h1>
            Product Images
        </h1>


        <a
            href="dashboard.php"
            class="back-btn"
        >
            Back to Dashboard
        </a>

    </div>


    <!-- MESSAGE -->

    <?php if ($message !== ""): ?>

        <div
            class="message <?= htmlspecialchars($messageType) ?>"
        >

            <?= htmlspecialchars($message) ?>

        </div>

    <?php endif; ?>


    <!-- INFO -->

    <div class="info-box">

        Total Products:
        <strong>
            <?= count($products) ?>
        </strong>

        &nbsp; |

        Each product image can be changed
        directly from this page.

    </div>


    <!-- PRODUCT GRID -->

    <div class="product-grid">


        <?php foreach ($products as $product): ?>


            <div class="product-card">


                <!-- IMAGE -->

                <?php if (
                    !empty($product["image"])
                ): ?>

                    <img
                        class="product-preview"
                        src="../<?= htmlspecialchars(
                            $product["image"]
                        ) ?>"
                        alt="<?= htmlspecialchars(
                            $product["name"]
                        ) ?>"
                    >

                <?php else: ?>

                    <div class="no-image">

                        No Image

                    </div>

                <?php endif; ?>


                <!-- CONTENT -->

                <div class="product-content">


                    <div class="category">

                        <?= htmlspecialchars(
                            $product["category"]
                        ) ?>

                    </div>


                    <h3>

                        <?= htmlspecialchars(
                            $product["name"]
                        ) ?>

                    </h3>


                    <?php if (
                        !empty(
                            $product["subcategory"]
                        )
                    ): ?>

                        <div class="subcategory">

                            <?= htmlspecialchars(
                                $product["subcategory"]
                            ) ?>

                        </div>

                    <?php endif; ?>


                    <?php if (
                        (int)$product["status"] === 1
                    ): ?>

                        <span
                            class="status active"
                        >
                            Active
                        </span>

                    <?php else: ?>

                        <span
                            class="status inactive"
                        >
                            Inactive
                        </span>

                    <?php endif; ?>


                    <!-- CHANGE IMAGE -->

                    <form
                        method="POST"
                        enctype="multipart/form-data"
                        class="image-form"
                    >


                        <input
                            type="hidden"
                            name="product_id"
                            value="<?= (int)$product["id"] ?>"
                        >


                        <input
                            type="file"
                            name="product_image"
                            accept=".jpg,.jpeg,.png,.webp"
                            required
                        >


                        <button
                            type="submit"
                            class="change-btn"
                        >
                            Change Image
                        </button>


                    </form>


                </div>


            </div>


        <?php endforeach; ?>


    </div>


</div>


</body>

</html>