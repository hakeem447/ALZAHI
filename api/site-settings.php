<?php

header("Content-Type: application/json; charset=UTF-8");

require_once "../config/database.php";

$sql = "
    SELECT setting_key, setting_value
    FROM site_settings
";

$result = $conn->query($sql);

$settings = [];

if ($result) {

    while ($row = $result->fetch_assoc()) {

        $settings[$row["setting_key"]] =
            $row["setting_value"];

    }

}

$heroSlides = [];

for ($i = 1; $i <= 5; $i++) {

    $heroSlides[] = [

        "title" =>
            $settings["hero_{$i}_title"] ?? "",

        "description" =>
            $settings["hero_{$i}_description"] ?? "",

        "label" =>
            $settings["hero_{$i}_label"] ?? "",

        "category" =>
            $settings["hero_{$i}_category"] ?? "fruits",

        "bg" =>
            $settings["hero_{$i}_bg"] ?? "#eef5ed",

        "accent" =>
            $settings["hero_{$i}_accent"] ?? "#168b5c",

        "image" =>
            $settings["hero_{$i}_image"] ?? ""

    ];

}

echo json_encode(
    [
        "success" => true,

        "interval" =>
            (int)($settings["hero_interval"] ?? 5),

        "categories" => [

            "all" =>
                $settings["main_all_image"] ?? "",

            "fruits" =>
                $settings["main_fruits_image"] ?? "",

            "vegetables" =>
                $settings["main_vegetables_image"] ?? "",

            "frozen" =>
                $settings["main_frozen_image"] ?? "",

            "other" =>
                $settings["main_other_image"] ?? ""

        ],

        "heroSlides" => $heroSlides
    ],

    JSON_UNESCAPED_UNICODE
);

$conn->close();

?>