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
?>
