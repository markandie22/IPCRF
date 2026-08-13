<?php
include("db.php");
include("ipcrf_reference_data.php");

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'teacher') {
    die("Access denied.");
}

$entryId = (int)($_GET['id'] ?? 0);
if ($entryId <= 0) {
    die("Invalid IPCRF entry.");
}

// A teacher may only export their own submitted entries — ownership is
// enforced in the WHERE clause itself.
$stmt = $conn->prepare("SELECT full_data FROM ipcrf_entries WHERE id = ? AND user_id = ? AND status = 'submitted'");
$stmt->bind_param("ii", $entryId, $_SESSION['user_id']);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if (!$row) {
    die("Access denied.");
}

$data = json_decode($row['full_data'] ?? '', true);
if (!is_array($data)) {
    $data = [];
}

function csv_val($data, $name, $default = '') {
    if (!array_key_exists($name, $data)) return $default;
    $v = $data[$name];
    if (is_array($v)) return implode('; ', $v);
    return (string)$v;
}

$ratingLabels = [
    1 => '1 - Needs Improvement',
    2 => '2 - Fair',
    3 => '3 - Satisfactory',
    4 => '4 - Very Satisfactory',
    5 => '5 - Outstanding',
];

$kraMeta = ipcrf_kra_meta();
$objectives = ipcrf_part1_objectives();

// Every row follows the same template — [Step, Field, Value] — instead of
// mixing section headers, label/value pairs, and a differently-shaped table
// for Part I. One consistent shape end to end sorts/filters cleanly in a
// spreadsheet and is easy to diff between two exports.
$rows = [];
$addRow = function ($step, $field, $value) use (&$rows) {
    $rows[] = [$step, $field, $value];
};

$addRow('Step 1 - Career Stage & School Year', 'School Year', csv_val($data, 'school_year'));
$addRow('Step 1 - Career Stage & School Year', 'Career Stage', csv_val($data, 'career_stage'));

$addRow('Step 2 - Quick Entry', 'Objective', csv_val($data, 'objective'));
$addRow('Step 2 - Quick Entry', 'Performance Indicator', csv_val($data, 'performance_indicator'));
$quickRating = (int)csv_val($data, 'rating', 0);
$addRow('Step 2 - Quick Entry', 'Rating', $ratingLabels[$quickRating] ?? '');
$addRow('Step 2 - Quick Entry', 'Remarks', csv_val($data, 'remarks'));

$demographicFields = [
    'last_name' => 'Last Name',
    'first_name' => 'First Name',
    'middle_name' => 'Middle Name',
    'sex' => 'Sex',
    'age' => 'Age',
    'employee_id' => 'Employee ID',
    'deped_email' => 'DepEd Email Address',
    'tin' => 'TIN',
    'region' => 'Region',
    'division' => 'Division',
    'school_name' => 'CLC / School Name',
    'school_id' => 'School ID',
    'school_type' => 'School Type',
    'school_size' => 'School Size',
    'curricular_classification' => 'Curricular Classification',
    'position' => 'Position',
    'employment_status' => 'Employment Status',
    'years_teaching' => 'Number of Years in Teaching',
    'highest_degree' => 'Highest Degree Obtained',
    'level_taught' => 'Level Taught',
    'specialization' => 'Area(s) of Specialization',
    'specialization_others' => 'Specialization - Others',
    'subjects_taught' => 'Subject(s) Taught',
    'subjects_others' => 'Subjects Taught - Others',
];
foreach ($demographicFields as $key => $label) {
    $addRow('Step 3 - Demographic Profile', $label, csv_val($data, $key));
}

foreach ($objectives as $num => $o) {
    $step = "Step 4 - Part I, Objective $num";
    $kraLabel = $kraMeta[$o['kra']]['name'] ?? ('KRA ' . $o['kra']);
    $hasEfficiency = $o['efficiency'] !== null;

    $addRow($step, 'PPST', $o['ppst']);
    $addRow($step, 'KRA', html_entity_decode($kraLabel, ENT_QUOTES));
    $addRow($step, 'Weight', $o['weight']);
    $addRow($step, 'Title', $o['title']);
    $addRow($step, 'Quality - Actual Results', csv_val($data, "s5_actual_{$num}_q"));
    $addRow($step, 'Quality - Rating', csv_val($data, "s5_rating_{$num}_q"));
    $addRow($step, 'Efficiency - Actual Results', $hasEfficiency ? csv_val($data, "s5_actual_{$num}_e") : 'N/A');
    $addRow($step, 'Efficiency - Rating', $hasEfficiency ? csv_val($data, "s5_rating_{$num}_e") : 'N/A');
    $addRow($step, 'Ave', csv_val($data, "s5_ave_{$num}"));
    $addRow($step, 'Score', csv_val($data, "s5_score_{$num}"));
}

$addRow('Step 4 - Part I, Final Rating', 'Final Rating (Numeric)', csv_val($data, 's5_final_numeric'));
$addRow('Step 4 - Part I, Final Rating', 'Final Rating (Adjectival)', csv_val($data, 's5_final_adjectival'));

$addRow('Step 4 - Part I, Signatories', 'Rater', csv_val($data, 's5_sign_rater_name'));
$addRow('Step 4 - Part I, Signatories', 'Rater Position', csv_val($data, 's5_sign_rater_position'));
$addRow('Step 4 - Part I, Signatories', 'Approving Authority', csv_val($data, 's5_sign_approver_name'));
$addRow('Step 4 - Part I, Signatories', 'Approving Authority Position', csv_val($data, 's5_sign_approver_position'));
$addRow('Step 4 - Part I, Signatories', 'Approving Authority Email', csv_val($data, 's5_sign_approver_email'));

$filename = 'ipcrf_steps1-4_' . preg_replace('/[^A-Za-z0-9_-]/', '_', csv_val($data, 'last_name', 'entry') . '_' . $entryId) . '.csv';

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '"');

$out = fopen('php://output', 'w');
// Excel needs a UTF-8 BOM to render accented characters correctly.
fwrite($out, "\xEF\xBB\xBF");

fputcsv($out, ['Step', 'Field', 'Value']);
foreach ($rows as $row) {
    fputcsv($out, $row);
}

fclose($out);
exit;
