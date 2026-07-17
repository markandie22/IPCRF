<?php
session_start();
$host = "localhost";
$user = "root";
$pass = "";
$db   = "ipcrf_db";

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
?>
