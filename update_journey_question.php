<?php
session_start();
include 'db.php'; // Include the database connection

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Check if the question ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header("Location: manage_journey_questions.php?message=Invalid+question+ID&message_type=error");
    exit();
}

$question_id = $_GET['id'];

// Handle form submission for updating the question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_question'])) {
    $question = $_POST['question'];
    $options = $_POST['options']; // Expecting an array of options
    $preferences = $_POST['preferences']; // Array of preferences

    // Update the question in the journey_questions table
    $stmt = $conn->prepare("UPDATE journey_questions SET question = ? WHERE id = ?");
    $stmt->bind_param("si", $question, $question_id);
    $stmt->execute();
    $stmt->close();

    // Delete existing options for this question
    $stmt = $conn->prepare("DELETE FROM journey_question_options WHERE question_id = ?");
    $stmt->bind_param("i", $question_id);
    $stmt->execute();
    $stmt->close();

    // Insert updated options into the journey_question_options table
    foreach ($options as $index => $option) {
        $preference = $preferences[$index];
        $stmt = $conn->prepare("INSERT INTO journey_question_options (question_id, option, preference) VALUES (?, ?, ?)");
        $stmt->bind_param("iss", $question_id, $option, $preference);
        $stmt->execute();
    }
    $stmt->close();

    header("Location: manage_journey_questions.php?message=Question+updated+successfully&message_type=success");
    exit();
}

// Retrieve the existing question and its options
$stmt = $conn->prepare("SELECT * FROM journey_questions WHERE id = ?");
$stmt->bind_param("i", $question_id);
$stmt->execute();
$result = $stmt->get_result();
$question = $result->fetch_assoc();
$stmt->close();

$stmt = $conn->prepare("SELECT * FROM journey_question_options WHERE question_id = ?");
$stmt->bind_param("i", $question_id);
$stmt->execute();
$options_result = $stmt->get_result();
$options = [];
while ($row = $options_result->fetch_assoc()) {
    $options[] = $row;
}
$stmt->close();

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
    <title>Update Journey Question</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php generateNavbar(); ?>

    <div class="container mt-4">
        <h2>Update Journey Question</h2>
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-<?php echo htmlspecialchars($_GET['message_type']); ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['message']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        <form action="update_journey_question.php?id=<?php echo htmlspecialchars($question_id); ?>" method="post">
            <div class="form-group">
                <label for="question">Question</label>
                <textarea class="form-control" id="question" name="question" rows="3" required><?php echo htmlspecialchars($question['question']); ?></textarea>
            </div>
            <div id="options-container">
                <?php foreach ($options as $index => $option): ?>
                    <div class="form-group">
                        <label for="option_<?php echo $index + 1; ?>">Option <?php echo $index + 1; ?></label>
                        <input type="text" class="form-control" id="option_<?php echo $index + 1; ?>" name="options[]" value="<?php echo htmlspecialchars($option['option']); ?>" required>
                        <label for="preference_<?php echo $index + 1; ?>">Preference</label>
                        <input type="number" class="form-control" id="preference_<?php echo $index + 1; ?>" name="preferences[]" value="<?php echo htmlspecialchars($option['preference']); ?>" step="0.01" min="0" max="1" required>
                    </div>
                <?php endforeach; ?>
            </div>
            <button type="button" class="btn btn-secondary" id="add-option-btn">Add More Options</button>
            <button type="submit" class="btn btn-primary" name="update_question">Update Question</button>
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
