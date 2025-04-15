<?php
session_start();
include 'header.php';
include 'db.php';
include 'session-check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Invalid request method.']);
    exit;
}
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not authenticated.']);
    if (isset($conn)) {
        $conn->close();
    }
    exit;
}
$user_id = get_user_id();
if ($user_id === null) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Could not retrieve user ID.']);
    if (isset($conn)) {
        $conn->close();
    }
    exit;
}

$notes = [];
$notesTable = 'notes';
$gradesTable = 'grades';

try {
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("DB connection failed: " . ($conn->connect_error ?? 'Unknown error'));
    }

    $sql = "SELECT
                n.id, n.title, n.content, n.subject_id, n.created_at,
                AVG(g.grade) AS average_grade,
                COUNT(g.id) AS rating_count
            FROM
                {$notesTable} n
            LEFT JOIN
                {$gradesTable} g ON n.id = g.note_id
            WHERE
                n.user_id = ?
            GROUP BY
                n.id, n.title, n.content, n.subject_id, n.created_at
            HAVING
                COUNT(g.id) > 0
            ORDER BY
                average_grade DESC, rating_count DESC, n.created_at DESC
            LIMIT 3";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }

    $stmt->bind_param("i", $user_id);

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
    echo json_encode(['success' => false, 'error' => 'An error occurred while retrieving top notes: ' . $e->getMessage()]);
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    exit;
}
