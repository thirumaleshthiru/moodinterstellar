<?php
session_start();
include 'db.php'; 
require 'vendor/autoload.php'; 

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
    $email = $_POST['email'];
    $profession = $_POST['profession']; // Include profession field

    // Handle profile image upload
    $profile_pic = null;
    if (isset($_FILES['profile_pic']) && $_FILES['profile_pic']['error'] === UPLOAD_ERR_OK) {
        $profile_pic = file_get_contents($_FILES['profile_pic']['tmp_name']);
    }

    // Check if username or email already exists
    $check_existing_query = "SELECT * FROM users WHERE name = ? OR email = ?";
    $stmt = $conn->prepare($check_existing_query);
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($result->num_rows > 0) {
        // Username or email already exists
        $message = "Username or email already registered. Please use a different username or email.";
    } else {
        // Generate OTP
        $otp = rand(100000, 999999); // Generate 6-digit OTP
        $_SESSION['otp'] = $otp; // Store OTP in session for verification
        $_SESSION['username'] = $username;
        $_SESSION['password'] = $password;
        $_SESSION['email'] = $email;
        $_SESSION['profession'] = $profession;
        $_SESSION['profile_pic'] = $profile_pic;
        $_SESSION['otp_expiration'] = time() + 300; // OTP expiration time (5 minutes)

        // Send OTP via email
        $mail = new PHPMailer(true);
        try {
            $mail->SMTPDebug = 0; // Disable verbose debug output
            $mail->isSMTP(); // Set mailer to use SMTP
            $mail->Host       = 'smtp.office365.com'; // Specify Outlook SMTP servers
            $mail->SMTPAuth   = true; // Enable SMTP authentication
            $mail->Username   = 'methirumaleshgandam@outlook.com'; // SMTP username
            $mail->Password   = 'Thiruout@79'; // SMTP password
            $mail->SMTPSecure = 'tls'; // Enable TLS encryption, `ssl` also accepted
            $mail->Port       = 587; // TCP port to connect to

            // Recipients
            $mail->setFrom('methirumaleshgandam@outlook.com', 'MOODINTERSTELLAR');
            $mail->addAddress($email);  
            $mail->addReplyTo('methirumaleshgandam@outlook.com', 'MOODINTERSTELLAR');

            $mail->isHTML(true);  
            $mail->Subject = 'OTP Verification for Registration';
            $mail->Body    = 'Your OTP for registration is: <b>' . $otp . '</b>';
            $mail->AltBody = 'Your OTP for registration is: ' . $otp;

            $mail->send();
            $message = "Registration successful. Check your email for OTP verification.";
            header("Location: verify_otp.php");
            exit();
        } catch (Exception $e) {
            $message = "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <title>Register</title>
    <style>
        .register-container {
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
        <div class="container register-container">
            <h2 class="text-center">Register</h2>
            <?php if (!empty($message)): ?>
                <div class="message"><?php echo $message; ?></div>
            <?php endif; ?>
            <form action="register.php" method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="profession">Profession:</label>
                    <select id="profession" name="profession" class="form-control" required>
                        <option value="student">Student</option>
                        <option value="employee">Employee</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="profile_pic">Profile Image:</label>
                    <input type="file" id="profile_pic" name="profile_pic" class="form-control-file">
                </div>
                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </form>
        </div>
    </main>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
