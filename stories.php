<?php
session_start();
include 'db.php'; // Ensure this file contains your database connection code

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch all stories
$stories = [];
$sql = "SELECT s.id, s.story_title, s.story_content, s.story_cover_image, 
               COUNT(l.id) AS likes
        FROM stories s
        LEFT JOIN likes l ON s.id = l.story_id
        GROUP BY s.id
        ORDER BY s.id DESC";
if ($stmt = $conn->prepare($sql)) {
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
    <title>Stories</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        .story-card {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            overflow: hidden;
            background-color: #fff;
            padding:10px;
        }
        .story-card img {
            width: 200px;
            height: auto;
            object-fit: cover;
        }
        .story-card-content {
            padding: 20px;
            flex: 1;
        }
        .story-card-content h5 {
            margin: 0 0 10px;
        }
        .story-card-content p {
            margin: 0 0 10px;
            white-space: pre-wrap; /* Preserve newlines and formatting in story content */
        }
        .story-card-content .likes {
            font-size: 16px;
            color: #888;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2>All Stories</h2>

        <?php foreach ($stories as $story): ?>
            <div class="story-card">
                <?php if ($story['story_cover_image']): ?>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($story['story_cover_image']); ?>" alt="<?php echo htmlspecialchars($story['story_title']); ?>">
                <?php else: ?>
                    <img src="path/to/default/image.jpg" alt="Default Image">
                <?php endif; ?>
                <div class="story-card-content">
                    <h5><?php echo htmlspecialchars($story['story_title']); ?></h5>
                  
                    <div class="likes">
                        <i class="fa fa-thumbs-up"></i> <?php echo $story['likes']; ?>
                    </div>
                    <br> 
                    <a href="story.php?id=<?php echo htmlspecialchars($story['id']); ?>" class="btn btn-primary">View Story</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Bootstrap and FontAwesome JS -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/js/all.min.js"></script>
</body>
</html>
