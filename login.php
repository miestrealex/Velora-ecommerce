<?php

// Start user session
session_start();

// Allow access only through POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

// Include database connection
require_once "includes/db.php";

// Retrieve and sanitize form data
$email = trim($_POST["email"]);
$password = trim($_POST["password"]);

// Validate required fields
if (empty($email) || empty($password)) {
    header("Location: index.php?error=emptyfields");
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: index.php?error=invalidemail");
    exit();
}

// Search user by email
$stmt = $conn->prepare("SELECT id, username, password, role FROM users WHERE email = ?");

$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Verify password and create session
if ($user && password_verify($password, $user["password"])) {

    $_SESSION["user"] = $user["username"];
    $_SESSION["user_id"] = $user["id"];
    $_SESSION["role"] = $user["role"];

    header("Location: index.php");
    exit();
}

// Invalid credentials
header("Location: index.php?error=invalidlogin");
exit();