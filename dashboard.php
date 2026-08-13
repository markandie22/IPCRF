<?php
include("db.php");

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'teacher') {
    header("Location: index.php");
    exit;
}

$ratingLabels = [
    1 => 'Needs Improvement',
    2 => 'Fair',
    3 => 'Satisfactory',
    4 => 'Very Satisfactory',
    5 => 'Outstanding',
];

$stepLabels = [
    1 => 'Career Stage & School Year',
    2 => 'Quick Entry',
    3 => 'Demographic Profile',
    4 => 'Part I: Official Rating Sheet',
    5 => 'Part II: Core Behavioral Competencies',
    6 => 'Part III: Summary of Ratings',
    7 => 'Part IV: Development Plans',
    8 => 'Review & Submit',
];

$draft = null;
$draftStmt = $conn->prepare(
    "SELECT last_step, updated_at FROM ipcrf_entries WHERE user_id = ? AND status = 'draft' LIMIT 1"
);
$draftStmt->bind_param("i", $_SESSION['user_id']);
$draftStmt->execute();
$draft = $draftStmt->get_result()->fetch_assoc();

$submissions = [];
$stmt = $conn->prepare(
    "SELECT e.objective, e.performance_indicator, e.rating, e.remarks, e.edited_at,
            editor.name AS editor_name
     FROM ipcrf_entries e
     LEFT JOIN users editor ON editor.id = e.edited_by
     WHERE e.user_id = ? AND e.status = 'submitted'
     ORDER BY e.id DESC"
);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $submissions[] = $row;
}
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard - IPCRF</title>
    <link rel="stylesheet" href="teacher_dashboard.css">
</head>
<body>
<div class="dashboard-shell">
    <header class="topbar">
        <div class="brand">
            <span class="brand-badge">IP</span>
            <span>IPCRF Classroom</span>
        </div>
        <div class="topbar-actions">
            <a class="btn btn-secondary" href="logout.php">Logout</a>
        </div>
    </header>

    <main class="page-wrap">
        <section class="hero-card">
            <h1 class="hero-title">Teacher Dashboard</h1>
            <p class="hero-subtitle">Welcome back! Manage your performance records and submit your IPCRF tasks quickly.</p>
        </section>

        <?php if ($draft): ?>
        <section class="draft-banner">
            <h3>You have an unfinished IPCRF</h3>
            <p>
                You left off at <strong><?php echo htmlspecialchars($stepLabels[(int)$draft['last_step']] ?? 'Step ' . (int)$draft['last_step']); ?></strong>
                <?php if ($draft['updated_at']): ?>
                    &mdash; last saved <?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($draft['updated_at']))); ?>
                <?php endif; ?>.
                Everything you already filled in has been kept; continue to pick up right where you stopped.
            </p>
            <a class="btn btn-primary" href="ipcrf_form.php">Continue IPCRF</a>
        </section>
        <?php endif; ?>

        <section class="card-grid">
            <article class="card">
                <h3>IPCRF Submission</h3>
                <p>Open and complete your Individual Performance Commitment and Review Form with updated objectives and outputs.</p>
                <a class="btn btn-primary" href="ipcrf_form.php">Fill IPCRF Form</a>
            </article>

            <article class="card">
                <h3>Quick Reminders</h3>
                <ul class="quick-list">
                    <li>Review your goals before final submission.</li>
                    <li>Keep supporting documents ready for validation.</li>
                    <li>Coordinate with your school head for deadlines.</li>
                </ul>
            </article>
        </section>

        <section class="submissions-card">
            <h3>My IPCRF Submissions</h3>
            <?php if (empty($submissions)): ?>
                <p class="submissions-empty">You haven't submitted an IPCRF yet.</p>
            <?php else: ?>
                <div class="submissions-table-wrap">
                    <table class="submissions-table">
                        <thead>
                            <tr>
                                <th>Objective</th>
                                <th>Performance Indicator</th>
                                <th>Rating</th>
                                <th>Remarks</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissions as $s): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($s['objective']); ?></td>
                                    <td><?php echo htmlspecialchars($s['performance_indicator']); ?></td>
                                    <td><?php echo (int)$s['rating']; ?> - <?php echo htmlspecialchars($ratingLabels[(int)$s['rating']] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($s['remarks'] ?? ''); ?></td>
                                    <td>
                                        <?php if ($s['edited_at']): ?>
                                            <span class="submission-badge">
                                                Edited by <?php echo htmlspecialchars($s['editor_name'] ?? 'principal'); ?>
                                                on <?php echo htmlspecialchars(date('M j, Y', strtotime($s['edited_at']))); ?>
                                            </span>
                                        <?php else: ?>
                                            <span class="submission-badge submission-badge--muted">Not edited</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </section>
    </main>
</div>
</body>
</html>
