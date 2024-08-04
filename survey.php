<?php
session_start();
include 'db.php'; // Include the database connection

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Check if survey is completed
$user_id = $_SESSION['user_id'];
$sql = "SELECT is_survey_completed FROM users WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($is_survey_completed);
    $stmt->fetch();
    $stmt->close();
    if ($is_survey_completed) {
        header("Location: dashboard.php");
        exit();
    }
} else {
    die("Error preparing statement: " . $conn->error);
}

// Handle survey submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $responses = [
        isset($_POST['question1']) ? floatval($_POST['question1']) : null,
        isset($_POST['question2']) ? floatval($_POST['question2']) : null,
        isset($_POST['question3']) ? floatval($_POST['question3']) : null,
        isset($_POST['question4']) ? floatval($_POST['question4']) : null,
        isset($_POST['question5']) ? floatval($_POST['question5']) : null,
    ];

    // Check if any response is missing
    if (in_array(null, $responses, true)) {
        echo '<script>alert("Please answer all questions before submitting.");</script>';
    } else {
        $total_preference = array_sum($responses);
        $num_questions = count($responses);
        $mental_score_percentage = ($total_preference / ($num_questions * 1.0)) * 100;

        // Update the user's survey completion status and mental score
        $sql = "UPDATE users SET is_survey_completed = TRUE, mental_score_percentage = ? WHERE id = ?";
        if ($stmt = $conn->prepare($sql)) {
            $stmt->bind_param("di", $mental_score_percentage, $user_id);
            if ($stmt->execute()) {
                header("Location: dashboard.php");
                exit();
            } else {
                die("Error updating survey status: " . $stmt->error);
            }
            $stmt->close();
        } else {
            die("Error preparing statement: " . $conn->error);
        }
    }
}

// Fetch questions and options
$questions = [];
$sql = "SELECT id, question FROM beginning_questions";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        $questions[$row['id']] = ['question' => $row['question'], 'options' => []];
    }
    $result->free();
} else {
    die("Error fetching questions: " . $conn->error);
}

$sql = "SELECT question_id, option, preference FROM beginning_question_options";
if ($result = $conn->query($sql)) {
    while ($row = $result->fetch_assoc()) {
        if (isset($questions[$row['question_id']])) {
            $questions[$row['question_id']]['options'][] = [
                'option' => $row['option'],
                'preference' => $row['preference']
            ];
        }
    }
    $result->free();
} else {
    die("Error fetching question options: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Survey</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .question-container {
            display: none;
            opacity: 0;
            transition: opacity 0.5s ease-in-out;
        }
        .question-container.active {
            display: block;
            opacity: 1;
        }
        .card-option {
            cursor: pointer;
            transition: background-color 0.3s, color 0.3s;
            text-align: center;
            border: 1px solid #ddd;
            margin-bottom: 0.5rem;
            padding: 10px;
            flex: 1;
            min-width: 100px;
        }
        .card-option:hover {
            background-color: #e9ecef;
        }
        .card-option.selected {
            background-color: #007bff;
            color: white;
        }
        .card-options-container {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2>Complete the Survey</h2>
        <form id="survey-form" action="survey.php" method="post">
            <?php $i = 1; ?>
            <?php foreach ($questions as $q_id => $question): ?>
                <div id="question<?php echo $i; ?>" class="question-container <?php echo ($i === 1) ? 'active' : ''; ?>">
                    <div class="form-group">
                        <label for="question<?php echo $i; ?>"><?php echo htmlspecialchars($question['question']); ?></label>
                        <div class="card-options-container">
                            <?php foreach ($question['options'] as $option): ?>
                                <div class="card card-option mb-2" data-question="<?php echo $i; ?>" data-value="<?php echo htmlspecialchars($option['preference']); ?>">
                                    <div class="card-body">
                                        <?php echo htmlspecialchars($option['option']); ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                    <div class="mt-3">
                        <?php if ($i > 1): ?>
                            <button type="button" class="btn btn-secondary" onclick="showQuestion(<?php echo $i - 1; ?>)">Previous</button>
                        <?php endif; ?>
                        <?php if ($i < count($questions)): ?>
                            <button type="button" class="btn btn-primary" onclick="showQuestion(<?php echo $i + 1; ?>)">Next</button>
                        <?php else: ?>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php $i++; ?>
            <?php endforeach; ?>
        </form>
    </div>

    <script>
        let responses = {}; // Object to store responses

        document.querySelectorAll('.card-option').forEach(card => {
            card.addEventListener('click', function() {
                let questionNumber = parseInt(this.dataset.question);
                let selectedValue = parseFloat(this.dataset.value);

                // Mark the selected option
                document.querySelectorAll(`.card-option[data-question="${questionNumber}"]`).forEach(option => {
                    option.classList.remove('selected');
                });
                this.classList.add('selected');

                // Store the selected value
                responses[questionNumber] = selectedValue;
                console.log(responses); // Debugging: check stored responses
            });
        });

        function showQuestion(questionNumber) {
            document.querySelectorAll('.question-container').forEach(container => {
                container.classList.remove('active');
            });
            document.getElementById('question' + questionNumber).classList.add('active');
        }

        document.getElementById('survey-form').addEventListener('submit', function(e) {
            // Check if all questions have been answered
            for (let i = 1; i <= <?php echo count($questions); ?>; i++) {
                if (responses[i] === undefined) {
                    e.preventDefault();
                    alert('Please answer all questions before submitting.');
                    return false;
                }

                // Append responses as hidden inputs to the form
                let input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'question' + i;
                input.value = responses[i];
                this.appendChild(input);
            }
        });
    </script>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
