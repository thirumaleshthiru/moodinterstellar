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

// Handle deletion of a question
if (isset($_GET['delete']) && is_numeric($_GET['delete'])) {
    $question_id = $_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM journey_questions WHERE id = ?");
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $stmt->close();
    header("Location: manage_journey_questions.php?message=Question+deleted+successfully&message_type=success");
    exit();
}

// Retrieve all journey questions
$questions = [];
$result = $conn->query("SELECT * FROM journey_questions");
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $questions[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Journey Questions</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php generateNavbar(); ?>

    <div class="container mt-4">
        <h2>Manage Journey Questions</h2>
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-<?php echo htmlspecialchars($_GET['message_type']); ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['message']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Question</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($questions as $question): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($question['id']); ?></td>
                        <td><?php echo htmlspecialchars($question['question']); ?></td>
                        <td>
                            <a href="update_journey_question.php?id=<?php echo htmlspecialchars($question['id']); ?>" class="btn btn-warning btn-sm">Update</a>
                            <a href="manage_journey_questions.php?delete=<?php echo htmlspecialchars($question['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this question?');">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php generateFooter(); ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
