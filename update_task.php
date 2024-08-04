<?php
session_start();
include 'db.php'; // Include the database connection

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Initialize variables
$taskId = '';
$taskName = '';
$taskDescription = '';
$taskOutput = '';
$taskRecommendationStart = '';
$taskRecommendationEnd = '';
$message = '';
$message_type = '';

// Check if ID is set in the URL
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $taskId = $_GET['id'];

    // Fetch task details from the database
    $stmt = $conn->prepare("SELECT * FROM tasks WHERE id = ?");
    $stmt->bind_param("i", $taskId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $task = $result->fetch_assoc();
        $taskName = $task['task_name'];
        $taskDescription = $task['task_description'];
        $taskOutput = $task['task_output'];
        $taskRecommendationStart = $task['task_recommendation_start'];
        $taskRecommendationEnd = $task['task_recommendation_end'];
    } else {
        $message = 'Task not found.';
        $message_type = 'error';
    }

    $stmt->close();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $taskName = $_POST['task_name'];
    $taskDescription = $_POST['task_description'];
    $taskOutput = $_POST['task_output'];
    $taskRecommendationStart = (int)$_POST['task_recommendation_start'];
    $taskRecommendationEnd = (int)$_POST['task_recommendation_end'];

    // Validate input
    if (empty($taskName) || empty($taskDescription) || empty($taskOutput) || !is_numeric($taskRecommendationStart) || !is_numeric($taskRecommendationEnd)) {
        $message = 'Please fill in all fields correctly.';
        $message_type = 'error';
    } else {
        // Update task in the database
        $stmt = $conn->prepare("UPDATE tasks SET task_name = ?, task_description = ?, task_output = ?, task_recommendation_start = ?, task_recommendation_end = ? WHERE id = ?");
        $stmt->bind_param("sssiii", $taskName, $taskDescription, $taskOutput, $taskRecommendationStart, $taskRecommendationEnd, $taskId);

        if ($stmt->execute()) {
            $message = 'Task updated successfully!';
            $message_type = 'success';
            // Clear form fields after successful update
            $taskName = '';
            $taskDescription = '';
            $taskOutput = '';
            $taskRecommendationStart = '';
            $taskRecommendationEnd = '';
        } else {
            $message = 'Error updating task: ' . $stmt->error;
            $message_type = 'error';
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
    <title>Update Task</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">

    <link rel="stylesheet" href="style.css">
    <style>
        .message {
            position: relative;
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
        }
        .message.success {
            color: #28a745;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .message.error {
            color: #dc3545;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .message .close {
            position: absolute;
            top: 10px;
            right: 15px;
            font-size: 1.25rem;
            font-weight: bold;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2>Update Task</h2>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo htmlspecialchars($message_type); ?>">
                <span class="close" onclick="this.parentElement.style.display='none'">&times;</span>
                <p><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php endif; ?>

        <?php if (!empty($taskId)): ?>
            <form method="POST" action="update_task.php?id=<?php echo htmlspecialchars($taskId); ?>">
                <div class="form-group">
                    <label for="task_name">Task Name</label>
                    <input type="text" class="form-control" id="task_name" name="task_name" value="<?php echo htmlspecialchars($taskName); ?>" required>
                </div>
                <div class="form-group">
                    <label for="task_description">Task Description</label>
                    <textarea class="form-control" id="task_description" name="task_description" rows="3" required><?php echo htmlspecialchars($taskDescription); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="task_output">Task Output</label>
                    <textarea class="form-control" id="task_output" name="task_output" rows="3" required><?php echo htmlspecialchars($taskOutput); ?></textarea>
                </div>
                <div class="form-group">
                    <label for="task_recommendation_start">Recommendation Start (%)</label>
                    <input type="number" class="form-control" id="task_recommendation_start" name="task_recommendation_start" value="<?php echo htmlspecialchars($taskRecommendationStart); ?>" required>
                </div>
                <div class="form-group">
                    <label for="task_recommendation_end">Recommendation End (%)</label>
                    <input type="number" class="form-control" id="task_recommendation_end" name="task_recommendation_end" value="<?php echo htmlspecialchars($taskRecommendationEnd); ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Update Task</button>
            </form>
        <?php endif; ?>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
