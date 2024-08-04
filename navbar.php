<?php
include 'db.php'; // Include the database connection

// Fetch the user's profile picture if logged in
$profile_pic_url = null;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    
    // Prepare and execute query to fetch profile picture
    $stmt = $conn->prepare("SELECT profile_pic FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $stmt->bind_result($profile_pic);
    if ($stmt->fetch()) {
        if ($profile_pic) {
            // Encode profile picture data to base64 for displaying as an image
            $profile_pic_url = 'data:image/jpeg;base64,' . base64_encode($profile_pic);
        }
    }
    $stmt->close();
}
?>

<nav class="navbar navbar-expand-lg navbar-light bg-light">
    <a class="navbar-brand" href="index.php">MOODINTERSTELLAR</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav ml-auto d-flex align-items-center">
            <?php if (isset($_SESSION['user_id'])): ?>
                <!-- User is logged in -->
                <li class="nav-item mx-2">
                    <a class="nav-link" href="<?php echo ($_SESSION['user_role'] === 'admin') ? 'admin_dashboard.php' : 'dashboard.php'; ?>">Dashboard</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="logout.php">Logout</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="stories.php">Stories</a>
                </li>
                <?php if ($profile_pic_url): ?>
                    <!-- Profile Picture -->
                    <li class="nav-item d-none d-md-block mx-2">
                        <img src="<?php echo $profile_pic_url; ?>" alt="Profile Picture" class="rounded-circle" style="width: 30px; height: 30px;">
                    </li>
                <?php endif; ?>
            <?php else: ?>
                <!-- User is not logged in -->
                <li class="nav-item mx-2">
                    <a class="nav-link" href="login.php">Login</a>
                </li>
                <li class="nav-item mx-2">
                    <a class="nav-link" href="register.php">Register</a>
                </li>
            <?php endif; ?>
        </ul>
    </div>
</nav>
