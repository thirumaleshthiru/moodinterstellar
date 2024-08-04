<?php
session_start();
include 'db.php'; // Include the database connection

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Check if task ID is provided
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $task_id = intval($_GET['id']);

    // Prepare and execute the delete query
    $stmt = $conn->prepare("DELETE FROM tasks WHERE id = ?");
    $stmt->bind_param("i", $task_id);

    if ($stmt->execute()) {
        // Redirect to manage_tasks.php with success message
        header("Location: manage_tasks.php?message=Task+deleted+successfully!&message_type=success");
    } else {
        // Redirect to manage_tasks.php with error message
        header("Location: manage_tasks.php?message=Failed+to+delete+task!&message_type=error");
    }

    $stmt->close();
} else {
    // Redirect to manage_tasks.php with error message if ID is not valid
    header("Location: manage_tasks.php?message=Invalid+task+ID!&message_type=error");
}

$conn->close();
?>
