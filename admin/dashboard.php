<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    header("Location: login.php");
    exit;
}

require_once "../config/database.php";


/* =========================================================
   DASHBOARD COUNTS
========================================================= */

$totalProducts = 0;
$newOrders = 0;
$totalOrders = 0;
$activeOffers = 0;
$feedbackCount = 0;
$averageRating = 0;


/* Products */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM products
    WHERE status = 1
");

if ($result) {

    $row = $result->fetch_assoc();

    $totalProducts =
        (int)$row["total"];

}


/* Orders */

$result = $conn->query("
    SELECT
        COUNT(*) AS total,
        SUM(
            CASE
                WHEN status = 'New'
                THEN 1
                ELSE 0
            END
        ) AS new_orders
    FROM orders
");

if ($result) {

    $row = $result->fetch_assoc();

    $totalOrders =
        (int)$row["total"];

    $newOrders =
        (int)($row["new_orders"] ?? 0);

}


/* Offers */

$result = $conn->query("
    SELECT COUNT(*) AS total
    FROM offers
    WHERE status = 1
");

if ($result) {

    $row = $result->fetch_assoc();

    $activeOffers =
        (int)$row["total"];

}


/* Feedback */

$result = $conn->query("
    SELECT
        COUNT(*) AS total,
        AVG(rating) AS average_rating
    FROM feedback
");

if ($result) {

    $row = $result->fetch_assoc();

    $feedbackCount =
        (int)$row["total"];

    $averageRating =
        round(
            (float)($row["average_rating"] ?? 0),
            1
        );

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
    Fresh Harvest Admin
</title>


<style>

* {
    box-sizing: border-box;
}


body {

    margin: 0;

    font-family:
        Arial,
        Helvetica,
        sans-serif;

    background:
        #f4f7f5;

    color:
        #1d2922;

}


/* =========================================================
   LAYOUT
========================================================= */

.admin-layout {

    min-height:
        100vh;

    display:
        flex;

}


/* =========================================================
   SIDEBAR
========================================================= */

.sidebar {

    width:
        245px;

    flex-shrink:
        0;

    background:
        #173f2c;

    color:
        #fff;

    padding:
        26px 16px;

    position:
        fixed;

    top: 0;
    bottom: 0;
    left: 0;

    overflow-y:
        auto;

}


.brand {

    padding:
        5px 12px 28px;

    border-bottom:
        1px solid
        rgba(255,255,255,.12);

    margin-bottom:
        22px;

}


.brand-main {

    font-size:
        21px;

    font-weight:
        800;

    letter-spacing:
        2px;

}


.brand-small {

    margin-top:
        4px;

    font-size:
        9px;

    letter-spacing:
        3px;

    opacity:
        .65;

}


.menu-title {

    font-size:
        10px;

    text-transform:
        uppercase;

    letter-spacing:
        1.5px;

    opacity:
        .55;

    padding:
        0 12px;

    margin-bottom:
        10px;

}


.menu {

    display:
        flex;

    flex-direction:
        column;

    gap:
        6px;

}


.menu button {

    width:
        100%;

    border:
        0;

    background:
        transparent;

    color:
        rgba(255,255,255,.75);

    text-align:
        left;

    padding:
        13px 14px;

    border-radius:
        10px;

    cursor:
        pointer;

    font-size:
        14px;

    font-weight:
        700;

    transition:
        .2s;

}


.menu button:hover {

    background:
        rgba(255,255,255,.08);

    color:
        #fff;

}


.menu button.active {

    background:
        #fff;

    color:
        #173f2c;

}


/* =========================================================
   MAIN
========================================================= */

.main {

    margin-left:
        245px;

    width:
        calc(100% - 245px);

    min-height:
        100vh;

    padding:
        32px 38px;

}


/* =========================================================
   TOP BAR
========================================================= */

.topbar {

    display:
        flex;

    justify-content:
        space-between;

    align-items:
        center;

    margin-bottom:
        32px;

}


.page-title h1 {

    margin:
        0;

    font-size:
        28px;

}


.page-title p {

    margin:
        7px 0 0;

    color:
        #718078;

    font-size:
        14px;

}


.admin-actions {

    display:
        flex;

    align-items:
        center;

    gap:
        12px;

}


.admin-name {

    font-size:
        13px;

    color:
        #68756d;

}


.logout {

    text-decoration:
        none;

    background:
        #fff;

    color:
        #1f6b45;

    padding:
        9px 15px;

    border:
        1px solid #dce5df;

    border-radius:
        8px;

    font-size:
        13px;

    font-weight:
        700;

}


/* =========================================================
   STAT CARDS
========================================================= */

.stats {

    display:
        grid;

    grid-template-columns:
        repeat(
            4,
            minmax(0,1fr)
        );

    gap:
        18px;

    margin-bottom:
        28px;

}


.stat-card {

    background:
        #fff;

    border:
        1px solid #e5ebe7;

    border-radius:
        16px;

    padding:
        22px;

    box-shadow:
        0 8px 25px
        rgba(0,0,0,.035);

}


.stat-label {

    font-size:
        12px;

    color:
        #718078;

    margin-bottom:
        10px;

}


.stat-value {

    font-size:
        30px;

    font-weight:
        800;

    color:
        #1f6b45;

}


.stat-note {

    margin-top:
        7px;

    color:
        #9aa39d;

    font-size:
        11px;

}


/* =========================================================
   DETAIL PANEL
========================================================= */

.detail-panel {

    background:
        #fff;

    border:
        1px solid #e4ebe6;

    border-radius:
        18px;

    padding:
        28px;

    min-height:
        430px;

    box-shadow:
        0 10px 30px
        rgba(0,0,0,.04);

}


.detail-panel h2 {

    margin:
        0 0 7px;

    font-size:
        22px;

}


.detail-description {

    color:
        #718078;

    margin:
        0 0 25px;

    font-size:
        14px;

}


/* =========================================================
   DETAIL GRID
========================================================= */

.detail-grid {

    display:
        grid;

    grid-template-columns:
        repeat(
            2,
            minmax(0,1fr)
        );

    gap:
        16px;

}


.detail-card {

    border:
        1px solid #e6ece8;

    border-radius:
        14px;

    padding:
        20px;

}


.detail-card h3 {

    margin:
        0 0 7px;

    font-size:
        16px;

}


.detail-card p {

    margin:
        0 0 15px;

    color:
        #718078;

    font-size:
        13px;

    line-height:
        1.5;

}


.detail-number {

    font-size:
        28px;

    font-weight:
        800;

    color:
        #1f6b45;

}


.open-btn {

    display:
        inline-block;

    text-decoration:
        none;

    background:
        #1f6b45;

    color:
        #fff;

    padding:
        10px 15px;

    border-radius:
        8px;

    font-size:
        12px;

    font-weight:
        700;

}


/* =========================================================
   WELCOME
========================================================= */

.welcome {

    background:
        linear-gradient(
            135deg,
            #edf7f0,
            #f8faf8
        );

    border:
        1px solid #dcebe1;

    border-radius:
        16px;

    padding:
        22px;

    margin-bottom:
        22px;

}


.welcome strong {

    color:
        #1f6b45;

}


/* =========================================================
   MOBILE
========================================================= */

@media (max-width: 900px) {

    .sidebar {

        width:
            205px;

    }


    .main {

        margin-left:
            205px;

        width:
            calc(100% - 205px);

        padding:
            25px;

    }


    .stats {

        grid-template-columns:
            repeat(2,1fr);

    }

}


@media (max-width: 650px) {

    .admin-layout {

        display:
            block;

    }


    .sidebar {

        position:
            relative;

        width:
            100%;

        min-height:
            auto;

    }


    .menu {

        display:
            grid;

        grid-template-columns:
            repeat(2,1fr);

    }


    .main {

        margin-left:
            0;

        width:
            100%;

        padding:
            18px;

    }


    .topbar {

        align-items:
            flex-start;

        gap:
            15px;

        flex-direction:
            column;

    }


    .stats {

        grid-template-columns:
            1fr;

    }


    .detail-grid {

        grid-template-columns:
            1fr;

    }

}

</style>

</head>


<body>


<div class="admin-layout">


    <!-- =====================================================
         LEFT SIDEBAR
    ====================================================== -->

    <aside class="sidebar">


        <div class="brand">

            <div class="brand-main">
                FRESH
            </div>

            <div class="brand-small">
                HARVEST ADMIN
            </div>

        </div>


        <div class="menu-title">
            Management
        </div>


        <nav class="menu">


            <button
                class="menu-item active"
                data-section="overview"
            >
                Dashboard
            </button>


            <button
                class="menu-item"
                data-section="products"
            >
                Products
            </button>


            <button
                class="menu-item"
                data-section="images"
            >
                Product Images
            </button>


            <button
                class="menu-item"
                data-section="orders"
            >
                Orders
            </button>


            <button
                class="menu-item"
                data-section="offers"
            >
                Weekend Offers
            </button>


            <button
                class="menu-item"
                data-section="feedback"
            >
                Feedback
            </button>


            <button
                class="menu-item"
                data-section="website"
            >
                Website Images & Animation
            </button>


        </nav>


    </aside>


    <!-- =====================================================
         MAIN
    ====================================================== -->

    <main class="main">


        <div class="topbar">


            <div class="page-title">

                <h1 id="pageTitle">
                    Dashboard
                </h1>

                <p id="pageSubtitle">
                    Manage your Fresh Harvest website from one place.
                </p>

            </div>


            <div class="admin-actions">

                <span class="admin-name">
                    Admin
                </span>

                <a
                    href="logout.php"
                    class="logout"
                >
                    Logout
                </a>

            </div>


        </div>


        <!-- =================================================
             STATISTICS
        ================================================== -->

        <div class="stats">


            <div class="stat-card">

                <div class="stat-label">
                    Active Products
                </div>

                <div class="stat-value">
                    <?= $totalProducts ?>
                </div>

                <div class="stat-note">
                    Products in catalogue
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-label">
                    New Orders
                </div>

                <div class="stat-value">
                    <?= $newOrders ?>
                </div>

                <div class="stat-note">
                    Waiting for confirmation
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-label">
                    Active Offers
                </div>

                <div class="stat-value">
                    <?= $activeOffers ?>
                </div>

                <div class="stat-note">
                    Weekend offers
                </div>

            </div>


            <div class="stat-card">

                <div class="stat-label">
                    Customer Rating
                </div>

                <div class="stat-value">
                    <?= number_format($averageRating, 1) ?>
                </div>

                <div class="stat-note">
                    <?= $feedbackCount ?> feedback entries
                </div>

            </div>


        </div>


        <!-- =================================================
             WELCOME
        ================================================== -->

        <div class="welcome">

            <strong>
                Fresh Harvest Admin
            </strong>

            <br>

            Use the menu on the left to manage products,
            product images, orders, weekend offers and
            customer feedback.

        </div>


        <!-- =================================================
             DYNAMIC DETAIL PANEL
        ================================================== -->

        <section
            class="detail-panel"
            id="detailPanel"
        >

        </section>


    </main>


</div>


<script>

const sections = {

    overview: {

        title: "Dashboard",

        subtitle:
            "Manage your Fresh Harvest website from one place.",

        html: `

            <h2>
                Welcome to Fresh Harvest
            </h2>

            <p class="detail-description">
                Select a section from the left menu to manage
                your website.
            </p>

            <div class="detail-grid">

                <div class="detail-card">

                    <h3>
                        Products
                    </h3>

                    <p>
                        Add, edit, remove products and manage
                        their stock and details.
                    </p>

                    <span class="detail-number">
                        <?= $totalProducts ?>
                    </span>

                    <br><br>

                    <a
                        href="products.php"
                        class="open-btn"
                    >
                        Manage Products
                    </a>

                </div>


                <div class="detail-card">

                    <h3>
                        Product Images
                    </h3>

                    <p>
                        Change product images without editing
                        frontend code.
                    </p>

                    <a
                        href="product-images.php"
                        class="open-btn"
                    >
                        Manage Images
                    </a>

                </div>


                <div class="detail-card">

                    <h3>
                        Orders
                    </h3>

                    <p>
                        View customer orders and update
                        their status.
                    </p>

                    <span class="detail-number">
                        <?= $totalOrders ?>
                    </span>

                    <br><br>

                    <a
                        href="orders.php"
                        class="open-btn"
                    >
                        View Orders
                    </a>

                </div>


                <div class="detail-card">

                    <h3>
                        Weekend Offers
                    </h3>

                    <p>
                        Create and manage weekly offer
                        images and products.
                    </p>

                    <a
                        href="offers.php"
                        class="open-btn"
                    >
                        Manage Offers
                    </a>

                </div>

            </div>

        `

    },


    products: {

        title: "Products",

        subtitle:
            "Manage your product catalogue.",

        html: `

            <h2>
                Product Management
            </h2>

            <p class="detail-description">
                Add new products, edit existing products,
                manage stock and product details.
            </p>

            <div class="detail-grid">

                <div class="detail-card">

                    <h3>
                        Catalogue
                    </h3>

                    <p>
                        Current active products:
                    </p>

                    <div class="detail-number">
                        <?= $totalProducts ?>
                    </div>

                    <br>

                    <a
                        href="products.php"
                        class="open-btn"
                    >
                        Open Products
                    </a>

                </div>


                <div class="detail-card">

                    <h3>
                        Add Product
                    </h3>

                    <p>
                        Add a new fruit, vegetable, frozen
                        product or other item.
                    </p>

                    <a
                        href="add-product.php"
                        class="open-btn"
                    >
                        Add Product
                    </a>

                </div>

            </div>

        `

    },


    images: {

        title: "Product Images",

        subtitle:
            "Change product images from one place.",

        html: `

            <h2>
                Product Image Manager
            </h2>

            <p class="detail-description">
                All products are displayed with their current
                image. Upload a new image whenever the customer
                wants a change.
            </p>

            <div class="detail-card">

                <h3>
                    Product Images
                </h3>

                <p>
                    Open the image manager to see all catalogue
                    products and change their images.
                </p>

                <a
                    href="product-images.php"
                    class="open-btn"
                >
                    Open Image Manager
                </a>

            </div>

        `

    },


    orders: {

        title: "Orders",

        subtitle:
            "View and manage customer orders.",

        html: `

            <h2>
                Customer Orders
            </h2>

            <p class="detail-description">
                View customer information, ordered products
                and update order status.
            </p>

            <div class="detail-card">

                <h3>
                    Total Orders
                </h3>

                <div class="detail-number">
                    <?= $totalOrders ?>
                </div>

                <br>

                <a
                    href="orders.php"
                    class="open-btn"
                >
                    View Orders
                </a>

            </div>

        `

    },


    offers: {

        title: "Weekend Offers",

        subtitle:
            "Manage weekly promotional content.",

        html: `

            <h2>
                Weekend Offers
            </h2>

            <p class="detail-description">
                Add offer images, descriptions, products,
                prices and dates.
            </p>

            <div class="detail-card">

                <h3>
                    Active Offers
                </h3>

                <div class="detail-number">
                    <?= $activeOffers ?>
                </div>

                <br>

                <a
                    href="offers.php"
                    class="open-btn"
                >
                    Manage Offers
                </a>

            </div>

        `

    },


    feedback: {

        title: "Feedback",

        subtitle:
            "Review customer ratings and comments.",

        html: `

            <h2>
                Customer Feedback
            </h2>

            <p class="detail-description">
                Review ratings and comments submitted by
                customers.
            </p>

            <div class="detail-grid">

                <div class="detail-card">

                    <h3>
                        Feedback
                    </h3>

                    <div class="detail-number">
                        <?= $feedbackCount ?>
                    </div>

                </div>


                <div class="detail-card">

                    <h3>
                        Average Rating
                    </h3>

                    <div class="detail-number">
                        <?= number_format($averageRating, 1) ?>
                        / 5
                    </div>

                </div>

            </div>

            <br>

            <a
                href="feedback.php"
                class="open-btn"
            >
                View Feedback
            </a>

        `

    },


    website: {

        title: "Website Images & Animation",

        subtitle:
            "Manage the visual content of the website.",

        html: `

            <h2>
                Website Images & Animation
            </h2>

            <p class="detail-description">
                This section will control the main category
                images, hero animation images and other
                website visual content.
            </p>

            <div class="detail-grid">

                <div class="detail-card">

                    <h3>
                        Main Categories
                    </h3>

                    <p>
                        All, Fruits, Vegetables, Frozen and
                        Other category images.
                    </p>

                    <a
                        href="#"
                        class="open-btn"
                    >
                        Manage Category Images
                    </a>

                </div>


                <div class="detail-card">

                    <h3>
                        Hero Animation
                    </h3>

                    <p>
                        Change hero images and control the
                        animation content.
                    </p>

                    <a
                        href="#"
                        class="open-btn"
                    >
                        Manage Animation
                    </a>

                </div>

            </div>

        `

    }

};


const menuItems =
    document.querySelectorAll(
        ".menu-item"
    );


const detailPanel =
    document.getElementById(
        "detailPanel"
    );


const pageTitle =
    document.getElementById(
        "pageTitle"
    );


const pageSubtitle =
    document.getElementById(
        "pageSubtitle"
    );


function showSection(section) {

    const data =
        sections[section];

    if (!data) {
        return;
    }


    pageTitle.textContent =
        data.title;


    pageSubtitle.textContent =
        data.subtitle;


    detailPanel.innerHTML =
        data.html;


    menuItems.forEach(button => {

        button.classList.toggle(
            "active",
            button.dataset.section === section
        );

    });

}


menuItems.forEach(button => {

    button.addEventListener(
        "click",
        function() {

            showSection(
                this.dataset.section
            );

        }
    );

});


showSection("overview");

</script>


</body>

</html>