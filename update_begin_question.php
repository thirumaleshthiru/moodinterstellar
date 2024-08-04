<?php
session_start();
include 'db.php'; // Include the database connection

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle form submission for updating a question
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_question'])) {
    $id = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $question = isset($_POST['question']) ? $_POST['question'] : '';
    $options = isset($_POST['options']) ? $_POST['options'] : []; // Array of options
    $preferences = isset($_POST['preferences']) ? $_POST['preferences'] : []; // Array of preferences

    // Update the question text
    $query = "UPDATE beginning_questions SET question = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("si", $question, $id);
    $stmt->execute();

    // Delete existing options for this question
    $query = "DELETE FROM beginning_question_options WHERE question_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Insert updated options
    foreach ($options as $index => $option) {
        $preference = isset($preferences[$index]) ? $preferences[$index] : 0;
        $query = "INSERT INTO beginning_question_options (question_id, option, preference) VALUES (?, ?, ?)";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iss", $id, $option, $preference);
        $stmt->execute();
    }

    // Check for errors
    if ($stmt->error) {
        $message = "Error updating beginning question: " . $stmt->error;
        $message_type = "danger";
    } else {
        $message = "Beginning question updated successfully!";
        $message_type = "success";
    }

    // Redirect to manage_begin_question.php with a success or error message
    header("Location: mange_begin_question.php?message=" . urlencode($message) . "&message_type=" . urlencode($message_type));
    exit();
}

// Fetch the question details if ID is provided
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id) {
    // Fetch question details
    $query = "SELECT * FROM beginning_questions WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $question = $result->fetch_assoc();

    // Fetch associated options
    $query = "SELECT * FROM beginning_question_options WHERE question_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $options_result = $stmt->get_result();
    $options = $options_result->fetch_all(MYSQLI_ASSOC);
} else {
    header("Location: mange_begin_question.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Beginning Question</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2>Update Beginning Question</h2>
        
        <?php if (isset($_GET['message'])): ?>
            <div class="alert alert-<?php echo htmlspecialchars($_GET['message_type']); ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['message']); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>

        <form action="update_begin_question.php" method="POST">
            <input type="hidden" name="id" value="<?php echo htmlspecialchars($question['id']); ?>">
            
            <div class="form-group">
                <label for="question">Question</label>
                <textarea class="form-control" id="question" name="question" rows="3" required><?php echo htmlspecialchars($question['question']); ?></textarea>
            </div>
            
            <div id="options-container">
                <?php foreach ($options as $index => $opt): ?>
                <div class="form-group">
                    <label for="option_<?php echo $index + 1; ?>">Option <?php echo $index + 1; ?></label>
                    <input type="text" class="form-control" id="option_<?php echo $index + 1; ?>" name="options[]" value="<?php echo htmlspecialchars($opt['option']); ?>" required>
                    <label for="preference_<?php echo $index + 1; ?>">Preference</label>
                    <input type="number" class="form-control" id="preference_<?php echo $index + 1; ?>" name="preferences[]" value="<?php echo htmlspecialchars($opt['preference']); ?>" step="0.01" min="0" max="1" required>
                </div>
                <?php endforeach; ?>
            </div>
            
            <button type="button" class="btn btn-secondary" id="add-option-btn">Add More Options</button>
            <button type="submit" class="btn btn-primary" name="update_question">Update Question</button>
        </form>
    </div>

    <?php include 'footer.php'; ?>

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
