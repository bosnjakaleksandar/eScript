<?php
session_start();

include 'header.php';
include 'db.php';
include 'session-check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Invalid request method. Only POST is allowed.']);
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

$user_id = get_user_id();
if ($user_id === null) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Could not retrieve user ID from session.']);
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    exit;
}

$input = file_get_contents("php://input");
$data = json_decode($input);

if (!$data || !isset($data->note_id) || !is_numeric($data->note_id)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing or invalid note ID.']);
    exit;
}
$note_id_to_delete = (int)$data->note_id;

try {
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? 'Unknown error'));
    }

    $tableName = 'notes';

    $sql = "DELETE FROM {$tableName} WHERE id = ? AND user_id = ?";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("Failed to prepare DELETE statement: " . $conn->error);
    }

    $stmt->bind_param("ii", $note_id_to_delete, $user_id);

    $executeSuccess = $stmt->execute();
    if ($executeSuccess === false) {
        throw new Exception("Failed to execute DELETE statement: " . $stmt->error);
    }

    $affected_rows = $stmt->affected_rows;

    $stmt->close();

    $conn->close();

    if ($affected_rows > 0) {
        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Note deleted successfully.']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Note not found or you do not have permission to delete it.']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'An error occurred while deleting the note: ' . $e->getMessage()]);

    if (isset($stmt) && $stmt instanceof mysqli_stmt && $stmt->errno === 0) {
        $stmt->close();
    }
    if (isset($conn) && $conn instanceof mysqli && $conn->errno === 0) {
        $conn->close();
    }
    exit;
}
