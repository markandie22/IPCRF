<?php
include("db.php");

$errorMessage = '';
$successMessage = '';

if (isset($_POST['forgot_password'])) {
    $forgotEmail = trim($_POST['forgot_email'] ?? '');
    $newPassword = $_POST['new_password'] ?? '';
    $confirmNewPassword = $_POST['confirm_new_password'] ?? '';

    if ($forgotEmail === '' || $newPassword === '' || $confirmNewPassword === '' || !filter_var($forgotEmail, FILTER_VALIDATE_EMAIL)) {
        $errorMessage = 'Please provide a valid email address and complete all fields.';
    } elseif ($newPassword !== $confirmNewPassword) {
        $errorMessage = 'New passwords do not match.';
    } else {
        try {
            $findStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $findStmt->bind_param("s", $forgotEmail);
            $findStmt->execute();
            $findResult = $findStmt->get_result();

            if ($findResult && $findResult->num_rows > 0) {
                $hashedNewPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                $updateStmt = $conn->prepare("UPDATE users SET password = ? WHERE email = ?");
                $updateStmt->bind_param("ss", $hashedNewPassword, $forgotEmail);

                if ($updateStmt->execute()) {
                    $successMessage = 'Password updated successfully. You can now log in.';
                } else {
                    $errorMessage = 'Failed to update password.';
                }
            } else {
                $errorMessage = 'No account found with that email.';
            }
        } catch (mysqli_sql_exception $e) {
            $errorMessage = 'Database error: ' . htmlspecialchars($e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Forgot Password - IPCRF</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="login-card">
    <div class="header-brand">
        <span class="header-brand__mark">IP</span>
        <div class="header-brand__text">Password Recovery</div>
    </div>
    <h2>Forgot Password</h2>
    <p>Reset your password using your registered email address.</p>

    <form method="POST" action="">
        <label for="forgot_email">Registered Email</label>
        <input type="email" id="forgot_email" name="forgot_email" required>

        <label for="new_password">New Password</label>
        <input type="password" id="new_password" name="new_password" required>

        <label for="confirm_new_password">Confirm New Password</label>
        <input type="password" id="confirm_new_password" name="confirm_new_password" required>

        <button type="submit" name="forgot_password">Reset Password</button>
    </form>

    <?php if ($successMessage !== ''): ?>
        <div class="message message--success"><?php echo $successMessage; ?></div>
    <?php endif; ?>

    <?php if ($errorMessage !== ''): ?>
        <div class="message"><?php echo $errorMessage; ?></div>
    <?php endif; ?>

    <p class="footer-note">
        Back to login: <a href="index.php">Login here</a><br>
        Need an account? <a href="register.php">Create account</a>
    </p>
</div>
</body>
</html>
