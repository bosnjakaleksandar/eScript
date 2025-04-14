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

$rater_user_id = get_user_id();
if ($rater_user_id === null) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Could not retrieve user ID from session.']);
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    exit;
}

$input = file_get_contents("php://input");
$data = json_decode($input);

if (!$data || !isset($data->note_id) || !is_numeric($data->note_id) || !isset($data->grade) || !is_numeric($data->grade)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Missing or invalid input data (note_id, grade).']);
    exit;
}
$note_id = (int)$data->note_id;
$grade = (int)$data->grade;

if ($grade < 0 || $grade > 5) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Grade must be between 0 and 5.']);
    exit;
}

try {
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? 'Unknown error'));
    }

    $notesTable = 'notes';
    $gradesTable = 'grades';

    $stmt_check_author = $conn->prepare("SELECT user_id FROM {$notesTable} WHERE id = ?");
    if ($stmt_check_author === false) throw new Exception('DB Check Author Prepare Error: ' . $conn->error);
    $stmt_check_author->bind_param("i", $note_id);
    if (!$stmt_check_author->execute()) throw new Exception('DB Check Author Execute Error: ' . $stmt_check_author->error);
    $result_author = $stmt_check_author->get_result();
    $note_data = $result_author->fetch_assoc();
    $stmt_check_author->close();

    if (!$note_data) {
        http_response_code(404);
        throw new Exception('Note not found.');
    }
    if ($note_data['user_id'] == $rater_user_id) {
        http_response_code(403); // Forbidden
        throw new Exception('You cannot rate your own note.');
    }

    $sql = "INSERT INTO {$gradesTable} (user_id, note_id, grade) VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE grade = VALUES(grade), created_at = CURRENT_TIMESTAMP";

    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        throw new Exception("DB Prepare Error (Insert/Update Grade): " . $conn->error);
    }

    $stmt->bind_param("iii", $rater_user_id, $note_id, $grade);

    $executeSuccess = $stmt->execute();
    if ($executeSuccess === false) {
        if ($conn->errno == 1452) {
            http_response_code(404);
            throw new Exception("Cannot rate. The specified note (ID: {$note_id}) may not exist.");
        }
        throw new Exception("DB Execute Error (Insert/Update Grade): " . $stmt->error);
    }

    $stmt->close();
    $conn->close();

    http_response_code(200);
    echo json_encode(['success' => true, 'message' => 'Rating saved successfully.']);
} catch (Exception $e) {
    if (http_response_code() < 400) {
        http_response_code(500);
    }
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    if (isset($stmt_check_author) && $stmt_check_author instanceof mysqli_stmt) {
        $stmt_check_author->close();
    }
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    exit;
}
