<?php

// Start user session
session_start();

// Restrict access to administrators only
if (isset($_SESSION["role"]) && $_SESSION["role"] != 2) {
    header("Location: index.php");
    exit();
}

// Include database connection
require_once "includes/db.php";

// Store admin error messages
$error = "";


/*
|--------------------------------------------------------------------------
| Product Deletion
|--------------------------------------------------------------------------
*/

// Delete a product by ID
if (isset($_GET['delete'])) {

    $id = (int) $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM products WHERE id = ?");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: admin.php");
    exit();
}


/*
|--------------------------------------------------------------------------
| Category Deletion
|--------------------------------------------------------------------------
*/

// Delete a category if it is not assigned to any products
if (isset($_GET['delete-category'])) {

    $id = (int) $_GET['delete-category'];

    $stmt = $conn->prepare("SELECT id FROM products WHERE category_id = ?");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $check = $stmt->get_result();

    if ($check->num_rows > 0) {

        header("Location: admin.php?error=categoryinuse");
        exit();

    } else {

        $stmt = $conn->prepare("DELETE FROM categories WHERE id = ?");

        $stmt->bind_param("i", $id);
        $stmt->execute();

        header("Location: admin.php");
        exit();
    }
}


/*
|--------------------------------------------------------------------------
| Load Categories
|--------------------------------------------------------------------------
*/

// Load all categories for dropdown menus
$categories = $conn->query("SELECT * FROM categories");


/*
|--------------------------------------------------------------------------
| Add Product
|--------------------------------------------------------------------------
*/

// Create a new product
if (isset($_POST["add-product"])) {

    $brand = trim($_POST["brand"]);
    $model = trim($_POST["model"]);
    $price = $_POST["price"];
    $badge = $_POST["badge"];
    $category_id = $_POST["category_id"];
    $stock = $_POST["stock"];

    // Generate unique filename
    $imageName = uniqid() . "_" . basename($_FILES['image']['name']);

    $imageTmp = $_FILES['image']['tmp_name'];

    // Upload destination
    $image = "uploads/products/" . $imageName;

    // Allowed image types
    $allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];

    // Validate image format
    if (!in_array($_FILES['image']['type'], $allowedTypes)) {

        die(
            "Only JPG, PNG and WEBP images are allowed."
        );
    }

    move_uploaded_file($imageTmp, $image);

    // Validate product values
    if ($price <= 0 || $stock <= 0) {

        echo "Price and stock must be greater than zero.";

    } else {

        $stmt = $conn->prepare("INSERT INTO products (brand, model, price, image, badge, category_id, stock) VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->bind_param("ssdssii", $brand, $model, $price, $image, $badge, $category_id, $stock);

        $stmt->execute();
    }
}


/*
|--------------------------------------------------------------------------
| Add Category
|--------------------------------------------------------------------------
*/

// Create a new category
if (isset($_POST["add-category"])) {

    $categoryName = trim($_POST["category-name"]);

    $stmt = $conn->prepare("SELECT id FROM categories WHERE name = ?");

    $stmt->bind_param("s", $categoryName);

    $stmt->execute();

    $check = $stmt->get_result();

    if ($check->num_rows == 0) {

        $stmt = $conn->prepare("INSERT INTO categories (name) VALUES (?)");

        $stmt->bind_param("s", $categoryName);

        $stmt->execute();

        header("Location: admin.php");
        exit();

    } else {

        header(
            "Location: admin.php?error=exists"
        );

        exit();
    }
}


/*
|--------------------------------------------------------------------------
| Load Product For Editing
|--------------------------------------------------------------------------
*/

$editProduct = null;

if (isset($_GET["edit"])) {

    $id = (int) $_GET["edit"];

    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");

    $stmt->bind_param("i", $id);
    $stmt->execute();

    $result = $stmt->get_result();

    $editProduct = $result->fetch_assoc();
}


/*
|--------------------------------------------------------------------------
| Update Product
|--------------------------------------------------------------------------
*/

// Update an existing product
if (isset($_POST["update-product"])) {

    $id = $_POST["id"];
    $brand = $_POST["brand"];
    $model = $_POST["model"];
    $badge = $_POST["badge"];
    $category_id = $_POST["category_id"];
    $price = $_POST["price"];
    $stock = $_POST["stock"];

    $image = $editProduct["image"];

    // Upload a new image if provided
    if (
        isset($_FILES['image']) && $_FILES['image']['error'] == 0
    ) {

        $image = "uploads/products/".basename($_FILES["image"]["name"]);

        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    }

    $stmt = $conn->prepare("UPDATE products SET brand = ?, model = ?, badge = ?, image = ?, category_id = ?, price = ?, stock = ? WHERE id = ?");

    $stmt->bind_param("ssssidii", $brand, $model, $badge, $image, $category_id, $price, $stock, $id);

    $stmt->execute();

    header("Location: admin.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Velora Admin</title>
    <link rel="stylesheet" href="css/admin.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
 </head>

<body>
    <!-- ===================== ADMIN HEADER =============================== -->
    <header class="admin-header">
        <div class="admin-logo">
            <img src="images/logo.png" alt="Velora">
        </div>
        <div class="admin-title">
            <h2>Admin Panel</h2>
            <a href="logout.php">Logout</a>
        </div>
    </header>
    <!-- ========================= ADMIN DASHBOARD ========================= -->
    <div class="admin-page">
        <div class="admin-dashboard">
            <!-- ================ ADMIN NAVIGATION TABS ============================ -->
            <div id="admin-tabs">
                <!-- Search Products Tab -->
                <div id="search-wrapper">
                    <button onclick="Showtab('search-products')">
                        <i class="fa-solid fa-magnifying-glass">
                            &nbsp;
                        </i>
                        Search Products

                    </button>
                </div>
                <!-- Add Product Tab -->
                <div id="add-product-wrapper">
                    <button onclick="Showtab('add-product')">
                        <i class="fa-solid fa-plus">
                            &nbsp;
                        </i>
                        Add Product
                    </button>
                </div>
                <!-- Edit Product Tab -->
                <div id ="edit-product-wrapper">
                    <button onclick="toggleEditMode()">
                        <i class="fa-solid fa-pen">
                            &nbsp;
                        </i>
                        Edit Product
                    </button>
                </div>
                <!-- Add Category Tab -->
                <div id="add-category-wrapper">
                    <button onclick="Showtab('add-category')">
                        <i class="fa-solid fa-folder-plus">
                            &nbsp;
                        </i>
                        Add Category
                    </button>
                </div>
                <!-- Categories Tab -->
                <div id="categories-wrapper">
                    <button onclick="Showtab('categories')">
                        <i class="fa-solid fa-list">
                            &nbsp;
                        </i>
                        Categories
                    </button>
                </div>
            </div>
            <!-- ================= ADMIN SECTIONS =============== -->
            <div id="tabs-content" class="admin-sections">

                <!-- Search Products Section -->
                <div id="search-products" class="admin-section">
                    <h2 class="section-title">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        &nbsp;Search Product
                    </h2>
                    <form method="GET">
                        <input type="text" name="search" placeholder="Search Product...">
                        <button type="submit">
                            Search
                        </button>
                    </form>
                </div>

                <!-- Add Product Section -->
                <div id="add-product" class="admin-section">
                    <h2 class="section-title">
                        <i class="fa-solid fa-plus"></i>
                        &nbsp; Add Product
                    </h2>
                    <form action="" method="POST" enctype="multipart/form-data">
                        <input type="text" name="brand" placeholder="Brand" required>
                        <input type="text" name="model" placeholder="Model" required>
                        <select name="category_id" required>
                            <option value="">
                                Select Category
                            </option>
                            <?php
                            $categories->data_seek(0);
                            while ($category = $categories->fetch_assoc()) {
                                ?>
                                <option value="<?php echo $category['id']; ?>">
                                    <?php echo $category['name']; ?>
                                </option>
                            <?php } ?>
                        </select>
                        <input type="number" name="price" min="0.01" step="0.01" placeholder="Price" required>
                        <label for="image-upload" class="custom-file-upload">
                            <i class="fa-solid fa-image"></i>
                            Choose Image
                        </label>
                        <input type="file" id="image-upload" name="image" accept="image/*"required>
                        <span id="file-name"> No image selected</span>
                        <img id="image-preview" src="" alt="Preview"></span>
                        <select name="badge" required>
                            <option value="">Select Badge</option>
                            <option value="NEW">NEW</option>
                            <option value="SALE">SALE</option>
                            <option value="HOT">HOT</option>
                            <option value="LIMITED">LIMITED</option>
                        </select>
                        <input type="number" name="stock" min="1" placeholder="Stock" required>
                        <button type="submit" name="add-product">
                            Add Product
                        </button>
                    </form>
                </div>

                <!-- Add Category Section -->
                <div id="add-category" class="admin-section">
                    <h2 class="section-title">
                        <i class="fa-solid fa-folder-plus"></i>
                        &nbsp; Add Category
                    </h2>
                    <?php
                    if (isset($_GET['error']) && $_GET['error'] == 'exists') {
                        echo "<p> Category already exists.</p>";
                    }
                    ?>
                    <form method="POST">
                        <input type="text" name="category-name" placeholder="Category Name" required>
                        <button type="submit" name="add-category">
                            Add Category
                        </button>
                    </form>
                    <div class="admin-categories">
                        <h3>Categories</h3>
                        <?php
                        $categories->data_seek(0);
                        while ($category = $categories->fetch_assoc()) {
                            ?>
                            <p>
                                <?php echo $category['name']; ?>
                                <a href="admin.php?delete-category=<?php echo $category['id']; ?>"
                                    onclick="return confirm ('Delete this category?')">Delete</a>
                            </p>
                        <?php } ?>
                    </div>
                </div>

                <!-- Categories Filter Section -->
                <div id="categories" class="admin-section">
                    <h2 class="section-title">
                        <i class="fa-solid fa-list"></i>
                        &nbsp; categories
                    </h2>

                    <div class="admin-categories">
                        <a href="admin.php">All Products</a>
                        <?php $categories->data_seek(0);
                        while ($category = $categories->fetch_assoc()) {
                            ?>
                            <a href="admin.php?category=<?php echo $category['id']; ?>">
                                <?php echo $category['name']; ?>
                            </a>
                        <?php } ?>
                    </div>
                </div>

            </div>
            <hr>
                <!-- =============== EDIT PRODUCT MODAL ====================== -->
                <?php if ($editProduct){?>
                <div class="edit-modal">
                    <div class="edit-modal-content">
                        <!--Modal Header -->
                        <div class="modal-header">
                        <h2>Edit Product</h2>
                        <button class="close-modal" onclick="closeEditModal()">
                            x
                        </button>
                        </div>
                        <!--Product Update Form -->
                        <form class="edit-form" method="POST" enctype="multipart/form-data">
                            <div class="edit-body">
                            <!--Product Image Section -->    
                            <div class="image-section">
                                <img id="edit-image-preview" src="<?php echo $editProduct['image'];?>">
                                <label for="edit-image-upload" class="upload-btn">
                                    <i class="fa-solid fa-image"></i>
                                    Change Image 
                                </label>
                                <input type="file" name="image" id="edit-image-upload" hidden>
                            </div>
                            <!--Product Information Form -->
                            <div class="form-grid">
                                <div class="form-group">
                                    <input type="hidden" name="id" value="<?php echo $editProduct['id']; ?>">
                                    <label>Brand</label>
                                    <input type="text" name="brand" value="<?php echo $editProduct['brand'];?>">
                                </div>
                                <div class="form-group">
                                    <label>Model</label>
                                <input type="text" name="model" value="<?php echo $editProduct['model'];?>">                                
                                </div>
                                <div class="form-group">
                                    <label>Badge</label>
                                     <select name="badge" required>
                                        <option value="NEW" <?php if ($editProduct['badge'] == 'NEW') echo "selected";?>>
                                            NEW
                                        </option>
                                        <option value="SALE" <?php if ($editProduct['badge'] == 'SALE') echo "selected";?>>
                                            SALE
                                        </option>
                                        <option value="HOT" <?php if ($editProduct['badge'] == 'HOT') echo "selected";?>>
                                            HOT
                                        </option>
                                        <option value="LIMITED" <?php if ($editProduct['badge'] == 'LIMITED') echo "selected";?>>
                                            LIMITED
                                        </option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Category</label>
                                    <select name="category_id" required>
                                        <option value="">
                                            Select Category
                                        </option>
                                        <?php
                                        $categories->data_seek(0);
                                        while ($category = $categories->fetch_assoc()) {
                                        ?>
                                        <option value="<?php echo $category['id']; ?>"
                                            <?php if ($editProduct['category_id'] == $category['id']) echo 'selected'; ?>>
                                            <?php echo $category['name']; ?>
                                        </option>
                                        <?php } ?>
                                    </select>
                                </div>
                                 <div class="form-group">
                                    <label>Price</label>
                                    <input type="number" name="price" value="<?php echo $editProduct['price'];?>">                                
                                </div>
                                 <div class="form-group">
                                    <label>Stock</label>
                                    <input type="number" name="stock" value="<?php echo $editProduct['stock'];?>">
                                </div>
                            </div>
                            </div>
                            <!-- Modal Actions -->
                            <div class="edit-buttons">
                                <button type="button" class="cancel-btn" onclick="closeEditModal()">
                                    Cancel
                                </button>
                                <button type="submit" name="update-product" class="preview-btn">
                                    Update Product
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php } ?>
                <!--============= Product List ============= -->
                <h2>Products</h2>
                <div class="admin-products">
                    <?php
                    if (isset($_GET['search']) && !empty($_GET['search'])) {
                        $search = $_GET['search'];
                        $products = $conn->query("SELECT * FROM products WHERE brand LIKE '%$search%' OR model LIKE '%$search%'");
                    } elseif (isset($_GET['category'])) {
                        $category_id = $_GET['category'];
                        $products = $conn->query("SELECT * FROM products WHERE category_id = '$category_id'");
                    } else {
                        $products = $conn->query("SELECT * FROM products");
                    }
                    while ($product = $products->fetch_assoc()) { ?>
                        <div class="admin-product">
                            <img src="<?php echo $product['image']; ?>" width="100">
                            <h3><?php echo $product['brand']; ?></h3>
                            <p><?php echo $product['model'];?></p>
                            <p>CHF <?php echo $product['price']; ?></p>
                            <p><?php echo $product['badge']; ?></p>
                            <p>Stock: <?php echo $product['stock']; ?></p>
                            
                            <div class="edit-actions">
                                <a href="admin.php?edit=<?php echo $product['id']; ?>">
                                Edit
                                </a>
                                <a href="admin.php?delete=<?php echo $product['id']; ?>" onclick="return confirm('Tem a certeza que quer apagar este produto?')">
                                    Delete
                                </a>
                            </div>
                        </div>
                    <?php } ?>
                </div>
            
        </div>
    </div>
    </div>
    </div>

    <script src="js/admin.js"></script>
</body>

</html>