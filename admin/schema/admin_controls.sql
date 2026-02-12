-- Admin control framework tables
CREATE TABLE IF NOT EXISTS permissions (
  permission_key VARCHAR(64) PRIMARY KEY,
  description VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS roles (
  role_id INT AUTO_INCREMENT PRIMARY KEY,
  role_name VARCHAR(64) UNIQUE NOT NULL,
  role_scope ENUM('global','academic','hr','exam','ops') DEFAULT 'global',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS role_permissions (
  role_id INT NOT NULL,
  permission_key VARCHAR(64) NOT NULL,
  PRIMARY KEY(role_id, permission_key),
  FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE CASCADE,
  FOREIGN KEY (permission_key) REFERENCES permissions(permission_key) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_roles (
  user_id VARCHAR(20) NOT NULL,
  role_id INT NOT NULL,
  assigned_by VARCHAR(20) DEFAULT NULL,
  assigned_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(user_id, role_id),
  FOREIGN KEY (role_id) REFERENCES roles(role_id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES user_login(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS audit_logs (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  actor_id VARCHAR(20) NOT NULL,
  actor_name VARCHAR(100) DEFAULT NULL,
  module VARCHAR(64) NOT NULL,
  action VARCHAR(64) NOT NULL,
  entity_type VARCHAR(64) NOT NULL,
  entity_id VARCHAR(64) DEFAULT NULL,
  before_json LONGTEXT NULL,
  after_json LONGTEXT NULL,
  ip_address VARCHAR(64) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS approval_requests (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  module VARCHAR(64) NOT NULL,
  action VARCHAR(64) NOT NULL,
  entity_type VARCHAR(64) NOT NULL,
  entity_id VARCHAR(64) DEFAULT NULL,
  payload_json LONGTEXT NOT NULL,
  requested_by VARCHAR(20) NOT NULL,
  status ENUM('pending','approved','rejected') DEFAULT 'pending',
  reviewed_by VARCHAR(20) DEFAULT NULL,
  reviewed_at DATETIME DEFAULT NULL,
  review_note VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS approval_policies (
  policy_key VARCHAR(64) PRIMARY KEY,
  require_approval TINYINT(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS security_policies (
  policy_key VARCHAR(64) PRIMARY KEY,
  policy_value VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS user_security (
  user_id VARCHAR(20) PRIMARY KEY,
  force_password_reset TINYINT(1) DEFAULT 0,
  failed_attempts INT DEFAULT 0,
  locked_until DATETIME DEFAULT NULL,
  last_password_changed DATETIME DEFAULT NULL,
  FOREIGN KEY (user_id) REFERENCES user_login(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS login_audit (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  user_id VARCHAR(20) DEFAULT NULL,
  username VARCHAR(100) DEFAULT NULL,
  status ENUM('success','failed') NOT NULL,
  ip_address VARCHAR(64) DEFAULT NULL,
  user_agent VARCHAR(255) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS timetable_slots (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  teacher_id VARCHAR(20) NOT NULL,
  standard INT NOT NULL,
  section VARCHAR(2) NOT NULL,
  subject_name VARCHAR(50) NOT NULL,
  day_of_week TINYINT NOT NULL,
  period_no TINYINT NOT NULL,
  created_by VARCHAR(20) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS attendance_day_lock (
  lock_date DATE PRIMARY KEY,
  is_locked TINYINT(1) DEFAULT 1,
  lock_reason VARCHAR(255) DEFAULT NULL,
  locked_by VARCHAR(20) DEFAULT NULL,
  locked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS exam_windows (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  exam_name VARCHAR(64) NOT NULL,
  starts_on DATE NOT NULL,
  ends_on DATE NOT NULL,
  marks_entry_locked TINYINT(1) DEFAULT 0,
  marks_published TINYINT(1) DEFAULT 0,
  created_by VARCHAR(20) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS marks_revisions (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  student_id VARCHAR(20) NOT NULL,
  test_name VARCHAR(64) NOT NULL,
  before_json LONGTEXT,
  after_json LONGTEXT,
  changed_by VARCHAR(20) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notification_templates (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(80) NOT NULL,
  body TEXT NOT NULL,
  created_by VARCHAR(20) DEFAULT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS scheduled_notifications (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  template_id BIGINT DEFAULT NULL,
  target_type VARCHAR(32) NOT NULL,
  target_value VARCHAR(64) DEFAULT NULL,
  message TEXT NOT NULL,
  scheduled_at DATETIME NOT NULL,
  status ENUM('scheduled','sent','cancelled') DEFAULT 'scheduled',
  created_by VARCHAR(20) DEFAULT NULL,
  FOREIGN KEY (template_id) REFERENCES notification_templates(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS notification_reads (
  notification_id BIGINT NOT NULL,
  user_id VARCHAR(20) NOT NULL,
  read_at DATETIME DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY(notification_id, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS data_quality_issues (
  id BIGINT AUTO_INCREMENT PRIMARY KEY,
  issue_type VARCHAR(64) NOT NULL,
  entity_type VARCHAR(64) NOT NULL,
  entity_id VARCHAR(64) DEFAULT NULL,
  issue_details VARCHAR(255) NOT NULL,
  status ENUM('open','resolved') DEFAULT 'open',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  resolved_at DATETIME DEFAULT NULL,
  resolved_by VARCHAR(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT IGNORE INTO permissions(permission_key, description) VALUES
('can_manage_users','Create/update/delete users'),
('can_edit_marks','Edit marks records'),
('can_delete_attendance','Delete or override attendance records'),
('can_manage_notifications','Broadcast and schedule notifications'),
('can_manage_planner','Manage timetable and workload planner'),
('can_manage_exams','Manage exam windows and marks lifecycle'),
('can_view_analytics','View analytics dashboards'),
('can_manage_security','Manage security policies and lockouts'),
('can_manage_data_quality','Run and resolve data quality checks'),
('can_manage_delegation','Assign sub-admin roles');

INSERT IGNORE INTO roles(role_id, role_name, role_scope) VALUES
(1,'Super Admin','global'),
(2,'Academic Admin','academic'),
(3,'HR Admin','hr'),
(4,'Exam Admin','exam'),
(5,'Ops Admin','ops');

INSERT IGNORE INTO role_permissions(role_id, permission_key)
SELECT 1, permission_key FROM permissions;

INSERT IGNORE INTO approval_policies(policy_key, require_approval) VALUES
('marks_edit',1),('attendance_override',1),('user_delete',1),('homework_delete',0);

INSERT IGNORE INTO security_policies(policy_key, policy_value) VALUES
('max_failed_attempts','5'),
('session_timeout_minutes','60'),
('password_min_length','8');
