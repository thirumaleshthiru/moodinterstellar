<?php
session_start();
include 'db.php'; // Include the database connection

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$profile_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Fetch user profile details
$sql = "SELECT id, name, email, profile_pic, role, profession, mental_score_percentage
        FROM users
        WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $profile_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $profile = $result->fetch_assoc();
    $stmt->close();
} else {
    die("Error preparing statement: " . $conn->error);
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
    <title>Profile</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
    <style>
        .profile-container {
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }
        .profile-pic {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
        }
        .profile-details {
            margin-left: 20px;
        }
    </style>
</head>
<body>
    <?php generateNavbar(); ?>

    <div class="container mt-4">
        <h2>Profile</h2>
        <div class="profile-container">
            <?php if (!empty($profile['profile_pic'])): ?>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($profile['profile_pic']); ?>" alt="<?php echo htmlspecialchars($profile['name']); ?>" class="profile-pic">
            <?php endif; ?>
            <div class="profile-details">
                <h3><?php echo htmlspecialchars($profile['name']); ?></h3>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($profile['email']); ?></p>
                <p><strong>Role:</strong> <?php echo htmlspecialchars($profile['role']); ?></p>
                <p><strong>Profession:</strong> <?php echo htmlspecialchars($profile['profession']); ?></p>
                <p><strong>Mental Score Percentage:</strong> <?php echo htmlspecialchars($profile['mental_score_percentage']); ?>%</p>
                
                <?php if ($profile_id == $user_id): ?>
                    <a href="update_profile.php" class="btn btn-primary mt-3">Update Profile</a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php generateFooter(); ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
