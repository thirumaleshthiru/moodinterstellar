<?php
session_start();
include 'db.php'; // Include the database connection

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Check if user has completed the survey
$user_id = $_SESSION['user_id'];
$sql = "SELECT is_survey_completed, mental_score_percentage FROM users WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($is_survey_completed, $mental_score_percentage);
    $stmt->fetch();
    $stmt->close();

    if (!$is_survey_completed) {
        header("Location: survey.php");
        exit();
    }
} else {
    die("Error preparing statement: " . $conn->error);
}

// Determine the mental health category
if ($mental_score_percentage <= 20) {
    $mental_health_status = "Low Mental Well-being";
    $status_description = "Indicates a low level of mental well-being. Consider seeking support or professional help.";
} elseif ($mental_score_percentage <= 40) {
    $mental_health_status = "Below Average Mental Well-being";
    $status_description = "Indicates below-average mental well-being. Explore activities or support that could help improve your mental health.";
} elseif ($mental_score_percentage <= 60) {
    $mental_health_status = "Average Mental Well-being";
    $status_description = "Represents an average level of mental well-being. Consider maintaining current habits and looking for ways to enhance mental health.";
} elseif ($mental_score_percentage <= 80) {
    $mental_health_status = "Good Mental Well-being";
    $status_description = "Indicates a good level of mental well-being. Continue with positive practices and habits that contribute to mental health.";
} else {
    $mental_health_status = "Excellent Mental Well-being";
    $status_description = "Represents an excellent level of mental well-being. Congratulations on maintaining a high level of mental health!";
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
    <title>User Dashboard</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php generateNavbar(); ?>

    <div class="container mt-4">
        <h2>User Dashboard</h2>
        <div class="row">
            <div class="col-md-3 mb-3">
                <div class="list-group">
                    <a href="add_story.php" class="list-group-item list-group-item-action">Add Story</a>
                    <a href="manage_stories.php" class="list-group-item list-group-item-action">Manage Stories</a>
                    
                    <a href="update_profile.php" class="list-group-item list-group-item-action">Update Profile</a>
                    <a href="user_statistics.php" class="list-group-item list-group-item-action">Statistics</a>
                    
                    <a href="followers.php" class="list-group-item list-group-item-action">Followers</a>
                    <a href="following.php" class="list-group-item list-group-item-action">Following</a>
                </div>
            </div>
            <div class="col-md-9">
                <h4>Welcome to your Dashboard</h4>
                <p>From this dashboard, you can manage your stories, update your profile, and view your statistics and followers.</p>

                <!-- Mental Health Status -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h5 class="card-title">Mental Health Status</h5>
                        <p class="card-text"><?php echo htmlspecialchars($mental_health_status); ?></p>
                        <p class="card-text"><?php echo htmlspecialchars($status_description); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php generateFooter(); ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
