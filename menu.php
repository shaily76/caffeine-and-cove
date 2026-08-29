<?php

session_start();

require_once __DIR__ . "/include/config.php";


/* =========================================================
   LOGIN STATUS
========================================================= */

$is_logged_in =
    isset($_SESSION["logged_in"]) &&
    $_SESSION["logged_in"] === true;


/* =========================================================
   CART COUNT
========================================================= */

$cart_count = 0;

if (
    isset($_SESSION["cart"]) &&
    is_array($_SESSION["cart"])
) {

    foreach (
        $_SESSION["cart"]
        as $cart_item
    ) {

        if (
            is_array($cart_item) &&
            isset($cart_item["quantity"])
        ) {

            $cart_count +=
                (int)$cart_item["quantity"];

        } else {

            $cart_count++;

        }

    }

}


/* =========================================================
   CLEAN CATEGORY
========================================================= */

function cleanCategory($category)
{
    $category = trim($category);

    $category = str_replace(
        [
            "☕",
            "🍵",
            "🥐",
            "🍰",
            "🍽️",
            "🍽",
            "🧁",
            "🥪",
            "🍪",
            "🍩",
            "🧋",
            "🥤",
            "🍮",
            "🍫",
            "🍦",
            "🎂"
        ],
        "",
        $category
    );

    return trim($category);
}


/* =========================================================
   NORMALIZED CATEGORY
========================================================= */

function normalizedCategory($category)
{
    $category =
        strtolower(
            cleanCategory($category)
        );

    $category =
        trim($category);


    if (
        $category === "quick bites" ||
        $category === "quickbite" ||
        $category === "bites"
    ) {

        return "bites";

    }


    if (
        $category === "coffee"
    ) {

        return "coffee";

    }


    if (
        $category === "tea"
    ) {

        return "tea";

    }


    if (
        $category === "dessert" ||
        $category === "desserts"
    ) {

        return "desserts";

    }


    return $category;
}


/* =========================================================
   CATEGORY DISPLAY NAME
========================================================= */

function categoryName($category)
{
    $clean =
        cleanCategory($category);


    $normalized =
        normalizedCategory($clean);


    switch ($normalized) {

        case "coffee":

            return "Coffee";


        case "tea":

            return "Tea";


        case "bites":

            return "Quick Bites";


        case "desserts":

            return "Desserts";


        default:

            return ucfirst($clean);

    }
}


/* =========================================================
   CATEGORY EMOJI
========================================================= */

function categoryEmoji($category)
{
    $normalized =
        normalizedCategory($category);


    switch ($normalized) {

        case "coffee":

            return "☕";


        case "tea":

            return "🍵";


        case "bites":

            return "🥐";


        case "desserts":

            return "🍰";


        default:

            return "🍽️";

    }
}


/* =========================================================
   GET ACTIVE PRODUCTS
========================================================= */

$sql = "
    SELECT
        id,
        name,
        category,
        description,
        price,
        image,
        status
    FROM products
    WHERE status = 'active'
    ORDER BY id DESC
";


$result =
    mysqli_query(
        $link,
        $sql
    );


$products = [];


if ($result) {

    while (
        $row =
        mysqli_fetch_assoc($result)
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
        Menu | Caffeine & Cove
    </title>

    <link
        rel="stylesheet"
        href="css/menu.css"
    >

</head>


<body>


<?php include "include/header.php"; ?>


<!-- =========================================================
     HERO
========================================================= -->

<section class="menu-hero">

    <div class="menu-hero-overlay"></div>

    <div class="menu-hero-content">

        <span class="menu-small-title">
            FRESHLY PREPARED FOR YOU
        </span>

        <h1>
            Our Menu
        </h1>

        <p>
            Good coffee, delicious bites and sweet moments.
        </p>

    </div>

</section>



<!-- =========================================================
     MENU PAGE
========================================================= -->

<main class="menu-page">


    <!-- =====================================================
         SEARCH + FILTER
    ====================================================== -->

    <section class="menu-explorer">


        <div class="menu-explorer-top">


            <!-- TITLE -->

            <div class="explorer-title">

                <span class="explorer-small-title">

                    ☕ EXPLORE OUR MENU

                </span>

                <h2>
                    Find something delicious.
                </h2>

            </div>



            <!-- SEARCH -->

            <div class="menu-search-wrapper">


                <div class="menu-search">


                    <span class="search-icon">
                        🔍
                    </span>


                    <input
                        type="text"
                        id="menuSearch"
                        name="menu_search"
                        placeholder="Search your favourite..."
                        autocomplete="new-password"
                        autocorrect="off"
                        autocapitalize="off"
                        spellcheck="false"
                    >


                    <button
                        type="button"
                        id="clearSearch"
                        class="clear-search"
                        aria-label="Clear search"
                    >
                        ×
                    </button>


                </div>


                <!-- SEARCH SUGGESTIONS -->

                <div
                    id="searchSuggestions"
                    class="search-suggestions"
                ></div>


            </div>


        </div>



        <div class="explorer-divider"></div>



        <!-- =================================================
             CATEGORY FILTERS
        ================================================== -->

        <div class="category-navigation">


            <button
                type="button"
                class="filter-button active"
                data-filter="all"
            >

                <span class="filter-emoji">
                    🍽️
                </span>

                <span>
                    All Menu
                </span>

            </button>



            <button
                type="button"
                class="filter-button"
                data-filter="coffee"
            >

                <span class="filter-emoji">
                    ☕
                </span>

                <span>
                    Coffee
                </span>

            </button>



            <button
                type="button"
                class="filter-button"
                data-filter="tea"
            >

                <span class="filter-emoji">
                    🍵
                </span>

                <span>
                    Tea
                </span>

            </button>



            <button
                type="button"
                class="filter-button"
                data-filter="bites"
            >

                <span class="filter-emoji">
                    🥐
                </span>

                <span>
                    Quick Bites
                </span>

            </button>



            <button
                type="button"
                class="filter-button"
                data-filter="desserts"
            >

                <span class="filter-emoji">
                    🍰
                </span>

                <span>
                    Desserts
                </span>

            </button>


        </div>


    </section>



    <!-- =====================================================
         MESSAGE
    ====================================================== -->

    <div
        id="menuMessage"
        class="menu-message"
    ></div>



    <!-- =====================================================
         PRODUCTS
    ====================================================== -->

    <section
        class="menu-grid"
        id="menuGrid"
    >


        <?php if (count($products) > 0): ?>


            <?php foreach ($products as $product): ?>


                <?php

                /* ---------------------------------------------
                   PRODUCT INFORMATION
                --------------------------------------------- */

                $product_id =
                    (int)$product["id"];


                $product_name =
                    htmlspecialchars(
                        $product["name"],
                        ENT_QUOTES,
                        "UTF-8"
                    );


                $product_category =
                    normalizedCategory(
                        $product["category"]
                    );


                $display_category =
                    categoryName(
                        $product["category"]
                    );


                $emoji =
                    categoryEmoji(
                        $product["category"]
                    );


                $product_description =
                    htmlspecialchars(
                        $product["description"] ?? "",
                        ENT_QUOTES,
                        "UTF-8"
                    );


                $product_price =
                    number_format(
                        (float)$product["price"],
                        0
                    );


                $image_name =
                    basename(
                        $product["image"]
                    );


                $image_path =
                    "img/" .
                    $image_name;


                $search_data =
                    strtolower(
                        $product["name"] . " " .
                        ($product["description"] ?? "") . " " .
                        $display_category
                    );

                ?>


                <!-- =================================================
                     PRODUCT CARD
                ================================================== -->

                <article
                    class="menu-card"

                    data-category="<?php
                        echo htmlspecialchars(
                            $product_category,
                            ENT_QUOTES,
                            "UTF-8"
                        );
                    ?>"

                    data-category-name="<?php
                        echo htmlspecialchars(
                            $display_category,
                            ENT_QUOTES,
                            "UTF-8"
                        );
                    ?>"

                    data-category-emoji="<?php
                        echo htmlspecialchars(
                            $emoji,
                            ENT_QUOTES,
                            "UTF-8"
                        );
                    ?>"

                    data-search="<?php
                        echo htmlspecialchars(
                            $search_data,
                            ENT_QUOTES,
                            "UTF-8"
                        );
                    ?>"
                >


                    <!-- IMAGE -->

                    <div class="menu-card-image">

                        <img
                            src="<?php
                                echo htmlspecialchars(
                                    $image_path,
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                            ?>"
                            alt="<?php echo $product_name; ?>"
                            loading="lazy"
                        >

                    </div>



                    <!-- CONTENT -->

                    <div class="menu-card-content">


                        <div class="product-top">

                            <h2>
                                <?php
                                echo $product_name;
                                ?>
                            </h2>

                            <span class="product-price">

                                ₹<?php
                                echo $product_price;
                                ?>

                            </span>

                        </div>



                        <p class="product-description">

                            <?php

                            if (
                                $product_description !== ""
                            ) {

                                echo $product_description;

                            } else {

                                echo
                                    "Freshly prepared at Caffeine & Cove.";

                            }

                            ?>

                        </p>



                        <!-- CATEGORY -->

                        <span class="product-category">

                            <span class="category-card-emoji">
                                <?php echo $emoji; ?>
                            </span>

                            <span>
                                <?php echo $display_category; ?>
                            </span>

                        </span>



                        <!-- ADD TO ORDER -->

                        <button
                            type="button"
                            class="add-order-button"

                            data-id="<?php
                                echo $product_id;
                            ?>"

                            data-name="<?php
                                echo $product_name;
                            ?>"

                            data-price="<?php
                                echo $product["price"];
                            ?>"

                            data-image="<?php
                                echo htmlspecialchars(
                                    $image_path,
                                    ENT_QUOTES,
                                    "UTF-8"
                                );
                            ?>"
                        >

                            <span class="plus-icon">
                                +
                            </span>

                            Add to order

                        </button>


                    </div>


                </article>


            <?php endforeach; ?>


        <?php else: ?>


            <div class="no-products">

                <div class="no-products-icon">
                    ☕
                </div>

                <h2>
                    Our menu is getting ready
                </h2>

                <p>
                    No active products are available right now.
                </p>

            </div>


        <?php endif; ?>


    </section>


</main>



<?php

include("include/footer.php");

?>


<!-- =========================================================
     JAVASCRIPT
========================================================= -->

<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {


        /* =====================================================
           ELEMENTS
        ====================================================== */

        const filterButtons =
            document.querySelectorAll(
                ".filter-button"
            );


        const menuCards =
            document.querySelectorAll(
                ".menu-card"
            );


        const menuSearch =
            document.getElementById(
                "menuSearch"
            );


        const clearSearch =
            document.getElementById(
                "clearSearch"
            );


        const searchSuggestions =
            document.getElementById(
                "searchSuggestions"
            );


        const menuGrid =
            document.getElementById(
                "menuGrid"
            );


        const messageBox =
            document.getElementById(
                "menuMessage"
            );


        const cartCount =
            document.getElementById(
                "cartCount"
            );


        let currentFilter =
            "all";



        /* =====================================================
           CATEGORY FILTER
        ====================================================== */

        filterButtons.forEach(
            function (button) {

                button.addEventListener(
                    "click",
                    function () {


                        currentFilter =
                            this.dataset.filter;


                        filterButtons.forEach(
                            function (btn) {

                                btn.classList.remove(
                                    "active"
                                );

                            }
                        );


                        this.classList.add(
                            "active"
                        );


                        applyMenuFilters();

                    }
                );

            }
        );



        /* =====================================================
           SEARCH
        ====================================================== */

        if (menuSearch) {

            menuSearch.addEventListener(
                "input",
                function () {


                    const searchText =
                        this.value
                            .trim()
                            .toLowerCase();


                    searchSuggestions.innerHTML =
                        "";


                    if (
                        searchText === ""
                    ) {

                        searchSuggestions.classList.remove(
                            "show"
                        );

                        applyMenuFilters();

                        return;

                    }



                    const matchingCards = [];


                    menuCards.forEach(
                        function (card) {


                            const searchData =
                                (
                                    card.dataset.search ||
                                    ""
                                ).toLowerCase();


                            const category =
                                (
                                    card.dataset.category ||
                                    ""
                                ).toLowerCase();


                            const categoryMatch =
                                currentFilter === "all" ||
                                category === currentFilter;


                            if (
                                searchData.includes(
                                    searchText
                                ) &&
                                categoryMatch
                            ) {

                                matchingCards.push(
                                    card
                                );

                            }

                        }
                    );



                    if (
                        matchingCards.length === 0
                    ) {


                        searchSuggestions.innerHTML = `

                            <div class="no-suggestions">

                                <span>
                                    🔍
                                </span>

                                <strong>
                                    No matching items
                                </strong>

                                <small>
                                    Try another item.
                                </small>

                            </div>

                        `;


                        searchSuggestions.classList.add(
                            "show"
                        );


                        applyMenuFilters();


                        return;

                    }



                    matchingCards
                        .slice(0, 6)
                        .forEach(
                            function (card) {


                                const name =
                                    card.querySelector(
                                        ".product-top h2"
                                    )
                                    .textContent
                                    .trim();


                                const price =
                                    card.querySelector(
                                        ".product-price"
                                    )
                                    .textContent
                                    .trim();


                                const cleanCategory =
                                    card.dataset.categoryName ||
                                    "";


                                const categoryEmoji =
                                    card.dataset.categoryEmoji ||
                                    "🍽️";


                                const image =
                                    card.querySelector(
                                        ".menu-card-image img"
                                    )
                                    .getAttribute(
                                        "src"
                                    );



                                const suggestion =
                                    document.createElement(
                                        "button"
                                    );


                                suggestion.type =
                                    "button";


                                suggestion.className =
                                    "search-suggestion-item";


                                suggestion.innerHTML = `

                                    <span class="suggestion-image">

                                        <img
                                            src="${image}"
                                            alt="${name}"
                                        >

                                    </span>


                                    <span class="suggestion-info">

                                        <strong>
                                            ${name}
                                        </strong>


                                        <small>

                                            <span class="suggestion-category-emoji">
                                                ${categoryEmoji}
                                            </span>

                                            ${cleanCategory}

                                        </small>

                                    </span>


                                    <span class="suggestion-price">

                                        ${price}

                                    </span>

                                `;



                                suggestion.addEventListener(
                                    "click",
                                    function () {


                                        menuSearch.value =
                                            name;


                                        searchSuggestions.classList.remove(
                                            "show"
                                        );


                                        applyMenuFilters();


                                        setTimeout(
                                            function () {


                                                card.scrollIntoView(
                                                    {
                                                        behavior:
                                                            "smooth",

                                                        block:
                                                            "center"
                                                    }
                                                );


                                                card.classList.add(
                                                    "search-highlight"
                                                );


                                                setTimeout(
                                                    function () {

                                                        card.classList.remove(
                                                            "search-highlight"
                                                        );

                                                    },
                                                    1800
                                                );


                                            },
                                            100
                                        );


                                    }
                                );


                                searchSuggestions.appendChild(
                                    suggestion
                                );


                            }
                        );



                    searchSuggestions.classList.add(
                        "show"
                    );


                    applyMenuFilters();


                }
            );

        }



        /* =====================================================
           CLEAR SEARCH
        ====================================================== */

        if (clearSearch) {

            clearSearch.addEventListener(
                "click",
                function () {


                    menuSearch.value =
                        "";


                    searchSuggestions.innerHTML =
                        "";


                    searchSuggestions.classList.remove(
                        "show"
                    );


                    applyMenuFilters();


                    menuSearch.focus();


                }
            );

        }



        /* =====================================================
           CLOSE SUGGESTIONS
        ====================================================== */

        document.addEventListener(
            "click",
            function (event) {


                const wrapper =
                    document.querySelector(
                        ".menu-search-wrapper"
                    );


                if (
                    wrapper &&
                    !wrapper.contains(
                        event.target
                    )
                ) {


                    searchSuggestions.classList.remove(
                        "show"
                    );

                }

            }
        );



        /* =====================================================
           FILTER PRODUCTS
        ====================================================== */

        function applyMenuFilters()
        {


            const searchText =
                menuSearch
                    ? menuSearch.value
                        .trim()
                        .toLowerCase()
                    : "";


            let visibleCount =
                0;


            menuCards.forEach(
                function (card) {


                    const category =
                        (
                            card.dataset.category ||
                            ""
                        ).toLowerCase();


                    const searchData =
                        (
                            card.dataset.search ||
                            ""
                        ).toLowerCase();


                    const categoryMatch =
                        currentFilter === "all" ||
                        category === currentFilter;


                    const searchMatch =
                        searchText === "" ||
                        searchData.includes(
                            searchText
                        );


                    if (
                        categoryMatch &&
                        searchMatch
                    ) {


                        card.style.display =
                            "block";


                        visibleCount++;


                    } else {


                        card.style.display =
                            "none";

                    }

                }
            );



            /* =================================================
               NO RESULTS
            ================================================== */

            let noSearchResults =
                document.getElementById(
                    "noSearchResults"
                );


            if (
                visibleCount === 0 &&
                (
                    searchText !== "" ||
                    currentFilter !== "all"
                )
            ) {


                if (!noSearchResults) {


                    noSearchResults =
                        document.createElement(
                            "div"
                        );


                    noSearchResults.id =
                        "noSearchResults";


                    noSearchResults.className =
                        "no-search-results";


                    noSearchResults.innerHTML = `

                        <div class="no-search-icon">
                            ☕
                        </div>

                        <h2>
                            Nothing found
                        </h2>

                        <p>
                            Try another search or category.
                        </p>

                    `;


                    menuGrid.appendChild(
                        noSearchResults
                    );

                }


                noSearchResults.style.display =
                    "block";


            } else if (
                noSearchResults
            ) {


                noSearchResults.style.display =
                    "none";

            }

        }



        /* =====================================================
           ADD TO ORDER
           STEP 1 SECURITY FIX
        ====================================================== */

        const addButtons =
            document.querySelectorAll(
                ".add-order-button"
            );


        addButtons.forEach(
            function (button) {


                button.addEventListener(
                    "click",
                    function () {


                        /*
                         * IMPORTANT:
                         *
                         * Only product ID is sent.
                         *
                         * We DO NOT send:
                         *
                         * product_name
                         * product_price
                         * product_image
                         *
                         * The PHP file gets the real
                         * product information from
                         * the database.
                         */

                        const productId =
                            this.dataset.id;


                        const isLoggedIn =
                            <?php
                            echo $is_logged_in
                                ? "true"
                                : "false";
                            ?>;


                        if (!isLoggedIn) {


                            showMessage(
                                "Please login to add items to your cart.",
                                "error"
                            );


                            return;

                        }


                        if (
                            !productId ||
                            !/^\d+$/.test(
                                productId
                            )
                        ) {


                            showMessage(
                                "Invalid product.",
                                "error"
                            );


                            return;

                        }


                        this.disabled =
                            true;


                        this.innerHTML =
                            "Adding...";


                        /* -----------------------------------------
                           FORM DATA
                        ------------------------------------------ */

                        const formData =
                            new FormData();


                        formData.append(
                            "product_id",
                            productId
                        );


                        /* -----------------------------------------
                           SEND TO SERVER
                        ------------------------------------------ */

                        fetch(
                            "add_to_cart.php",
                            {
                                method:
                                    "POST",

                                body:
                                    formData
                            }
                        )

                        .then(
                            function (response) {

                                if (
                                    !response.ok
                                ) {

                                    throw new Error(
                                        "Server error"
                                    );

                                }


                                return response.json();

                            }
                        )

                        .then(
                            function (data) {


                                if (
                                    data.success
                                ) {


                                    /* -----------------------------
                                       UPDATE CART COUNT
                                    ------------------------------ */

                                    if (
                                        cartCount &&
                                        data.cart_count !==
                                            undefined
                                    ) {

                                        cartCount.textContent =
                                            data.cart_count;

                                    }


                                    /* -----------------------------
                                       SUCCESS
                                    ------------------------------ */

                                    showMessage(
                                        data.message,
                                        "success"
                                    );


                                    button.innerHTML =
                                        '<span class="plus-icon">✓</span> Added';


                                    /* -----------------------------
                                       RESET BUTTON
                                    ------------------------------ */

                                    setTimeout(
                                        function () {


                                            button.innerHTML =
                                                '<span class="plus-icon">+</span> Add to order';


                                            button.disabled =
                                                false;


                                        },
                                        1200
                                    );


                                } else {


                                    /* -----------------------------
                                       ERROR
                                    ------------------------------ */

                                    showMessage(
                                        data.message ||
                                        "Unable to add item.",
                                        "error"
                                    );


                                    button.innerHTML =
                                        '<span class="plus-icon">+</span> Add to order';


                                    button.disabled =
                                        false;

                                }

                            }
                        )

                        .catch(
                            function (error) {


                                console.error(
                                    error
                                );


                                showMessage(
                                    "Something went wrong. Please try again.",
                                    "error"
                                );


                                button.innerHTML =
                                    '<span class="plus-icon">+</span> Add to order';


                                button.disabled =
                                    false;

                            }
                        );


                    }
                );

            }
        );



        /* =====================================================
           MESSAGE
        ====================================================== */

        function showMessage(
            message,
            type
        ) {


            if (!messageBox) {

                return;

            }


            messageBox.textContent =
                message;


            messageBox.className =
                "menu-message " +
                type;


            messageBox.style.display =
                "block";


            setTimeout(
                function () {

                    messageBox.style.display =
                        "none";

                },
                3000
            );

        }


    }
);

</script>


</body>

</html>