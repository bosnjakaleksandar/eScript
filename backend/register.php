<?php

include 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include 'db.php';

$data = json_decode(file_get_contents("php://input"));

$username = $data->username ?? null;
$email = $data->email ?? null;
$password = $data->password ?? null;

if (!$username || !$email || !$password) {
    echo json_encode(["error" => "Missing required fields"]);
    exit;
}

$checkEmail = $conn->query("SELECT * FROM users WHERE email = '$email'");
$checkUsername = $conn->query("SELECT * FROM users WHERE username = '$username'");

if ($checkEmail->num_rows > 0) {
    echo json_encode(["error" => "Email already exists"]);
    exit;
}

if ($checkUsername->num_rows > 0) {
    echo json_encode(["error" => "Username already exists"]);
    exit;
}

$hashedPassword = password_hash($password, PASSWORD_DEFAULT);

$query = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$hashedPassword')";
$conn->query($query);

echo json_encode(["success" => true, "message" => "User registered successfully"]);
