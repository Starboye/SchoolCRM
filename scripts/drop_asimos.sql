-- Drop all tables in `asimos` (full schema reset)
-- Reference dump: asimos-sql-04-09-2026.sql (generated 2026-09-04)
--
-- Use this BEFORE importing asimos-sql-04-09-2026.sql when you see:
--   #1050 - Table 'approval_policies' already exists
--
-- Usage (XAMPP / MariaDB):
--   mysql -u root asimos < scripts/drop_asimos.sql
--   mysql -u root asimos < asimos-sql-04-09-2026.sql
--
-- Or PowerShell:
--   Get-Content scripts/drop_asimos.sql | mysql -u root asimos
--   Get-Content asimos-sql-04-09-2026.sql | mysql -u root asimos
--
-- WARNING: Drops every application table and all data.

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS `notification_reads`;
DROP TABLE IF EXISTS `scheduled_notifications`;
DROP TABLE IF EXISTS `marks_details`;
DROP TABLE IF EXISTS `marks_master`;
DROP TABLE IF EXISTS `class_subjects`;
DROP TABLE IF EXISTS `student_fee_status`;
DROP TABLE IF EXISTS `role_permissions`;
DROP TABLE IF EXISTS `user_roles`;
DROP TABLE IF EXISTS `user_security`;
DROP TABLE IF EXISTS `approval_requests`;
DROP TABLE IF EXISTS `audit_logs`;
DROP TABLE IF EXISTS `login_audit`;
DROP TABLE IF EXISTS `marks_revisions`;
DROP TABLE IF EXISTS `data_quality_issues`;
DROP TABLE IF EXISTS `attendance`;
DROP TABLE IF EXISTS `attendance_day_lock`;
DROP TABLE IF EXISTS `assessments`;
DROP TABLE IF EXISTS `marks`;
DROP TABLE IF EXISTS `marks_new`;
DROP TABLE IF EXISTS `homeworks`;
DROP TABLE IF EXISTS `notification`;
DROP TABLE IF EXISTS `timetable_slots`;
DROP TABLE IF EXISTS `class_teacher_assignments`;
DROP TABLE IF EXISTS `class_timetables`;
DROP TABLE IF EXISTS `fee_structures`;
DROP TABLE IF EXISTS `teacher_subject_allocation`;
DROP TABLE IF EXISTS `exams`;
DROP TABLE IF EXISTS `exam_windows`;
DROP TABLE IF EXISTS `teacher_info`;
DROP TABLE IF EXISTS `student_info`;
DROP TABLE IF EXISTS `user_login`;
DROP TABLE IF EXISTS `class_sections`;
DROP TABLE IF EXISTS `subjects`;
DROP TABLE IF EXISTS `notification_templates`;
DROP TABLE IF EXISTS `permissions`;
DROP TABLE IF EXISTS `roles`;
DROP TABLE IF EXISTS `approval_policies`;
DROP TABLE IF EXISTS `security_policies`;

SET FOREIGN_KEY_CHECKS = 1;
