<?php
// Superseded by ipcrf_form.php's full wizard, opened with ?entry_id=… —
// principals now view/edit a teacher's entire submitted IPCRF (all 4 parts),
// not just this page's 4 summary fields. Kept as a redirect for old links.
include("db.php");

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'principal') {
    die("Access denied.");
}

$entryId = (int)($_POST['id'] ?? $_GET['id'] ?? 0);
if ($entryId <= 0) {
    die("Invalid IPCRF entry.");
}

header("Location: ipcrf_form.php?entry_id=" . $entryId);
exit;
