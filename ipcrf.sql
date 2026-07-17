-- IPCRF MySQL Setup Script
-- Fixes "no connection/database selected" issues by creating and selecting the DB first.

CREATE DATABASE IF NOT EXISTS ipcrf_db
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ipcrf_db;

-- Drop in FK-safe order for repeatable imports
DROP TABLE IF EXISTS ipcrf_entries;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS schools;

CREATE TABLE schools (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    address VARCHAR(255)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role VARCHAR(20) NOT NULL DEFAULT 'teacher',
    school_id INT NULL,
    CONSTRAINT chk_users_role CHECK (role IN ('teacher', 'admin')),
    CONSTRAINT fk_users_school FOREIGN KEY (school_id) REFERENCES schools(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE ipcrf_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    objective TEXT NOT NULL,
    performance_indicator TEXT NOT NULL,
    rating INT NOT NULL,
    remarks TEXT,
    CONSTRAINT fk_entries_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
