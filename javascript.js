/* =========================================================
   FRESH HARVEST
   COMPLETE FRONTEND JAVASCRIPT
   MYSQL + 38 DEFAULT PRODUCTS
========================================================= */

"use strict";


/* =========================================================
   CONFIG
========================================================= */

const API_URL = "api/products.php";

const WHATSAPP_NUMBER = "971554480623";

const CART_STORAGE_KEY = "freshHarvestCart";

const FALLBACK_IMAGE =
    "https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=900&q=85";


/* =========================================================
   38 DEFAULT PRODUCTS
   These remain visible even when MySQL has only a few products.
========================================================= */

const defaultProducts = [

    /* =========================
       FRUITS
    ========================= */

    {
        id: 1,
        name: "Apple",
        category: "fruits",
        subcategory: "apple",
        unit: "kg",
        image: "https://images.unsplash.com/photo-1560806887-1e4cd0b6cbd6?auto=format&fit=crop&w=900&q=90",
        description: "Fresh, crisp apples selected for everyday freshness."
    },

    {
        id: 2,
        name: "Banana",
        category: "fruits",
        subcategory: "banana",
        unit: "kg",
        image: "https://images.unsplash.com/photo-1571771894821-ce9b6c11b08e?auto=format&fit=crop&w=900&q=90",
        description: "Naturally sweet bananas with a smooth texture."
    },

    {
        id: 3,
        name: "Mango",
        category: "fruits",
        subcategory: "mango",
        unit: "kg",
        image: "https://images.unsplash.com/photo-1553279768-865429fa0078?auto=format&fit=crop&w=900&q=90",
        description: "Juicy premium mangoes with rich tropical flavour."
    },

    {
        id: 4,
        name: "Orange",
        category: "fruits",
        subcategory: "orange",
        unit: "kg",
        image: "https://images.unsplash.com/photo-1547514701-42782101795e?auto=format&fit=crop&w=900&q=90",
        description: "Bright and juicy oranges."
    },

    {
        id: 5,
        name: "Papaya",
        category: "fruits",
        subcategory: "papaya",
        unit: "kg",
        image: "https://images.unsplash.com/photo-1526318472351-c75fcf070305?auto=format&fit=crop&w=900&q=90",
        description: "Fresh ripe papaya with a naturally soft texture."
    },

    {
        id: 6,
        name: "Pineapple",
        category: "fruits",
        subcategory: "pineapple",
        unit: "box",
        image: "https://images.unsplash.com/photo-1550258987-190a2d41a8ba?auto=format&fit=crop&w=900&q=90",
        description: "Fresh tropical pineapple."
    },

    {
        id: 7,
        name: "Watermelon",
        category: "fruits",
        subcategory: "watermelon",
        unit: "kg",
        image: "https://images.unsplash.com/photo-1563114773-84221bd62daa?auto=format&fit=crop&w=900&q=90",
        description: "Refreshing watermelon selected for freshness."
    },

    {
        id: 8,
        name: "Grapes",
        category: "fruits",
        subcategory: "grapes",
        unit: "box",
        image: "https://images.unsplash.com/photo-1537640538966-79f369143f8f?auto=format&fit=crop&w=900&q=90",
        description: "Fresh, naturally sweet grapes."
    },

    {
        id: 9,
        name: "Kiwi",
        category: "fruits",
        subcategory: "kiwi",
        unit: "box",
        image: "https://images.unsplash.com/photo-1585059895524-72359e06133a?auto=format&fit=crop&w=900&q=90",
        description: "Fresh green kiwi fruits."
    },

    {
        id: 10,
        name: "Strawberry",
        category: "fruits",
        subcategory: "strawberry",
        unit: "box",
        image: "https://images.unsplash.com/photo-1464965911861-746a04b4bca6?auto=format&fit=crop&w=900&q=90",
        description: "Fresh premium strawberries."
    },

    {
        id: 11,
        name: "Guava",
        category: "fruits",
        subcategory: "guava",
        unit: "kg",
        image: "https://images.unsplash.com/photo-1536511132770-e5058c7e8c46?auto=format&fit=crop&w=900&q=90",
        description: "Fresh guava with natural tropical flavour."
    },

    {
        id: 12,
        name: "Pomegranate",
        category: "fruits",
        subcategory: "pomegranate",
        unit: "kg",
        image: "https://images.unsplash.com/photo-1541344999736-83eca272f6fc?auto=format&fit=crop&w=900&q=90",
        description: "Ruby-red pomegranate packed with flavour."
    },

    {
        id: 13,
        name: "Dragon Fruit",
        category: "fruits",
        subcategory: "dragon-fruit",
        unit: "kg",
        image: "https://images.unsplash.com/photo-1527325678964-54921661f888?auto=format&fit=crop&w=900&q=90",
        description: "Fresh exotic dragon fruit."
    },

    {
        id: 14,
        name: "Avocado",
        category: "fruits",
        subcategory: "avocado",
        unit: "kg",
        image: "https://images.unsplash.com/photo-1523049673857-eb18f1d7b578?auto=format&fit=crop&w=900&q=90",
        description: "Creamy premium avocado."
    },

    {
        id: 15,
        name: "Coconut",
        category: "fruits",
        subcategory: "coconut",
        unit: "box",
        image: "https://images.unsplash.com/photo-1449900302594-d41baf9c8b7c?auto=format&fit=crop&w=900&q=90",
        description: "Fresh coconut selected for quality."
    },

    {
        id: 16,
        name: "Cut Fruits",
        category: "fruits",
        subcategory: "cut-fruits",
        unit: "box",
        image: "https://images.unsplash.com/photo-1490474418585-ba9bad8fd0ea?auto=format&fit=crop&w=900&q=90",
        description: "Freshly prepared mixed cut fruits."
    },

    {
        id: 17,
        name: "Fresh Juice",
        category: "fruits",
        subcategory: "fresh-juices",
        unit: "box",
        image: "https://images.unsplash.com/photo-1600271886742-f049cd451bba?auto=format&fit=crop&w=900&q=90",
        description: "Freshly prepared fruit juice selection."
    },


    /* =========================
       VEGETABLES
    ========================= */

    {
        id: 18,
        name: "Tomato",
        category: "vegetables",
        subcategory: "tomato",
        unit: "kg",
        image: "https://images.unsplash.com/photo-1546094096-0df4bcaaa337?auto=format&fit=crop&w=900&q=90",
        description: "Fresh red tomatoes."
    },

    {
        id: 19,
        name: "Potato",
        category: "vegetables",
        subcategory: "potato",
        unit: "kg",
        image: "https://images.unsplash.com/photo-1518977676601-b53f82aba655?auto=format&fit=crop&w=900&q=90",
        description: "Fresh quality potatoes."
    },

    {
        id: 20,
        name: "Onion",
        category: "vegetables",
        subcategory: "onion",
        unit: "kg",
        image: "https://images.unsplash.com/photo-1518977956812-cd3dbadaaf31?auto=format&fit=crop&w=900&q=90",
        description: "Fresh onions for everyday cooking."
    },

    {
        id: 21,
        name: "Carrot",
        category: "vegetables",
        subcategory: "carrot",
        unit: "kg",
        image: "https://images.unsplash.com/photo-1445282768818-728615cc910a?auto=format&fit=crop&w=900&q=90",
        description: "Fresh crisp carrots."
    },

    {
        id: 22,
        name: "Cucumber",
        category: "vegetables",
        subcategory: "cucumber",
        unit: "kg",
        image: "https://images.unsplash.com/photo-1604977048611-28b0b9b3c4f7?auto=format&fit=crop&w=900&q=90",
        description: "Fresh green cucumber."
    },

    {
        id: 23,
        name: "Capsicum",
        category: "vegetables",
        subcategory: "capsicum",
        unit: "kg",
        image: "https://images.unsplash.com/photo-1563565375-f3fdfdbefa83?auto=format&fit=crop&w=900&q=90",
        description: "Fresh colourful capsicum."
    },

    {
        id: 24,
        name: "Broccoli",
        category: "vegetables",
        subcategory: "broccoli",
        unit: "kg",
        image: "https://images.unsplash.com/photo-1459411621453-7b03977f4bfc?auto=format&fit=crop&w=900&q=90",
        description: "Fresh green broccoli."
    },

    {
        id: 25,
        name: "Cauliflower",
        category: "vegetables",
        subcategory: "cauliflower",
        unit: "kg",
        image: "https://images.unsplash.com/photo-1568584711271-8d9c1c9c5e3c?auto=format&fit=crop&w=900&q=90",
        description: "Fresh cauliflower."
    },

    {
        id: 26,
        name: "Cabbage",
        category: "vegetables",
        subcategory: "cabbage",
        unit: "kg",
        image: "https://images.unsplash.com/photo-1594282486552-05b4d80fbb9f?auto=format&fit=crop&w=900&q=90",
        description: "Fresh green cabbage."
    },

    {
        id: 27,
        name: "Spinach",
        category: "vegetables",
        subcategory: "spinach",
        unit: "box",
        image: "https://images.unsplash.com/photo-1576045057995-568f588f82fb?auto=format&fit=crop&w=900&q=90",
        description: "Fresh leafy spinach."
    },

    {
        id: 28,
        name: "Lettuce",
        category: "vegetables",
        subcategory: "lettuce",
        unit: "box",
        image: "https://images.unsplash.com/photo-1556801712-76c8eb07bbc9?auto=format&fit=crop&w=900&q=90",
        description: "Fresh crisp lettuce."
    },

    {
        id: 29,
        name: "Beans",
        category: "vegetables",
        subcategory: "beans",
        unit: "kg",
        image: "https://images.unsplash.com/photo-1567375698348-5d9d5ae99de0?auto=format&fit=crop&w=900&q=90",
        description: "Fresh green beans."
    },

    {
        id: 30,
        name: "Beetroot",
        category: "vegetables",
        subcategory: "beetroot",
        unit: "kg",
        image: "https://images.unsplash.com/photo-1607305387299-a3d9611cd469?auto=format&fit=crop&w=900&q=90",
        description: "Fresh beetroot."
    },

    {
        id: 31,
        name: "Eggplant",
        category: "vegetables",
        subcategory: "eggplant",
        unit: "kg",
        image: "https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?auto=format&fit=crop&w=900&q=90",
        description: "Fresh eggplant."
    },

    {
        id: 32,
        name: "Pumpkin",
        category: "vegetables",
        subcategory: "pumpkin",
        unit: "kg",
        image: "https://images.unsplash.com/photo-1501004318641-b39e6451bec6?auto=format&fit=crop&w=900&q=90",
        description: "Fresh pumpkin."
    },


    /* =========================
       FROZEN
    ========================= */

    {
        id: 33,
        name: "Frozen Mixed Fruits",
        category: "frozen",
        subcategory: "frozen-fruits",
        unit: "box",
        image: "https://images.unsplash.com/photo-1502741338009-cac2772e18bc?auto=format&fit=crop&w=900&q=90",
        description: "Convenient frozen fruit selection."
    },

    {
        id: 34,
        name: "Frozen Vegetables",
        category: "frozen",
        subcategory: "frozen-vegetables",
        unit: "box",
        image: "https://images.unsplash.com/photo-1572449043416-55f4685c9bb7?auto=format&fit=crop&w=900&q=90",
        description: "Convenient frozen vegetable selection."
    },

    {
        id: 35,
        name: "Frozen Food",
        category: "frozen",
        subcategory: "frozen-food",
        unit: "box",
        image: "https://images.unsplash.com/photo-1601050690117-94f5f6fa8bd7?auto=format&fit=crop&w=900&q=90",
        description: "Frozen food selection."
    },


    /* =========================
       OTHER
    ========================= */

    {
        id: 36,
        name: "Dates",
        category: "other",
        subcategory: "dates",
        unit: "box",
        image: "https://images.unsplash.com/photo-1596591868231-05e3c7bfc3a4?auto=format&fit=crop&w=900&q=90",
        description: "Premium dates."
    },

    {
        id: 37,
        name: "Mixed Nuts",
        category: "other",
        subcategory: "nuts",
        unit: "box",
        image: "https://images.unsplash.com/photo-1536591375731-6f6c0e5b0a2f?auto=format&fit=crop&w=900&q=90",
        description: "Premium mixed nuts."
    },

    {
        id: 38,
        name: "Dry Fruits",
        category: "other",
        subcategory: "dry-fruits",
        unit: "box",
        image: "https://images.unsplash.com/photo-1599599810694-57a9a5e1e1e4?auto=format&fit=crop&w=900&q=90",
        description: "Premium dry fruit selection."
    }

];


/* =========================================================
   HERO SLIDES
========================================================= */

const heroSlides = [

    {
        title: "Naturally fresh.<br>Beautifully delivered.",
        description: "Premium fruits selected with care for your everyday table.",
        label: "Fresh Fruits",
        category: "fruits",
        image: "https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=1500&q=90",
        bg: "#eef5ed",
        accent: "#168b5c"
    },

    {
        title: "Fresh vegetables.<br>Every single day.",
        description: "Crisp vegetables selected for freshness, quality and flavour.",
        label: "Fresh Vegetables",
        category: "vegetables",
        image: "https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=1500&q=90",
        bg: "#edf5ec",
        accent: "#348b4d"
    },

    {
        title: "Tropical flavour.<br>At its freshest.",
        description: "Discover beautiful seasonal fruits for your home.",
        label: "Tropical Fruits",
        category: "fruits",
        image: "https://images.unsplash.com/photo-1553279768-865429fa0078?auto=format&fit=crop&w=1500&q=90",
        bg: "#fff1df",
        accent: "#d27728"
    },

    {
        title: "Colour your table.<br>Eat beautifully.",
        description: "Fresh produce chosen to make everyday meals better.",
        label: "Fresh Produce",
        category: "vegetables",
        image: "https://images.unsplash.com/photo-1518977676601-b53f82aba655?auto=format&fit=crop&w=1500&q=90",
        bg: "#f0eee5",
        accent: "#8b7039"
    },

    {
        title: "A better way<br>to shop fresh.",
        description: "Explore our growing selection of premium produce.",
        label: "Premium Selection",
        category: "fruits",
        image: "https://images.unsplash.com/photo-1490474418585-ba9bad8fd0ea?auto=format&fit=crop&w=1500&q=90",
        bg: "#f2eee4",
        accent: "#75643d"
    }

];


/* =========================================================
   APPLICATION STATE
========================================================= */

let allProducts = [...defaultProducts];

let selectedCategory = "all";

let selectedSubcategory = "all";

let searchTerm = "";

let sortMode = "default";

let currentProductId = null;

let currentHeroIndex = 0;

let heroMode = "auto";

let heroTimer = null;

let selectedRating = 0;

let toastTimer = null;


/* =========================================================
   CART
========================================================= */

let cart = [];

try {

    cart = JSON.parse(
        localStorage.getItem(CART_STORAGE_KEY) || "[]"
    );

    if (!Array.isArray(cart)) {
        cart = [];
    }

} catch (error) {

    cart = [];

}


/* =========================================================
   DOM HELPER
========================================================= */

function $(id) {

    return document.getElementById(id);

}


/* =========================================================
   DOM ELEMENTS
========================================================= */

const productGrid =
    $("productGrid");

const productCount =
    $("productCount");

const productsTitle =
    $("productsTitle");

const productsDescription =
    $("productsDescription");

const emptyProducts =
    $("emptyProducts");

const cartCount =
    $("cartCount");

const cartItems =
    $("cartItems");

const cartHeaderCount =
    $("cartHeaderCount");

const invoiceItemCount =
    $("invoiceItemCount");

const suggestionGrid =
    $("suggestionGrid");

const heroContainer =
    $("heroContainer");

const heroSection =
    document.querySelector(".hero-section");

const heroImage =
    $("heroImage");

const heroTitle =
    $("heroTitle");

const heroDescription =
    $("heroDescription");

const heroSlideLabel =
    $("heroSlideLabel");

const heroDots =
    $("heroDots");

const toast =
    $("toast");

const toastText =
    $("toastText");


/* =========================================================
   SAFE HTML
========================================================= */

function escapeHTML(value) {

    return String(value ?? "")
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");

}


/* =========================================================
   NORMALISE PRODUCT
========================================================= */

function normaliseProduct(product) {

    return {

        id: Number(product.id),

        name: String(
            product.name || "Unnamed Product"
        ),

        category: String(
            product.category || "other"
        ).toLowerCase(),

        subcategory: String(
            product.subcategory || ""
        ).toLowerCase(),

        unit:
            String(product.unit || "kg").toLowerCase()
            === "box"
                ? "box"
                : "kg",

        price: Number(product.price || 0),

        stock: Number(product.stock || 0),

        available_kg:
            Number(product.available_kg ?? (String(product.unit || "kg").toLowerCase() === "kg" ? 1 : 0)),

        available_box:
            Number(product.available_box ?? (String(product.unit || "kg").toLowerCase() === "box" ? 1 : 0)),

        box_label:
            String(product.box_label || "Box"),

        image:
            product.image
                ? String(product.image)
                : FALLBACK_IMAGE,

        description:
            product.description
                ? String(product.description)
                : "Fresh premium quality."

    };

}


/* =========================================================
   PRODUCT IMAGE PATH
========================================================= */

function getProductImage(product) {

    if (!product || !product.image) {
        return FALLBACK_IMAGE;
    }

    let image = String(product.image).trim();

    if (!image) {
        return FALLBACK_IMAGE;
    }

    if (
        image.startsWith("http://") ||
        image.startsWith("https://") ||
        image.startsWith("data:")
    ) {

        return image;

    }

    image = image.replace(/\\/g, "/");

    if (image.startsWith("/jasir3/")) {
        return image;
    }

    if (image.startsWith("/uploads/")) {
        return "/jasir3" + image;
    }

    if (image.startsWith("uploads/")) {
        return image;
    }

    if (image.startsWith("./uploads/")) {
        return image.substring(2);
    }

    return image;

}


/* =========================================================
   IMAGE FALLBACK
========================================================= */

function imageFallback(img) {

    if (!img) return;

    img.addEventListener(
        "error",
        function () {

            if (
                this.dataset.fallbackUsed === "true"
            ) {
                return;
            }

            this.dataset.fallbackUsed = "true";

            this.src = FALLBACK_IMAGE;

        }
    );

}


/* =========================================================
   MERGE MYSQL PRODUCTS WITH 38 DEFAULT PRODUCTS
========================================================= */

function mergeProducts(databaseProducts) {

    const merged = [...defaultProducts];

    if (!Array.isArray(databaseProducts)) {
        return merged.map(normaliseProduct);
    }

    databaseProducts.forEach(rawProduct => {

        const dbProduct =
            normaliseProduct(rawProduct);

        if (!dbProduct.id) {
            return;
        }

        const sameNameIndex =
            merged.findIndex(product => {

                return (
                    product.name.toLowerCase()
                    === dbProduct.name.toLowerCase()
                );

            });

        if (sameNameIndex !== -1) {

            /*
              If Mango already exists in the 38 default
              products, replace it with the MySQL version.
            */

            merged[sameNameIndex] = dbProduct;

        } else {

            /*
              New product added from admin panel
              gets appended to the product list.
            */

            merged.push(dbProduct);

        }

    });

    return merged.map(normaliseProduct);

}


/* =========================================================
   LOAD PRODUCTS FROM MYSQL
========================================================= */

async function loadProducts() {

    if (!productGrid) {
        console.error(
            "productGrid element not found."
        );
        return;
    }

    productGrid.innerHTML = `
        <div class="products-loading">
            Loading fresh products...
        </div>
    `;

    try {

        const response =
            await fetch(
                API_URL,
                {
                    method: "GET",
                    cache: "no-store"
                }
            );

        if (!response.ok) {
            throw new Error(
                "API HTTP error: " +
                response.status
            );
        }

        const data =
            await response.json();

        if (!data.success) {
            throw new Error(
                "API returned success=false"
            );
        }

        allProducts =
            mergeProducts(
                data.products || []
            );

        console.log(
            "Products loaded:",
            allProducts
        );

        renderProducts();

        updateCartUI();

    } catch (error) {

        console.error(
            "Product API error:",
            error
        );

        /*
          IMPORTANT:
          Even if MySQL/API fails,
          the original 38 products remain visible.
        */

        allProducts =
            defaultProducts.map(
                normaliseProduct
            );

        renderProducts();

        updateCartUI();

    }

}


/* =========================================================
   FILTER PRODUCTS
========================================================= */

function getFilteredProducts() {

    let result =
        [...allProducts];


    /* CATEGORY */

    if (
        selectedCategory &&
        selectedCategory !== "all"
    ) {

        result =
            result.filter(
                product =>
                    product.category
                    === selectedCategory
            );

    }


    /* SUBCATEGORY */

    if (
        selectedSubcategory &&
        selectedSubcategory !== "all" &&
        selectedSubcategory !== "all-vegetables"
    ) {

        result =
            result.filter(
                product =>
                    String(product.subcategory || "")
                        .trim()
                        .toLowerCase()
                    === String(selectedSubcategory || "")
                        .trim()
                        .toLowerCase()
            );

    }


    if (
        selectedSubcategory
        === "all-vegetables"
    ) {

        result =
            result.filter(
                product =>
                    product.category
                    === "vegetables"
            );

    }


    /* SEARCH */

    const search =
        searchTerm
            .trim()
            .toLowerCase();

    if (search) {

        result =
            result.filter(product => {

                return (

                    product.name
                        .toLowerCase()
                        .includes(search)

                    ||

                    product.category
                        .toLowerCase()
                        .includes(search)

                    ||

                    product.subcategory
                        .toLowerCase()
                        .includes(search)

                    ||

                    product.description
                        .toLowerCase()
                        .includes(search)

                );

            });

    }


    /* SORT */

    if (sortMode === "az") {

        result.sort(
            (a, b) =>
                a.name.localeCompare(
                    b.name
                )
        );

    }

    if (sortMode === "za") {

        result.sort(
            (a, b) =>
                b.name.localeCompare(
                    a.name
                )
        );

    }


    return result;

}


/* =========================================================
   CATEGORY NAME
========================================================= */

function formatCategory(category) {

    const names = {

        fruits: "Fresh Fruits",

        vegetables: "Fresh Vegetables",

        frozen: "Frozen",

        other: "Other"

    };

    return (
        names[category]
        || category
        || "Product"
    );

}


/* =========================================================
   PRODUCT TITLE
========================================================= */

function updateProductHeading() {

    if (!productsTitle) return;

    if (searchTerm.trim()) {

        productsTitle.textContent =
            "Search Results";

        if (productsDescription) {

            productsDescription.textContent =
                "Products matching your search.";

        }

        return;

    }


    const titles = {

        all: "All Products",

        fruits: "Fresh Fruits",

        vegetables: "Fresh Vegetables",

        frozen: "Frozen Selection",

        other: "Other Products"

    };


    productsTitle.textContent =
        titles[selectedCategory]
        || "All Products";


    if (productsDescription) {

        const descriptions = {

            all:
                "Carefully selected produce for your everyday needs.",

            fruits:
                "Fresh fruits selected for quality and freshness.",

            vegetables:
                "Fresh vegetables for your everyday table.",

            frozen:
                "Convenient frozen food selections.",

            other:
                "Dates, nuts and other selected products."

        };

        productsDescription.textContent =
            descriptions[selectedCategory]
            || descriptions.all;

    }

}


/* =========================================================
   RENDER PRODUCTS
========================================================= */

function renderProducts() {

    if (!productGrid) {
        return;
    }

    const filtered =
        getFilteredProducts();


    productGrid.innerHTML = "";


    if (productCount) {

        productCount.textContent =
            `${filtered.length} product${filtered.length !== 1 ? "s" : ""}`;

    }


    updateProductHeading();


    if (
        emptyProducts
    ) {

        emptyProducts.hidden =
            filtered.length !== 0;

    }


    if (filtered.length === 0) {

        return;

    }


    filtered.forEach(product => {

        const card =
            document.createElement("article");

        card.className =
            "product-card";

        card.dataset.productId =
            String(product.id);

        card.dataset.category =
            product.category;

        card.dataset.subcategory =
            product.subcategory;


        const productImage =
            getProductImage(product);


        card.innerHTML = `

            <div
                class="product-image-wrap"
                data-product-action="open"
                data-product-id="${product.id}"
            >

                <img
                    class="product-image"
                    src="${escapeHTML(productImage)}"
                    alt="${escapeHTML(product.name)}"
                    loading="lazy"
                >

                <button
                    type="button"
                    class="wishlist-btn"
                    data-product-action="wishlist"
                    data-product-id="${product.id}"
                    aria-label="Add to wishlist"
                >

                    <svg
                        viewBox="0 0 24 24"
                        width="22"
                        height="22"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="2"
                    >

                        <path
                            d="
                            M20.8 8.6
                            C20.8 13.7 12 20 12 20
                            S3.2 13.7 3.2 8.6
                            A4.8 4.8 0 0 1 12 5.7
                            A4.8 4.8 0 0 1 20.8 8.6Z
                            "
                        ></path>

                    </svg>

                </button>

            </div>


            <div class="product-info">

                <span class="product-category">
                    ${escapeHTML(
                        formatCategory(
                            product.category
                        )
                    )}
                </span>


                <h3>
                    ${escapeHTML(product.name)}
                </h3>


                <p class="product-description">
                    ${escapeHTML(
                        product.description
                    )}
                </p>


                <div class="product-bottom">

                   <div class="product-pricing">

  ${
    product.price && !isNaN(product.price) && Number(product.price) > 0
    ? `
        <span class="product-price">
            AED ${Number(product.price).toFixed(2)}
        </span>
      `
    : `
        <span class="product-price">
            KG/BOX
        </span>
      `
}


</div>






                    <button
                        type="button"
                        class="add-product-btn"
                        data-product-action="add"
                        data-product-id="${product.id}"
                    >
                        ADD
                    </button>

                </div>

            </div>

        `;


        productGrid.appendChild(card);


        const img =
            card.querySelector("img");

        imageFallback(img);

    });

}


/* =========================================================
   PRODUCT GRID CLICK
========================================================= */

if (productGrid) {

    productGrid.addEventListener(
        "click",
        function(event) {

            const actionElement =
                event.target.closest(
                    "[data-product-action]"
                );

            if (!actionElement) {
                return;
            }


            const action =
                actionElement.dataset.productAction;

            const productId =
                Number(
                    actionElement.dataset.productId
                );


            if (!productId) {
                return;
            }


            /* ADD */

            if (action === "add") {

                openProductModal(
                    productId
                );

                return;

            }


            /* WISHLIST */

            if (action === "wishlist") {

                event.stopPropagation();

                toggleWishlist(
                    actionElement
                );

                return;

            }


            /* OPEN PRODUCT */

            if (action === "open") {

                openProductModal(
                    productId
                );

            }

        }
    );


    /*
      Also allow clicking directly on
      the image itself.
    */

    productGrid.addEventListener(
        "click",
        function(event) {

            const img =
                event.target.closest(
                    ".product-image"
                );

            if (!img) {
                return;
            }

            const wrap =
                img.closest(
                    ".product-image-wrap"
                );

            if (!wrap) {
                return;
            }

            const id =
                Number(
                    wrap.dataset.productId
                );

            if (id) {
                openProductModal(id);
            }

        }
    );

}


/* =========================================================
   WISHLIST
========================================================= */

function toggleWishlist(button) {

    if (!button) return;

    button.classList.toggle("liked");

}


/* =========================================================
   PRODUCT UNIT BUTTONS
========================================================= */

function renderModalUnits(product) {

    const container = $("modalUnitButtons");

    if (!container) {
        return;
    }

    container.innerHTML = "";

    const units = [];

    if (Number(product.available_kg) === 1) {
        units.push({
            value: "kg",
            label: "KG"
        });
    }

    if (Number(product.available_box) === 1) {
        units.push({
            value: "box",
            label: product.box_label || "Box"
        });
    }

    /* Backward compatibility for old/default products. */
    if (!units.length) {
        units.push({
            value: product.unit === "box" ? "box" : "kg",
            label: product.unit === "box"
                ? (product.box_label || "Box")
                : "KG"
        });
    }

    units.forEach((unit, index) => {

        const button = document.createElement("button");
        button.type = "button";
        button.className = "unit-select-btn";
        button.dataset.unit = unit.value;
        button.textContent = unit.label;

        if (index === 0) {
            button.classList.add("active");
        }

        container.appendChild(button);
    });

    window.selectedProductUnit =
        container.querySelector(".unit-select-btn")?.dataset.unit || "kg";

    container.querySelectorAll(".unit-select-btn").forEach(button => {

        button.addEventListener("click", function() {

            container
                .querySelectorAll(".unit-select-btn")
                .forEach(btn => btn.classList.remove("active"));

            this.classList.add("active");
            window.selectedProductUnit = this.dataset.unit;
        });
    });
}


/* =========================================================
   OPEN PRODUCT MODAL
========================================================= */

function openProductModal(productId) {

    const product =
        allProducts.find(
            item =>
                Number(item.id)
                === Number(productId)
        );

    if (!product) {

        console.error(
            "Product not found:",
            productId
        );

        return;

    }


    currentProductId =
        Number(product.id);


    const image =
        $("modalProductImage");

    const name =
        $("modalProductName");

    const category =
        $("modalProductCategory");

    const description =
        $("modalProductDescription");

    const quantity =
        $("modalQuantity");


    if (image) {

        image.src =
            getProductImage(product);

        image.alt =
            product.name;

        imageFallback(image);

    }


    if (name) {

        name.textContent =
            product.name;

    }


    if (category) {

        category.textContent =
            formatCategory(
                product.category
            );

    }


    if (description) {

        description.textContent =
            product.description;

    }


    renderModalUnits(product);


    if (quantity) {

        quantity.value = 1;

        quantity.min = 1;

        quantity.step = 1;

    }


    openModal("productModal");

}


/* =========================================================
   MAKE FUNCTION AVAILABLE TO HTML IF NEEDED
========================================================= */

window.openProductModal =
    openProductModal;

window.toggleWishlist =
    toggleWishlist;


/* =========================================================
   ADD PRODUCT TO CART
========================================================= */

const addProductToCartBtn =
    $("addProductToCartBtn");


if (addProductToCartBtn) {

    addProductToCartBtn.addEventListener(
        "click",
        function() {

            if (!currentProductId) {

                return;

            }


            const product =
                allProducts.find(
                    item =>
                        Number(item.id)
                        === Number(currentProductId)
                );


            if (!product) {

                return;

            }


            const unit =
                window.selectedProductUnit
                || (product.unit === "box" ? "box" : "kg");


            let quantity =
                $("modalQuantity")
                    ? Number(
                        $("modalQuantity").value
                    )
                    : 1;


            if (
                !Number.isFinite(quantity)
                ||
                quantity < 1
            ) {

                quantity = 1;

            }


            quantity =
                Math.floor(quantity);


            const existing =
                cart.find(
                    item =>
                        Number(item.productId)
                        === Number(product.id)
                        &&
                        item.unit
                        === unit
                );


            if (existing) {

                existing.quantity +=
                    quantity;

            } else {

                cart.push({

                    productId:
                        Number(product.id),

                    quantity:
                        quantity,

                    unit:
                        unit

                });

            }


            saveCart();

            updateCartUI();

            closeModal(
                "productModal"
            );


            showToast(
                `${product.name} added to your order`
            );

        }
    );

}


/* =========================================================
   SAVE CART
========================================================= */

function saveCart() {

    localStorage.setItem(
        CART_STORAGE_KEY,
        JSON.stringify(cart)
    );

}


/* =========================================================
   CART QUANTITY
========================================================= */

function getCartQuantity() {

    return cart.reduce(
        (total, item) =>
            total +
            Number(item.quantity || 0),
        0
    );

}


/* =========================================================
   CLEAN INVALID CART ITEMS
========================================================= */

function cleanCart() {

    cart =
        cart.filter(item => {

            return allProducts.some(
                product =>
                    Number(product.id)
                    === Number(item.productId)
            );

        });

    saveCart();

}


/* =========================================================
   UPDATE CART UI
========================================================= */

function updateCartUI() {

    cleanCart();

    const count =
        getCartQuantity();


    if (cartCount) {

        cartCount.textContent =
            count;

    }


    if (cartHeaderCount) {

        cartHeaderCount.textContent =
            `${count} item${count !== 1 ? "s" : ""}`;

    }


    if (invoiceItemCount) {

        invoiceItemCount.textContent =
            count;

    }


    renderCartItems();

    renderSuggestions();

}


/* =========================================================
   RENDER CART ITEMS
========================================================= */

function renderCartItems() {

    if (!cartItems) {
        return;
    }


    if (cart.length === 0) {

        cartItems.innerHTML = `

            <div class="cart-empty">

                <h3>Your order is empty</h3>

                <p>
                    Add some fresh products to continue.
                </p>

            </div>

        `;

        return;

    }


    cartItems.innerHTML = "";


    cart.forEach(
        (item, index) => {

            const product =
                allProducts.find(
                    p =>
                        Number(p.id)
                        === Number(item.productId)
                );


            if (!product) {
                return;
            }


            const row =
                document.createElement(
                    "div"
                );

            row.className =
                "cart-item";


            row.innerHTML = `

                <div class="cart-item-image">

                    <img
                        src="${escapeHTML(
                            getProductImage(product)
                        )}"
                        alt="${escapeHTML(
                            product.name
                        )}"
                    >

                </div>


                <div class="cart-item-info">

                    <h4>
                        ${escapeHTML(
                            product.name
                        )}
                    </h4>

                    <p>
                        ${Number(
                            item.quantity
                        )} ${escapeHTML(
                            item.unit
                        )}
                    </p>

                </div>


                <div class="cart-item-actions">

                    <div class="cart-quantity">

                        <button
                            type="button"
                            data-cart-minus="${index}"
                            aria-label="Decrease quantity"
                        >
                            −
                        </button>


                        <span>
                            ${Number(
                                item.quantity
                            )}
                        </span>


                        <button
                            type="button"
                            data-cart-plus="${index}"
                            aria-label="Increase quantity"
                        >
                            +
                        </button>

                    </div>


                    <button
                        type="button"
                        class="remove-cart-item"
                        data-cart-remove="${index}"
                        aria-label="Remove product"
                    >
                        ×
                    </button>

                </div>

            `;


            cartItems.appendChild(
                row
            );


            imageFallback(
                row.querySelector("img")
            );

        }
    );

}


/* =========================================================
   CART CONTROLS
========================================================= */

if (cartItems) {

    cartItems.addEventListener(
        "click",
        function(event) {

            const plus =
                event.target.closest(
                    "[data-cart-plus]"
                );


            if (plus) {

                const index =
                    Number(
                        plus.dataset.cartPlus
                    );


                if (cart[index]) {

                    cart[index].quantity++;

                    saveCart();

                    updateCartUI();

                }

                return;

            }


            const minus =
                event.target.closest(
                    "[data-cart-minus]"
                );


            if (minus) {

                const index =
                    Number(
                        minus.dataset.cartMinus
                    );


                if (cart[index]) {

                    cart[index].quantity--;


                    if (
                        cart[index].quantity <= 0
                    ) {

                        cart.splice(
                            index,
                            1
                        );

                    }


                    saveCart();

                    updateCartUI();

                }

                return;

            }


            const remove =
                event.target.closest(
                    "[data-cart-remove]"
                );


            if (remove) {

                const index =
                    Number(
                        remove.dataset.cartRemove
                    );


                if (
                    index >= 0
                    &&
                    index < cart.length
                ) {

                    cart.splice(
                        index,
                        1
                    );

                    saveCart();

                    updateCartUI();

                }

            }

        }
    );

}


/* =========================================================
   CART BUTTON
========================================================= */

const cartBtn =
    $("cartBtn");


if (cartBtn) {

    cartBtn.addEventListener(
        "click",
        function() {

            updateCartUI();

            openModal(
                "cartModal"
            );

        }
    );

}


/* =========================================================
   SUGGESTIONS
========================================================= */

function renderSuggestions() {

    if (!suggestionGrid) {
        return;
    }

    /*
       During order review, show the 4 main categories instead of
       only 4-5 individual products. The customer can continue
       shopping without losing the current cart.
    */
    const categories = [
        {
            key: "fruits",
            name: "Fruits",
            image: "https://images.unsplash.com/photo-1610832958506-aa56368176cf?auto=format&fit=crop&w=700&q=90"
        },
        {
            key: "vegetables",
            name: "Vegetables",
            image: "https://images.unsplash.com/photo-1540420773420-3366772f4999?auto=format&fit=crop&w=700&q=90"
        },
        {
            key: "frozen",
            name: "Frozen",
            image: "https://images.unsplash.com/photo-1502741338009-cac2772e18bc?auto=format&fit=crop&w=700&q=90"
        },
        {
            key: "other",
            name: "Other",
            image: "https://images.unsplash.com/photo-1596591868231-05e3c7bfc3a4?auto=format&fit=crop&w=700&q=90"
        }
    ];

    suggestionGrid.innerHTML = "";

    categories.forEach(category => {

        const card = document.createElement("button");
        card.type = "button";
        card.className = "order-category-card";
        card.dataset.orderCategory = category.key;

        card.innerHTML = `
            <span class="order-category-image">
                <img
                    src="${escapeHTML(category.image)}"
                    alt="${escapeHTML(category.name)}"
                >
            </span>
            <strong>${escapeHTML(category.name)}</strong>
            <span class="order-category-action">Browse products</span>
        `;

        suggestionGrid.appendChild(card);
        imageFallback(card.querySelector("img"));
    });
}


/* =========================================================
   SUGGESTION CLICK
========================================================= */

if (suggestionGrid) {

    suggestionGrid.addEventListener(
        "click",
        function(event) {

            const button = event.target.closest(
                "[data-order-category]"
            );

            if (!button) {
                return;
            }

            const category = String(
                button.dataset.orderCategory || "all"
            ).toLowerCase();

            /* Keep cart data. Only close the review modal and
               take the customer to the selected category. */
            closeModal("cartModal");

            setCategory(category, true);
        }
    );

}


/* =========================================================
   WHATSAPP ORDER + SAVE ORDER
========================================================= */

document
    .getElementById("whatsappOrderBtn")
    .addEventListener("click", async () => {


        /* -------------------------------------------------
           CHECK CART
        ------------------------------------------------- */

        if (cart.length === 0) {

            showToast(
                "Please add at least one product"
            );

            return;

        }


        /* -------------------------------------------------
           CUSTOMER NAME
        ------------------------------------------------- */

        const name =
            document
                .getElementById("customerName")
                .value
                .trim();


        /* -------------------------------------------------
           CUSTOMER PHONE
        ------------------------------------------------- */

        const phone =
            document
                .getElementById("customerPhone")
                .value
                .trim();


        if (!name) {

            showToast(
                "Please enter your name"
            );

            return;

        }


        if (!phone) {

            showToast(
                "Please enter your phone number"
            );

            return;

        }


        /* -------------------------------------------------
           PREPARE ORDER ITEMS
        ------------------------------------------------- */

        const orderItems = [];


        cart.forEach(item => {

            const product =
                allProducts.find(
                    p => p.id === item.productId
                );


            if (!product) {
                return;
            }


           orderItems.push({

    product_id:
        product.id,

    product_name:
        product.name,

    category:
        product.category,

    unit:
        item.unit,

    quantity:
        item.quantity,

    price:
        Number(product.price || 0)

});

        });


        if (orderItems.length === 0) {

            showToast(
                "No valid products in your order"
            );

            return;

        }


        /* -------------------------------------------------
           SAVE ORDER TO MYSQL
        ------------------------------------------------- */

        let savedOrderId = null;


        try {

            const response =
                await fetch(
                    "api/orders.php",
                    {
                        method: "POST",

                        headers: {
                            "Content-Type":
                                "application/json"
                        },

                        body: JSON.stringify({

                            customer_name:
                                name,

                            customer_phone:
                                phone,

                            items:
                                orderItems

                        })

                    }
                );


            const data =
                await response.json();


            if (!data.success) {

                showToast(
                    data.message ||
                    "Unable to save order"
                );

                return;

            }


            savedOrderId =
                data.order_id;

                window.lastOrderId = savedOrderId;


        } catch (error) {

            console.error(
                "Order save error:",
                error
            );


            showToast(
                "Unable to connect to order system"
            );

            return;

        }


        /* -------------------------------------------------
           CREATE WHATSAPP MESSAGE
        ------------------------------------------------- */

        let message =
`NEW ORDER
-------------------------

Order ID:
#${savedOrderId}

Customer:
${name}

Phone:
${phone}

Products:
`;


       orderItems.forEach(item => {

    const priceText =
        Number(item.price) > 0
            ? ` — AED ${Number(item.price).toFixed(2)}`
            : "";

    message +=
`
• ${item.product_name} — ${item.quantity} ${item.unit}${priceText}`;
});
        message +=
`

-------------------------
Please confirm product availability and final pricing.

Thank you.
`;


        /* -------------------------------------------------
           WHATSAPP
        ------------------------------------------------- */

        const whatsappNumber =
            "971554480623";


        const url =
            `https://wa.me/${whatsappNumber}?text=${
                encodeURIComponent(message)
            }`;


        window.open(
            url,
            "_blank"
        );


        /* -------------------------------------------------
           CLOSE CART
        ------------------------------------------------- */

        closeModal(
            "cartModal"
        );


        /* -------------------------------------------------
           FEEDBACK
        ------------------------------------------------- */

        setTimeout(() => {

            openModal(
                "feedbackModal"
            );

        }, 700);

    });


/* =========================================================
   EMAIL ORDER
========================================================= */

const emailOrderBtn =
    document.getElementById("emailOrderBtn");

if (emailOrderBtn) {

    emailOrderBtn.addEventListener(
        "click",
        async function () {

            if (cart.length === 0) {
                showToast("Please add at least one product");
                return;
            }

            const name =
                document.getElementById("customerName")?.value.trim() || "";

            const phone =
                document.getElementById("customerPhone")?.value.trim() || "";

            if (!name) {
                showToast("Please enter your name");
                return;
            }

            if (!phone) {
                showToast("Please enter your phone number");
                return;
            }

            const orderItems = [];

            cart.forEach(item => {

                const product = allProducts.find(
                    p => Number(p.id) === Number(item.productId)
                );

                if (!product) return;

                orderItems.push({
                    product_id: product.id,
                    product_name: product.name,
                    category: product.category,
                    unit: item.unit,
                    quantity: item.quantity
                });
            });

            if (!orderItems.length) {
                showToast("No valid products in your order");
                return;
            }

            emailOrderBtn.disabled = true;

            try {

                const response = await fetch(
                    "api/orders.php",
                    {
                        method: "POST",
                        headers: {
                            "Content-Type": "application/json"
                        },
                        body: JSON.stringify({
                            customer_name: name,
                            customer_phone: phone,
                            items: orderItems,
                            delivery_method: "email"
                        })
                    }
                );

                const data = await response.json();

                if (!data.success) {
                    showToast(data.message || "Unable to send email order");
                    return;
                }

                window.lastOrderId = data.order_id;

                showToast(
                    data.email_sent
                        ? "Order sent successfully by email"
                        : "Order saved, but email is not configured yet"
                );

                closeModal("cartModal");

                setTimeout(() => {
                    openModal("feedbackModal");
                }, 700);

            } catch (error) {

                console.error("Email order error:", error);
                showToast("Unable to connect to order system");

            } finally {

                emailOrderBtn.disabled = false;

            }
        }
    );
}


/* =========================================================
   FEEDBACK
========================================================= */

const feedbackStars =
    $("feedbackStars");


if (feedbackStars) {

    feedbackStars.addEventListener(
        "click",
        function(event) {

            const star =
                event.target.closest(
                    "[data-rating]"
                );


            if (!star) {
                return;
            }


            selectedRating =
                Number(
                    star.dataset.rating
                );


            feedbackStars
                .querySelectorAll(
                    "[data-rating]"
                )
                .forEach(
                    button => {

                        button.classList.toggle(
                            "active",
                            Number(
                                button.dataset.rating
                            )
                            <= selectedRating
                        );

                    }
                );

        }
    );

}


/* =========================================================
   FEEDBACK SYSTEM
========================================================= */




/* ---------------------------------------------------------
   STAR SELECTION
--------------------------------------------------------- */

document
    .querySelectorAll("#feedbackStars button")
    .forEach(button => {

        button.addEventListener(
            "click",
            () => {

                selectedRating =
                    Number(
                        button.dataset.rating
                    );


                document
                    .querySelectorAll(
                        "#feedbackStars button"
                    )
                    .forEach(star => {

                        const rating =
                            Number(
                                star.dataset.rating
                            );


                        star.classList.toggle(
                            "active",
                            rating <= selectedRating
                        );

                    });

            }
        );

    });


/* ---------------------------------------------------------
   SUBMIT FEEDBACK
--------------------------------------------------------- */

document
    .getElementById("submitFeedback")
    .addEventListener(
        "click",
        async () => {


            /* ---------------------------------------------
               CHECK RATING
            --------------------------------------------- */

            if (selectedRating === 0) {

                showToast(
                    "Please select a star rating"
                );

                return;

            }


            /* ---------------------------------------------
               CUSTOMER DETAILS
            --------------------------------------------- */

            const customerName =
                document
                    .getElementById("customerName")
                    ?.value
                    .trim() || "";


            const customerPhone =
                document
                    .getElementById("customerPhone")
                    ?.value
                    .trim() || "";


            /* ---------------------------------------------
               ORDER ID
            --------------------------------------------- */

            const orderId =
                window.lastOrderId || 0;


            /* ---------------------------------------------
               COMMENT
            --------------------------------------------- */

            const comment =
                document
                    .getElementById("feedbackText")
                    .value
                    .trim();


            /* ---------------------------------------------
               SEND TO PHP
            --------------------------------------------- */

            try {

                const response =
                    await fetch(
                        "api/feedback.php",
                        {
                            method: "POST",

                            headers: {
                                "Content-Type":
                                    "application/json"
                            },

                            body: JSON.stringify({

                                order_id:
                                    orderId,

                                customer_name:
                                    customerName,

                                customer_phone:
                                    customerPhone,

                                rating:
                                    selectedRating,

                                comment:
                                    comment

                            })

                        }
                    );


                const data =
                    await response.json();


                if (!data.success) {

                    showToast(
                        data.message ||
                        "Unable to submit feedback"
                    );

                    return;

                }


                /* -----------------------------------------
                   SUCCESS
                ----------------------------------------- */

                showToast(
                    "Thank you for your feedback"
                );


                document
                    .getElementById(
                        "feedbackText"
                    )
                    .value = "";


                selectedRating = 0;


                document
                    .querySelectorAll(
                        "#feedbackStars button"
                    )
                    .forEach(star => {

                        star.classList.remove(
                            "active"
                        );

                    });


                closeModal(
                    "feedbackModal"
                );


            } catch (error) {

                console.error(
                    "Feedback error:",
                    error
                );


                showToast(
                    "Unable to connect to feedback system"
                );

            }

        }
    );


/* =========================================================
   SEARCH
========================================================= */

const searchBtn =
    $("searchBtn");

const searchOverlay =
    $("searchOverlay");

const searchClose =
    $("searchClose");

const searchInput =
    $("searchInput");

const searchResults =
    $("searchResults");


if (searchBtn) {

    searchBtn.addEventListener(
        "click",
        function() {

            if (!searchOverlay) {
                return;
            }


            searchOverlay.classList.add(
                "show"
            );


            setTimeout(
                function() {

                    if (searchInput) {
                        searchInput.focus();
                    }

                },
                100
            );

        }
    );

}


if (searchClose) {

    searchClose.addEventListener(
        "click",
        function() {

            if (searchOverlay) {

                searchOverlay.classList.remove(
                    "show"
                );

            }

        }
    );

}


if (searchOverlay) {

    searchOverlay.addEventListener(
        "click",
        function(event) {

            if (
                event.target
                === searchOverlay
            ) {

                searchOverlay.classList.remove(
                    "show"
                );

            }

        }
    );

}


if (searchInput) {

    searchInput.addEventListener(
        "input",
        function() {

            const value =
                searchInput.value
                    .trim()
                    .toLowerCase();


            if (!searchResults) {
                return;
            }


            if (!value) {

                searchResults.innerHTML =
                    "";

                return;

            }


            const matches =
                allProducts
                    .filter(
                        product =>
                            product.name
                                .toLowerCase()
                                .includes(value)
                    )
                    .slice(0, 8);


            searchResults.innerHTML =
                "";


            if (matches.length === 0) {

                searchResults.innerHTML = `

                    <div class="search-no-results">
                        No products found
                    </div>

                `;

                return;

            }


            matches.forEach(
                product => {

                    const result =
                        document.createElement(
                            "div"
                        );

                    result.className =
                        "search-result";


                    result.innerHTML = `

                        <img
                            src="${escapeHTML(
                                getProductImage(product)
                            )}"
                            alt="${escapeHTML(
                                product.name
                            )}"
                        >

                        <span>
                            ${escapeHTML(
                                product.name
                            )}
                        </span>

                    `;


                    result.addEventListener(
                        "click",
                        function() {

                            if (searchOverlay) {

                                searchOverlay.classList.remove(
                                    "show"
                                );

                            }


                            openProductModal(
                                product.id
                            );

                        }
                    );


                    searchResults.appendChild(
                        result
                    );


                    imageFallback(
                        result.querySelector("img")
                    );

                }
            );

        }
    );

}


/* =========================================================
   SORT
========================================================= */

const sortSelect =
    $("sortSelect");


if (sortSelect) {

    sortSelect.addEventListener(
        "change",
        function() {

            sortMode =
                sortSelect.value
                || "default";


            renderProducts();

        }
    );

}


/* =========================================================
   CATEGORY FILTER
========================================================= */

function setCategory(
    category,
    scroll = true
) {

    selectedCategory =
        (
            category
            || "all"
        ).toLowerCase();


    selectedSubcategory =
        "all";


    updateCategoryButtons();

    renderProducts();


    if (
        scroll
        &&
        $("shop")
    ) {

        $("shop").scrollIntoView({
            behavior: "smooth",
            block: "start",
            inline: "nearest"
        });

    }

}


/* =========================================================
   UPDATE MAIN CATEGORY BUTTONS
   Supports BOTH:
   data-category
   and
   data-category-filter
========================================================= */

function updateCategoryButtons() {

    document
        .querySelectorAll(
            ".main-category-card"
        )
        .forEach(
            button => {

                const category =
                    (
                        button.dataset.category
                        ||
                        button.dataset.categoryFilter
                        ||
                        ""
                    ).toLowerCase();


                button.classList.toggle(
                    "active",
                    category
                    === selectedCategory
                );

            }
        );

}


/* =========================================================
   MAIN CATEGORY CLICK
========================================================= */

document.addEventListener(
    "click",
    function(event) {

        const button =
            event.target.closest(
                ".main-category-card"
            );


        if (!button) {
            return;
        }


        const category =
            button.dataset.category
            ||
            button.dataset.categoryFilter;


        if (!category) {
            return;
        }


        setCategory(
            category,
            true
        );


        document
            .querySelectorAll(
                ".sidebar-category"
            )
            .forEach(
                sidebarButton =>
                    sidebarButton.classList.remove(
                        "active"
                    )
            );

    }
);


/* =========================================================
   SIDEBAR FILTER
   Supports:
   data-subcategory
   and
   data-subcategory-filter
========================================================= */

document.addEventListener(
    "click",
    function(event) {

        const button =
            event.target.closest(
                ".sidebar-category"
            );


        if (!button) {
            return;
        }


        const subcategory =
            (
                button.dataset.subcategory
                ||
                button.dataset.subcategoryFilter
                ||
                ""
            ).toLowerCase();


        if (!subcategory) {
            return;
        }


        selectedSubcategory =
            subcategory;


        if (subcategory === "all") {

            selectedCategory =
                "fruits";

        }

        else if (
            subcategory
            === "all-vegetables"
        ) {

            selectedCategory =
                "vegetables";

        }

        else {

            const matchingProduct =
                allProducts.find(
                    product =>
                        product.subcategory
                        === subcategory
                );


            if (matchingProduct) {

                selectedCategory =
                    matchingProduct.category;

            }

        }


        document
            .querySelectorAll(
                ".sidebar-category"
            )
            .forEach(
                sidebarButton =>
                    sidebarButton.classList.remove(
                        "active"
                    )
            );


        button.classList.add(
            "active"
        );


        updateCategoryButtons();

        renderProducts();


        if ($("shop")) {

            $("shop").scrollIntoView({
                behavior: "smooth",
                block: "start"
            });

        }

    }
);


/* =========================================================
   PUBLIC FILTER FUNCTIONS
========================================================= */

function filterProducts(category) {

    setCategory(
        category,
        true
    );

}


function filterSubcategory(
    subcategory
) {

    selectedSubcategory =
        String(
            subcategory || "all"
        ).toLowerCase();


    if (
        selectedSubcategory
        === "all"
    ) {

        selectedCategory =
            "fruits";

    }

    else if (
        selectedSubcategory
        === "all-vegetables"
    ) {

        selectedCategory =
            "vegetables";

    }

    else {

        const product =
            allProducts.find(
                item =>
                    item.subcategory
                    === selectedSubcategory
            );


        if (product) {

            selectedCategory =
                product.category;

        }

    }


    updateCategoryButtons();

    renderProducts();


    if ($("shop")) {

        $("shop").scrollIntoView({
            behavior: "smooth",
            block: "start"
        });

    }

}


window.filterProducts =
    filterProducts;

window.filterSubcategory =
    filterSubcategory;


/* =========================================================
   HERO DOTS
========================================================= */

function createHeroDots() {

    if (!heroDots) {
        return;
    }


    heroDots.innerHTML =
        "";


    heroSlides.forEach(
        (slide, index) => {

            const dot =
                document.createElement(
                    "button"
                );


            dot.type =
                "button";


            dot.className =
                "hero-dot";


            dot.setAttribute(
                "aria-label",
                `Slide ${index + 1}`
            );


            dot.dataset.index =
                index;


            dot.addEventListener(
                "click",
                function() {

                    currentHeroIndex =
                        index;


                    renderHero();


                    if (
                        heroMode
                        === "auto"
                    ) {

                        restartHeroTimer();

                    }

                }
            );


            heroDots.appendChild(
                dot
            );

        }
    );

}


/* =========================================================
   RENDER HERO
========================================================= */

function renderHero() {

    if (
        !heroSlides.length
    ) {

        return;

    }


    const slide =
        heroSlides[
            currentHeroIndex
        ];


    if (
        heroContainer
    ) {

        heroContainer.classList.add(
            "changing"
        );

    }


    setTimeout(
        function() {

            if (heroTitle) {

                heroTitle.innerHTML =
                    slide.title;

            }


            if (heroDescription) {

                heroDescription.textContent =
                    slide.description;

            }


            if (heroSlideLabel) {

                heroSlideLabel.textContent =
                    slide.label;

            }


            if (heroImage) {

                heroImage.src =
                    slide.image;

                heroImage.alt =
                    slide.label;

                imageFallback(
                    heroImage
                );

            }


            if (heroSection) {

                heroSection.style.setProperty(
                    "--hero-bg",
                    slide.bg
                );

                heroSection.style.setProperty(
                    "--hero-accent",
                    slide.accent
                );

            }


            if (heroDots) {

                heroDots
                    .querySelectorAll(
                        ".hero-dot"
                    )
                    .forEach(
                        (dot, index) => {

                            dot.classList.toggle(
                                "active",
                                index
                                === currentHeroIndex
                            );

                        }
                    );

            }


            if (heroContainer) {

                heroContainer.classList.remove(
                    "changing"
                );

            }

        },
        180
    );

}


/* =========================================================
   NEXT HERO
========================================================= */

function nextHero() {

    currentHeroIndex =
        (
            currentHeroIndex
            + 1
        )
        %
        heroSlides.length;


    renderHero();

}


/* =========================================================
   PREVIOUS HERO
========================================================= */

function previousHero() {

    currentHeroIndex =
        (
            currentHeroIndex
            - 1
            +
            heroSlides.length
        )
        %
        heroSlides.length;


    renderHero();

}


/* =========================================================
   HERO NEXT BUTTON
========================================================= */

const heroNext =
    $("heroNext");


if (heroNext) {

    heroNext.addEventListener(
        "click",
        function(event) {

            event.stopPropagation();

            nextHero();


            if (
                heroMode
                === "auto"
            ) {

                restartHeroTimer();

            }

        }
    );

}


/* =========================================================
   HERO PREVIOUS BUTTON
========================================================= */

const heroPrev =
    $("heroPrev");


if (heroPrev) {

    heroPrev.addEventListener(
        "click",
        function(event) {

            event.stopPropagation();

            previousHero();


            if (
                heroMode
                === "auto"
            ) {

                restartHeroTimer();

            }

        }
    );

}


/* =========================================================
   HERO IMAGE CLICK
========================================================= */

if (heroImage) {

    heroImage.addEventListener(
        "click",
        function() {

            const slide =
                heroSlides[
                    currentHeroIndex
                ];


            if (
                slide
                &&
                slide.category
            ) {

                setCategory(
                    slide.category,
                    true
                );

            }

        }
    );

}


/* =========================================================
   HERO MODE
========================================================= */

document
    .querySelectorAll(
        'input[name="heroMode"]'
    )
    .forEach(
        radio => {

            radio.addEventListener(
                "change",
                function() {

                    heroMode =
                        radio.value;


                    if (
                        heroMode
                        === "auto"
                    ) {

                        startHeroTimer();

                    }

                    else {

                        stopHeroTimer();

                    }

                }
            );

        }
    );


/* =========================================================
   HERO TIMER
========================================================= */

function startHeroTimer() {

    stopHeroTimer();


    heroTimer =
        setInterval(
            function() {

                nextHero();

            },
            5000
        );

}


function stopHeroTimer() {

    if (heroTimer) {

        clearInterval(
            heroTimer
        );

        heroTimer =
            null;

    }

}


function restartHeroTimer() {

    if (
        heroMode
        === "auto"
    ) {

        startHeroTimer();

    }

}


/* =========================================================
   HERO SHOP BUTTON
========================================================= */

const heroShopBtn =
    $("heroShopBtn");


if (heroShopBtn) {

    heroShopBtn.addEventListener(
        "click",
        function() {

            if ($("shop")) {

                $("shop").scrollIntoView({
                    behavior: "smooth",
                    block: "start"
                });

            }

        }
    );

}


/* =========================================================
   HERO EXPLORE BUTTON
========================================================= */

const heroExploreBtn =
    $("heroExploreBtn");


if (heroExploreBtn) {

    heroExploreBtn.addEventListener(
        "click",
        function() {

            if ($("shop")) {

                $("shop").scrollIntoView({
                    behavior: "smooth",
                    block: "start"
                });

            }

        }
    );

}


/* =========================================================
   OFFER BUTTON
========================================================= */

const offerShopBtn =
    $("offerShopBtn");


if (offerShopBtn) {

    offerShopBtn.addEventListener(
        "click",
        function() {

            if ($("shop")) {

                $("shop").scrollIntoView({
                    behavior: "smooth",
                    block: "start"
                });

            }

        }
    );

}


/* =========================================================
   MOBILE MENU
========================================================= */

const mobileMenuBtn =
    $("mobileMenuBtn");

const mobileNav =
    $("mobileNav");


if (mobileMenuBtn) {

    mobileMenuBtn.addEventListener(
        "click",
        function() {

            if (mobileNav) {

                mobileNav.classList.toggle(
                    "show"
                );

            }

        }
    );

}


if (mobileNav) {

    mobileNav
        .querySelectorAll("a")
        .forEach(
            link => {

                link.addEventListener(
                    "click",
                    function() {

                        mobileNav.classList.remove(
                            "show"
                        );

                    }
                );

            }
        );

}


/* =========================================================
   MODAL OPEN
========================================================= */

function openModal(id) {

    const modal =
        $(id);


    if (!modal) {
        return;
    }


    modal.classList.add(
        "show"
    );


    document.body.classList.add(
        "modal-open"
    );

}


/* =========================================================
   MODAL CLOSE
========================================================= */

function closeModal(id) {

    const modal =
        $(id);


    if (!modal) {
        return;
    }


    modal.classList.remove(
        "show"
    );


    const anotherModal =
        document.querySelector(
            ".modal-backdrop.show"
        );


    if (!anotherModal) {

        document.body.classList.remove(
            "modal-open"
        );

    }

}


/* =========================================================
   CLOSE BUTTONS
========================================================= */

document
    .querySelectorAll(
        "[data-close-modal]"
    )
    .forEach(
        button => {

            button.addEventListener(
                "click",
                function() {

                    closeModal(
                        button.dataset.closeModal
                    );

                }
            );

        }
    );


/* =========================================================
   CLOSE MODAL BY BACKDROP
========================================================= */

document
    .querySelectorAll(
        ".modal-backdrop"
    )
    .forEach(
        backdrop => {

            backdrop.addEventListener(
                "click",
                function(event) {

                    if (
                        event.target
                        === backdrop
                    ) {

                        closeModal(
                            backdrop.id
                        );

                    }

                }
            );

        }
    );


/* =========================================================
   ESC KEY
========================================================= */

document.addEventListener(
    "keydown",
    function(event) {

        if (
            event.key
            === "Escape"
        ) {

            document
                .querySelectorAll(
                    ".modal-backdrop.show"
                )
                .forEach(
                    modal => {

                        closeModal(
                            modal.id
                        );

                    }
                );


            if (searchOverlay) {

                searchOverlay.classList.remove(
                    "show"
                );

            }

        }

    }
);


/* =========================================================
   FOOTER CATEGORY LINKS
========================================================= */

document
    .querySelectorAll(
        "[data-footer-category]"
    )
    .forEach(
        link => {

            link.addEventListener(
                "click",
                function() {

                    const category =
                        link.dataset.footerCategory;


                    setCategory(
                        category,
                        true
                    );


                    document
                        .querySelectorAll(
                            ".sidebar-category"
                        )
                        .forEach(
                            button =>
                                button.classList.remove(
                                    "active"
                                )
                        );

                }
            );

        }
    );


/* =========================================================
   TOAST
========================================================= */

function showToast(message) {

    if (!toast || !toastText) {
        return;
    }


    toastText.textContent =
        message;


    toast.classList.add(
        "show"
    );


    clearTimeout(
        toastTimer
    );


    toastTimer =
        setTimeout(
            function() {

                toast.classList.remove(
                    "show"
                );

            },
            2800
        );

}


/* =========================================================
   STATIC IMAGE FALLBACK
========================================================= */

function setupStaticImageFallbacks() {

    document
        .querySelectorAll(
            "img"
        )
        .forEach(
            img => {

                imageFallback(
                    img
                );

            }
        );

}


/* =========================================================
   START APPLICATION
========================================================= */

document.addEventListener(
    "DOMContentLoaded",
    async function() {

        createHeroDots();

        renderHero();

        /*
          Show the original 38 products
          immediately.
        */

        renderProducts();

        updateCartUI();

        setupStaticImageFallbacks();

        /*
          Then load MySQL products.
        */

        await loadProducts();

        /*
          Start hero after page is ready.
        */

        startHeroTimer();

    }
);


/* =========================================================
   FINAL CATEGORY SYSTEM
   All / Fruits / Vegetables / Frozen / Other
========================================================= */

(function () {

    function updateSidebarGroups(category) {

        const groups =
            document.querySelectorAll(
                ".sidebar-group[data-sidebar-category]"
            );


        groups.forEach(group => {

            const groupCategory =
                group.dataset.sidebarCategory;


            if (category === "all") {

                group.style.display = "";

            } else {

                group.style.display =
                    groupCategory === category
                        ? ""
                        : "none";

            }

        });

    }


    function updateMainCategoryActive(category) {

        document
            .querySelectorAll(".main-category-card")
            .forEach(card => {

                card.classList.toggle(
                    "active",
                    card.dataset.category === category
                );

            });

    }


    function updateSidebarActive(subcategory) {

        document
            .querySelectorAll(".sidebar-category")
            .forEach(button => {

                button.classList.toggle(
                    "active",
                    button.dataset.subcategory === subcategory
                );

            });

    }


    function selectMainCategory(category) {

        selectedCategory =
            category || "all";

        selectedSubcategory =
            "all";


        updateMainCategoryActive(
            selectedCategory
        );


        updateSidebarGroups(
            selectedCategory
        );


        updateSidebarActive(
            ""
        );


        if (
            selectedCategory === "all"
        ) {

            if (
                typeof renderProducts ===
                "function"
            ) {

                renderProducts(
                    allProducts
                );

            }

        } else {

            if (
                typeof filterProducts ===
                "function"
            ) {

                filterProducts(
                    selectedCategory
                );

            }

        }

    }


    function selectSubcategory(
        button
    ) {

        const subcategory =
            button.dataset.subcategory;


        selectedSubcategory =
            subcategory;


        let category =
            "all";


        if (
            subcategory === "all"
        ) {

            category = "fruits";

        }

        else if (
            subcategory ===
            "all-vegetables"
        ) {

            category = "vegetables";

        }

        else {

            const product =
                allProducts.find(
                    item =>
                        String(
                            item.subcategory
                        ).toLowerCase() ===
                        String(
                            subcategory
                        ).toLowerCase()
                );


            if (product) {

                category =
                    product.category;

            }

        }


        selectedCategory =
            category;


        updateMainCategoryActive(
            category
        );


        updateSidebarGroups(
            category
        );


        updateSidebarActive(
            subcategory
        );


        if (
            typeof filterSubcategory ===
            "function"
        ) {

            filterSubcategory(
                subcategory
            );

        }

    }


    /*
       CAPTURE MODE

       Existing old category listeners
       irundhalum avanga conflict panna
       koodadhu.
    */

    document.addEventListener(
        "click",
        function (event) {


            const mainCard =
                event.target.closest(
                    ".main-category-card"
                );


            if (mainCard) {

                event.preventDefault();

                event.stopPropagation();


                selectMainCategory(
                    mainCard.dataset.category
                );


                const shop =
                    document.getElementById(
                        "shop"
                    );


                if (shop) {

                    shop.scrollIntoView({
                        behavior: "smooth",
                        block: "start"
                    });

                }


                return;

            }


            const sidebarButton =
                event.target.closest(
                    ".sidebar-category"
                );


            if (sidebarButton) {

                event.preventDefault();

                event.stopPropagation();


                selectSubcategory(
                    sidebarButton
                );

                return;

            }

        },
        true
    );


    /*
       INITIAL STATE
    */

    updateSidebarGroups(
        "all"
    );

})();





/* =========================================================
   LOAD WEBSITE IMAGES + HERO SETTINGS
========================================================= */

(function () {

    let adminHeroInterval = 5000;


    async function loadWebsiteSettings() {

        try {

            const response =
                await fetch(
                    "api/site-settings.php",
                    {
                        cache: "no-store"
                    }
                );


            const data =
                await response.json();


            if (!data.success) {

                return;

            }


            /* =========================================
               MAIN CATEGORY IMAGES
            ========================================== */

            const categoryImageMap = {

                all:
                    "mainCategoryImageAll",

                fruits:
                    "mainCategoryImageFruits",

                vegetables:
                    "mainCategoryImageVegetables",

                frozen:
                    "mainCategoryImageFrozen",

                other:
                    "mainCategoryImageOther"

            };


            Object.keys(
                categoryImageMap
            ).forEach(category => {

                const image =
                    document.getElementById(
                        categoryImageMap[
                            category
                        ]
                    );


                if (
                    image &&
                    data.categories &&
                    data.categories[category]
                ) {

                    image.src =
                        data.categories[
                            category
                        ];

                }

            });


            /* =========================================
               HERO SLIDES
            ========================================== */

            if (
                Array.isArray(
                    data.heroSlides
                ) &&
                data.heroSlides.length
            ) {

                heroSlides.splice(
                    0,
                    heroSlides.length,
                    ...data.heroSlides
                );

            }


            /* =========================================
               HERO TIME
            ========================================== */

            const seconds =
                Number(
                    data.interval
                ) || 5;


            adminHeroInterval =
                Math.max(
                    2,
                    Math.min(
                        60,
                        seconds
                    )
                ) * 1000;


            /*
               Existing hero timer-ai
               new admin time-ku change pannuvom.
            */

            if (
                typeof stopHeroTimer ===
                "function"
            ) {

                stopHeroTimer();

            }


            /*
               Existing function-ai override
               pannrom.
            */

            window.startHeroTimer =
                function () {

                    if (
                        typeof stopHeroTimer ===
                        "function"
                    ) {

                        stopHeroTimer();

                    }


                    heroTimer =
                        setInterval(
                            function () {

                                nextHero();

                            },
                            adminHeroInterval
                        );

                };


            /*
               Existing code direct function
               call use pannum.
            */

            startHeroTimer =
                window.startHeroTimer;


            if (
                heroDots
            ) {

                createHeroDots();

            }


            renderHero();


            if (
                heroMode === "auto"
            ) {

                startHeroTimer();

            }


        }

        catch (error) {

            console.error(
                "Website settings loading error:",
                error
            );

        }

    }


    loadWebsiteSettings();

})();