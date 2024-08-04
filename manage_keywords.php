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
if (isset($_POST['delete_keyword'])) {
    $keyword_id = $_POST['keyword_id'];
    $stmt = $conn->prepare("DELETE FROM keywords WHERE id = ?");
    $stmt->bind_param("i", $keyword_id);
    $stmt->execute();
    $stmt->close();

    $message = "Keyword deleted successfully";
    $message_type = "success";
}

// Handle update request
if (isset($_POST['update_keyword'])) {
    $keyword_id = $_POST['keyword_id'];
    $recommend_percent_start = $_POST['recommend_percent_start'];
    $recommend_percent_end = $_POST['recommend_percent_end'];
    $keyword = $_POST['keyword'];

    if (!empty($recommend_percent_start) && !empty($recommend_percent_end) && !empty($keyword)) {
        $stmt = $conn->prepare("UPDATE keywords SET recommend_percent_start = ?, recommend_percent_end = ?, keyword = ? WHERE id = ?");
        $stmt->bind_param("iisi", $recommend_percent_start, $recommend_percent_end, $keyword, $keyword_id);
        $stmt->execute();
        $stmt->close();

        $message = "Keyword updated successfully";
        $message_type = "success";
    } else {
        $message = "Please fill in all fields";
        $message_type = "error";
    }
}

// Fetch all keywords
$result = $conn->query("SELECT * FROM keywords");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Keywords</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php generateNavbar(); ?>

    <div class="container mt-4">
        <h2>Manage Keywords</h2>
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
                    <th>Recommend Percent Start</th>
                    <th>Recommend Percent End</th>
                    <th>Keyword</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id']); ?></td>
                        <td><?php echo htmlspecialchars($row['recommend_percent_start']); ?></td>
                        <td><?php echo htmlspecialchars($row['recommend_percent_end']); ?></td>
                        <td><?php echo htmlspecialchars($row['keyword']); ?></td>
                        <td>
                            <button class="btn btn-primary btn-sm" data-toggle="modal" data-target="#updateModal<?php echo $row['id']; ?>">Update</button>
                            <form action="manage_keywords.php" method="post" style="display:inline-block;">
                                <input type="hidden" name="keyword_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_keyword" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this keyword?');">Delete</button>
                            </form>
                        </td>
                    </tr>

                    <!-- Update Modal -->
                    <div class="modal fade" id="updateModal<?php echo $row['id']; ?>" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel<?php echo $row['id']; ?>" aria-hidden="true">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="updateModalLabel<?php echo $row['id']; ?>">Update Keyword</h5>
                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                        <span aria-hidden="true">&times;</span>
                                    </button>
                                </div>
                                <form action="manage_keywords.php" method="post">
                                    <div class="modal-body">
                                        <input type="hidden" name="keyword_id" value="<?php echo $row['id']; ?>">
                                        <div class="form-group">
                                            <label for="recommend_percent_start<?php echo $row['id']; ?>">Recommend Percent Start</label>
                                            <input type="number" class="form-control" id="recommend_percent_start<?php echo $row['id']; ?>" name="recommend_percent_start" value="<?php echo htmlspecialchars($row['recommend_percent_start']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="recommend_percent_end<?php echo $row['id']; ?>">Recommend Percent End</label>
                                            <input type="number" class="form-control" id="recommend_percent_end<?php echo $row['id']; ?>" name="recommend_percent_end" value="<?php echo htmlspecialchars($row['recommend_percent_end']); ?>" required>
                                        </div>
                                        <div class="form-group">
                                            <label for="keyword<?php echo $row['id']; ?>">Keyword</label>
                                            <input type="text" class="form-control" id="keyword<?php echo $row['id']; ?>" name="keyword" value="<?php echo htmlspecialchars($row['keyword']); ?>" required>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                                        <button type="submit" name="update_keyword" class="btn btn-primary">Update</button>
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
