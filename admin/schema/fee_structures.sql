-- Fee structure (admin-managed) and per-student payment status
-- Import after admin_controls.sql

CREATE TABLE IF NOT EXISTS fee_structures (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  standard INT NOT NULL,
  section VARCHAR(2) NOT NULL,
  term_label VARCHAR(32) NOT NULL,
  term_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
  special_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
  tuition_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
  lab_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
  total_fee DECIMAL(12,2) NOT NULL DEFAULT 0,
  academic_year VARCHAR(16) NOT NULL DEFAULT '',
  updated_by VARCHAR(20) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_fee_class_term (standard, section, term_label, academic_year)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS student_fee_status (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  student_id VARCHAR(20) NOT NULL,
  fee_structure_id BIGINT NOT NULL,
  status ENUM('paid','unpaid','partial') NOT NULL DEFAULT 'unpaid',
  amount_paid DECIMAL(12,2) NOT NULL DEFAULT 0,
  paid_on DATE DEFAULT NULL,
  updated_by VARCHAR(20) DEFAULT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uk_student_fee (student_id, fee_structure_id),
  CONSTRAINT fk_student_fee_structure FOREIGN KEY (fee_structure_id) REFERENCES fee_structures(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO permissions (permission_key, description) VALUES
('can_manage_fees', 'Manage class fee structures and student payment status');
