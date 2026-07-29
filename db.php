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
?>
