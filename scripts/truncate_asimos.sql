-- Truncate all data in `asimos` (schema preserved)
-- Reference dump: asimos-sql-04-09-2026.sql (generated 2026-09-04)
--
-- Usage (XAMPP / MariaDB) — data-only reset (tables must already exist):
--   mysql -u root asimos < scripts/truncate_asimos.sql
--
-- To load South Indian demo data (truncate + insert):
--   php scripts/seed_demo_data.php
-- See docs/DEMO_CREDENTIALS.md for usernames (password: Demo@2026)
--
-- WARNING: This deletes ALL rows from every application table.
--          Tables and foreign keys are kept; only data is removed.
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- Child / dependent tables first
TRUNCATE TABLE `notification_reads`;
TRUNCATE TABLE `scheduled_notifications`;
TRUNCATE TABLE `marks_details`;
TRUNCATE TABLE `marks_master`;
TRUNCATE TABLE `class_subjects`;
TRUNCATE TABLE `student_fee_status`;
TRUNCATE TABLE `role_permissions`;
TRUNCATE TABLE `user_roles`;
TRUNCATE TABLE `user_security`;

-- Operational / transactional data
TRUNCATE TABLE `approval_requests`;
TRUNCATE TABLE `audit_logs`;
TRUNCATE TABLE `login_audit`;
TRUNCATE TABLE `marks_revisions`;
TRUNCATE TABLE `data_quality_issues`;
TRUNCATE TABLE `attendance`;
TRUNCATE TABLE `attendance_day_lock`;
TRUNCATE TABLE `assessments`;
TRUNCATE TABLE `marks`;
TRUNCATE TABLE `marks_new`;
TRUNCATE TABLE `homeworks`;
TRUNCATE TABLE `notification`;
TRUNCATE TABLE `timetable_slots`;
TRUNCATE TABLE `class_teacher_assignments`;
TRUNCATE TABLE `class_timetables`;
TRUNCATE TABLE `fee_structures`;
TRUNCATE TABLE `teacher_subject_allocation`;
TRUNCATE TABLE `exams`;
TRUNCATE TABLE `exam_windows`;

-- Core entities
TRUNCATE TABLE `teacher_info`;
TRUNCATE TABLE `student_info`;
TRUNCATE TABLE `user_login`;
TRUNCATE TABLE `class_sections`;
TRUNCATE TABLE `subjects`;

-- System configuration (re-seeded by asimos-sql-04-09-2026.sql)
TRUNCATE TABLE `notification_templates`;
TRUNCATE TABLE `permissions`;
TRUNCATE TABLE `roles`;
TRUNCATE TABLE `approval_policies`;
TRUNCATE TABLE `security_policies`;

SET FOREIGN_KEY_CHECKS = 1;

-- Verify (optional — uncomment to run manually after import)
-- SELECT table_name, table_rows
-- FROM information_schema.tables
-- WHERE table_schema = 'asimos'
-- ORDER BY table_name;
