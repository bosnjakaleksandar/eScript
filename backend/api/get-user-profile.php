<?php
session_start();
include 'header.php';
include 'db.php';
include 'session-check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    exit;
}
if (!is_logged_in()) {
    http_response_code(401);
    if (isset($conn)) {
        $conn->close();
    }
    exit;
}

$profile_user_id = null;
if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $profile_user_id = (int)$_GET['user_id'];
} else {
    $profile_user_id = get_user_id();
}

if ($profile_user_id === null) {
    http_response_code(500);
    if (isset($conn)) {
        $conn->close();
    }
    exit;
}

$profileData = null;
$noteCount = 0;
$usersTable = 'users';
$notesTable = 'notes';

try {
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("DB connection failed: " . ($conn->connect_error ?? 'Unknown error'));
    }

    $sqlUser = "SELECT id, username, email, role, profile_image_path FROM {$usersTable} WHERE id = ?";

    $stmtUser = $conn->prepare($sqlUser);
    if ($stmtUser === false) throw new Exception("DB User Prepare Error: " . $conn->error);
    $stmtUser->bind_param("i", $profile_user_id);
    if (!$stmtUser->execute()) throw new Exception("DB User Execute Error: " . $stmtUser->error);
    $resultUser = $stmtUser->get_result();
    if ($resultUser === false) throw new Exception("DB User Result Error: " . $stmtUser->error);
    $profileData = $resultUser->fetch_assoc();
    $stmtUser->close();

    if (!$profileData) {
        http_response_code(404);
        throw new Exception("User profile not found.");
    }

    $sqlCount = "SELECT COUNT(*) as note_count FROM {$notesTable} WHERE user_id = ?";
    $stmtCount = $conn->prepare($sqlCount);
    if ($stmtCount === false) throw new Exception("DB Count Prepare Error: " . $conn->error);
    $stmtCount->bind_param("i", $profile_user_id);
    if (!$stmtCount->execute()) throw new Exception("DB Count Execute Error: " . $stmtCount->error);
    $resultCount = $stmtCount->get_result();
    if ($resultCount === false) throw new Exception("DB Count Result Error: " . $stmtCount->error);
    $countData = $resultCount->fetch_assoc();
    $noteCount = $countData ? (int)$countData['note_count'] : 0;
    $stmtCount->close();
    $conn->close();

    $profileData['note_count'] = $noteCount;

    http_response_code(200);
    echo json_encode(['success' => true, 'profile' => $profileData]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Error retrieving profile data: ' . $e->getMessage()]);
    exit;
}
