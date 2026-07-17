<?php
include("db.php");

$loginMessage = "";

$forgotStatus = trim($_GET['forgot_status'] ?? '');
$forgotMessage = '';
if ($forgotStatus === 'success') {
    $forgotMessage = '<div class="message message--success">Password updated successfully. You can now log in.</div>';
} elseif ($forgotStatus === 'notfound') {
    $forgotMessage = '<div class="message">No account found with that email.</div>';
} elseif ($forgotStatus === 'mismatch') {
    $forgotMessage = '<div class="message">New passwords do not match.</div>';
} elseif ($forgotStatus === 'invalid') {
    $forgotMessage = '<div class="message">Please provide a valid email address and complete all fields.</div>';
} elseif ($forgotStatus === 'error') {
    $forgotMessage = '<div class="message">Failed to update password.</div>';
}

if (isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
    } catch (mysqli_sql_exception $e) {
        $loginMessage = '<div class="message">Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }

    if (isset($result) && $result && $result->num_rows > 0) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] == 'teacher') {
                header("Location: dashboard.php");
                exit;
            } else {
                header("Location: view_ipcrf.php");
                exit;
            }
        } else {
            $loginMessage = '<div class="message">Invalid password.</div>';
        }
    } elseif (isset($_POST['login']) && $loginMessage === "") {
        $loginMessage = '<div class="message">No user found.</div>';
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Login - IPCRF</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="login-card">
    <div class="header-brand">
        <span class="header-brand__mark">IP</span>
        <div class="header-brand__text">IPCRF Login</div>
    </div>
    <h2>Login</h2>
    <p>Access your IPCRF dashboard with your DepEd login details.</p>
    <form method="POST" action="">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit" name="login">Login</button>
    </form>
    <?php echo $loginMessage; ?>
    <?php echo $forgotMessage; ?>

    <p class="footer-note">
        Don't have an account? <a href="register.php">Create account</a><br>
        Forgot password? <a href="forgot_password.php">Reset it here</a>
    </p>
</div>
</body>
</html>
