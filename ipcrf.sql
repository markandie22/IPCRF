-- IPCRF MySQL Setup Script
-- Fixes "no connection/database selected" issues by creating and selecting the DB first.
-- WARNING: this script DROPS and recreates ipcrf_entries/users/schools. Only run it for a
-- fresh install; running it against a live "ipcrf" database wipes existing data.

CREATE DATABASE IF NOT EXISTS ipcrf
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE ipcrf;

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
    full_data LONGTEXT NULL,
    edited_by INT NULL,
    edited_at DATETIME NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'submitted',
    last_step INT NULL,
    updated_at DATETIME NULL,
    CONSTRAINT fk_entries_user FOREIGN KEY (user_id) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE CASCADE,
    CONSTRAINT fk_entries_editor FOREIGN KEY (edited_by) REFERENCES users(id)
        ON UPDATE CASCADE
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
