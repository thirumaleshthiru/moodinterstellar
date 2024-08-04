<?php
session_start();
include 'db.php'; 

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $entered_otp = $_POST['otp'];

    // Check if the OTP is correct and not expired
    if (isset($_SESSION['otp']) && $entered_otp == $_SESSION['otp'] && time() <= $_SESSION['otp_expiration']) {
        $username = $_SESSION['username'];
        $password = $_SESSION['password'];
        $email = $_SESSION['email'];
        $profession = $_SESSION['profession'];
        $profile_pic = $_SESSION['profile_pic'];

        // Insert user data into the database
        $insert_query = "INSERT INTO users (name, email, password, profile_pic, profession) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($insert_query);
        if ($stmt) {
            $stmt->bind_param("sssss", $username, $email, $password, $profile_pic, $profession);
            if ($stmt->execute()) {
                $message = "Registration successful!";
                session_unset();
                session_destroy();
                header("Location: login.php");
                exit();
            } else {
                $message = "Failed to register user. Please try again.";
            }
            $stmt->close();
        } else {
            $message = "Failed to prepare the statement.";
        }
    } else {
        $message = "Invalid OTP or OTP has expired.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Verify OTP</title>
    <style>
        .verify-container {
            max-width: 600px;
            margin: 50px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .message {
            margin-bottom: 20px;
            padding: 10px;
            border: 1px solid transparent;
            border-radius: 4px;
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <main>
        <div class="container verify-container">
            <h2 class="text-center">Verify OTP</h2>
            <?php if (!empty($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>
            <form action="verify_otp.php" method="POST">
                <div class="form-group">
                    <label for="otp">Enter OTP:</label>
                    <input type="text" id="otp" name="otp" class="form-control" required>
                </div>
                <button type="submit" class="btn btn-primary btn-block">Verify OTP</button>
            </form>
        </div>
    </main>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
