<?php
include("db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'principal') {
    die("Access denied.");
}

$principalSchool = trim($_SESSION['school_name'] ?? '');
$entryId = (int)($_POST['id'] ?? $_GET['id'] ?? 0);

if ($entryId <= 0) {
    die("Invalid IPCRF entry.");
}

$ratingLabels = [
    1 => 'Needs Improvement',
    2 => 'Fair',
    3 => 'Satisfactory',
    4 => 'Very Satisfactory',
    5 => 'Outstanding',
];

$updateMessage = '';

if (isset($_POST['update_entry'])) {
    $objective = trim($_POST['objective'] ?? '');
    $performanceIndicator = trim($_POST['performance_indicator'] ?? '');
    $rating = (int)($_POST['rating'] ?? 0);
    $remarks = trim($_POST['remarks'] ?? '');

    if ($objective === '' || $performanceIndicator === '' || $rating < 1 || $rating > 5) {
        $updateMessage = '<div class="message">Please complete all required fields with a valid rating (1-5).</div>';
    } else {
        // Re-check the entry still belongs to this principal's school before writing.
        $ownerStmt = $conn->prepare("SELECT u.school_name FROM ipcrf_entries e JOIN users u ON e.user_id = u.id WHERE e.id = ?");
        $ownerStmt->bind_param("i", $entryId);
        $ownerStmt->execute();
        $ownerRow = $ownerStmt->get_result()->fetch_assoc();

        if (!$ownerRow || $ownerRow['school_name'] !== $principalSchool) {
            die("Access denied.");
        }

        $updateStmt = $conn->prepare(
            "UPDATE ipcrf_entries
             SET objective = ?, performance_indicator = ?, rating = ?, remarks = ?, edited_by = ?, edited_at = NOW()
             WHERE id = ?"
        );
        $updateStmt->bind_param("ssisii", $objective, $performanceIndicator, $rating, $remarks, $_SESSION['user_id'], $entryId);

        if ($updateStmt->execute()) {
            header("Location: principal_edit_entry.php?id=" . $entryId . "&updated=1");
            exit;
        } else {
            $updateMessage = '<div class="message">Failed to update this IPCRF entry.</div>';
        }
    }
} elseif (($_GET['updated'] ?? '') === '1') {
    $updateMessage = '<div class="message message--success">IPCRF entry updated successfully.</div>';
}

$stmt = $conn->prepare(
    "SELECT e.id, e.objective, e.performance_indicator, e.rating, e.remarks, e.edited_at,
            u.name AS teacher_name, u.school_name,
            editor.name AS editor_name
     FROM ipcrf_entries e
     JOIN users u ON e.user_id = u.id
     LEFT JOIN users editor ON editor.id = e.edited_by
     WHERE e.id = ?"
);
$stmt->bind_param("i", $entryId);
$stmt->execute();
$entry = $stmt->get_result()->fetch_assoc();

if (!$entry || $entry['school_name'] !== $principalSchool) {
    die("Access denied.");
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Edit IPCRF Entry - IPCRF</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="admin-wrapper">
    <div class="admin-card">
        <div class="admin-header">
            <div>
                <h2>Edit IPCRF Entry</h2>
                <p><?php echo htmlspecialchars($entry['teacher_name']); ?> &mdash; <?php echo htmlspecialchars($entry['school_name']); ?></p>
            </div>
            <div class="admin-actions">
                <a class="btn btn-inline btn-muted" href="principal_dashboard.php">Back to Dashboard</a>
            </div>
        </div>

        <?php if ($entry['edited_at']): ?>
            <div class="message message--info">
                Last edited by <?php echo htmlspecialchars($entry['editor_name'] ?? 'a principal'); ?>
                on <?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($entry['edited_at']))); ?>.
            </div>
        <?php endif; ?>

        <form method="POST" action="" class="admin-form">
            <input type="hidden" name="id" value="<?php echo (int)$entry['id']; ?>">

            <div class="form-group">
                <label for="objective">Objective</label>
                <textarea id="objective" name="objective" rows="4" required><?php echo htmlspecialchars($entry['objective']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="performance_indicator">Performance Indicator</label>
                <textarea id="performance_indicator" name="performance_indicator" rows="4" required><?php echo htmlspecialchars($entry['performance_indicator']); ?></textarea>
            </div>

            <div class="form-group">
                <label for="rating">Rating (1-5)</label>
                <select id="rating" name="rating" required>
                    <option value="">-- Select Rating --</option>
                    <?php foreach ($ratingLabels as $value => $label): ?>
                        <option value="<?php echo $value; ?>" <?php echo (int)$entry['rating'] === $value ? 'selected' : ''; ?>>
                            <?php echo $value; ?> - <?php echo htmlspecialchars($label); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="remarks">Remarks</label>
                <textarea id="remarks" name="remarks" rows="3"><?php echo htmlspecialchars($entry['remarks'] ?? ''); ?></textarea>
            </div>

            <button type="submit" name="update_entry" class="btn btn-inline">Save Changes</button>
            <?php echo $updateMessage; ?>
        </form>
    </div>
</div>
</body>
</html>
