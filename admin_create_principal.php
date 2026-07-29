<?php
include("db.php");
include("school_helper.php");

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'super_admin'], true)) {
    die("Access denied.");
}

$schoolChoices = get_bataan_public_schools();
$createMessage = '';

if (isset($_POST['create_principal'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['principal_email'] ?? '');
    $password = $_POST['principal_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $schoolName = trim($_POST['school_name'] ?? '');

    if ($name === '' || $email === '' || $password === '' || $confirmPassword === '') {
        $createMessage = '<div class="message">Please fill in all fields.</div>';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $createMessage = '<div class="message">Please provide a valid email address.</div>';
    } elseif ($password !== $confirmPassword) {
        $createMessage = '<div class="message">Passwords do not match.</div>';
    } elseif ($schoolName === '' || !is_valid_school_name($schoolName)) {
        $createMessage = '<div class="message">Please select a valid school.</div>';
    } else {
        try {
            $checkStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
            $checkStmt->bind_param("s", $email);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult && $checkResult->num_rows > 0) {
                $createMessage = '<div class="message">Email is already registered.</div>';
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $role = 'principal';
                $schoolId = null;

                $insertStmt = $conn->prepare("INSERT INTO users (name, email, password, role, school_id, school_name) VALUES (?, ?, ?, ?, ?, ?)");
                $insertStmt->bind_param("ssssis", $name, $email, $hashedPassword, $role, $schoolId, $schoolName);

                if ($insertStmt->execute()) {
                    $createMessage = '<div class="message message--success">Principal account created successfully for ' . htmlspecialchars($schoolName) . '.</div>';
                } else {
                    $createMessage = '<div class="message">Failed to create account.</div>';
                }
            }
        } catch (mysqli_sql_exception $e) {
            $createMessage = '<div class="message">Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    }
}

$principalsResult = $conn->query("SELECT name, email, school_name FROM users WHERE role = 'principal' ORDER BY school_name, name");
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Create Principal Account - IPCRF</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="admin-wrapper">
    <div class="admin-card">
        <div class="admin-header">
            <div>
                <h2>Create Principal Account</h2>
                <p>Set up login credentials for a school principal.</p>
            </div>
            <div class="admin-actions">
                <a class="btn btn-inline btn-muted" href="view_ipcrf.php">Back to Admin View</a>
            </div>
        </div>

        <form method="POST" action="" class="admin-form">
            <div class="form-group">
                <label for="name">Full Name</label>
                <input type="text" id="name" name="name" required>
            </div>

            <div class="form-group">
                <label for="principal_email">Email</label>
                <input type="email" id="principal_email" name="principal_email" required>
            </div>

            <div class="form-group">
                <label for="principal_password">Password</label>
                <input type="password" id="principal_password" name="principal_password" required>
            </div>

            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>

            <div class="form-group">
                <label for="school_name">School</label>
                <select id="school_name" name="school_name" required>
                    <option value="">Select school</option>
                    <?php foreach ($schoolChoices as $schoolNameOption): ?>
                        <option value="<?php echo htmlspecialchars($schoolNameOption); ?>">
                            <?php echo htmlspecialchars($schoolNameOption); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" name="create_principal" class="btn btn-inline">Create Account</button>
            <?php echo $createMessage; ?>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>School</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($principalsResult && $principalsResult->num_rows > 0): ?>
                    <?php while ($row = $principalsResult->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td><?php echo htmlspecialchars($row['school_name'] ?? ''); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" class="admin-empty">No principal accounts created yet.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
