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

if (!isset($_GET['subject_id']) || !is_numeric($_GET['subject_id'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing or invalid subject ID parameter.']);
    exit;
}
$subjectId = (int)$_GET['subject_id'];

$notes = [];
$notesTable = 'notes';
$usersTable = 'users';
$userNameColumn = 'username';

try {
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? 'Unknown error'));
    }

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
            WHERE
                n.subject_id = ?
            ORDER BY
                n.created_at DESC";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }

    $stmt->bind_param("i", $subjectId);

    if (!$stmt->execute()) {
        throw new Exception("Failed to execute statement: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result === false) {
        throw new Exception("Failed to get result: " . $stmt->error);
    }

    $notes = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
    $conn->close();

    http_response_code(200);
    echo json_encode(['success' => true, 'notes' => $notes]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'An error occurred while retrieving notes: ' . $e->getMessage()]);
    if (isset($stmt) && $stmt instanceof mysqli_stmt && $stmt->errno === 0) {
        $stmt->close();
    }
    if (isset($conn) && $conn instanceof mysqli && $conn->errno === 0) {
        $conn->close();
    }
    exit;
}
