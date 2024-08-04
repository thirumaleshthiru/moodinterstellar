<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Register</title>
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="style.css">
<script src="nav.js" defer></script>
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container mt-4">
<h2 class="mb-4">Admin Registration</h2>

<?php
session_start();
include 'db.php';

// Check if user is already logged in
if (isset($_SESSION['user_id']) || isset($_COOKIE['user_id'])) {
    $role = isset($_SESSION['user_role']) ? $_SESSION['user_role'] : (isset($_COOKIE['user_role']) ? $_COOKIE['user_role'] : null);

    if ($role === 'admin') {
        header("Location: admin_dashboard.php");
    } else {
        header("Location: dashboard.php");
    }
    exit();
}

if (isset($_POST['register'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $role = 'admin';
    $profilePic = null;
    $pendingVerification = true; // Set pending verification to true by default

    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $profilePic = file_get_contents($_FILES['profile_pic']['tmp_name']);
    }

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, profile_pic, role, pending_verification) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssssi", $name, $email, $password, $profilePic, $role, $pendingVerification);

    if ($stmt->execute()) {
        header("Location: admin_register.php?success=1");
        exit();
    } else {
        echo "<div class='alert alert-danger' role='alert'>Error: " . $stmt->error . "</div>";
    }

    $stmt->close();
}

$conn->close();
?>

<!-- Display success message if redirected from successful registration -->
<?php if (isset($_GET['success']) && $_GET['success'] == '1'): ?>
<div class="alert alert-success" role="alert">Admin registered successfully!</div>
<?php endif; ?>

<form action="admin_register.php" method="post" enctype="multipart/form-data">
<div class="form-group">
<label for="name">Name:</label>
<input type="text" id="name" name="name" class="form-control" required>
</div>
<div class="form-group">
<label for="email">Email:</label>
<input type="email" id="email" name="email" class="form-control" required>
</div>
<div class="form-group">
<label for="password">Password:</label>
<input type="password" id="password" name="password" class="form-control" required>
</div>
<div class="form-group">
<label for="profile_pic">Profile Picture:</label>
<input type="file" id="profile_pic" name="profile_pic" class="form-control-file" accept="image/*">
</div>
<div class="form-group">
<button type="submit" name="register" class="btn btn-primary">Register</button>
</div>
</form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
