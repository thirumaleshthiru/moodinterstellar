<?php
session_start();
include 'db.php'; // Include the database connection

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Function to generate navbar
function generateNavbar() {
    include 'navbar.php'; // Include the navbar
}

// Function to generate footer
function generateFooter() {
    include 'footer.php'; // Include the footer
}

// Handle delete request
if (isset($_POST['delete_story'])) {
    $story_id = $_POST['story_id'];
    $delete_reason = $_POST['delete_reason'];

    // Get user ID associated with the story
    $stmt = $conn->prepare("SELECT user_id FROM stories WHERE id = ?");
    $stmt->bind_param("i", $story_id);
    $stmt->execute();
    $stmt->bind_result($user_id);
    $stmt->fetch();
    $stmt->close();

    // Store delete reason
    $stmt = $conn->prepare("INSERT INTO delete_reasons (user_id, reason) VALUES (?, ?)");
    $stmt->bind_param("is", $user_id, $delete_reason);
    $stmt->execute();
    $stmt->close();

    // Delete story
    $stmt = $conn->prepare("DELETE FROM stories WHERE id = ?");
    $stmt->bind_param("i", $story_id);
    $stmt->execute();
    $stmt->close();

    $message = "Story deleted successfully";
    $message_type = "success";
}

// Fetch all stories
$result = $conn->query("SELECT stories.id, stories.story_title, users.name AS username FROM stories JOIN users ON stories.user_id = users.id");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Stories</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php generateNavbar(); ?>

    <div class="container mt-4">
        <h2>Manage Stories</h2>
        <?php if (isset($message)): ?>
            <div class="alert alert-<?php echo htmlspecialchars($message_type); ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Title</th>
                    <th>Username</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['story_title']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td>
                            <button class="btn btn-danger btn-sm" data-toggle="modal" data-target="#deleteModal<?php echo $row['id']; ?>">Delete</button>
                        </td>
                    </tr>

                    <!-- Delete Modal -->
                    <div class="modal fade" id="deleteModal<?php echo $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="deleteModalLabel<?php echo $row['id']; ?>">Delete Story</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form action="admin_manage_stories.php" method="post">
                                    <div class="modal-body">
                                        <input type="hidden" name="story_id" value="<?php echo $row['id']; ?>">
                                        <div class="form-group">
                                            <label for="delete_reason<?php echo $row['id']; ?>">Reason for Deletion</label>
                                            <textarea class="form-control" id="delete_reason<?php echo $row['id']; ?>" name="delete_reason" required></textarea>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" name="delete_story" class="btn btn-danger">Delete</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php generateFooter(); ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
