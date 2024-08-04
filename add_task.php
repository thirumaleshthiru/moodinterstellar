<?php
session_start();
include 'db.php'; // Include the database connection

// Initialize message variables
$message = '';
$message_type = '';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle form submission
if (isset($_POST['add_task'])) {
    $task_name = mysqli_real_escape_string($conn, $_POST['task_name']);
    $task_description = mysqli_real_escape_string($conn, $_POST['task_description']);
    $task_output = mysqli_real_escape_string($conn, $_POST['task_output']); // Ensure task_output is treated as TEXT
    $task_recommendation_start = intval($_POST['task_recommendation_start']);
    $task_recommendation_end = intval($_POST['task_recommendation_end']);

    // Prepare and execute the insert query
    $stmt = $conn->prepare("INSERT INTO tasks (task_name, task_description, task_output, task_recommendation_start, task_recommendation_end) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("sssii", $task_name, $task_description, $task_output, $task_recommendation_start, $task_recommendation_end);

    if ($stmt->execute()) {
        // Redirect to the same page to prevent resubmission
        header("Location: add_task.php?success=1");
        exit();
    } else {
        $message = 'Error: ' . $stmt->error;
        $message_type = 'error';
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
    <title>Add Task</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <style>
        .message {
            position: relative;
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
        }
        .message.success {
            color: #28a745; /* Bootstrap success color */
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        .message.error {
            color: #dc3545; /* Bootstrap error color */
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
        <h2>Add New Task</h2>

        <?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
            <div class="message success">
                <span class="close" onclick="this.parentElement.style.display='none'">&times;</span>
                <p>Task added successfully!</p>
            </div>
        <?php elseif (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <span class="close" onclick="this.parentElement.style.display='none'">&times;</span>
                <p><?php echo htmlspecialchars($message); ?></p>
            </div>
        <?php endif; ?>

        <form action="add_task.php" method="post">
            <div class="form-group">
                <label for="task_name">Task Name:</label>
                <input type="text" id="task_name" name="task_name" class="form-control" value="<?php echo htmlspecialchars($_POST['task_name'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="task_description">Task Description:</label>
                <textarea id="task_description" name="task_description" class="form-control" rows="4" required><?php echo htmlspecialchars($_POST['task_description'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="task_output">Task Output:</label>
                <textarea id="task_output" name="task_output" class="form-control" rows="4" required><?php echo htmlspecialchars($_POST['task_output'] ?? ''); ?></textarea>
            </div>
            <div class="form-group">
                <label for="task_recommendation_start">Recommendation Start (Percentage):</label>
                <input type="number" id="task_recommendation_start" name="task_recommendation_start" class="form-control" min="0" max="100" value="<?php echo htmlspecialchars($_POST['task_recommendation_start'] ?? ''); ?>" required>
            </div>
            <div class="form-group">
                <label for="task_recommendation_end">Recommendation End (Percentage):</label>
                <input type="number" id="task_recommendation_end" name="task_recommendation_end" class="form-control" min="0" max="100" value="<?php echo htmlspecialchars($_POST['task_recommendation_end'] ?? ''); ?>" required>
            </div>
            <button type="submit" name="add_task" class="btn btn-primary">Add Task</button>
        </form>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
