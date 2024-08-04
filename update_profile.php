<?php
session_start();
include 'db.php'; // Include the database connection

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Retrieve and sanitize input data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $role = trim($_POST['role']);
    $profession = trim($_POST['profession']);
    $mental_score_percentage = intval($_POST['mental_score_percentage']);
    
    // Handle profile picture upload
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] == 0) {
        $profile_pic = file_get_contents($_FILES['profile_pic']['tmp_name']);
    } else {
        $profile_pic = null; // No update for profile picture
    }
    
    // Update user information
    $sql = "UPDATE users 
            SET name = ?, email = ?, profile_pic = ?, role = ?, profession = ?, mental_score_percentage = ?
            WHERE id = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("ssssiii", $name, $email, $profile_pic, $role, $profession, $mental_score_percentage, $user_id);
        $stmt->execute();
        $stmt->close();
        
        $success_message = "Profile updated successfully!";
    } else {
        $error_message = "Error preparing statement: " . $conn->error;
    }
}

// Fetch current user profile details
$sql = "SELECT name, email, role, profession, mental_score_percentage, profile_pic 
        FROM users 
        WHERE id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
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
    <title>Update Profile</title>
    <!-- Bootstrap CSS -->
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <?php generateNavbar(); ?>

    <div class="container mt-4">
        <h2>Update Profile</h2>
        
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php elseif (!empty($error_message)): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form action="update_profile.php" method="post" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($profile['name']); ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($profile['email']); ?>" required>
            </div>
            
            <div class="form-group">
                <label for="profession">Profession</label>
                <select class="form-control" id="profession" name="profession" required>
                    <option value="student" <?php echo $profile['profession'] == 'student' ? 'selected' : ''; ?>>Student</option>
                    <option value="employee" <?php echo $profile['profession'] == 'employee' ? 'selected' : ''; ?>>Employee</option>
                </select>
            </div>
             
            <div class="form-group">
                <label for="profile_pic">Profile Picture</label>
                <input type="file" class="form-control-file" id="profile_pic" name="profile_pic">
                <?php if (!empty($profile['profile_pic'])): ?>
                    <img src="data:image/jpeg;base64,<?php echo base64_encode($profile['profile_pic']); ?>" alt="Profile Picture" class="img-thumbnail mt-2" style="width: 150px; height: 150px; object-fit: cover;">
                <?php endif; ?>
            </div>
            <button type="submit" class="btn btn-primary">Update Profile</button>
        </form>
    </div>

    <?php generateFooter(); ?>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
