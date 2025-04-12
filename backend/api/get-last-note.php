<?php
session_start();

include 'header.php';
include 'db.php';
include 'session-check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Invalid request method. Only GET is allowed.']);
    exit;
}

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not authenticated.']);
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    exit;
}

$script = null;
$notesTable = 'notes';
$usersTable = 'users';
$userNameColumn = 'username';


try {
    $sql = "SELECT
                n.id,
                n.title,
                n.content,
                n.subject_id,
                n.user_id,
                n.created_at,
                u.{$userNameColumn} AS author_name
            FROM
                {$notesTable} n
            LEFT JOIN
                {$usersTable} u ON n.user_id = u.id
            ORDER BY
                n.created_at DESC
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Failed to prepare SQL statement: " . $conn->error);
    }

    if (!$stmt->execute()) {
        throw new Exception("Failed to execute SQL statement: " . $stmt->error);
    }
    $result = $stmt->get_result();
    if ($result === false) {
        throw new Exception("Failed to get result set: " . $stmt->error);
    }
    $script = $result->fetch_assoc();
    $stmt->close();
    $conn->close();

    if ($script) {
        http_response_code(200);
        echo json_encode(['success' => true, 'script' => $script]);
    } else {
        http_response_code(404);
        echo json_encode(['success' => true, 'script' => null, 'message' => 'No scripts found yet.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'An error occurred while retrieving the last script: ' . $e->getMessage()]);

    if (isset($stmt) && $stmt instanceof mysqli_stmt && $stmt->errno === 0) {
        $stmt->close();
    }
    if (isset($conn) && $conn instanceof mysqli && $conn->errno === 0) {
        $conn->close();
    }
    exit;
}
