<?php
session_start();
include 'db.php'; // Ensure this file contains your database connection code

// Check if user is logged in and has the correct role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$story_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$story = null;
$categories = [];
$tags = [];

// Initialize variables
$title = $content = $category = $tags_str = "";
$message = "";

// Fetch story details
if ($story_id > 0) {
    $sql = "SELECT s.*, c.category_name, GROUP_CONCAT(t.tag_name SEPARATOR ', ') AS tags
            FROM stories s
            LEFT JOIN categories c ON s.story_category = c.id
            LEFT JOIN story_tags st ON s.id = st.story_id
            LEFT JOIN tags t ON st.tag_id = t.id
            WHERE s.id = ? AND s.user_id = ?
            GROUP BY s.id";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ii", $story_id, $_SESSION['user_id']);
        $stmt->execute();
        $result = $stmt->get_result();
        $story = $result->fetch_assoc();
        $stmt->close();

        if ($story) {
            $title = htmlspecialchars($story['story_title']);
            $content = htmlspecialchars_decode($story['story_content']);
            $category = $story['story_category'];
            $tags_str = htmlspecialchars($story['tags']);
        }
    }
}

// Fetch categories
$sql = "SELECT id, category_name FROM categories";
if ($stmt = $conn->prepare($sql)) {
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $categories[] = $row;
    }
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = mysqli_real_escape_string($conn, $_POST['title']);
    $content = mysqli_real_escape_string($conn, $_POST['content']);
    $category = intval($_POST['category']);
    $tags = mysqli_real_escape_string($conn, $_POST['tags']);

    // Update story in the database
    $sql = "UPDATE stories SET story_title = ?, story_content = ?, story_category = ? WHERE id = ? AND user_id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssiii", $title, $content, $category, $story_id, $_SESSION['user_id']);
        if ($stmt->execute()) {
            // Update tags
            $conn->query("DELETE FROM story_tags WHERE story_id = $story_id");
            if (!empty($tags)) {
                $tags_array = array_map('trim', explode(',', $tags));
                foreach ($tags_array as $tag) {
                    // Check if tag exists, if not create it
                    $tag_query = $conn->prepare("SELECT id FROM tags WHERE tag_name = ?");
                    $tag_query->bind_param("s", $tag);
                    $tag_query->execute();
                    $tag_result = $tag_query->get_result();
                    if ($tag_result->num_rows > 0) {
                        $tag_id = $tag_result->fetch_assoc()['id'];
                    } else {
                        $conn->query("INSERT INTO tags (tag_name) VALUES ('$tag')");
                        $tag_id = $conn->insert_id;
                    }
                    $tag_query->close();

                    $conn->query("INSERT INTO story_tags (story_id, tag_id) VALUES ($story_id, $tag_id)");
                }
            }

            $message = '<div class="alert alert-success">Story updated successfully!</div>';
        } else {
            $message = '<div class="alert alert-danger">Error updating story: ' . $stmt->error . '</div>';
        }
        $stmt->close();
    } else {
        $message = '<div class="alert alert-danger">Error preparing statement: ' . $conn->error . '</div>';
    }
    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Story</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        #editor-container {
            height: 300px;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2>Edit Story</h2>

        <!-- Display message -->
        <?php if ($message): ?>
            <?php echo $message; ?>
        <?php endif; ?>

        <form action="update_story.php?id=<?php echo htmlspecialchars($story_id); ?>" method="post">
            <div class="form-group">
                <label for="title">Story Title:</label>
                <input type="text" id="title" name="title" class="form-control" value="<?php echo $title; ?>" required>
            </div>

            <div class="form-group">
                <label for="category">Category:</label>
                <select id="category" name="category" class="form-control" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?php echo htmlspecialchars($cat['id']); ?>" <?php if ($category == $cat['id']) echo 'selected'; ?>>
                            <?php echo htmlspecialchars($cat['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="content">Story Content:</label>
                <textarea id="content" name="content" class="form-control" style="display: none;"><?php echo htmlspecialchars($content); ?></textarea>
                <div id="editor-container"></div>
            </div>

            <div class="form-group">
                <label for="tags">Tags (comma-separated):</label>
                <input type="text" id="tags" name="tags" class="form-control" value="<?php echo htmlspecialchars($tags_str); ?>">
            </div>

            <button type="submit" class="btn btn-primary">Update Story</button>
        </form>
    </div>

    <!-- Quill Editor JS -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        var quill = new Quill('#editor-container', {
            theme: 'snow',
            modules: {
                toolbar: [
                    [{ 'header': '1'}, { 'header': '2' }],
                    ['bold', 'italic', 'underline'],
                    ['link'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    ['code-block']
                ]
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            var content = document.querySelector('#content');
            quill.root.innerHTML = content.value;
        });

        document.querySelector('form').addEventListener('submit', function(event) {
            var content = document.querySelector('#content');
            content.value = quill.root.innerHTML;
        });
    </script>
</body>
</html>
