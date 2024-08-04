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

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php generateNavbar(); ?>

    <div class="container mt-4">
        <h2>Admin Dashboard</h2>
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="list-group">
                    <a href="add_task.php" class="list-group-item list-group-item-action">Add Task</a>
                    <a href="manage_tasks.php" class="list-group-item list-group-item-action">Manage Tasks</a>
                    
                    <a href="add_begin_questions.php" class="list-group-item list-group-item-action">Add Beginning Questions</a>
                    <a href="manage_begin_question.php" class="list-group-item list-group-item-action">Manage Beginning Questions</a>
                    
                    <a href="add_journey_questions.php" class="list-group-item list-group-item-action">Add Journey Questions</a>
                    <a href="manage_journey_questions.php" class="list-group-item list-group-item-action">Manage Journey Questions</a>
                    
                    <a href="add_keyword.php" class="list-group-item list-group-item-action">Add Keyword</a>
                    <a href="manage_keywords.php" class="list-group-item list-group-item-action">Manage Keywords</a>
                    
                    <a href="admin_manage_stories.php" class="list-group-item list-group-item-action">Manage Stories</a>

                    <!-- New Links -->
                    <a href="add_category.php" class="list-group-item list-group-item-action">Add Category</a>
                    <a href="manage_categories.php" class="list-group-item list-group-item-action">Manage Categories</a>
                </div>
            </div>
            <div class="col-md-9">
                <h4>Welcome to the Admin Dashboard</h4>
                <p>From this dashboard, you can manage tasks, questions, keywords, user stories, and categories. Use the links on the left to navigate through the admin functions.</p>
            </div>
        </div>
    </div>

    <?php generateFooter(); ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
