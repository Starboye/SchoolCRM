# Functional Documentation (Students, Teachers, Admin, Utilities)

This document describes each major feature area and maps it to implementation files.

---

## 1. Authentication and Session Utilities

### What it does
- Handles login, logout, password flow, and session lifecycle.
- Routes users to role-specific dashboards.

### Main files
- Entry UI: `index.php`
- Login handler: `backoffice/login.php`
- Logout handlers: `backoffice/logout.php`, `student/logout.php`, root wrapper `logout.php`
- Password update: `backoffice/changePassword.php`, root wrapper `changePassword.php`
- DB connectivity: `config/db.php`
- Student auth guard: `middleware/auth_student.php`

### Notes
- Role is selected during login (`student/teacher/admin`).
- Session variables are used to identify the current user.

---

## 2. Student Functionalities

### 2.1 Student dashboard
- Provides student overview widgets and academic snapshots.
- Files:
  - `student/studentDashboard.php`
  - Legacy dashboard implementation: `modules/dashboards/studentDashboard.php`
  - Compatibility URL: `studentDashboard.php`

### 2.2 Attendance
- Student can view attendance records and today's status.
- Files:
  - `student/attendance.php`
  - `backoffice/attendanceToday.php`

### 2.3 Marks and report cards
- Student can review marks and report card data.
- Files:
  - `student/marks.php`
  - `modules/student/academic/reportCard.php`
  - `modules/student/academic/reportCard_pdf.php`
  - Compatibility URLs: `reportCard.php`, `reportCard_pdf.php`

### 2.4 Homework and notifications
- Student can read assigned homework and school notifications.
- Files:
  - `student/homework.php`
  - `student/notifications.php`
  - Shared communication modules:
    - `modules/communication/homework.php`
    - `modules/communication/announcements.php`

### 2.5 Timetable and fee details
- Student can inspect timetable and fee-related data.
- Files:
  - `modules/student/academic/TimeTable.php`
  - `modules/student/academic/FeeDetails.php`
  - Compatibility URLs: `TimeTable.php`, `FeeDetails.php`

---

## 3. Teacher Functionalities

### 3.1 Teacher dashboard and profile
- Teacher landing dashboard and profile controls.
- Files:
  - `teacher/dashboard.php`
  - `teacher/teacher_profile.php`
  - Shared layout/auth includes:
    - `teacher/includes/teacher_auth.php`
    - `teacher/includes/teacher_header.php`
    - `teacher/includes/teacher_sidebar.php`
    - `teacher/includes/teacher_footer.php`

### 3.2 Attendance operations
- Teachers can submit and monitor attendance.
- Files:
  - `teacher/attendance.php`
  - Form endpoints:
    - `forms/submitAttendance.php`
    - `forms/updateAttendanceAjax.php`

### 3.3 Marks management
- Teachers can add marks and review student data.
- Files:
  - `teacher/add_marks.php`
  - `teacher/view_studentData.php`

### 3.4 Announcement and homework publishing
- Teachers can publish class notices and homework.
- Files:
  - `teacher/add_announcement.php`
  - `teacher/add_homework.php`

---

## 4. Admin Functionalities

### 4.1 Core admin shell
- Admin entry dashboard and shared layout.
- Files:
  - `admin/index.php`
  - `admin/dashboard.php`
  - Shared includes:
    - `admin/includes/bootstrap.php`
    - `admin/includes/header.php`
    - `admin/includes/sidebar.php`
    - `admin/includes/footer.php`

### 4.2 Governance and controls
- Approval workflows, delegation, permissions, security, and compliance-like controls.
- Files:
  - `admin/approvals.php`
  - `admin/delegation.php`
  - `admin/permissions.php`
  - `admin/security.php`
  - `admin/attendance_governance.php`
  - `admin/data_quality.php`

### 4.3 Academic operations
- Student/teacher data operations and exam/marks management.
- Files:
  - `admin/students.php`
  - `admin/teachers.php`
  - `admin/attendance.php`
  - `admin/exams.php`
  - `admin/marks.php`
  - `admin/homework.php`

### 4.4 Planning, analytics, and communications
- Schedule planning, notifications, bulk actions, and insight pages.
- Files:
  - `admin/planner.php`
  - `admin/analytics.php`
  - `admin/notification_center.php`
  - `admin/bulk_ops.php`

### 4.5 Admin schema artifact
- SQL structure snippets for admin controls.
- Files:
  - `admin/schema/admin_controls.sql`

---

## 5. Shared UI and Static Utilities

### What it does
- Shared JS/CSS components and third-party frontend libraries.

### Main files/folders
- `assets/js/` → main app scripts and sidebar logic.
- `assets/css/` and `assets/scss/` → styling.
- `assets/vendor/` → bootstrap, charts, editors, icons, etc.
- `partials/` and `includes/` → reusable template fragments.

---

## 6. Functional User Flows

### Student flow
1. Login from `index.php`.
2. Reach dashboard and access attendance/marks/homework/notifications.
3. Open timetable, fee details, and report cards as needed.

### Teacher flow
1. Login from `index.php` as teacher.
2. Open teacher dashboard.
3. Publish homework/announcements.
4. Record attendance and upload marks.

### Admin flow
1. Login from `index.php` as admin.
2. Open admin dashboard.
3. Manage users, permissions, governance, planning, and analytics.

