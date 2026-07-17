<?php
include("db.php");
include("school_helper.php");

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['admin', 'super_admin'], true)) {
    die("Access denied.");
}

$bataanPublicSchools = get_bataan_public_schools();

$selectedSchool = trim($_GET['school'] ?? '');
$resetStatus = trim($_GET['reset_status'] ?? '');
$resetEmail = trim($_GET['reset_email'] ?? '');
$adminResetMessage = '';

if ($resetStatus === 'success') {
    $emailText = $resetEmail !== '' ? htmlspecialchars($resetEmail) : 'selected user';
    $adminResetMessage = '<div class="message message--success">Password reset successfully for ' . $emailText . '.</div>';
} elseif ($resetStatus === 'notfound') {
    $adminResetMessage = '<div class="message">No user found with that email.</div>';
} elseif ($resetStatus === 'invalid') {
    $adminResetMessage = '<div class="message">Please provide a valid user email and temporary password.</div>';
} elseif ($resetStatus === 'error') {
    $adminResetMessage = '<div class="message">Failed to reset password.</div>';
}

$sql = "SELECT u.name, e.objective, e.performance_indicator, e.rating, e.remarks,
               COALESCE(NULLIF(TRIM(u.school_name), ''), 'N/A') AS school_name
        FROM ipcrf_entries e
        JOIN users u ON e.user_id = u.id";

$params = [];
$types = "";

if ($selectedSchool !== '') {
    $sql .= " WHERE u.school_name = ?";
    $params[] = $selectedSchool;
    $types .= "s";
}

$sql .= " ORDER BY e.id DESC";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Admin View - IPCRF Records</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="admin-wrapper">
    <div class="admin-card">
        <div class="admin-header">
            <div>
                <h2>Admin View - IPCRF Records</h2>
                <p>Monitor teacher submissions and filter by public schools in Bataan.</p>
            </div>
            <div class="admin-actions">
                <a class="btn btn-inline" href="export_ipcrf.php">Download Excel Report</a>
                <a class="btn btn-inline btn-muted" href="logout.php">Logout</a>
            </div>
        </div>

        <?php if ($_SESSION['role'] === 'super_admin'): ?>
        <form method="POST" action="admin_reset_password.php" class="admin-reset">
            <label for="reset_user_email">Reset User Password (Email)</label>
            <input type="email" id="reset_user_email" name="reset_user_email" placeholder="user@email.com" required>

            <label for="temporary_password">Temporary Password</label>
            <input type="text" id="temporary_password" name="temporary_password" placeholder="Enter temporary password" required>

            <button type="submit" name="admin_reset_password" class="btn btn-inline">Reset Password</button>
            <?php echo $adminResetMessage; ?>
        </form>
        <?php endif; ?>

        <form method="GET" class="admin-filter">
            <label for="school">Public School (Bataan)</label>
            <select id="school" name="school">
                <option value="">All Schools</option>
                <?php foreach ($bataanPublicSchools as $school): ?>
                    <option value="<?php echo htmlspecialchars($school); ?>" <?php echo $selectedSchool === $school ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($school); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <button type="submit" class="btn btn-inline">Apply Filter</button>
            <a href="view_ipcrf.php" class="link-btn">Reset</a>
        </form>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Teacher</th>
                        <th>School</th>
                        <th>Objective</th>
                        <th>Performance Indicator</th>
                        <th>Rating</th>
                        <th>Remarks</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['name']); ?></td>
                            <td><?php echo htmlspecialchars($row['school_name']); ?></td>
                            <td><?php echo htmlspecialchars($row['objective']); ?></td>
                            <td><?php echo htmlspecialchars($row['performance_indicator']); ?></td>
                            <td><?php echo htmlspecialchars($row['rating']); ?></td>
                            <td><?php echo htmlspecialchars($row['remarks']); ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" class="admin-empty">No IPCRF records found for the selected filter.</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
