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

if (!isset($data->name) || empty($data->name) || !isset($data->year) || !in_array($data->year, [1, 2, 3, 4])) {
    echo json_encode(['success' => false, 'error' => 'Invalid subject data']);
    exit;
}

if (!is_logged_in() || !is_admin()) {
    echo json_encode([
        'success' => false,
        'error' => 'Permission denied',
        'debug' => [
            'is_logged_in' => is_logged_in(),
            'user_role' => get_user_role()
        ]
    ]);
    exit;
}

try {
    $stmt = $conn->prepare("SELECT id FROM subjects WHERE name = ?");
    $stmt->bind_param("s", $data->name);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo json_encode(['success' => false, 'error' => 'Subject already exists']);
        exit;
    }

    $stmt = $conn->prepare("INSERT INTO subjects (name, year) VALUES (?, ?)");
    $stmt->bind_param("si", $data->name, $data->year);
    $success = $stmt->execute();

    if ($success) {
        $subject_id = $conn->insert_id;
        echo json_encode([
            'success' => true,
            'message' => 'Subject created successfully',
            'subject' => [
                'id' => $subject_id,
                'name' => $data->name,
                'year' => $data->year
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Failed to create subject']);
    }

    $stmt->close();
    $conn->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}
