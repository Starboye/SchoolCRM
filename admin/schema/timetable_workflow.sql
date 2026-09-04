-- Class timetable approval workflow (class teacher → admin approval → student view)
-- Import after admin_controls.sql
-- Note: legacy `class_sections` (id, standard, section) is used by teacher forms — do not alter it.

CREATE TABLE IF NOT EXISTS class_teacher_assignments (
  standard INT NOT NULL,
  section VARCHAR(2) NOT NULL,
  academic_year VARCHAR(16) NOT NULL,
  class_teacher_id VARCHAR(20) NOT NULL,
  assigned_by VARCHAR(20) DEFAULT NULL,
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (standard, section, academic_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS class_timetables (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  standard INT NOT NULL,
  section VARCHAR(2) NOT NULL,
  academic_year VARCHAR(16) NOT NULL,
  status ENUM('draft','pending','approved','rejected') NOT NULL DEFAULT 'draft',
  submitted_by VARCHAR(20) DEFAULT NULL,
  submitted_at DATETIME DEFAULT NULL,
  reviewed_by VARCHAR(20) DEFAULT NULL,
  reviewed_at DATETIME DEFAULT NULL,
  review_note VARCHAR(255) DEFAULT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_class_tt (standard, section, academic_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO approval_policies (policy_key, require_approval) VALUES
('timetable_submit', 1);
