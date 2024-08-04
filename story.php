<?php
session_start();
include 'db.php'; // Ensure this file contains your database connection code

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch story details
if (!isset($_GET['id'])) {
    echo "Story ID is required.";
    exit();
}

$story_id = $_GET['id'];
$story = null;
$sql = "SELECT s.id, s.story_title, s.story_content, s.story_cover_image, 
               u.id AS author_id, u.name AS author_name, COUNT(l.id) AS likes
        FROM stories s
        LEFT JOIN likes l ON s.id = l.story_id
        LEFT JOIN users u ON s.user_id = u.id
        WHERE s.id = ?
        GROUP BY s.id";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $story_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $story = $result->fetch_assoc();
    $stmt->close();
}

if (!$story) {
    echo "Story not found.";
    exit();
}

// Check if the user has liked the story
$liked = false;
$sql = "SELECT 1 FROM likes WHERE story_id = ? AND user_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("ii", $story_id, $_SESSION['user_id']);
    $stmt->execute();
    $stmt->store_result();
    if ($stmt->num_rows > 0) {
        $liked = true;
    }
    $stmt->close();
}

// Check if the user is following the author of the story
$following = false;
if ($story['author_id'] !== $_SESSION['user_id']) {
    $sql = "SELECT 1 FROM following WHERE user_id = ? AND following_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $_SESSION['user_id'], $story['author_id']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $following = true;
        }
        $stmt->close();
    }
}

// Check if the user is followed by the story author
$followedByAuthor = false;
if ($story['author_id'] !== $_SESSION['user_id']) {
    $sql = "SELECT 1 FROM followers WHERE user_id = ? AND follower_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $story['author_id'], $_SESSION['user_id']);
        $stmt->execute();
        $stmt->store_result();
        if ($stmt->num_rows > 0) {
            $followedByAuthor = true;
        }
        $stmt->close();
    }
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($story['story_title']); ?></title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .story-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .story-image {
            max-width: 100%;
            height: 300px;
            object-fit: cover;
            margin-bottom: 20px;
        }
        .story-content p {
            white-space: pre-wrap; /* Preserve newlines and formatting in story content */
        }
        .like-button, .follow-button {
            background-color: #ccc;
            color: #fff;
            border: none;
            padding: 10px 20px;
            cursor: pointer;
        }
        .like-button.liked {
            background-color: #ff0000;
        }
        .follow-button.following {
            background-color: #ff0000;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4 story-container">
        <h2><?php echo htmlspecialchars($story['story_title']); ?></h2>
        
        <?php if ($story['story_cover_image']): ?>
            <img src="data:image/jpeg;base64,<?php echo base64_encode($story['story_cover_image']); ?>" alt="<?php echo htmlspecialchars($story['story_title']); ?>" class="story-image">
        <?php endif; ?>
        
        <div class="story-content">
            <?php echo nl2br( $story['story_content'] ); ?>
        </div>
        
        <div class="mt-4">
            <form action="like_story.php" method="post" style="display: inline;">
                <input type="hidden" name="story_id" value="<?php echo htmlspecialchars($story['id']); ?>">
                <button type="submit" class="like-button <?php echo $liked ? 'liked' : ''; ?>"><?php echo $liked ? 'Unlike' : 'Like'; ?> (<?php echo $story['likes']; ?>)</button>
            </form>
            
            <?php if ($story['author_id'] !== $_SESSION['user_id']): ?>
                <form action="follow_user.php" method="post" style="display: inline;">
                    <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($story['author_id']); ?>">
                    <input type="hidden" name="story_id" value="<?php echo htmlspecialchars($story['id']); ?>">
                    <button type="submit" class="follow-button <?php echo $following ? 'following' : ''; ?>"><?php echo $following ? 'Unfollow' : 'Follow'; ?></button>
                </form>
            <?php endif; ?>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
