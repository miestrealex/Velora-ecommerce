<?php
require_once ("includes/db.php");

$categories = $conn->query("SELECT * FROM categories");
$id = $_GET["id"];
$result = $conn->query("SELECT * FROM products WHERE id = '$id'");

$product = $result->fetch_assoc();

if (isset($_POST["update"])) {
    $brand = $_POST["brand"];
    $model = $_POST["model"];
    $price = $_POST["price"];
    $image = $_POST["image"];
    $badge = $_POST["badge"];
    $stock = $_POST["stock"];
    $category_id = $_POST["category_id"];
    $conn->query("UPDATE products SET brand='$brand', model='$model', price='$price', image='$image', badge='$badge', stock='$stock', category_id='$category_id'  
    WHERE id='$id'");
    header("Location: admin.php");
    exit();
}


?>
<html>
    <head>
        <link rel="stylesheet" href="css/style.css">
    </head>
    <body>

        <h1>Edit Product</h1>
        <div class="edit-container">
            <form method="POST">
                <input type="text" name="brand" value="<?php echo $product['brand']; ?>" required>
                <input type="text" name="model" value="<?php echo $product['model']; ?>" required>
                <input type="number" name="price" value="<?php echo $product['price']; ?>" required>
                <input type="text" name="image" value="<?php echo $product['image']; ?>" required>
                <select name="category_id" required>
                    <?php while($category = $categories->fetch_assoc()) { ?><option value="<?php echo $category ['id']; ?>" <?php if ($product['category_id'] == $category['id']) echo "selected";?>> <?php echo $category ["name"]; ?>
                        </option>
                    <?php } ?>
                </select>
                <select name="badge" required>
                    <option value="NEW" <?php if( $product['badge']=="NEW") echo "seleted"; ?>>NEW</option>
                    <option value="SALE" <?php if( $product['badge']=="SALE") echo "seleted"; ?>>SALE</option>
                    <option value="HOT" <?php if( $product['badge']=="HOT") echo "seleted"; ?>>HOT</option>
                    <option value="LIMITED"<?php if( $product['badge']=="LIMITED") echo "seleted"; ?>>LIMITED</option>
                </select>
                <input type="number" name="stock" value="<?php echo $product['stock']; ?>" required>
                <button type="submit" name="update">
                    Update Product
                </button>
            </form>
        </div>
    </body>
    
</html>
