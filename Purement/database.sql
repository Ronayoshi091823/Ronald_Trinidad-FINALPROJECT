CREATE DATABASE IF NOT EXISTS purement_db
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE purement_db;

CREATE TABLE IF NOT EXISTS admins (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(60) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'staff') NOT NULL DEFAULT 'admin',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reservations (
    id INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL,
    phone VARCHAR(40) NOT NULL,
    reservation_date DATE NOT NULL,
    reservation_time VARCHAR(30) NOT NULL,
    guests TINYINT UNSIGNED NOT NULL,
    status ENUM('confirmed', 'cancelled') NOT NULL DEFAULT 'confirmed',
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_reservations_date (reservation_date),
    INDEX idx_reservations_status (status)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS reservation_history (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    reservation_id BIGINT UNSIGNED NOT NULL,
    action VARCHAR(20) NOT NULL,
    snapshot LONGTEXT NOT NULL,
    actor VARCHAR(60) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_history_reservation (reservation_id),
    INDEX idx_history_created (created_at)
) ENGINE=InnoDB;
