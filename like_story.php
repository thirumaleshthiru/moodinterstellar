<?php
session_start();
include 'db.php'; // Ensure this file contains your database connection code

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$story_id = isset($_POST['story_id']) ? intval($_POST['story_id']) : 0;

// Check if the user has already liked the story
$sql = "SELECT 1 FROM likes WHERE user_id = ? AND story_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ii", $user_id, $story_id);
    $stmt->execute();
    $stmt->store_result();
    $is_liked = $stmt->num_rows > 0;
    $stmt->close();
} else {
    die("Error preparing statement: " . $conn->error);
}

if ($is_liked) {
    // Unlike the story
    $sql = "DELETE FROM likes WHERE user_id = ? AND story_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $user_id, $story_id);
        if (!$stmt->execute()) {
            die("Error deleting like: " . $stmt->error);
        }
        $stmt->close();
    } else {
        die("Error preparing statement: " . $conn->error);
    }

    // Decrement the like count in the stories table
    $sql = "UPDATE stories SET likes = likes - 1 WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $story_id);
        if (!$stmt->execute()) {
            die("Error updating like count: " . $stmt->error);
        }
        $stmt->close();
    } else {
        die("Error preparing statement: " . $conn->error);
    }
} else {
    // Like the story
    $sql = "INSERT INTO likes (user_id, story_id) VALUES (?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $user_id, $story_id);
        if (!$stmt->execute()) {
            die("Error inserting like: " . $stmt->error);
        }
        $stmt->close();
    } else {
        die("Error preparing statement: " . $conn->error);
    }

    // Increment the like count in the stories table
    $sql = "UPDATE stories SET likes = likes + 1 WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("i", $story_id);
        if (!$stmt->execute()) {
            die("Error updating like count: " . $stmt->error);
        }
        $stmt->close();
    } else {
        die("Error preparing statement: " . $conn->error);
    }
}

header("Location: story.php?id=" . $story_id);
exit();
