<?php 
require 'db.php';

$username = $_POST["username"];
$email = $_POST["email"];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute([$email]);
if ($stmt->fetch()){
    die ("Email already exists");
}

$stmt = $conn ->prepare("INSERT INTO users (username, email, password) VALUES (?,?,?)");
$stmt->bind_param("sss", $username, $email, $password);
$stmt ->execute([$username, $email, $password]);

header ("Location: index.php");
exit;


?>