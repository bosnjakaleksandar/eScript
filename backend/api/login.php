<?php
session_start();

include 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

try {
    include 'db.php';
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed");
    }

    $input = file_get_contents("php://input");
    $data = json_decode($input);

    if (!$data || !isset($data->email) || !isset($data->password)) {
        throw new Exception("Invalid input data");
    }

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->bind_param("s", $data->email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($data->password, $user['password'])) {
        unset($user['password']);
        $_SESSION['user'] = $user;
        error_log("login.php: Authentication successful. Session data: " . print_r($_SESSION, true));


        $response = [
            "success" => true,
            "user" => $user,
            "role" => $user['role'],
        ];
        echo json_encode($response);
    } else {
        $response = ["success" => false, "error" => "Invalid username or password"];
        echo json_encode($response);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
