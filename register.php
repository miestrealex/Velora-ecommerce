<?php

// Include database connection
require_once "includes/db.php";

// Allow access only through POST requests
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: index.php");
    exit();
}

// Retrieve and sanitize form data
$username = trim($_POST["username"]);
$email = trim($_POST["email"]);
$password = $_POST["password"];

// Validate required fields
if (empty($username) || empty($email) || empty($password)) {
    header("Location: index.php?registererror=emptyfields");
    exit();
}

// Validate email format
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: index.php?registererror=invalidemail");
    exit();
}

// Check if email already exists
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();

$result = $stmt->get_result();

// Redirect if email is already registered
if ($result->num_rows > 0) {
    header("Location: index.php?registererror=emailtaken");
    exit();
}

// Close first statement
$stmt->close();

// Hash password before storing it
$password = password_hash($password, PASSWORD_DEFAULT);

// Insert new user into database
$stmt = $conn->prepare(
    "INSERT INTO users (username, email, password) VALUES (?, ?, ?)"
);

$stmt->bind_param("sss", $username, $email, $password);
$stmt->execute();

// Redirect to homepage after successful registration
header("Location: index.php");
exit();
?>