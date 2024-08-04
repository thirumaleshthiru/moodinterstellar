<?php
session_start();
include 'db.php'; // Ensure this file contains your database connection code

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch the list of users the logged-in user follows
$sql = "SELECT u.id, u.name, u.email
        FROM following f
        INNER JOIN users u ON f.following_id = u.id
        WHERE f.user_id = ?";
if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $following = $result->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
}
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Following</title>
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container mt-4">
        <h2>Following</h2>
        <?php if (empty($following)): ?>
            <p>You are not following anyone.</p>
        <?php else: ?>
            <ul class="list-group">
                <?php foreach ($following as $user): ?>
                    <li class="list-group-item">
                        <a href="user_profile.php?id=<?php echo $user['id']; ?>">
                            <?php echo htmlspecialchars($user['name']); ?>
                        </a>
                        <span class="text-muted">(<?php echo htmlspecialchars($user['email']); ?>)</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>

    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
