<?php 
require_once '<include/db.php';

$username = trim($_POST["username"]);
$email = trim($_POST["email"]);
$password = $_POST['password'];

if (empty($username) || empty($email) || empty($password)) {
    header("Location: index.php?registererror=emptyfields");
    exit();
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: index.php?registererror=invalidemail");
    exit();
}
$stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    header ("Location: index.php?registererror=emailtaken");
    exit();
}
$stmt->close();
$password = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn ->prepare("INSERT INTO users (username, email, password) VALUES (?,?,?)");
$stmt->bind_param("sss", $username, $email, $password);
$stmt ->execute();

header ("Location: index.php");
exit;
?>