<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";


$products = [

    [
        "Apple",
        "fruits",
        "apple",
        "kg",
        "https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?auto=format&fit=crop&w=900&q=90",
        "Fresh, crisp apples selected for everyday freshness."
    ],

    [
        "Banana",
        "fruits",
        "banana",
        "kg",
        "https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?auto=format&fit=crop&w=900&q=90",
        "Naturally sweet bananas with a smooth texture."
    ],

    [
        "Mango",
        "fruits",
        "mango",
        "kg",
        "https://images.unsplash.com/photo-1553279768-865429fa0078?auto=format&fit=crop&w=900&q=90",
        "Juicy premium mangoes with rich tropical flavour."
    ],

    [
        "Orange",
        "fruits",
        "orange",
        "kg",
        "https://images.unsplash.com/photo-1547514701-42782101795e?auto=format&fit=crop&w=900&q=90",
        "Bright and juicy oranges."
    ],

    [
        "Papaya",
        "fruits",
        "papaya",
        "kg",
        "https://images.unsplash.com/photo-1526318472351-c75fcf070305?auto=format&fit=crop&w=900&q=90",
        "Fresh ripe papaya with a naturally soft texture."
    ],

    [
        "Pineapple",
        "fruits",
        "pineapple",
        "box",
        "https://images.unsplash.com/photo-1550258987-190a2d41a8ba?auto=format&fit=crop&w=900&q=90",
        "Fresh tropical pineapple."
    ],

    [
        "Watermelon",
        "fruits",
        "watermelon",
        "kg",
        "https://images.unsplash.com/photo-1563114773-84221bd62daa?auto=format&fit=crop&w=900&q=90",
        "Refreshing watermelon selected for freshness."
    ],

    [
        "Grapes",
        "fruits",
        "grapes",
        "box",
        "https://images.unsplash.com/photo-1537640538966-79f369143f8f?auto=format&fit=crop&w=900&q=90",
        "Fresh, naturally sweet grapes."
    ],

    [
        "Kiwi",
        "fruits",
        "kiwi",
        "box",
        "https://images.unsplash.com/photo-1585059895524-72359e06133a?auto=format&fit=crop&w=900&q=90",
        "Fresh green kiwi fruits."
    ],

    [
        "Strawberry",
        "fruits",
        "strawberry",
        "box",
        "https://images.unsplash.com/photo-1464965911861-746a04b4bca6?auto=format&fit=crop&w=900&q=90",
        "Fresh premium strawberries."
    ],

    [
        "Guava",
        "fruits",
        "guava",
        "kg",
        "https://images.unsplash.com/photo-1536511132770-e5058c7e8c46?auto=format&fit=crop&w=900&q=90",
        "Fresh guava with natural tropical flavour."
    ],

    [
        "Pomegranate",
        "fruits",
        "pomegranate",
        "kg",
        "https://images.unsplash.com/photo-1541344999736-83eca272f6fc?auto=format&fit=crop&w=900&q=90",
        "Ruby-red pomegranate packed with flavour."
    ],

    [
        "Dragon Fruit",
        "fruits",
        "dragon-fruit",
        "kg",
        "https://images.unsplash.com/photo-1527325678964-54921661f888?auto=format&fit=crop&w=900&q=90",
        "Fresh exotic dragon fruit."
    ],

    [
        "Avocado",
        "fruits",
        "avocado",
        "kg",
        "https://images.unsplash.com/photo-1523049673857-eb18f1d7b578?auto=format&fit=crop&w=900&q=90",
        "Creamy premium avocado."
    ],

    [
        "Coconut",
        "fruits",
        "coconut",
        "box",
        "https://images.unsplash.com/photo-1449900302594-d41baf9c8b7c?auto=format&fit=crop&w=900&q=90",
        "Fresh coconut selected for quality."
    ],

    [
        "Cut Fruits",
        "fruits",
        "cut-fruits",
        "box",
        "https://images.unsplash.com/photo-1490474418585-ba9bad8fd0ea?auto=format&fit=crop&w=900&q=90",
        "Freshly prepared mixed cut fruits."
    ],

    [
        "Fresh Juice",
        "fruits",
        "fresh-juices",
        "box",
        "https://images.unsplash.com/photo-1600271886742-f049cd451bba?auto=format&fit=crop&w=900&q=90",
        "Freshly prepared fruit juice selection."
    ],


    /* VEGETABLES */

    [
        "Tomato",
        "vegetables",
        "tomato",
        "kg",
        "https://images.unsplash.com/photo-1546094096-0df4bcaaa337?auto=format&fit=crop&w=900&q=90",
        "Fresh red tomatoes."
    ],

    [
        "Potato",
        "vegetables",
        "potato",
        "kg",
        "https://images.unsplash.com/photo-1518977676601-b53f82aba655?auto=format&fit=crop&w=900&q=90",
        "Fresh quality potatoes."
    ],

    [
        "Onion",
        "vegetables",
        "onion",
        "kg",
        "https://images.unsplash.com/photo-1518977956812-cd3dbadaaf31?auto=format&fit=crop&w=900&q=90",
        "Fresh onions for everyday cooking."
    ],

    [
        "Carrot",
        "vegetables",
        "carrot",
        "kg",
        "https://images.unsplash.com/photo-1445282768818-728615cc910a?auto=format&fit=crop&w=900&q=90",
        "Fresh crisp carrots."
    ],

    [
        "Cucumber",
        "vegetables",
        "cucumber",
        "kg",
        "https://images.unsplash.com/photo-1604977048611-28b0b9b3c4f7?auto=format&fit=crop&w=900&q=90",
        "Fresh green cucumber."
    ],

    [
        "Capsicum",
        "vegetables",
        "capsicum",
        "kg",
        "https://images.unsplash.com/photo-1563565375-f3fdfdbefa83?auto=format&fit=crop&w=900&q=90",
        "Fresh colourful capsicum."
    ],

    [
        "Broccoli",
        "vegetables",
        "broccoli",
        "kg",
        "https://images.unsplash.com/photo-1459411621453-7b03977f4bfc?auto=format&fit=crop&w=900&q=90",
        "Fresh green broccoli."
    ],

    [
        "Cauliflower",
        "vegetables",
        "cauliflower",
        "kg",
        "https://images.unsplash.com/photo-1568584711271-8d9c1c9c5e3c?auto=format&fit=crop&w=900&q=90",
        "Fresh cauliflower."
    ],

    [
        "Cabbage",
        "vegetables",
        "cabbage",
        "kg",
        "https://images.unsplash.com/photo-1594282486552-05b4d80fbb9f?auto=format&fit=crop&w=900&q=90",
        "Fresh green cabbage."
    ],

    [
        "Spinach",
        "vegetables",
        "spinach",
        "box",
        "https://images.unsplash.com/photo-1576045057995-568f588f82fb?auto=format&fit=crop&w=900&q=90",
        "Fresh leafy spinach."
    ],

    [
        "Lettuce",
        "vegetables",
        "lettuce",
        "box",
        "https://images.unsplash.com/photo-1556801712-76c8eb07bbc9?auto=format&fit=crop&w=900&q=90",
        "Fresh crisp lettuce."
    ],

    [
        "Beans",
        "vegetables",
        "beans",
        "kg",
        "https://images.unsplash.com/photo-1567375698348-5d9d5ae99de0?auto=format&fit=crop&w=900&q=90",
        "Fresh green beans."
    ],

    [
        "Beetroot",
        "vegetables",
        "beetroot",
        "kg",
        "https://images.unsplash.com/photo-1607305387299-a3d9611cd469?auto=format&fit=crop&w=900&q=90",
        "Fresh beetroot."
    ],

    [
        "Eggplant",
        "vegetables",
        "eggplant",
        "kg",
        "https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?auto=format&fit=crop&w=900&q=90",
        "Fresh eggplant."
    ],

    [
        "Pumpkin",
        "vegetables",
        "pumpkin",
        "kg",
        "https://images.unsplash.com/photo-1501004318641-b39e6451bec6?auto=format&fit=crop&w=900&q=90",
        "Fresh pumpkin."
    ],


    /* FROZEN */

    [
        "Frozen Mixed Fruits",
        "frozen",
        "frozen-fruits",
        "box",
        "https://images.unsplash.com/photo-1502741338009-cac2772e18bc?auto=format&fit=crop&w=900&q=90",
        "Convenient frozen fruit selection."
    ],

    [
        "Frozen Vegetables",
        "frozen",
        "frozen-vegetables",
        "box",
        "https://images.unsplash.com/photo-1572449043416-55f4685c9bb7?auto=format&fit=crop&w=900&q=90",
        "Convenient frozen vegetable selection."
    ],

    [
        "Frozen Food",
        "frozen",
        "frozen-food",
        "box",
        "https://images.unsplash.com/photo-1601050690117-94f5f6fa8bd7?auto=format&fit=crop&w=900&q=90",
        "Frozen food selection."
    ],


    /* OTHER */

    [
        "Dates",
        "other",
        "dates",
        "box",
        "https://images.unsplash.com/photo-1596591868231-05e3c7bfc3a4?auto=format&fit=crop&w=900&q=90",
        "Premium dates."
    ],

    [
        "Mixed Nuts",
        "other",
        "nuts",
        "box",
        "https://images.unsplash.com/photo-1536591375731-6f6c0e5b0a2f?auto=format&fit=crop&w=900&q=90",
        "Premium mixed nuts."
    ],

    [
        "Dry Fruits",
        "other",
        "dry-fruits",
        "box",
        "https://images.unsplash.com/photo-1599599810694-57a9a5e1e1e4?auto=format&fit=crop&w=900&q=90",
        "Premium dry fruit selection."
    ]

];


$inserted = 0;
$existing = 0;


foreach ($products as $product) {

    $name = $product[0];
    $category = $product[1];
    $subcategory = $product[2];
    $oldUnit = $product[3];
    $image = $product[4];
    $description = $product[5];

    $check = $conn->prepare("
        SELECT id
        FROM products
        WHERE name = ?
        LIMIT 1
    ");

    $check->bind_param(
        "s",
        $name
    );

    $check->execute();

    $result = $check->get_result();

    $alreadyExists = $result->fetch_assoc();

    $check->close();


    if ($alreadyExists) {

        $existing++;

        continue;

    }


    $availableKg =
        $oldUnit === "kg" ? 1 : 0;

    $availableBox =
        $oldUnit === "box" ? 1 : 0;


    $boxLabel = "Box";

    $price = 0;

    $stock = 100;

    $status = 1;


    $stmt = $conn->prepare("
        INSERT INTO products
        (
            name,
            category,
            subcategory,
            image,
            price,
            unit,
            available_kg,
            available_box,
            box_label,
            stock,
            description,
            status
        )
        VALUES
        (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");


    $stmt->bind_param(
        "ssssdsiiisis",
        $name,
        $category,
        $subcategory,
        $image,
        $price,
        $oldUnit,
        $availableKg,
        $availableBox,
        $boxLabel,
        $stock,
        $description,
        $status
    );


    if ($stmt->execute()) {

        $inserted++;

    }


    $stmt->close();

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
    Import Products
</title>

<style>

body {
    margin: 0;
    font-family: Arial, sans-serif;
    background: #f4f7f5;
}

.box {
    max-width: 700px;
    margin: 80px auto;
    background: #fff;
    padding: 35px;
    border-radius: 18px;
    box-shadow: 0 15px 40px rgba(0,0,0,.08);
}

h1 {
    margin-top: 0;
    color: #1f6b45;
}

.number {
    font-size: 30px;
    font-weight: 800;
    color: #1f6b45;
}

a {
    display: inline-block;
    margin-top: 20px;
    padding: 12px 18px;
    background: #1f6b45;
    color: #fff;
    text-decoration: none;
    border-radius: 8px;
}

</style>

</head>

<body>

<div class="box">

    <h1>
        Product Import Complete
    </h1>

    <p>
        Products added:
    </p>

    <div class="number">
        <?= $inserted ?>
    </div>

    <p>
        Already existing:
        <strong><?= $existing ?></strong>
    </p>

    <p>
        Total catalogue should now contain
        <strong>38 products</strong>.
    </p>

    <a href="product-images.php">
        Open Product Images
    </a>

    <br>

    <a href="dashboard.php">
        Back to Dashboard
    </a>

</div>

</body>

</html>