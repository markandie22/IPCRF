<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$db   = "ipcrf";

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$createUsersTable = "CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'teacher',
    school_id INT NULL,
    school_name VARCHAR(150) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$conn->query($createUsersTable);

$createEntriesTable = "CREATE TABLE IF NOT EXISTS ipcrf_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    objective TEXT NOT NULL,
    performance_indicator TEXT NOT NULL,
    rating INT NOT NULL,
    remarks TEXT,
    full_data LONGTEXT NULL,
    CONSTRAINT fk_entries_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
$conn->query($createEntriesTable);

/**
 * Lightweight schema migration for existing databases.
 * Ensures required columns exist for current app features.
 */
$usersColumnResult = $conn->query("SHOW COLUMNS FROM users");
$existingUserColumns = [];

if ($usersColumnResult) {
    while ($column = $usersColumnResult->fetch_assoc()) {
        $existingUserColumns[] = $column['Field'];
    }
}

if (!in_array('school_id', $existingUserColumns, true)) {
    $conn->query("ALTER TABLE users ADD COLUMN school_id INT NULL");
}

if (!in_array('school_name', $existingUserColumns, true)) {
    $conn->query("ALTER TABLE users ADD COLUMN school_name VARCHAR(150) NULL");
}

/**
 * Normalize role values for current app roles.
 * This works even if role column is already VARCHAR(20).
 */
$conn->query("ALTER TABLE users MODIFY COLUMN role VARCHAR(20) NOT NULL DEFAULT 'teacher'");

/**
 * Installs created from ipcrf.sql have a chk_users_role CHECK constraint that
 * only allowed 'teacher'/'admin', silently blocking newer roles like
 * 'super_admin' and 'principal'. Drop it if present so role checks live only
 * in application code, matching this file's schema.
 */
$roleConstraintResult = $conn->query("
    SELECT CONSTRAINT_NAME FROM information_schema.TABLE_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME = 'users'
      AND CONSTRAINT_NAME = 'chk_users_role'
");
if ($roleConstraintResult && $roleConstraintResult->num_rows > 0) {
    $conn->query("ALTER TABLE users DROP CONSTRAINT chk_users_role");
}

/**
 * Ensure ipcrf_entries can store the full wizard submission (Parts I-IV)
 * as JSON, in addition to the original structured columns.
 */
$entriesColumnResult = $conn->query("SHOW COLUMNS FROM ipcrf_entries");
$existingEntriesColumns = [];

if ($entriesColumnResult) {
    while ($column = $entriesColumnResult->fetch_assoc()) {
        $existingEntriesColumns[] = $column['Field'];
    }
}

if (!empty($existingEntriesColumns) && !in_array('full_data', $existingEntriesColumns, true)) {
    $conn->query("ALTER TABLE ipcrf_entries ADD COLUMN full_data LONGTEXT NULL");
}

/**
 * Track when a principal edits a teacher's IPCRF entry, so the teacher can
 * see it was changed and by whom.
 */
if (!empty($existingEntriesColumns) && !in_array('edited_by', $existingEntriesColumns, true)) {
    $conn->query("ALTER TABLE ipcrf_entries ADD COLUMN edited_by INT NULL");
    $conn->query("ALTER TABLE ipcrf_entries ADD CONSTRAINT fk_entries_editor FOREIGN KEY (edited_by) REFERENCES users(id) ON DELETE SET NULL ON UPDATE CASCADE");
}

if (!empty($existingEntriesColumns) && !in_array('edited_at', $existingEntriesColumns, true)) {
    $conn->query("ALTER TABLE ipcrf_entries ADD COLUMN edited_at DATETIME NULL");
}

/**
 * Support in-progress IPCRF drafts: a teacher can leave the wizard partway
 * through and resume later without losing what they already filled in.
 * status distinguishes an autosaved draft from a finalized submission, and
 * last_step remembers which wizard step to resume on.
 */
if (!empty($existingEntriesColumns) && !in_array('status', $existingEntriesColumns, true)) {
    $conn->query("ALTER TABLE ipcrf_entries ADD COLUMN status VARCHAR(20) NOT NULL DEFAULT 'submitted'");
}

if (!empty($existingEntriesColumns) && !in_array('last_step', $existingEntriesColumns, true)) {
    $conn->query("ALTER TABLE ipcrf_entries ADD COLUMN last_step INT NULL");
}

if (!empty($existingEntriesColumns) && !in_array('updated_at', $existingEntriesColumns, true)) {
    $conn->query("ALTER TABLE ipcrf_entries ADD COLUMN updated_at DATETIME NULL");
}

/**
 * Ensure a fixed admin account always exists, since public registration no
 * longer allows self-signing up as admin/super_admin.
 */
$fixedAdminEmail = 'admin@gmail.com';
$adminCheckStmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
$adminCheckStmt->bind_param("s", $fixedAdminEmail);
$adminCheckStmt->execute();
if ($adminCheckStmt->get_result()->num_rows === 0) {
    $fixedAdminPasswordHash = password_hash('admin1234', PASSWORD_DEFAULT);
    $fixedAdminName = 'Admin';
    $fixedAdminRole = 'admin';
    $adminInsertStmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $adminInsertStmt->bind_param("ssss", $fixedAdminName, $fixedAdminEmail, $fixedAdminPasswordHash, $fixedAdminRole);
    $adminInsertStmt->execute();
}
?>
