<?php
session_start();

include 'header.php';

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

include 'db.php';

try {
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? 'Unknown error'));
    }

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        throw new Exception('Invalid request method. Only POST is allowed.');
    }

    $input = file_get_contents("php://input");
    $data = json_decode($input);

    if (!$data || !isset($data->username) || !isset($data->email) || !isset($data->password)) {
        http_response_code(400);
        throw new Exception("Missing required fields (username, email, password)");
    }

    $username = trim($data->username);
    $email = trim($data->email);
    $password = $data->password;

    if (empty($username) || empty($email) || empty($password)) {
        http_response_code(400);
        throw new Exception("Required fields cannot be empty.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        throw new Exception("Invalid email format.");
    }

    $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    if ($stmt === false) throw new Exception("DB prepare error (email check): " . $conn->error);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultEmail = $stmt->get_result();
    if ($resultEmail->num_rows > 0) {
        http_response_code(409);
        $stmt->close();
        throw new Exception("Email already exists.");
    }
    $stmt->close();

    $stmt = $conn->prepare("SELECT id FROM users WHERE username = ? LIMIT 1");
    if ($stmt === false) throw new Exception("DB prepare error (username check): " . $conn->error);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $resultUsername = $stmt->get_result();
    if ($resultUsername->num_rows > 0) {
        http_response_code(409);
        $stmt->close();
        throw new Exception("Username already exists.");
    }
    $stmt->close();

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    if ($hashedPassword === false) {
        throw new Exception("Failed to hash password.");
    }

    $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
    if ($stmt === false) throw new Exception("DB prepare error (insert user): " . $conn->error);
    $stmt->bind_param("sss", $username, $email, $hashedPassword);

    $success = $stmt->execute();

    if ($success) {
        http_response_code(201);
        $newUserId = $conn->insert_id;
        $stmt->close();

        $stmt_get = $conn->prepare("SELECT id, username, email, role FROM users WHERE id = ?");

        if ($stmt_get) {
            $stmt_get->bind_param("i", $newUserId);
            if ($stmt_get->execute()) {
                $result_get = $stmt_get->get_result();
                $newUser = $result_get->fetch_assoc();
                if ($newUser) {
                    $_SESSION['user'] = $newUser;
                    error_log("register.php: Registration successful. User automatically logged in. Session data: " . print_r($_SESSION, true));
                    echo json_encode([
                        "success" => true,
                        "message" => "User registered and logged in successfully",
                        "user" => $newUser
                    ]);
                    $stmt_get->close();
                    $conn->close();
                    exit;
                } else {
                    error_log("register.php: User inserted (ID: {$newUserId}) but failed to fetch for auto-login.");
                }
            } else {
                error_log("register.php: Failed to execute statement to fetch new user: " . $stmt_get->error);
            }
            $stmt_get->close();
        } else {
            error_log("register.php: Failed to prepare statement to fetch new user: " . $conn->error);
        }

        echo json_encode(["success" => true, "message" => "User registered successfully (auto-login failed)."]);
    } else {
        http_response_code(500);
        if (isset($stmt) && $stmt instanceof mysqli_stmt) {
            $stmt->close();
        }
        throw new Exception("Failed to register user: " . $conn->error);
    }

    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
} catch (Exception $e) {
    if (http_response_code() < 400) {
        http_response_code(500);
    }
    echo json_encode(["success" => false, "error" => $e->getMessage()]);

    if (isset($stmt) && $stmt instanceof mysqli_stmt && $stmt->errno === 0) {
        $stmt->close();
    }
    if (isset($stmt_get) && $stmt_get instanceof mysqli_stmt && $stmt_get->errno === 0) {
        $stmt_get->close();
    }
    if (isset($conn) && $conn instanceof mysqli && $conn->errno === 0) {
        $conn->close();
    }
    exit;
}
