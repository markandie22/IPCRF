<?php
include("db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'principal') {
    die("Access denied.");
}

$schoolName = trim($_SESSION['school_name'] ?? '');

$ratingLabels = [
    1 => 'Needs Improvement',
    2 => 'Fair',
    3 => 'Satisfactory',
    4 => 'Very Satisfactory',
    5 => 'Outstanding',
];
$ratingColors = [
    1 => '#d9455f',
    2 => '#e08a3c',
    3 => '#d9b23c',
    4 => '#3c8fd9',
    5 => '#2fa661',
];
$QUALIFY_THRESHOLD = 3;

$teachers = [];
$stmt = $conn->prepare(
    "SELECT u.id, u.name, e.id AS entry_id, e.rating, e.objective, e.performance_indicator, e.edited_at
     FROM users u
     LEFT JOIN ipcrf_entries e ON e.id = (
         SELECT e2.id FROM ipcrf_entries e2 WHERE e2.user_id = u.id ORDER BY e2.id DESC LIMIT 1
     )
     WHERE u.role = 'teacher' AND u.school_name = ?
     ORDER BY u.name"
);
$stmt->bind_param("s", $schoolName);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $teachers[] = $row;
}

$totalTeachers = count($teachers);
$submittedCount = 0;
$qualifiedCount = 0;
$notQualifiedCount = 0;
$pendingCount = 0;
$ratingCounts = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0];

foreach ($teachers as $t) {
    if ($t['rating'] === null) {
        $pendingCount++;
        continue;
    }
    $submittedCount++;
    $ratingCounts[(int)$t['rating']]++;
    if ((int)$t['rating'] >= $QUALIFY_THRESHOLD) {
        $qualifiedCount++;
    } else {
        $notQualifiedCount++;
    }
}

$pieSegments = [];
$pieTotal = $submittedCount + $pendingCount;
$cursor = 0;
$gradientParts = [];
if ($pieTotal > 0) {
    foreach ($ratingCounts as $ratingValue => $count) {
        if ($count === 0) {
            continue;
        }
        $slicePercent = ($count / $pieTotal) * 100;
        $start = $cursor;
        $end = $cursor + $slicePercent;
        $gradientParts[] = "{$ratingColors[$ratingValue]} " . round($start, 2) . "% " . round($end, 2) . "%";
        $cursor = $end;
        $pieSegments[] = ['label' => $ratingLabels[$ratingValue], 'color' => $ratingColors[$ratingValue], 'count' => $count];
    }
    if ($pendingCount > 0) {
        $slicePercent = ($pendingCount / $pieTotal) * 100;
        $start = $cursor;
        $end = $cursor + $slicePercent;
        $gradientParts[] = "#b7c2de " . round($start, 2) . "% " . round($end, 2) . "%";
        $cursor = $end;
        $pieSegments[] = ['label' => 'No submission yet', 'color' => '#b7c2de', 'count' => $pendingCount];
    }
}
$pieGradient = !empty($gradientParts) ? implode(', ', $gradientParts) : '#e5eaf6 0% 100%';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Principal Dashboard - IPCRF</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="admin-wrapper">
    <div class="admin-card">
        <div class="admin-header">
            <div>
                <h2>Principal Dashboard</h2>
                <p>Teacher evaluation overview for <?php echo htmlspecialchars($schoolName !== '' ? $schoolName : 'your school'); ?>.</p>
            </div>
            <div class="admin-actions">
                <a class="btn btn-inline" href="view_ipcrf.php">View Records</a>
                <a class="btn btn-inline btn-muted" href="logout.php">Logout</a>
            </div>
        </div>

        <div class="stat-tiles">
            <div class="stat-tile">
                <span class="stat-tile__value"><?php echo $totalTeachers; ?></span>
                <span class="stat-tile__label">Teachers</span>
            </div>
            <div class="stat-tile">
                <span class="stat-tile__value"><?php echo $submittedCount; ?></span>
                <span class="stat-tile__label">Submitted</span>
            </div>
            <div class="stat-tile stat-tile--good">
                <span class="stat-tile__value"><?php echo $qualifiedCount; ?></span>
                <span class="stat-tile__label">Qualified</span>
            </div>
            <div class="stat-tile stat-tile--bad">
                <span class="stat-tile__value"><?php echo $notQualifiedCount; ?></span>
                <span class="stat-tile__label">Not Qualified</span>
            </div>
            <div class="stat-tile stat-tile--muted">
                <span class="stat-tile__value"><?php echo $pendingCount; ?></span>
                <span class="stat-tile__label">No Submission</span>
            </div>
        </div>

        <?php if ($totalTeachers === 0): ?>
            <div class="admin-table-wrap">
                <p class="admin-empty">No teachers are linked to your school yet.</p>
            </div>
        <?php else: ?>

        <div class="charts-row">
            <div class="chart-panel">
                <h3>Ratings by Teacher</h3>
                <?php if ($submittedCount === 0): ?>
                    <p class="admin-empty">No teacher has submitted an IPCRF yet.</p>
                <?php else: ?>
                    <div class="chart-bars">
                        <?php foreach ($teachers as $t): if ($t['rating'] === null) continue; ?>
                            <div class="chart-bar-col">
                                <span class="chart-bar-value"><?php echo (int)$t['rating']; ?></span>
                                <div class="chart-bar" style="height: <?php echo ((int)$t['rating'] / 5 * 100); ?>%; background: <?php echo $ratingColors[(int)$t['rating']]; ?>;"></div>
                                <span class="chart-bar-label"><?php echo htmlspecialchars($t['name']); ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="chart-panel">
                <h3>Rating Distribution</h3>
                <div class="chart-pie-wrap">
                    <div class="chart-pie" style="background: conic-gradient(<?php echo $pieGradient; ?>);"></div>
                    <ul class="chart-legend">
                        <?php foreach ($pieSegments as $segment): ?>
                            <li>
                                <span class="chart-legend__swatch" style="background: <?php echo $segment['color']; ?>;"></span>
                                <?php echo htmlspecialchars($segment['label']); ?> (<?php echo $segment['count']; ?>)
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>

        <div class="admin-table-wrap">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Teacher</th>
                        <th>Latest Objective</th>
                        <th>Rating</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($teachers as $t): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($t['name']); ?></td>
                            <td><?php echo $t['objective'] !== null ? htmlspecialchars($t['objective']) : '&mdash;'; ?></td>
                            <td>
                                <?php if ($t['rating'] !== null): ?>
                                    <?php echo (int)$t['rating']; ?> - <?php echo htmlspecialchars($ratingLabels[(int)$t['rating']]); ?>
                                <?php else: ?>
                                    &mdash;
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($t['rating'] === null): ?>
                                    <span class="badge badge--muted">No Submission</span>
                                <?php elseif ((int)$t['rating'] >= $QUALIFY_THRESHOLD): ?>
                                    <span class="badge badge--good">Qualified</span>
                                <?php else: ?>
                                    <span class="badge badge--bad">Not Qualified</span>
                                <?php endif; ?>
                                <?php if ($t['edited_at']): ?>
                                    <span class="badge badge--muted">Edited</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($t['entry_id'] !== null): ?>
                                    <a class="link-btn" href="principal_edit_entry.php?id=<?php echo (int)$t['entry_id']; ?>">View / Edit</a>
                                <?php else: ?>
                                    &mdash;
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>
</body>
</html>
