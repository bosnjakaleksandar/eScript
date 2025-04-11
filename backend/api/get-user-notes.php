<?php

session_start();

include 'header.php';

include 'db.php';

include 'session-check.php';

if (!is_logged_in()) {
    echo json_encode([
        'success' => false,
        'error' => 'User not authenticated'
    ]);
    exit;
}

$user_id = get_user_id();

if ($user_id === null) {
    echo json_encode([
        'success' => false,
        'error' => 'Could not retrieve user ID'
    ]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT id, title, content, created_at FROM notes WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->bind_param("i", $user_id);

    $stmt->execute();

    $result = $stmt->get_result();

    $notes = [];
    while ($row = $result->fetch_assoc()) {
        $notes[] = $row;
    }

    echo json_encode([
        'success' => true,
        'notes' => $notes
    ]);

    $stmt->close();
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} finally {
    if (isset($conn)) {
        $conn->close();
    }
}
