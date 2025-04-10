<?php

session_start();

include 'header.php';

include 'db.php';

include 'session-check.php';

if (!is_logged_in()) {
    echo json_encode([
        'success' => false,
        'error' => 'User not authenticated',
        'debug' => [
            'session_exists' => isset($_SESSION),
            'session_data' => $_SESSION
        ]
    ]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT id, name, year FROM subjects ORDER BY year ASC, name ASC");
    $stmt->execute();
    $result = $stmt->get_result();

    $subjects = [];
    while ($row = $result->fetch_assoc()) {
        $subjects[] = $row;
    }

    echo json_encode([
        'success' => true,
        'subjects' => $subjects
    ]);

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
