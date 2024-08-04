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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $recommend_percent_start = $_POST['recommend_percent_start'];
    $recommend_percent_end = $_POST['recommend_percent_end'];
    $keyword = $_POST['keyword'];

    if (!empty($recommend_percent_start) && !empty($recommend_percent_end) && !empty($keyword)) {
        $stmt = $conn->prepare("INSERT INTO keywords (recommend_percent_start, recommend_percent_end, keyword) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $recommend_percent_start, $recommend_percent_end, $keyword);
        $stmt->execute();
        $stmt->close();

        $message = "Keyword added successfully";
        $message_type = "success";
    } else {
        $message = "Please fill in all fields";
        $message_type = "error";
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Keyword</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php generateNavbar(); ?>

    <div class="container mt-4">
        <h2>Add Keyword</h2>
        <?php if (isset($message)): ?>
            <div class="alert alert-<?php echo htmlspecialchars($message_type); ?> alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($message); ?>
                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
        <?php endif; ?>
        <form action="add_keyword.php" method="post">
            <div class="form-group">
                <label for="recommend_percent_start">Recommend Percent Start</label>
                <input type="number" class="form-control" id="recommend_percent_start" name="recommend_percent_start" required>
            </div>
            <div class="form-group">
                <label for="recommend_percent_end">Recommend Percent End</label>
                <input type="number" class="form-control" id="recommend_percent_end" name="recommend_percent_end" required>
            </div>
            <div class="form-group">
                <label for="keyword">Keyword</label>
                <input type="text" class="form-control" id="keyword" name="keyword" required>
            </div>
            <button type="submit" class="btn btn-primary">Add Keyword</button>
        </form>
    </div>

    <?php generateFooter(); ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
