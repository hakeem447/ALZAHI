<?php

header(
    "Content-Type: application/json; charset=UTF-8"
);

require_once "../config/database.php";


/* =========================================================
   GET ACTIVE OFFERS
========================================================= */

$sql = "
    SELECT
        id,
        title,
        description,
        image,
        product_id,
        offer_price,
        start_date,
        end_date,
        status
    FROM offers
    WHERE status = 1
    ORDER BY id DESC
";


$result = $conn->query($sql);


$offers = [];


if ($result) {

    while ($row = $result->fetch_assoc()) {

        $image = trim(
            (string)($row["image"] ?? "")
        );


        /* =================================================
           FIX IMAGE PATH
        ================================================= */

        if ($image !== "") {

            $image = str_replace(
                "\\",
                "/",
                $image
            );


            /*
             * Full URL
             */

            if (
                strpos($image, "http://") === 0 ||
                strpos($image, "https://") === 0
            ) {

                // Keep as it is.

            } else {

                $image = ltrim(
                    $image,
                    "/"
                );


                /*
                 * Already contains jasir3/
                 */

                if (
                    strpos(
                        $image,
                        "jasir3/"
                    ) === 0
                ) {

                    $image = "/" . $image;

                }


                /*
                 * Correct offer path:
                 *
                 * uploads/product/filename
                 */

                elseif (
                    strpos(
                        $image,
                        "uploads/products/"
                    ) === 0
                ) {

                    $image =
                        "/jasir3/" .
                        $image;

                }


                /*
                 * If database contains only filename,
                 * use the correct folder.
                 */

                else {

                    $image =
                        "/jasir3/uploads/products/" .
                        basename($image);
                }
            }
        }


        $offers[] = [

            "id" =>
                (int)$row["id"],

            "title" =>
                $row["title"],

            "description" =>
                $row["description"] ?? "",

            "image" =>
                $image,

            "product_id" =>
                $row["product_id"] !== null
                    ? (int)$row["product_id"]
                    : null,

            "offer_price" =>
                (float)$row["offer_price"],

            "start_date" =>
                $row["start_date"],

            "end_date" =>
                $row["end_date"],

            "status" =>
                (int)$row["status"]
        ];
    }
}


$conn->close();


/* =========================================================
   JSON RESPONSE
========================================================= */

echo json_encode(
    [
        "success" => true,
        "offers" => $offers
    ],
    JSON_UNESCAPED_UNICODE |
    JSON_UNESCAPED_SLASHES
);

?>