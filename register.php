<?php
include("db.php");
include("school_helper.php");

$registerMessage = '';
$schoolChoices = get_bataan_public_schools();

if (isset($_POST['register'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['reg_email'] ?? '');
    $password = $_POST['reg_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $role = $_POST['role'] ?? 'teacher';
    $schoolName = trim($_POST['school_name'] ?? '');
    $schoolId = null;

    if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
        $registerMessage = '<div class="message">Please fill in all registration fields.</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $registerMessage = '<div class="message">Please provide a valid email address.</div>';
    } elseif ($password !== $confirmPassword) {
        $registerMessage = '<div class="message">Passwords do not match.</div>';
    } elseif (!in_array($role, ['teacher', 'admin', 'super_admin'], true)) {
        $registerMessage = '<div class="message">Invalid role selection.</div>';
    } elseif ($role === 'teacher' && $schoolName === '') {
        $registerMessage = '<div class="message">Please select your school.</div>';
    } elseif ($role === 'teacher' && !is_valid_school_name($schoolName)) {
        $registerMessage = '<div class="message">Invalid school selection.</div>';
    } else {
        try {
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult && $checkResult->num_rows > 0) {
                $registerMessage = '<div class="message">Email is already registered.</div>';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                if ($role !== 'teacher') {
                    $schoolName = null;
                }

                $insertStmt = $conn->prepare("INSERT INTO users (name, email, password, role, school_id, school_name) VALUES (?, ?, ?, ?, ?, ?)");
                $insertStmt->bind_param("ssssis", $name, $email, $hashedPassword, $role, $schoolId, $schoolName);

                if ($insertStmt->execute()) {
                    $registerMessage = '<div class="message message--success">Account created successfully. <a href="index.php">Go to login</a>.</div>';
                } else {
                    $registerMessage = '<div class="message">Failed to create account.</div>';
                }
            }
        } catch (mysqli_sql_exception $e) {
            $registerMessage = '<div class="message">Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Create Account - IPCRF</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="login-card">
    <div class="header-brand">
        <span class="header-brand__mark">IP</span>
        <div class="header-brand__text">IPCRF Account</div>
    </div>
    <h2>Create Account</h2>
    <p>Create your account to access the IPCRF system.</p>
    <form method="POST" action="">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" required>

        <label for="reg_email">Email</label>
        <input type="email" id="reg_email" name="reg_email" required>

        <label for="reg_password">Password</label>
        <input type="password" id="reg_password" name="reg_password" required>

        <label for="confirm_password">Confirm Password</label>
        <input type="password" id="confirm_password" name="confirm_password" required>

        <label for="role">Role</label>
        <select id="role" name="role">
            <option value="teacher" selected>Teacher</option>
            <option value="admin">Admin</option>
            <option value="super_admin">Super Admin</option>
        </select>

        <label for="school_name">School (for Teacher)</label>
        <select id="school_name" name="school_name">
            <option value="">Select school</option>
            <?php foreach ($schoolChoices as $schoolNameOption): ?>
                <option value="<?php echo htmlspecialchars($schoolNameOption); ?>">
                    <?php echo htmlspecialchars($schoolNameOption); ?>
                </option>
            <?php endforeach; ?>
        </select>

        <button type="submit" name="register">Create Account</button>
    </form>

    <?php echo $registerMessage; ?>

    <p class="footer-note">
        Already have an account? <a href="index.php">Login here</a><br>
        Forgot your password? <a href="forgot_password.php">Reset it here</a>
    </p>
</div>
</body>
</html>
