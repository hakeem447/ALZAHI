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
   GET SETTING
========================================================= */

function getSetting($conn, $key)
{
    $stmt = $conn->prepare("
        SELECT setting_value
        FROM site_settings
        WHERE setting_key = ?
        LIMIT 1
    ");

    $stmt->bind_param("s", $key);

    $stmt->execute();

    $result = $stmt->get_result();

    $row = $result->fetch_assoc();

    $stmt->close();

    return $row["setting_value"] ?? "";
}


/* =========================================================
   SAVE SETTING
========================================================= */

function saveSetting($conn, $key, $value)
{
    $stmt = $conn->prepare("
        INSERT INTO site_settings
        (setting_key, setting_value)
        VALUES (?, ?)

        ON DUPLICATE KEY UPDATE
        setting_value = VALUES(setting_value)
    ");

    $stmt->bind_param(
        "ss",
        $key,
        $value
    );

    $stmt->execute();

    $stmt->close();
}


/* =========================================================
   IMAGE UPLOAD
========================================================= */

function uploadSiteImage($fieldName, $oldImage = "")
{

    if (
        !isset($_FILES[$fieldName]) ||
        $_FILES[$fieldName]["error"] === UPLOAD_ERR_NO_FILE
    ) {

        return $oldImage;

    }


    if (
        $_FILES[$fieldName]["error"] !== UPLOAD_ERR_OK
    ) {

        return $oldImage;

    }


    $allowedTypes = [
        "image/jpeg" => "jpg",
        "image/png"  => "png",
        "image/webp" => "webp"
    ];


    $mimeType = mime_content_type(
        $_FILES[$fieldName]["tmp_name"]
    );


    if (!isset($allowedTypes[$mimeType])) {

        return $oldImage;

    }


    if (
        $_FILES[$fieldName]["size"] >
        5 * 1024 * 1024
    ) {

        return $oldImage;

    }


    $uploadDir =
        "../uploads/site/";


    if (!is_dir($uploadDir)) {

        mkdir(
            $uploadDir,
            0777,
            true
        );

    }


    $extension =
        $allowedTypes[$mimeType];


    $fileName =
        $fieldName .
        "_" .
        time() .
        "_" .
        bin2hex(random_bytes(4)) .
        "." .
        $extension;


    $target =
        $uploadDir .
        $fileName;


    if (
        move_uploaded_file(
            $_FILES[$fieldName]["tmp_name"],
            $target
        )
    ) {

        return
            "uploads/site/" .
            $fileName;

    }


    return $oldImage;
}


/* =========================================================
   SAVE FORM
========================================================= */

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    try {

        /* =============================================
           ANIMATION TIME
        ============================================= */

        $interval =
            isset($_POST["hero_interval"])
                ? (int)$_POST["hero_interval"]
                : 5;


        if ($interval < 2) {
            $interval = 2;
        }


        if ($interval > 60) {
            $interval = 60;
        }


        saveSetting(
            $conn,
            "hero_interval",
            (string)$interval
        );


        /* =============================================
           MAIN CATEGORY IMAGES
        ============================================= */

        $categories = [
            "all",
            "fruits",
            "vegetables",
            "frozen",
            "other"
        ];


        foreach ($categories as $category) {

            $key =
                "main_" .
                $category .
                "_image";


            $oldImage =
                getSetting(
                    $conn,
                    $key
                );


            $newImage =
                uploadSiteImage(
                    "main_" .
                    $category .
                    "_image",
                    $oldImage
                );


            saveSetting(
                $conn,
                $key,
                $newImage
            );

        }


        /* =============================================
           HERO SLIDES
        ============================================= */

        for ($i = 1; $i <= 5; $i++) {

            $title =
                $_POST["hero_{$i}_title"]
                ?? "";


            $description =
                $_POST["hero_{$i}_description"]
                ?? "";


            $label =
                $_POST["hero_{$i}_label"]
                ?? "";


            $category =
                $_POST["hero_{$i}_category"]
                ?? "fruits";


            $bg =
                $_POST["hero_{$i}_bg"]
                ?? "#eef5ed";


            $accent =
                $_POST["hero_{$i}_accent"]
                ?? "#168b5c";


            saveSetting(
                $conn,
                "hero_{$i}_title",
                $title
            );


            saveSetting(
                $conn,
                "hero_{$i}_description",
                $description
            );


            saveSetting(
                $conn,
                "hero_{$i}_label",
                $label
            );


            saveSetting(
                $conn,
                "hero_{$i}_category",
                $category
            );


            saveSetting(
                $conn,
                "hero_{$i}_bg",
                $bg
            );


            saveSetting(
                $conn,
                "hero_{$i}_accent",
                $accent
            );


            $oldHeroImage =
                getSetting(
                    $conn,
                    "hero_{$i}_image"
                );


            $newHeroImage =
                uploadSiteImage(
                    "hero_{$i}_image",
                    $oldHeroImage
                );


            saveSetting(
                $conn,
                "hero_{$i}_image",
                $newHeroImage
            );

        }


        $message =
            "Website images and animation settings updated successfully.";

        $messageType =
            "success";

    }

    catch (Exception $e) {

        $message =
            "Unable to save settings.";

        $messageType =
            "error";

    }

}


/* =========================================================
   CURRENT SETTINGS
========================================================= */

$heroInterval =
    getSetting(
        $conn,
        "hero_interval"
    );


$categorySettings = [];

foreach (
    [
        "all",
        "fruits",
        "vegetables",
        "frozen",
        "other"
    ] as $category
) {

    $categorySettings[$category] =
        getSetting(
            $conn,
            "main_{$category}_image"
        );

}


$heroSettings = [];

for ($i = 1; $i <= 5; $i++) {

    $heroSettings[$i] = [

        "title" =>
            getSetting(
                $conn,
                "hero_{$i}_title"
            ),

        "description" =>
            getSetting(
                $conn,
                "hero_{$i}_description"
            ),

        "label" =>
            getSetting(
                $conn,
                "hero_{$i}_label"
            ),

        "category" =>
            getSetting(
                $conn,
                "hero_{$i}_category"
            ),

        "bg" =>
            getSetting(
                $conn,
                "hero_{$i}_bg"
            ),

        "accent" =>
            getSetting(
                $conn,
                "hero_{$i}_accent"
            ),

        "image" =>
            getSetting(
                $conn,
                "hero_{$i}_image"
            )

    ];

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

<title>Website Images & Animation</title>

<style>

* {
    box-sizing: border-box;
}

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f5f7f5;
    color: #1d2a22;
}

.page {
    max-width: 1250px;
    margin: auto;
    padding: 30px 20px 60px;
}

.topbar {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 15px;
    margin-bottom: 25px;
}

.topbar h1 {
    margin: 0;
    font-size: 28px;
}

.back-btn {
    text-decoration: none;
    background: #1f6b45;
    color: white;
    padding: 11px 17px;
    border-radius: 8px;
    font-weight: 700;
}

.message {
    padding: 14px 18px;
    border-radius: 10px;
    margin-bottom: 20px;
    font-weight: 700;
}

.message.success {
    background: #e4f6ea;
    color: #1c7040;
}

.message.error {
    background: #fdeaea;
    color: #a32323;
}

.section {
    background: white;
    border-radius: 16px;
    padding: 25px;
    margin-bottom: 25px;
    box-shadow: 0 8px 25px rgba(0,0,0,.05);
}

.section h2 {
    margin-top: 0;
}

.section-description {
    color: #6c776f;
    margin-bottom: 20px;
}

.interval-box {
    display: flex;
    align-items: center;
    gap: 15px;
    flex-wrap: wrap;
}

.interval-box input {
    width: 120px;
}

.category-grid {
    display: grid;
    grid-template-columns: repeat(5, 1fr);
    gap: 18px;
}

.category-card,
.hero-card {
    border: 1px solid #e1e7e2;
    border-radius: 14px;
    padding: 15px;
    background: #fbfcfb;
}

.category-card h3,
.hero-card h3 {
    margin-top: 0;
}

.preview {
    width: 100%;
    aspect-ratio: 16 / 10;
    object-fit: cover;
    border-radius: 10px;
    background: #e9eeea;
    display: block;
    margin-bottom: 12px;
}

.hero-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

input,
textarea,
select {
    width: 100%;
    padding: 11px 12px;
    border: 1px solid #ccd6cf;
    border-radius: 8px;
    font: inherit;
    margin-top: 6px;
}

textarea {
    min-height: 90px;
    resize: vertical;
}

label {
    display: block;
    font-weight: 700;
    margin-top: 12px;
}

input[type="file"] {
    padding: 8px;
    background: white;
}

.color-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.save-btn {
    width: 100%;
    border: 0;
    background: #1f6b45;
    color: white;
    padding: 15px;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 800;
    cursor: pointer;
}

.save-btn:hover {
    background: #175637;
}

@media (max-width: 800px) {

    .category-grid {
        grid-template-columns: repeat(2, 1fr);
    }

    .hero-grid {
        grid-template-columns: 1fr;
    }

}

</style>

</head>

<body>

<div class="page">

    <div class="topbar">

        <h1>
            Website Images & Animation
        </h1>

        <a
            href="dashboard.php"
            class="back-btn"
        >
            Back to Dashboard
        </a>

    </div>


    <?php if ($message !== ""): ?>

        <div
            class="message <?= htmlspecialchars($messageType) ?>"
        >
            <?= htmlspecialchars($message) ?>
        </div>

    <?php endif; ?>


    <!-- =====================================================
         ANIMATION TIME
    ====================================================== -->

    <div class="section">

        <h2>
            Hero Animation Time
        </h2>

        <p class="section-description">
            Hero image ethana seconds-ku once change aaganum
            enbadhai inga set pannalam.
        </p>

        <div class="interval-box">

            <label>
                Seconds

                <input
                    type="number"
                    name="hero_interval"
                    form="siteSettingsForm"
                    min="2"
                    max="60"
                    value="<?= htmlspecialchars($heroInterval ?: 5) ?>"
                >

            </label>

            <span>
                Example: 5 = every 5 seconds
            </span>

        </div>

    </div>


    <form
        method="POST"
        enctype="multipart/form-data"
        id="siteSettingsForm"
    >


        <!-- =================================================
             MAIN CATEGORY IMAGES
        ================================================== -->

        <div class="section">

            <h2>
                Main Category Images
            </h2>

            <p class="section-description">
                Website top-la irukkura All, Fruits, Vegetables,
                Frozen, Other images-ai inga change pannalam.
            </p>


            <div class="category-grid">

                <?php

                $categoryNames = [
                    "all" => "All",
                    "fruits" => "Fruits",
                    "vegetables" => "Vegetables",
                    "frozen" => "Frozen",
                    "other" => "Other"
                ];

                ?>


                <?php foreach ($categoryNames as $key => $name): ?>

                    <div class="category-card">

                        <h3>
                            <?= $name ?>
                        </h3>

                        <?php if (!empty($categorySettings[$key])): ?>

                            <img
                                src="../<?= htmlspecialchars($categorySettings[$key]) ?>"
                                class="preview"
                                alt="<?= htmlspecialchars($name) ?>"
                            >

                        <?php else: ?>

                            <div class="preview"></div>

                        <?php endif; ?>


                        <label>
                            Change Image

                            <input
                                type="file"
                                name="main_<?= $key ?>_image"
                                accept=".jpg,.jpeg,.png,.webp"
                            >

                        </label>

                    </div>

                <?php endforeach; ?>

            </div>

        </div>


        <!-- =================================================
             HERO SLIDES
        ================================================== -->

        <div class="section">

            <h2>
                Hero / Animation Slides
            </h2>

            <p class="section-description">
                Customer virumbura image, title, text,
                category and colour-ai slide-by-slide change pannalam.
            </p>


            <div class="hero-grid">

                <?php for ($i = 1; $i <= 5; $i++): ?>

                    <?php $hero = $heroSettings[$i]; ?>

                    <div class="hero-card">

                        <h3>
                            Hero Slide <?= $i ?>
                        </h3>


                        <?php if (!empty($hero["image"])): ?>

                            <img
                                src="../<?= htmlspecialchars($hero["image"]) ?>"
                                class="preview"
                                alt="Hero Slide <?= $i ?>"
                            >

                        <?php else: ?>

                            <div class="preview"></div>

                        <?php endif; ?>


                        <label>
                            Slide Image

                            <input
                                type="file"
                                name="hero_<?= $i ?>_image"
                                accept=".jpg,.jpeg,.png,.webp"
                            >

                        </label>


                        <label>
                            Small Label

                            <input
                                type="text"
                                name="hero_<?= $i ?>_label"
                                value="<?= htmlspecialchars($hero["label"]) ?>"
                            >

                        </label>


                        <label>
                            Title

                            <textarea
                                name="hero_<?= $i ?>_title"
                            ><?= htmlspecialchars($hero["title"]) ?></textarea>

                        </label>


                        <label>
                            Description

                            <textarea
                                name="hero_<?= $i ?>_description"
                            ><?= htmlspecialchars($hero["description"]) ?></textarea>

                        </label>


                        <label>
                            Category

                            <select
                                name="hero_<?= $i ?>_category"
                            >

                                <option
                                    value="fruits"
                                    <?= $hero["category"] === "fruits" ? "selected" : "" ?>
                                >
                                    Fruits
                                </option>

                                <option
                                    value="vegetables"
                                    <?= $hero["category"] === "vegetables" ? "selected" : "" ?>
                                >
                                    Vegetables
                                </option>

                                <option
                                    value="frozen"
                                    <?= $hero["category"] === "frozen" ? "selected" : "" ?>
                                >
                                    Frozen
                                </option>

                                <option
                                    value="other"
                                    <?= $hero["category"] === "other" ? "selected" : "" ?>
                                >
                                    Other
                                </option>

                            </select>

                        </label>


                        <div class="color-row">

                            <label>
                                Background

                                <input
                                    type="color"
                                    name="hero_<?= $i ?>_bg"
                                    value="<?= htmlspecialchars($hero["bg"] ?: "#eef5ed") ?>"
                                >

                            </label>


                            <label>
                                Accent

                                <input
                                    type="color"
                                    name="hero_<?= $i ?>_accent"
                                    value="<?= htmlspecialchars($hero["accent"] ?: "#168b5c") ?>"
                                >

                            </label>

                        </div>

                    </div>

                <?php endfor; ?>

            </div>

        </div>


        <button
            type="submit"
            class="save-btn"
        >
            Save Website Settings
        </button>

    </form>

</div>

</body>

</html>