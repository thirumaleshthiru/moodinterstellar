<?php
session_start();
include 'db.php'; // Ensure this file contains your database connection code

// Check if user is logged in and form data is set
if (!isset($_SESSION['user_id']) || !isset($_POST['user_id']) || !isset($_POST['story_id'])) {
    header("Location: story.php?id=" . $_POST['story_id']);
    exit();
}

$user_id = $_SESSION['user_id'];
$following_id = $_POST['user_id'];
$story_id = $_POST['story_id'];

// Check if the user is already following the author
$sql = "SELECT 1 FROM following WHERE user_id = ? AND following_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ii", $user_id, $following_id);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        // User is following, so unfollow
        $sql = "DELETE FROM following WHERE user_id = ? AND following_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $user_id, $following_id);
            $stmt->execute();
        }
        
        // Also remove from followers table if they follow you
        $sql = "DELETE FROM followers WHERE user_id = ? AND follower_id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $following_id, $user_id);
            $stmt->execute();
        }
    } else {
        // User is not following, so follow
        $sql = "INSERT INTO following (user_id, following_id) VALUES (?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $user_id, $following_id);
            $stmt->execute();
        }
        
        // Also add to followers table if they don't follow you yet
        $sql = "INSERT INTO followers (user_id, follower_id) VALUES (?, ?)";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("ii", $following_id, $user_id);
            $stmt->execute();
        }
    }
    $stmt->close();
} else {
    // Handle SQL prepare error
    echo "Database query error.";
    exit();
}

$conn->close();

// Redirect back to the story page with the correct story ID
header("Location: story.php?id=" . $story_id);
exit();
?>
