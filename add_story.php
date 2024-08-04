<?php
session_start();
include 'db.php'; // Include the database connection

$response = ['status' => 'error', 'message' => ''];

// Check if user is logged in and has the correct role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    $response['message'] = 'User not logged in.';
    echo json_encode($response);
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $title = $_POST['title'];
    $content = $_POST['content'];
    $category = intval($_POST['category']);
    $tags = $_POST['tags'];

    // Handle file upload
    if (isset($_FILES['cover_image']) && $_FILES['cover_image']['error'] === UPLOAD_ERR_OK) {
        $cover_image = file_get_contents($_FILES['cover_image']['tmp_name']);
    } else {
        $response['message'] = 'Cover image is required.';
        echo json_encode($response);
        exit();
    }

    // Prepare and execute the SQL statement to insert the story
    $sql = "INSERT INTO stories (user_id, story_title, story_content, story_cover_image, story_category) VALUES (?, ?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sql)) {
        $user_id = $_SESSION['user_id'];
        $stmt->bind_param("isssi", $user_id, $title, $content, $cover_image, $category);

        if ($stmt->execute()) {
            $story_id = $stmt->insert_id;

            // Handle tags
            $tags_array = array_map('trim', explode(',', $tags));
            foreach ($tags_array as $tag) {
                $tag = mysqli_real_escape_string($conn, $tag);

                // Check if tag exists or insert new tag
                $sql_tag = "SELECT id FROM tags WHERE tag_name = ?";
                if ($tag_stmt = $conn->prepare($sql_tag)) {
                    $tag_stmt->bind_param("s", $tag);
                    $tag_stmt->execute();
                    $result = $tag_stmt->get_result();
                    
                    if ($result->num_rows > 0) {
                        $tag_id = $result->fetch_assoc()['id'];
                    } else {
                        $sql_insert_tag = "INSERT INTO tags (tag_name) VALUES (?)";
                        if ($insert_tag_stmt = $conn->prepare($sql_insert_tag)) {
                            $insert_tag_stmt->bind_param("s", $tag);
                            $insert_tag_stmt->execute();
                            $tag_id = $insert_tag_stmt->insert_id;
                        }
                    }
                    $tag_stmt->close();
                }

                if (isset($tag_id)) {
                    $sql_story_tag = "INSERT INTO story_tags (story_id, tag_id) VALUES (?, ?)";
                    if ($story_tag_stmt = $conn->prepare($sql_story_tag)) {
                        $story_tag_stmt->bind_param("ii", $story_id, $tag_id);
                        $story_tag_stmt->execute();
                    }
                }
            }

            $response['status'] = 'success';
            $response['message'] = 'Story added successfully!';
        } else {
            $response['message'] = 'Error inserting story: ' . $stmt->error;
        }

        $stmt->close();
    } else {
        $response['message'] = 'Error preparing statement: ' . $conn->error;
    }

    $conn->close();
    echo json_encode($response);
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Story</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <!-- Quill Editor CSS -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        #editor-container {
            height: 300px; /* Adjust height as needed */
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2>Add New Story</h2>
        <div id="response-message"></div>

        <form id="story-form" action="add_story.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="title">Story Title:</label>
                <input type="text" id="title" name="title" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="content">Story Content:</label>
                <textarea id="content" name="content" class="form-control" style="display: none;"></textarea>
                <div id="editor-container"></div>
            </div>
            <div class="form-group">
                <label for="category">Category:</label>
                <select id="category" name="category" class="form-control" required>
                    <?php
                    // Fetch categories from the database
                    $sql = "SELECT id, category_name FROM categories";
                    if ($result = $conn->query($sql)) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<option value=\"" . $row['id'] . "\">" . htmlspecialchars($row['category_name']) . "</option>";
                        }
                        $result->free();
                    }
                    ?>
                </select>
            </div>
            <div class="form-group">
                <label for="cover_image">Cover Image:</label>
                <input type="file" id="cover_image" name="cover_image" class="form-control-file" required>
            </div>
            <div class="form-group">
                <label for="tags">Tags (comma-separated):</label>
                <input type="text" id="tags" name="tags" class="form-control">
            </div>
            <button type="submit" class="btn btn-primary">Submit</button>
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

        // Sync Quill editor content with hidden textarea
        document.querySelector('#story-form').addEventListener('submit', function(event) {
            event.preventDefault();
            var content = document.querySelector('textarea[name="content"]');
            content.value = quill.root.innerHTML;

            var formData = new FormData(this);
            fetch('add_story.php', {
                method: 'POST',
                body: formData
            }).then(response => response.json())
              .then(data => {
                  var responseMessage = document.getElementById('response-message');
                  if (data.status === 'success') {
                      responseMessage.innerHTML = '<div class="alert alert-success">' + data.message + '</div>';
                      document.getElementById('story-form').reset();
                      quill.root.innerHTML = '';
                  } else {
                      responseMessage.innerHTML = '<div class="alert alert-danger">' + data.message + '</div>';
                  }
              }).catch(error => {
                  document.getElementById('response-message').innerHTML = '<div class="alert alert-danger">An error occurred: ' + error.message + '</div>';
              });
        });
    </script>
    
    <!-- Bootstrap JS and dependencies -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
