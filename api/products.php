<?php

header("Content-Type: application/json; charset=UTF-8");

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
        box_label
    FROM products
    WHERE status = 1
    ORDER BY id DESC
";


$result = $conn->query($sql);


$products = [];


if ($result) {

    while ($row = $result->fetch_assoc()) {

        $image = $row["image"] ?? "";


        /*
         * Make sure image path is correct
         */

        if (
            $image !== "" &&
            !str_starts_with($image, "http://") &&
            !str_starts_with($image, "https://") &&
            !str_starts_with($image, "/")
        ) {

            $image = "/jasir3/" . ltrim($image, "/");
        }


        $products[] = [

            "id" =>
                (int)$row["id"],

            "name" =>
                $row["name"],

            "category" =>
                $row["category"],

            "subcategory" =>
                $row["subcategory"],

            "image" =>
                $image,

            "price" =>
                (float)$row["price"],

            "unit" =>
                $row["unit"],

            "stock" =>
                (int)$row["stock"],

            "description" =>
                $row["description"],

            "available_kg" =>
                (int)$row["available_kg"],

            "available_box" =>
                (int)$row["available_box"],

            "box_label" =>
                $row["box_label"] ?? "Box"
        ];
    }
}


echo json_encode(
    [
        "success" => true,
        "products" => $products
    ],
    JSON_UNESCAPED_UNICODE
);


$conn->close();

?>