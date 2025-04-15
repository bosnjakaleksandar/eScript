<?php
session_start();
include 'header.php';
include 'db.php';
include 'session-check.php';

$uploadDir = '../uploads/profile_pictures/';
$allowedTypes = ['jpg', 'jpeg', 'png']; 
$maxFileSize = 5 * 1024 * 1024;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Invalid request method. Only POST allowed.']);
    exit;
}

if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'User not authenticated.']);
    if (isset($conn)) $conn->close();
    exit;
}
$user_id = get_user_id();
if ($user_id === null) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Could not retrieve user ID.']);
    if (isset($conn)) $conn->close();
    exit;
}

if (!isset($_FILES['profile_picture']) || $_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
    $uploadErrors = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize directive in php.ini.',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE directive specified in the HTML form.',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
        UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the file upload.',
    ];
    $errorCode = $_FILES['profile_picture']['error'] ?? UPLOAD_ERR_NO_FILE;
    $errorMessage = $uploadErrors[$errorCode] ?? 'Unknown upload error.';
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'File upload error: ' . $errorMessage]);
    exit;
}

$file = $_FILES['profile_picture'];
$fileSize = $file['size'];
$fileTmpName = $file['tmp_name'];
$fileType = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if ($fileSize > $maxFileSize) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'File is too large. Max size is ' . ($maxFileSize / 1024 / 1024) . 'MB.']);
    exit;
}

if (!in_array($fileType, $allowedTypes)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid file type. Allowed types: ' . implode(', ', $allowedTypes) . '.']);
    exit;
}

$newFileName = 'user_' . $user_id . '_' . uniqid('', true) . '.' . $fileType;
$destinationPath = $uploadDir . $newFileName;
$relativePathForDb = 'uploads/profile_pictures/' . $newFileName; // Путања коју чувамо у бази

if (!move_uploaded_file($fileTmpName, $destinationPath)) {
    error_log("Failed to move uploaded file to: " . $destinationPath);
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Failed to save uploaded file. Check server permissions.']);
    exit;
}

$stmt = null;
try {
    if (!isset($conn) || $conn->connect_error) {
        throw new Exception("Database connection failed: " . ($conn->connect_error ?? 'Unknown error'));
    }
    $usersTable = 'users';

    $oldImagePath = null;
    $stmtOld = $conn->prepare("SELECT profile_image_path FROM {$usersTable} WHERE id = ?");
    if ($stmtOld) {
        $stmtOld->bind_param("i", $user_id);
        if ($stmtOld->execute()) {
            $resultOld = $stmtOld->get_result();
            if ($resultOld) {
                $oldData = $resultOld->fetch_assoc();
                $oldImagePath = $oldData['profile_image_path'] ?? null;
            }
        }
        $stmtOld->close();
    }

    $sql = "UPDATE {$usersTable} SET profile_image_path = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    if ($stmt === false) throw new Exception("DB Update Prepare Error: " . $conn->error);
    $stmt->bind_param("si", $relativePathForDb, $user_id);
    if (!$stmt->execute()) throw new Exception("DB Update Execute Error: " . $stmt->error);

    if ($stmt->affected_rows > 0) {
        $stmt->close();
        $conn->close();

        if ($oldImagePath && file_exists('../' . $oldImagePath) && strpos($oldImagePath, 'default') === false) {
            @unlink('../' . $oldImagePath);
        }

        http_response_code(200);
        echo json_encode(['success' => true, 'message' => 'Profile picture updated successfully.', 'new_path' => '/' . $relativePathForDb]);
        exit;
    } else {
        throw new Exception("Failed to update profile picture path in database.");
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'DB Error during profile picture update: ' . $e->getMessage()]);
    if (isset($destinationPath) && file_exists($destinationPath)) {
        @unlink($destinationPath);
    }
    if (isset($stmt) && $stmt instanceof mysqli_stmt) {
        $stmt->close();
    }
    if (isset($conn) && $conn instanceof mysqli) {
        $conn->close();
    }
    exit;
}
