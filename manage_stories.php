<?php
session_start();
include 'db.php'; // Include your database connection file

// Check if user is logged in and has the correct role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Fetch stories from the database
$stories = [];
$sql = "SELECT stories.*, categories.category_name FROM stories
        LEFT JOIN categories ON stories.story_category = categories.id
        WHERE stories.user_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $stories[] = $row;
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Stories</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2>Manage Stories</h2>
        <div id="response-message"></div>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($stories as $story): ?>
                    <tr id="story-<?php echo $story['id']; ?>">
                        <td><?php echo htmlspecialchars($story['story_title']); ?></td>
                        <td><?php echo htmlspecialchars($story['category_name']); ?></td>
                        <td>
                            <a href="update_story.php?id=<?php echo $story['id']; ?>" class="btn btn-info btn-sm">Edit</a>
                            <button class="btn btn-danger btn-sm" onclick="deleteStory(<?php echo $story['id']; ?>)">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>

    <script>
        function deleteStory(storyId) {
            if (confirm('Are you sure you want to delete this story?')) {
                fetch('delete_story.php?id=' + storyId, { method: 'POST' })
                    .then(response => response.json())
                    .then(data => {
                        const messageDiv = document.getElementById('response-message');
                        if (data.status === 'success') {
                            document.getElementById('story-' + storyId).remove();
                            messageDiv.innerHTML = '<div class="alert alert-success">Story deleted successfully.</div>';
                        } else {
                            messageDiv.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                        }
                    })
                    .catch(error => {
                        const messageDiv = document.getElementById('response-message');
                        messageDiv.innerHTML = '<div class="alert alert-danger">An error occurred while deleting the story.</div>';
                        console.error('Error:', error);
                    });
            }
        }
    </script>
</body>
</html>
