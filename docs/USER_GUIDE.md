# SchoolCRM User Guide (Detailed)

This guide helps a new user understand how to use SchoolCRM end-to-end.

## 1) Login and Role Selection

1. Open `index.php` in your browser.
2. Enter username and password.
3. Select one role before signing in:
   - Student
   - Teacher
   - Admin
4. Click **Login**.

If credentials and role match, you are redirected to role-appropriate pages.

## 2) Student Guide

### Dashboard
- Use dashboard cards/charts to monitor academic progress and alerts.
- Access quickly from student navigation.

### Attendance
- Open attendance page to verify present/absent status and history.
- Check daily attendance confirmation.

### Marks and Report Card
- Open marks page to review subject scores.
- Generate report card view or PDF where available.

### Homework and Notifications
- Homework page lists assigned tasks from teachers.
- Notifications page shows class or school-wide announcements.

### Timetable and Fee Information
- Timetable page displays class schedule.
- Fee details page shows payable and paid status (based on configured data).

## 3) Teacher Guide

### Dashboard and Profile
- Use dashboard to review class-level activity.
- Manage profile from teacher profile page.

### Attendance Submission
- Open attendance screen.
- Mark students and submit attendance.
- AJAX update endpoints support incremental updates.

### Marks Entry
- Open marks entry page.
- Select test/term and enter subject marks.
- Save and verify in student data views.

### Communication
- Add announcements for class/school audiences.
- Publish homework assignments.

## 4) Admin Guide

### User and Academic Management
- `students.php` and `teachers.php`: maintain master records.
- `attendance.php`, `exams.php`, `marks.php`: monitor and adjust core academic operations.

### Governance and Security
- `permissions.php`: role privilege adjustments.
- `approvals.php`: controlled approval workflows.
- `delegation.php`: authority delegation patterns.
- `security.php`: operational security controls.
- `attendance_governance.php` and `data_quality.php`: compliance and quality checks.

### Planning and Reporting
- `planner.php`: calendar/task-style planning.
- `analytics.php`: aggregate performance and trend views.
- `notification_center.php`: centralized communication management.
- `bulk_ops.php`: high-volume administrative updates.

## 5) Troubleshooting

- **Cannot login**: verify role radio button and credentials.
- **Blank data pages**: check DB connection in `config/db.php` and source table data.
- **Session issues**: ensure PHP sessions are enabled and writable.
- **Permission mismatch**: verify role mapping in login flow and page-level guards.

## 6) Admin Setup Checklist for New Deployment

1. Configure DB credentials in `config/db.php`.
2. Import schema and seed data.
3. Verify at least one account per role (admin/teacher/student).
4. Test login for each role.
5. Test student attendance, marks, and notification visibility.
6. Test teacher submission workflows.
7. Test admin governance and management screens.

