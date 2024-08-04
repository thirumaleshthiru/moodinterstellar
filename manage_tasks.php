<?php
session_start();
include 'db.php'; // Include the database connection

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Initialize message variables
$message = '';
$message_type = '';

// Check if message and message_type are set in the query string
if (isset($_GET['message']) && isset($_GET['message_type'])) {
    $message = htmlspecialchars($_GET['message']);
    $message_type = htmlspecialchars($_GET['message_type']);
}

// Fetch tasks from the database
$stmt = $conn->prepare("SELECT * FROM tasks");
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Tasks</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .message {
            position: relative;
            margin-top: 20px;
            padding: 15px;
            border-radius: 5px;
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }
        .message.success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }
        .message.error {
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
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
        <h2>Manage Tasks</h2>

        <?php if ($message): ?>
            <div class="message <?php echo htmlspecialchars($message_type); ?>">
                <span class="close" onclick="this.parentElement.style.display='none'">&times;</span>
                <p><?php echo $message; ?></p>
            </div>
        <?php endif; ?>

        <table class="table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Task Name</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($task = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($task['id']); ?></td>
                        <td><?php echo htmlspecialchars($task['task_name']); ?></td>
                        <td>
                            <a href="update_task.php?id=<?php echo htmlspecialchars($task['id']); ?>" class="btn btn-warning btn-sm">Update</a>
                            <a href="delete_task.php?id=<?php echo htmlspecialchars($task['id']); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this task?');">Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <?php include 'footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
