-- =========================================================
-- Company Portal - database schema
-- MySQL 5.7+ / MariaDB 10.4+
-- =========================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS activity_logs;
DROP TABLE IF EXISTS uploads;
DROP TABLE IF EXISTS documents;
DROP TABLE IF EXISTS announcements;
DROP TABLE IF EXISTS employees;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS departments;
DROP TABLE IF EXISTS settings;
DROP TABLE IF EXISTS failed_attempts;
DROP TABLE IF EXISTS media;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE departments (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    department_name VARCHAR(100) NOT NULL,
    description     TEXT NULL,
    status          ENUM('active', 'disabled') NOT NULL DEFAULT 'active',
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_departments_name (department_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

/* All uploaded binary contents live here, not on disk. */
CREATE TABLE media (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    kind          VARCHAR(20) NOT NULL DEFAULT 'file',
    original_name VARCHAR(190) NOT NULL,
    mime_type     VARCHAR(120) NOT NULL DEFAULT 'application/octet-stream',
    filesize      BIGINT UNSIGNED NOT NULL DEFAULT 0,
    data          LONGBLOB NOT NULL,
    uploaded_by   INT UNSIGNED NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_media_kind (kind),
    KEY idx_media_uploader (uploaded_by)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE users (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    fullname      VARCHAR(120) NOT NULL,
    username      VARCHAR(60) NOT NULL,
    email         VARCHAR(150) NOT NULL,
    photo_id      BIGINT UNSIGNED NULL,
    password      VARCHAR(255) NOT NULL,
    role          ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    department_id INT UNSIGNED NULL,
    status        ENUM('active', 'disabled') NOT NULL DEFAULT 'active',
    last_login    DATETIME NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_username (username),
    UNIQUE KEY uq_users_email (email),
    KEY idx_users_photo (photo_id),
    KEY idx_users_department (department_id),
    CONSTRAINT fk_users_photo
        FOREIGN KEY (photo_id) REFERENCES media (id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_users_department
        FOREIGN KEY (department_id) REFERENCES departments (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE employees (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    employee_code VARCHAR(40) NULL,
    fullname      VARCHAR(120) NOT NULL,
    email         VARCHAR(150) NULL,
    phone         VARCHAR(40) NULL,
    designation   VARCHAR(100) NULL,
    department_id INT UNSIGNED NULL,
    joining_date  DATE NULL,
    address       TEXT NULL,
    status        ENUM('active', 'disabled') NOT NULL DEFAULT 'active',
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_employees_code (employee_code),
    KEY idx_employees_department (department_id),
    CONSTRAINT fk_employees_department
        FOREIGN KEY (department_id) REFERENCES departments (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE announcements (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title       VARCHAR(180) NOT NULL,
    description TEXT NOT NULL,
    image_id    BIGINT UNSIGNED NULL,
    created_by  INT UNSIGNED NULL,
    created_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_announcements_image (image_id),
    KEY idx_announcements_author (created_by),
    CONSTRAINT fk_announcements_image
        FOREIGN KEY (image_id) REFERENCES media (id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_announcements_author
        FOREIGN KEY (created_by) REFERENCES users (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE attendance_schedules (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT,
    name           VARCHAR(100) NOT NULL,
    days_of_week   VARCHAR(20) NOT NULL DEFAULT '1,2,3,4,5',
    start_time     TIME NOT NULL,
    end_time       TIME NOT NULL,
    late_tolerance SMALLINT UNSIGNED NOT NULL DEFAULT 15,
    location       VARCHAR(120) NULL,
    is_active      TINYINT(1) NOT NULL DEFAULT 1,
    created_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_attendance_schedules_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE attendance_records (
    id              INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id         INT UNSIGNED NOT NULL,
    schedule_id     INT UNSIGNED NULL,
    attendance_date DATE NOT NULL,
    check_in        TIME NULL,
    check_out       TIME NULL,
    status          ENUM('present', 'late', 'permission', 'sick') NOT NULL DEFAULT 'present',
    note            VARCHAR(255) NULL,
    created_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_attendance_user_date (user_id, attendance_date),
    KEY idx_attendance_schedule (schedule_id),
    KEY idx_attendance_date (attendance_date),
    CONSTRAINT fk_attendance_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_attendance_schedule
        FOREIGN KEY (schedule_id) REFERENCES attendance_schedules (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE documents (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title         VARCHAR(180) NOT NULL,
    description   TEXT NULL,
    filename      VARCHAR(255) NOT NULL,
    original_name VARCHAR(190) NOT NULL,
    media_id      BIGINT UNSIGNED NULL,
    filesize      BIGINT UNSIGNED NOT NULL DEFAULT 0,
    filetype      VARCHAR(120) NOT NULL DEFAULT 'application/octet-stream',
    uploaded_by   INT UNSIGNED NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_documents_media (media_id),
    KEY idx_documents_uploader (uploaded_by),
    CONSTRAINT fk_documents_media
        FOREIGN KEY (media_id) REFERENCES media (id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_documents_uploader
        FOREIGN KEY (uploaded_by) REFERENCES users (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE uploads (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title         VARCHAR(180) NOT NULL,
    description   TEXT NULL,
    filename      VARCHAR(255) NOT NULL,
    original_name VARCHAR(190) NOT NULL,
    media_id      BIGINT UNSIGNED NULL,
    filesize      BIGINT UNSIGNED NOT NULL DEFAULT 0,
    filetype      VARCHAR(120) NOT NULL DEFAULT 'application/octet-stream',
    uploaded_by   INT UNSIGNED NULL,
    created_at    DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_uploads_media (media_id),
    KEY idx_uploads_uploader (uploaded_by),
    CONSTRAINT fk_uploads_media
        FOREIGN KEY (media_id) REFERENCES media (id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_uploads_uploader
        FOREIGN KEY (uploaded_by) REFERENCES users (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT,
    setting_key   VARCHAR(80) NOT NULL,
    setting_value TEXT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_settings_key (setting_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE activity_logs (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    INT UNSIGNED NULL,
    activity   VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL DEFAULT '0.0.0.0',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_logs_user (user_id),
    KEY idx_logs_created (created_at),
    CONSTRAINT fk_logs_user
        FOREIGN KEY (user_id) REFERENCES users (id)
        ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
