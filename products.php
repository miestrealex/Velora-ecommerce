<?php

/*Products API*/

//Database connetion
require_once "includes/db.php";

//Set JSON response header
header("Content-Type: application/json");

//Fetch all products
$result = $conn->query ("SELECT * FROM products");

//Store products
$products =[];

//Build products array
while ($row = $result->fetch_assoc()){
    $products[] = $row;
}
//Return JSON response
echo json_encode($products);
?>