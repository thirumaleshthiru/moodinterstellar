<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Login</title>
<link rel="stylesheet" href="style.css">
<link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<?php include 'navbar.php'; ?>
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

// Process login form submission
if (isset($_POST['login'])) {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $remember_me = isset($_POST['remember_me']);

    $sql = "SELECT id, name, email, password, role FROM users WHERE email = ?";
    if ($stmt = $conn->prepare($sql)) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($id, $name, $email, $hashed_password, $role);
            $stmt->fetch();

            if (password_verify($password, $hashed_password)) {
                $_SESSION['user_id'] = $id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = $role;

                if ($remember_me) {
                    setcookie('user_id', $id, time() + (30 * 24 * 60 * 60), '/');
                    setcookie('user_name', $name, time() + (30 * 24 * 60 * 60), '/');
                    setcookie('user_email', $email, time() + (30 * 24 * 60 * 60), '/');
                    setcookie('user_role', $role, time() + (30 * 24 * 60 * 60), '/');
                }

                if ($role === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit();
            } else {
                echo "<p>Incorrect password. Please try again.</p>";
            }
        } else {
            echo "<p>No account found with that email. Please register first.</p>";
        }

        $stmt->close();
    } else {
        echo "<p>Database error: " . $conn->error . "</p>";
    }

    $conn->close();
}
?>

<div class="container">
    <h2>Login</h2>
    <form action="login.php" method="post">
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" id="password" name="password" class="form-control" required>
        </div>
        <div class="form-group">
            <label for="remember_me">
                <input type="checkbox" id="remember_me" name="remember_me">
                Remember Me
            </label>
        </div>
        <div class="form-group">
            <button type="submit" name="login" class="btn btn-primary">Login</button>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.10.2/dist/umd/popper.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
