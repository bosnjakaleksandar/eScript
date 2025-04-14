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

$rankings = [];
$usersTable = 'users';
$notesTable = 'notes';
$gradesTable = 'grades';
$userNameColumn = 'username';

try {
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? 'Unknown error'));
    }

    $sql = "SELECT
                u.id AS user_id,
                u.{$userNameColumn} AS author_name,
                COUNT(DISTINCT n.id) AS note_count,
                COALESCE(AVG(g.grade), 0) AS overall_average_grade,
                COUNT(DISTINCT g.id) AS total_ratings_received
            FROM
                {$usersTable} u
            LEFT JOIN
                {$notesTable} n ON u.id = n.user_id
            LEFT JOIN
                {$gradesTable} g ON n.id = g.note_id
            GROUP BY
                u.id, u.{$userNameColumn}
            ORDER BY
                note_count DESC,
                overall_average_grade DESC
            LIMIT 10";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Failed to prepare statement: " . $conn->error);
    }

    if (!$stmt->execute()) {
        throw new Exception("Failed to execute statement: " . $stmt->error);
    }

    $result = $stmt->get_result();
    if ($result === false) {
        throw new Exception("Failed to get result: " . $stmt->error);
    }

    $rankings = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();
    $conn->close();

    http_response_code(200);
    echo json_encode(['success' => true, 'rankings' => $rankings]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'An error occurred while retrieving rankings: ' . $e->getMessage()]);
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    exit;
}
