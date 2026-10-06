<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";

$message = "";
$error = "";


/* =========================================================
   DELETE OFFER
========================================================= */

if (isset($_GET["delete"])) {

    $deleteId = (int)$_GET["delete"];

    if ($deleteId > 0) {

        $stmt = $conn->prepare(
            "DELETE FROM offers WHERE id = ?"
        );

        $stmt->bind_param(
            "i",
            $deleteId
        );

        if ($stmt->execute()) {
            $message = "Offer deleted successfully.";
        } else {
            $error = "Unable to delete offer.";
        }

        $stmt->close();
    }
}


/* =========================================================
   ADD OFFER
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $title = trim($_POST["title"] ?? "");

    $description = trim(
        $_POST["description"] ?? ""
    );

    $productId = (int)(
        $_POST["product_id"] ?? 0
    );

    $offerPrice = is_numeric(
        $_POST["offer_price"] ?? ""
    )
        ? (float)$_POST["offer_price"]
        : 0;

    $startDate = !empty($_POST["start_date"])
        ? $_POST["start_date"]
        : null;

    $endDate = !empty($_POST["end_date"])
        ? $_POST["end_date"]
        : null;


    if ($title === "") {

        $error = "Offer title is required.";

    } else {

        $imagePath = "";


        /* =================================================
           IMAGE UPLOAD
        ================================================= */

        if (
            isset($_FILES["image"]) &&
            $_FILES["image"]["error"] !== UPLOAD_ERR_NO_FILE
        ) {

            if (
                $_FILES["image"]["error"] !== UPLOAD_ERR_OK
            ) {

                $error = "Image upload failed.";

            } else {

                /*
                 * IMPORTANT:
                 * Offer images are stored here:
                 *
                 * C:\xampp\htdocs\jasir3\
                 * uploads\products\
                 */

                $uploadDir =
                    "../uploads/products/";


                if (!is_dir($uploadDir)) {

                    mkdir(
                        $uploadDir,
                        0777,
                        true
                    );
                }


                $extension = strtolower(
                    pathinfo(
                        $_FILES["image"]["name"],
                        PATHINFO_EXTENSION
                    )
                );


                $allowed = [
                    "jpg",
                    "jpeg",
                    "png",
                    "webp"
                ];


                if (!in_array(
                    $extension,
                    $allowed,
                    true
                )) {

                    $error =
                        "Only JPG, JPEG, PNG and WEBP images are allowed.";

                } else {

                    $fileName =
                        "offer_" .
                        uniqid() .
                        "." .
                        $extension;


                    $target =
                        $uploadDir .
                        $fileName;


                    if (
                        move_uploaded_file(
                            $_FILES["image"]["tmp_name"],
                            $target
                        )
                    ) {

                        /*
                         * This is what will be saved
                         * in the database.
                         */

                        $imagePath =
                            "uploads/products/" .
                            $fileName;

                    } else {

                        $error =
                            "Image upload failed.";

                    }
                }
            }
        }


        /* =================================================
           SAVE OFFER
        ================================================= */

        if ($error === "") {

            $stmt = $conn->prepare("
                INSERT INTO offers
                (
                    title,
                    description,
                    image,
                    product_id,
                    offer_price,
                    start_date,
                    end_date,
                    status
                )
                VALUES (?, ?, ?, ?, ?, ?, ?, 1)
            ");


            if (!$stmt) {

                $error =
                    "Database prepare error: " .
                    $conn->error;

            } else {

                $stmt->bind_param(
                    "sssidss",
                    $title,
                    $description,
                    $imagePath,
                    $productId,
                    $offerPrice,
                    $startDate,
                    $endDate
                );


                if ($stmt->execute()) {

                    $message =
                        "Weekend offer added successfully.";

                    /*
                     * Clear form values after successful save.
                     */

                    $_POST = [];

                } else {

                    $error =
                        "Unable to save offer: " .
                        $stmt->error;
                }


                $stmt->close();
            }
        }
    }
}


/* =========================================================
   GET OFFERS
========================================================= */

$offers = [];

$result = $conn->query("
    SELECT
        o.*,
        p.name AS product_name
    FROM offers o
    LEFT JOIN products p
        ON o.product_id = p.id
    ORDER BY o.id DESC
");


if ($result) {

    while ($row = $result->fetch_assoc()) {

        $offers[] = $row;
    }
}


/* =========================================================
   GET PRODUCTS
========================================================= */

$products = [];

$productResult = $conn->query("
    SELECT
        id,
        name
    FROM products
    WHERE status = 1
    ORDER BY name ASC
");


if ($productResult) {

    while ($row = $productResult->fetch_assoc()) {

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

<title>Weekend Offers</title>


<style>

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    padding: 35px 20px;

    background: #f5f7f6;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    color: #222;
}


.container {

    max-width: 1100px;

    margin: auto;
}


.header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    gap: 20px;

    margin-bottom: 25px;
}


h1 {

    margin: 0 0 5px;
}


.subtitle {

    color: #777;

    margin: 0;
}


.back {

    text-decoration: none;

    background: #e9ecef;

    color: #222;

    padding: 11px 16px;

    border-radius: 9px;
}


.card {

    background: white;

    padding: 25px;

    border-radius: 16px;

    box-shadow:
        0 8px 30px
        rgba(0,0,0,.07);

    margin-bottom: 25px;
}


.grid {

    display: grid;

    grid-template-columns:
        repeat(2, 1fr);

    gap: 18px;
}


.full {

    grid-column: 1 / -1;
}


label {

    display: block;

    font-weight: 600;

    margin-bottom: 7px;

    font-size: 14px;
}


input,
textarea,
select {

    width: 100%;

    padding: 12px;

    border: 1px solid #ddd;

    border-radius: 9px;

    font-size: 15px;
}


textarea {

    min-height: 100px;

    resize: vertical;
}


.submit {

    margin-top: 20px;

    border: 0;

    background: #236b43;

    color: white;

    padding: 13px 22px;

    border-radius: 9px;

    cursor: pointer;

    font-size: 15px;
}


.message {

    padding: 13px;

    border-radius: 9px;

    background: #eaf8ee;

    color: #18733a;

    margin-bottom: 20px;
}


.error {

    padding: 13px;

    border-radius: 9px;

    background: #fff0f0;

    color: #b42318;

    margin-bottom: 20px;
}


.offer-list {

    display: grid;

    gap: 15px;
}


.offer-item {

    display: grid;

    grid-template-columns:
        130px 1fr auto;

    gap: 20px;

    align-items: center;

    padding: 15px;

    border: 1px solid #eee;

    border-radius: 12px;
}


.offer-item img {

    width: 130px;

    height: 90px;

    object-fit: cover;

    border-radius: 9px;
}


.offer-item h3 {

    margin:
        0 0 7px;
}


.offer-item p {

    margin: 4px 0;

    color: #666;
}


.delete {

    text-decoration: none;

    background: #fff0f0;

    color: #b42318;

    padding: 9px 13px;

    border-radius: 8px;
}


@media (max-width: 700px) {

    .grid {

        grid-template-columns: 1fr;
    }

    .full {

        grid-column: auto;
    }

    .header {

        align-items: flex-start;

        flex-direction: column;
    }

    .offer-item {

        grid-template-columns: 1fr;
    }

    .offer-item img {

        width: 100%;

        height: 180px;
    }

}

</style>

</head>


<body>


<div class="container">


    <div class="header">

        <div>

            <h1>Weekend Offers</h1>

            <p class="subtitle">
                Manage your weekly special offers.
            </p>

        </div>


        <a
            href="dashboard.php"
            class="back"
        >
            Back to Dashboard
        </a>

    </div>


    <?php if ($message !== ""): ?>

        <div class="message">
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>


    <?php if ($error !== ""): ?>

        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>

    <?php endif; ?>


    <!-- =================================================
         ADD OFFER
    ================================================== -->

    <div class="card">

        <h2>Add Weekend Offer</h2>


        <form
            method="POST"
            enctype="multipart/form-data"
        >

            <div class="grid">


                <div>

                    <label>
                        Offer Title
                    </label>

                    <input
                        type="text"
                        name="title"
                        placeholder="Weekend Mango Special"
                        required
                    >

                </div>


                <div>

                    <label>
                        Product
                    </label>

                    <select name="product_id">

                        <option value="0">
                            Select Product
                        </option>

                        <?php foreach (
                            $products as $product
                        ): ?>

                            <option
                                value="<?= (int)$product["id"] ?>"
                            >
                                <?= htmlspecialchars(
                                    $product["name"]
                                ) ?>
                            </option>

                        <?php endforeach; ?>

                    </select>

                </div>


                <div>

                    <label>
                        Offer Price
                    </label>

                    <input
                        type="number"
                        name="offer_price"
                        step="0.01"
                        min="0"
                        placeholder="10.00"
                    >

                </div>


                <div>

                    <label>
                        Offer Image
                    </label>

                    <input
                        type="file"
                        name="image"
                        accept=".jpg,.jpeg,.png,.webp"
                    >

                </div>


                <div>

                    <label>
                        Start Date
                    </label>

                    <input
                        type="date"
                        name="start_date"
                    >

                </div>


                <div>

                    <label>
                        End Date
                    </label>

                    <input
                        type="date"
                        name="end_date"
                    >

                </div>


                <div class="full">

                    <label>
                        Description
                    </label>

                    <textarea
                        name="description"
                        placeholder="Fresh mango weekend special..."
                    ></textarea>

                </div>


            </div>


            <button
                type="submit"
                class="submit"
            >
                Add Weekend Offer
            </button>

        </form>

    </div>


    <!-- =================================================
         EXISTING OFFERS
    ================================================== -->

    <div class="card">

        <h2>Existing Offers</h2>


        <div class="offer-list">


            <?php if (empty($offers)): ?>

                <p>
                    No offers added yet.
                </p>

            <?php endif; ?>


            <?php foreach (
                $offers as $offer
            ): ?>


                <div class="offer-item">


                    <?php if (
                        !empty($offer["image"])
                    ): ?>

                        <img
                            src="../<?= htmlspecialchars(
                                $offer["image"]
                            ) ?>"
                            alt="<?= htmlspecialchars(
                                $offer["title"]
                            ) ?>"
                        >

                    <?php else: ?>

                        <div>
                            No image
                        </div>

                    <?php endif; ?>


                    <div>

                        <h3>
                            <?= htmlspecialchars(
                                $offer["title"]
                            ) ?>
                        </h3>

                        <p>
                            Product:
                            <?= htmlspecialchars(
                                $offer["product_name"]
                                ?? "None"
                            ) ?>
                        </p>

                        <p>
                            Offer Price:
                            <?= number_format(
                                (float)$offer["offer_price"],
                                2
                            ) ?>
                        </p>

                        <p>
                            <?= htmlspecialchars(
                                $offer["start_date"]
                                ?? ""
                            ) ?>

                            →

                            <?= htmlspecialchars(
                                $offer["end_date"]
                                ?? ""
                            ) ?>
                        </p>

                        <?php if (
                            !empty($offer["description"])
                        ): ?>

                            <p>
                                <?= htmlspecialchars(
                                    $offer["description"]
                                ) ?>
                            </p>

                        <?php endif; ?>

                    </div>


                    <div>

                        <a
                            class="delete"
                            href="offers.php?delete=<?= (int)$offer["id"] ?>"
                            onclick="return confirm('Delete this offer?')"
                        >
                            Delete
                        </a>

                    </div>


                </div>


            <?php endforeach; ?>


        </div>

    </div>


</div>


</body>

</html>