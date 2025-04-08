<?php

session_start();

include 'header.php';

include 'db.php';

$response = ["success" => false];

$userResult = $conn->query("SELECT COUNT(*) as total FROM users");
if ($userResult) {
    $userData = $userResult->fetch_assoc();
    $response["totalUsers"] = (int)$userData["total"];
} else {
    echo json_encode(["success" => false, "error" => "Failed to count users."]);
    exit();
}

$subjectResult = $conn->query("SELECT COUNT(*) as total FROM subjects");
if ($subjectResult) {
    $subjectData = $subjectResult->fetch_assoc();
    $response["totalSubjects"] = (int)$subjectData["total"];
} else {
    echo json_encode(["success" => false, "error" => "Failed to count subjects."]);
    exit();
}

$response["success"] = true;
echo json_encode($response);
