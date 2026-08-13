<?php
include("db.php");

header('Content-Type: application/json');

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'teacher') {
    http_response_code(403);
    echo json_encode(['success' => false]);
    exit;
}

$step = (int)($_POST['_step'] ?? 1);
if ($step < 1) $step = 1;
if ($step > 8) $step = 8;

$objective = trim($_POST['objective'] ?? '');
$performanceIndicator = trim($_POST['performance_indicator'] ?? '');
$rating = (int)($_POST['rating'] ?? 0);
$remarks = trim($_POST['remarks'] ?? '');

$dataToStore = $_POST;
unset($dataToStore['_step']);
$fullData = json_encode($dataToStore, JSON_UNESCAPED_UNICODE);

$draftStmt = $conn->prepare("SELECT id FROM ipcrf_entries WHERE user_id = ? AND status = 'draft' LIMIT 1");
$draftStmt->bind_param("i", $_SESSION['user_id']);
$draftStmt->execute();
$existing = $draftStmt->get_result()->fetch_assoc();

if ($existing) {
    $stmt = $conn->prepare(
        "UPDATE ipcrf_entries
         SET objective = ?, performance_indicator = ?, rating = ?, remarks = ?, full_data = ?, last_step = ?, updated_at = NOW()
         WHERE id = ?"
    );
    $stmt->bind_param("ssissii", $objective, $performanceIndicator, $rating, $remarks, $fullData, $step, $existing['id']);
} else {
    $stmt = $conn->prepare(
        "INSERT INTO ipcrf_entries (user_id, objective, performance_indicator, rating, remarks, full_data, status, last_step, updated_at)
         VALUES (?, ?, ?, ?, ?, ?, 'draft', ?, NOW())"
    );
    $stmt->bind_param("ississi", $_SESSION['user_id'], $objective, $performanceIndicator, $rating, $remarks, $fullData, $step);
}

$ok = $stmt->execute();
echo json_encode(['success' => (bool)$ok]);
