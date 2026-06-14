<?php

session_start();

$conn = new mysqli("localhost", "root", "", "loja");
$email = trim($_POST["email"]);
$password = trim($_POST["password"]);

if(empty($email) || empty($password)){
    header("Location: index.php?error=emptyfields");
    exit();
}
if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
    header("location: index.php?error=invalidemail");
    exit();
}

$stmt = $conn-> prepare("SELECT * FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

if ($user && password_verify($password, $user["password"])){
    $_SESSION["user"] = $user["username"];
    $_SESSION["user_id"] = $user["id"];
    $_SESSION["role"] = $user["role"];

    header("Location: index.php");
    exit();
} else {
    header("location: index.php?error=invalidlogin");
    exit();
}
$stmt->close();
?>