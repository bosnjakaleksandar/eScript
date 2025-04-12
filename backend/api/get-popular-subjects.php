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

$subjects = [];
$notesTable = 'notes';
$subjectsTable = 'subjects';
$subjectNameCol = 'name';

try {
    $sql = "SELECT
                s.id AS subject_id,
                s.{$subjectNameCol} AS subject_name,
                COUNT(n.id) AS note_count
            FROM
                {$subjectsTable} s
            LEFT JOIN
                {$notesTable} n ON s.id = n.subject_id
            GROUP BY
                s.id, s.{$subjectNameCol}
            HAVING
                COUNT(n.id) > 0
            ORDER BY
                note_count DESC
            LIMIT 2";

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

    $subjects = $result->fetch_all(MYSQLI_ASSOC);

    $stmt->close();

    $conn->close();

    http_response_code(200);
    echo json_encode(['success' => true, 'subjects' => $subjects]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'An error occurred while retrieving popular subjects: ' . $e->getMessage()]);

    if (isset($stmt) && $stmt instanceof mysqli_stmt && $stmt->errno === 0) {
        $stmt->close();
    }
    if (isset($conn) && $conn instanceof mysqli && $conn->errno === 0) {
        $conn->close();
    }
    exit;
}
