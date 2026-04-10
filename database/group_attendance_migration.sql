-- ======================================================
-- FYP Management System - Group Submission + Attendance
-- MySQL / phpMyAdmin (XAMPP)
-- ======================================================

-- 1) GROUP TABLE
CREATE TABLE IF NOT EXISTS project_groups (
    id INT AUTO_INCREMENT PRIMARY KEY,
    group_name VARCHAR(120) NOT NULL,
    group_code VARCHAR(50) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2) EACH STUDENT BELONGS TO ONE GROUP (nullable until admin assigns)
ALTER TABLE users
    ADD COLUMN group_id INT NULL AFTER role;

ALTER TABLE users
    ADD CONSTRAINT fk_users_group
    FOREIGN KEY (group_id) REFERENCES project_groups(id)
    ON DELETE SET NULL
    ON UPDATE CASCADE;

CREATE INDEX idx_users_group_id ON users(group_id);

-- 3) PROJECTS MUST LINK TO GROUP
ALTER TABLE projects
    ADD COLUMN group_id INT NULL AFTER student_id;

ALTER TABLE projects
    ADD CONSTRAINT fk_projects_group
    FOREIGN KEY (group_id) REFERENCES project_groups(id)
    ON DELETE RESTRICT
    ON UPDATE CASCADE;

CREATE INDEX idx_projects_group_id ON projects(group_id);

-- Optional but recommended:
-- One project submission per group
ALTER TABLE projects
    ADD UNIQUE KEY uq_projects_group (group_id);

-- Optional data migration for existing records:
-- Copy each old student_id's group into projects.group_id
UPDATE projects p
JOIN users u ON u.id = p.student_id
SET p.group_id = u.group_id
WHERE p.group_id IS NULL;

-- 4) STUDENT ATTENDANCE (MANUAL BY SUPERVISOR)
CREATE TABLE IF NOT EXISTS student_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    supervisor_id INT NOT NULL,
    group_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present', 'absent') NOT NULL,
    marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_student_attendance_student
        FOREIGN KEY (student_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_student_attendance_supervisor
        FOREIGN KEY (supervisor_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_student_attendance_group
        FOREIGN KEY (group_id) REFERENCES project_groups(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uq_student_date (student_id, attendance_date),
    KEY idx_student_attendance_group_date (group_id, attendance_date),
    KEY idx_student_attendance_supervisor (supervisor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5) SUPERVISOR ATTENDANCE (MANUAL BY ADMIN)
CREATE TABLE IF NOT EXISTS supervisor_attendance (
    id INT AUTO_INCREMENT PRIMARY KEY,
    supervisor_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present', 'absent') NOT NULL,
    marked_by_admin_id INT NULL,
    marked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_supervisor_attendance_supervisor
        FOREIGN KEY (supervisor_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_supervisor_attendance_admin
        FOREIGN KEY (marked_by_admin_id) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    UNIQUE KEY uq_supervisor_date (supervisor_id, attendance_date),
    KEY idx_supervisor_attendance_date (attendance_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ======================================================
-- IMPORTANT AFTER RUNNING:
-- - Assign each student to a group (users.group_id)
-- - Ensure projects.group_id is populated
-- ======================================================
