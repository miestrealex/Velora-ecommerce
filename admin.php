<?php
include "db.php";


$error = "";


if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    $sql = "DELETE FROM products WHERE id = '$id'";
    $conn->query($sql);
}
if (isset($_GET['delete-category'])) {
    $id = $_GET['delete-category'];
    $check = $conn->query("SELECT * FROM products WHERE category_id = '$id'");
    if ($check->num_rows > 0) {
        header("Location: admin.php?error=categoryinuse");
        exit();
    } else {
        $conn->query("DELETE FROM categories WHERE id = '$id'");
        header("Location: admin.php");
        exit();
    }
}

$categories = $conn->query("SELECT * FROM categories");



if (isset($_POST["add-product"])) {
    $brand = $_POST["brand"];
    $model = $_POST["model"];
    $price = $_POST["price"];
    $badge = $_POST["badge"];
    $imageName = uniqid() . "_" . basename($_FILES['image']['name']);
    $imageTmp = $_FILES['image']['tmp_name'];
    $image = "upload/products/" . $imageName;
    move_uploaded_file($imageTmp, $image);
    $category_id = $_POST["category_id"];
    $stock = $_POST["stock"];
    if ($price <= 0 || $stock <= 0) {
        echo "Price and Stock must be greater than Zero.";
    } else {
        $sql = "INSERT INTO products (brand, model, price, image, badge, category_id, stock) VALUES ('$brand', '$model', '$price', '$image', '$badge', '$category_id', '$stock')";
        $conn->query($sql);
    }
}
if (isset($_POST["add-category"])) {
    $categoryName = $_POST["category-name"];
    $check = $conn->query("SELECT * FROM categories WHERE name = '$categoryName'");
    if ($check->num_rows == 0) {
        $sql = "INSERT INTO categories (name) VALUES ('$categoryName')";
        $conn->query($sql);
        header("Location: admin.php");
        exit();
    } else {
        $error = "Category already exists.";
        header("Location: admin.php?error=exists");
        exit();

    }

}
$editProduct = null;
if (isset($_GET["edit"])) {
    $id = $_GET["edit"];
    $result = $conn->query("SELECT * FROM products WHERE id = '$id'");
    $editProduct = $result->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Velora Admin</title>
    <link rel="stylesheet" href="admin.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="http://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" </head>

<body>
    <header class="admin-header">
        <div class="admin-logo">
            <img src="images/logo.png" alt="Velora">
        </div>
        <div class="admin-title">
            <h2>Admin Panel</h2>
            <a href="logout.php">Logout</a>
        </div>
    </header>
    <div class="admin-page">
        <div class="admin-dashboard">
            <div id="admin-tabs">
                <div id="search-wrapper">
                    <button onclick="Showtab('search-products')">
                        <i class="fa-solid fa-magnifying-glass">
                            &nbsp;
                        </i>
                        Search Products

                    </button>
                </div>
                <div id="add-product-wrapper">
                    <button onclick="Showtab('add-product')">
                        <i class="fa-solid fa-plus">
                            &nbsp;
                        </i>
                        Add Product
                    </button>
                </div>
                <div id ="edit-product-wrapper">
                    <button onclick="toggleEditMode()">
                        <i class="fa-solid fa-pen">
                            &nbsp;
                        </i>
                        Edit Product
                    </button>
                </div>
                <div id="add-category-wrapper">
                    <button onclick="Showtab('add-category')">
                        <i class="fa-solid fa-folder-plus">
                            &nbsp;
                        </i>
                        Add Category
                    </button>
                </div>
                <div id="categories-wrapper">
                    <button onclick="Showtab('categories')">
                        <i class="fa-solid fa-list">
                            &nbsp;
                        </i>
                        Category
                    </button>
                </div>
            </div>
            <div id="tabs-content" class="admin-sections">

                <!---interior do botao search---->
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

                <!---interior do botao add-product---->
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

                <!---interior do botao add-category---->
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

                <!---interior do botao categories---->
                <div id="categories" class="admin-section">
                    <h2 class="section-title">
                        <i class="fa-solid fa-list"></i>
                        &nbsp; Add Category
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
                <?php if ($editProduct){?>
                <div class="edit-modal">
                    <div class="edit-modal-content">
                        <div class="modal-header">
                        <h2>Edit Product</h2>
                        <button class="close-modal" onclick="closeEditModal()">
                            x
                        </button>
                        </div>
                        <form class="edit-form">
                            <div class="edit-body">
                            <div class="image-section">
                                <img id="edit-image-preview" src="<?php echo $editProduct['image'];?>">
                                <label for="edit-image-upload" class="upload-btn">
                                    Change Image 
                                </label>
                                <input type="file" name="image" id="edit-image-upload" hidden>
                            </div>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Brand</label>
                                    <input type="text" value="<?php echo $editProduct['brand'];?>">
                                </div>
                                <div class="form-group">
                                    <label>Model</label>
                                <input type="text" value="<?php echo $editProduct['model'];?>">                                
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
                                    <input type="number" value="<?php echo $editProduct['price'];?>">                                
                                </div>
                                 <div class="form-group">
                                    <label>Stock</label>
                                    <input type="number" value="<?php echo $editProduct['stock'];?>">
                                </div>
                            </div>
                            </div>
                            <div class="edit-buttons">
                                <button type="button" class="cancel-btn" onclick="closeEditModal()">
                                    Cancel
                                </button>
                                <button type="submit" class="preview-btn">
                                    Update Product
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php } ?>
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
                                <a href="#" onclick="return confirm('Tem a certeza que quer apagar este produto?')">
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

    <script>

        function Showtab(tabId) {

            let tab = document.getElementById(tabId);
            let tabsContent = document.getElementById("tabs-content");

            // Fecha se já estiver aberto
            if (tab.style.display === "block") {
                tab.style.display = "none";
                tabsContent.style.display ="none";

                document.getElementById("search-wrapper").classList.remove("active");
                document.getElementById("add-product-wrapper").classList.remove("active");
                document.getElementById("edit-product-wrapper").classList.remove("edit-active");
                document.getElementById("add-category-wrapper").classList.remove("active");
                document.getElementById("categories-wrapper").classList.remove("active");

                return;
            }

            // Fecha todos os painéis
            document.getElementById("search-products").style.display = "none";
            document.getElementById("add-product").style.display = "none";
            document.getElementById("add-category").style.display = "none";
            document.getElementById("categories").style.display = "none";

            // Remove molduras ativas
            document.getElementById("search-wrapper").classList.remove("active");
            document.getElementById("add-product-wrapper").classList.remove("active");
            document.getElementById("edit-product-wrapper").classList.remove("active");
            document.getElementById("add-category-wrapper").classList.remove("active");
            document.getElementById("categories-wrapper").classList.remove("active");

            //desliga o modo edicao 
            document.body.classList.remove("edit-mode");
            document.getElementById("edit-product-wrapper").classList.remove("edit-active");

            // Abre o painel clicado
            tabsContent.style.display = "block";
            tab.style.display = "block";

            // Ativa a moldura correspondente
            if (tabId === "search-products") {
                document.getElementById("search-wrapper").classList.add("active");
            }

            if (tabId === "add-product") {
                document.getElementById("add-product-wrapper").classList.add("active");
            }

            if (tabId === "add-category") {
                document.getElementById("add-category-wrapper").classList.add("active");
            }

            if (tabId === "categories") {
                document.getElementById("categories-wrapper").classList.add("active");
            }
        }
        document.addEventListener("click", function(event) {

    const tabsContent = document.getElementById("tabs-content");
    const adminTabs = document.getElementById("admin-tabs");

    if (
        adminTabs.contains(event.target) ||
        tabsContent.contains(event.target)
    ) {
        return;
    }

    // Esconde todos os painéis
    document.getElementById("search-products").style.display = "none";
    document.getElementById("add-product").style.display = "none";
    document.getElementById("add-category").style.display = "none";
    document.getElementById("categories").style.display = "none";

    // Esconde o contentor principal
    tabsContent.style.display = "none";

    // Remove os estados ativos
    document.getElementById("search-wrapper").classList.remove("active");
    document.getElementById("add-product-wrapper").classList.remove("active");
    document.getElementById("add-category-wrapper").classList.remove("active");
    document.getElementById("categories-wrapper").classList.remove("active");

});

document.getElementById("image-upload").addEventListener("change", function(){

    const file = this.files[0];

    if(file){

        document.getElementById("file-name").textContent = file.name;

        const reader = new FileReader();

        reader.onload = function(e){

            const preview = document.getElementById("image-preview");

            preview.src = e.target.result;
            preview.style.display = "block";

        }

        reader.readAsDataURL(file);

    }

});
let editMode = false;

function toggleEditMode(){
    // Fecha todos os painéis
    document.getElementById("search-products").style.display = "none";
    document.getElementById("add-product").style.display = "none";
    document.getElementById("add-category").style.display = "none";
    document.getElementById("categories").style.display = "none";
    // Remove os estados ativos
    document.getElementById("search-wrapper").classList.remove("active");
    document.getElementById("add-product-wrapper").classList.remove("active");
    document.getElementById("add-category-wrapper").classList.remove("active");
    document.getElementById("categories-wrapper").classList.remove("active");

    editMode = !editMode;
    document.body.classList.toggle("edit-mode");
    document.getElementById("edit-product-wrapper").classList.toggle("edit-active");

    
}

function closeEditModal(){
    window.location.href="admin.php";
}
document.getElementById("edit-image-upload").addEventListener("change", function(){
    const file = this.files[0];
    if (file){
        const reader = new FileReader ();
        reader.onload = function(e){
            document.getElementById("edit-image-preview").src =e.target.result;
        }
        reader.readAsDataURL (file);
    }
});
    </script>
</body>

</html>