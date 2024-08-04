<?php
session_start();
include 'db.php'; // Include your database connection file

// Check if user is logged in and has the correct role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit();
}

// Check if the story ID is provided
if (isset($_GET['id'])) {
    $storyId = intval($_GET['id']);
    $userId = $_SESSION['user_id'];

    // Prepare and execute the delete statement
    $sql = "DELETE FROM stories WHERE id = ? AND user_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $storyId, $userId);
        if ($stmt->execute()) {
            echo json_encode(['status' => 'success']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Failed to delete story']);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Failed to prepare statement']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'No story ID provided']);
}

$conn->close();
?>
