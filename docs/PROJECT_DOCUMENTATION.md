# SchoolCRM – Full Detailed Documentation

## 1. Project Overview
SchoolCRM is a role-based school management system built with PHP, MySQL, Bootstrap, and shared frontend assets. It supports three core personas:
- **Student**: views attendance, marks, homework, notifications, timetable, fee info.
- **Teacher**: records attendance, publishes homework and announcements, updates marks.
- **Admin**: governs users, academic operations, approvals, permissions, analytics, planning.

---

## 2. Architecture and Folder Responsibilities

### 2.1 Entry and compatibility layer
- `index.php` — common login UI.
- Root compatibility wrappers (e.g., `adminDashboard.php`, `FeeDetails.php`) keep older links working and delegate to `modules/...`.

### 2.2 Role modules
- `student/` — student-first pages and role-specific workflows.
- `teacher/` — teacher-first pages and data-entry workflows.
- `admin/` — admin governance + operations pages.

### 2.3 Shared/cross-cutting
- `backoffice/` — login, logout, password/session paths and legacy aliases.
- `forms/` — submission and AJAX endpoints.
- `config/` — database wiring.
- `middleware/` — access checks.
- `assets/` — CSS/JS/vendor dependencies.
- `modules/` — reorganized legacy page implementations grouped by domain.

---

## 3. Authentication and Session Flow

## 3.1 Login flow
1. User opens `index.php`.
2. Submits username, password, and role.
3. `backoffice/login.php` validates credentials against `user_login`.
4. On success, session variables are set (`access`, `name`, `id`).
5. Redirect target depends on role:
   - student → student dashboard route
   - teacher → `teacher/dashboard.php`
   - admin → `admin/dashboard.php`

## 3.2 Legacy admin route compatibility
To support older bookmarks/redirects, these compatibility entrypoints exist:
- `backoffice/adminDashBoard.php`
- `backoffice/adminDashboard.php`

Both load `admin/dashboard.php`.

---

## 4. Feature Documentation by Persona

## 4.1 Student features

### Dashboard
- Overview cards/charts and academic summaries.
- Files:
  - `student/studentDashboard.php`
  - legacy: `modules/dashboards/studentDashboard.php`

### Attendance
- Daily status and historical attendance.
- Files:
  - `student/attendance.php`
  - support: `backoffice/attendanceToday.php`

### Marks + report cards
- Subject marks display by exam/term.
- Report card rendering and PDF export support.
- Files:
  - `student/marks.php`
  - `modules/student/academic/reportCard.php`
  - `modules/student/academic/reportCard_pdf.php`

### Homework + notifications
- Reads class/school assignments and notices.
- Files:
  - `student/homework.php`
  - `student/notifications.php`
  - shared feeds: `modules/communication/homework.php`, `modules/communication/announcements.php`

### Timetable + fee details
- Accesses schedule and fee detail screens.
- Files:
  - `modules/student/academic/TimeTable.php`
  - `modules/student/academic/FeeDetails.php`

## 4.2 Teacher features

### Dashboard and profile
- Teacher overview and profile management.
- Files:
  - `teacher/dashboard.php`
  - `teacher/teacher_profile.php`
  - includes: `teacher/includes/*`

### Attendance operations
- Records attendance and updates entries.
- Files:
  - `teacher/attendance.php`
  - `forms/submitAttendance.php`
  - `forms/updateAttendanceAjax.php`

### Marks operations
- Adds marks and reviews student records.
- Files:
  - `teacher/add_marks.php`
  - `teacher/view_studentData.php`

### Communication operations
- Publishes announcements and homework.
- Files:
  - `teacher/add_announcement.php`
  - `teacher/add_homework.php`

## 4.3 Admin features

### Core shell
- Files:
  - `admin/index.php`
  - `admin/dashboard.php`
  - `admin/includes/*`

### Governance + control
- Approval, delegation, permissions, security, governance, and data quality.
- Files:
  - `admin/approvals.php`
  - `admin/delegation.php`
  - `admin/permissions.php`
  - `admin/security.php`
  - `admin/attendance_governance.php`
  - `admin/data_quality.php`

### Academic administration
- Master entities and academic operation pages.
- Files:
  - `admin/students.php`
  - `admin/teachers.php`
  - `admin/attendance.php`
  - `admin/exams.php`
  - `admin/marks.php`
  - `admin/homework.php`

### Planning + insights
- Files:
  - `admin/planner.php`
  - `admin/analytics.php`
  - `admin/notification_center.php`
  - `admin/bulk_ops.php`

---

## 5. Database Notes
- Default DB name appears as `asimos` in app code.
- Update credentials in `config/db.php` (and any hardcoded local configs if present).
- Ensure required tables are imported before first login.

---

## 6. Local Setup (Developer)
1. Install PHP 8+ and MySQL.
2. Place repo under web root (e.g., `htdocs/Asimos` for XAMPP).
3. Import DB schema/data.
4. Configure DB credentials.
5. Open `/Asimos/index.php` and test each role login.

---

## 7. Troubleshooting

### 404 on old routes
- Use compatibility endpoints in `backoffice/` and root wrappers.
- Verify Apache document root points to the project folder.

### Login fails
- Confirm role selected at login matches user role in DB.
- Confirm DB connectivity and table data.

### Blank pages
- Enable PHP errors in local dev and check Apache/PHP error logs.

---

## 8. Contribution Guide (Where to Add Code)
- Student functionality: `student/` or `modules/student/academic/`
- Teacher functionality: `teacher/`
- Admin functionality: `admin/`
- Shared account/session logic: `backoffice/` or `modules/common/account/`
- Submission/AJAX endpoints: `forms/`
- Shared frontend resources: `assets/`

Keep root mostly for top-level entrypoints and compatibility wrappers.

---

## 9. Documentation Map
- Full project document: this file (`docs/PROJECT_DOCUMENTATION.md`)
- Functional summary: `docs/FUNCTIONALITIES.md`
- Structure map: `docs/STRUCTURE.md`
- User operations guide: `docs/USER_GUIDE.md`
