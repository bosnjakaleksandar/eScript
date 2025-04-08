<?php

session_start();

include 'header.php';

include 'db.php';

include 'session-check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid request method']);
    exit;
}

$input = file_get_contents("php://input");
$data = json_decode($input);

if (
    !isset($data->content) || empty($data->content) ||
    !isset($data->subject_id) || empty($data->subject_id)
) {
    echo json_encode(['success' => false, 'error' => 'Missing required fields']);
    exit;
}

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'error' => 'User not authenticated']);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT id FROM subjects WHERE id = ?");
    $stmt->bind_param("i", $data->subject_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid subject']);
        exit;
    }

    $user_id = get_user_id();
    $stmt = $conn->prepare("INSERT INTO notes (user_id, subject_id, content) VALUES (?, ?, ?)");
    $stmt->bind_param("iis", $user_id, $data->subject_id, $data->content);
    $success = $stmt->execute();

    if ($success) {
        $note_id = $conn->insert_id;
        echo json_encode([
            'success' => true,
            'message' => 'Script submitted successfully',
            'note' => [
                'id' => $note_id,
                'subject_id' => $data->subject_id,
                'content' => $data->content
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to submit script']);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
