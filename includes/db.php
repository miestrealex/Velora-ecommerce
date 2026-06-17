<?php
$conn = new mysqli("localhost", "root", "", "loja");
if ($conn->connect_error) {
    die ("Connection failed:" . $conn->connect_error);
}
?>