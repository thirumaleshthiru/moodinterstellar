<?php
session_start();
include 'db.php'; // Include the database connection

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle form submission for adding a new journey question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_question'])) {
    $question = $_POST['question'];
    $options = $_POST['options']; // Expecting an array of options
    $preferences = $_POST['preferences']; // Array of preferences

    // Insert the question into the journey_questions table
    $stmt = $conn->prepare("INSERT INTO journey_questions (question) VALUES (?)");
    $stmt->bind_param("s", $question);
    $stmt->execute();
    $question_id = $stmt->insert_id;
    $stmt->close();

    // Insert options into the journey_question_options table
    foreach ($options as $index => $option) {
        $preference = $preferences[$index];
        $stmt = $conn->prepare("INSERT INTO journey_question_options (question_id, option, preference) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $question_id, $option, $preference);
        $stmt->execute();
        $stmt->close();
    }

    header("Location: add_journey_questions.php?message=Question+added+successfully&message_type=success");
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
    <title>Add Journey Questions</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php generateNavbar(); ?>

    <div class="container mt-4">
        <h2>Add Journey Questions</h2>
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-<?php echo htmlspecialchars($_GET['message_type']); ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['message']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        <form action="add_journey_questions.php" method="post">
            <div class="form-group">
                <label for="question">Question</label>
                <textarea class="form-control" id="question" name="question" rows="3" required></textarea>
            </div>
            <div id="options-container">
                <div class="form-group">
                    <label for="option_1">Option 1</label>
                    <input type="text" class="form-control" id="option_1" name="options[]" required>
                    <label for="preference_1">Preference</label>
                    <input type="number" class="form-control" id="preference_1" name="preferences[]" step="0.01" min="0" max="1" required>
                </div>
            </div>
            <button type="button" class="btn btn-secondary" id="add-option-btn">Add More Options</button>
            <button type="submit" class="btn btn-primary" name="add_question">Add Question</button>
        </form>
    </div>

    <?php generateFooter(); ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.getElementById('add-option-btn').addEventListener('click', function() {
            const container = document.getElementById('options-container');
            const optionCount = container.getElementsByClassName('form-group').length + 1;
            const newOptionHTML = `
                <div class="form-group">
                    <label for="option_${optionCount}">Option ${optionCount}</label>
                    <input type="text" class="form-control" id="option_${optionCount}" name="options[]" required>
                    <label for="preference_${optionCount}">Preference</label>
                    <input type="number" class="form-control" id="preference_${optionCount}" name="preferences[]" step="0.01" min="0" max="1" required>
                </div>
            `;
            container.insertAdjacentHTML('beforeend', newOptionHTML);
        });
    </script>
</body>
</html>
