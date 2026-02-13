# SchoolCRM

SchoolCRM is a PHP/MySQL school management platform with role-based workflows for **students**, **teachers**, and **administrators**.

## 1) Project Structure (Reorganized)

The repository is now grouped by domain responsibility:

- `admin/` → Modern admin module (governance, approvals, analytics, security, planning).
- `teacher/` → Teacher workflows (attendance, marks, homework, announcements, profile).
- `student/` → Student self-service area (dashboard, attendance, homework, marks, notifications).
- `modules/` → Reorganized legacy root pages grouped by capability:
  - `modules/dashboards/` → legacy dashboard implementations.
  - `modules/student/academic/` → fees, timetable, report cards.
  - `modules/communication/` → announcements and homework feeds.
  - `modules/common/account/` → account/profile/logout/password pages.
- `backoffice/` → Authentication/session backend endpoints.
- `forms/` → Form processors and AJAX endpoints.
- `config/` → Database connection setup.
- `middleware/` → Access guards.
- `assets/` → shared frontend assets.
- `docs/` → detailed product and feature documentation.

For backwards compatibility, original root URLs remain available as lightweight wrappers.

## 2) Getting Started

1. Install and run a PHP server (Apache/Nginx or `php -S`).
2. Create/import MySQL database (`asimos`) and required tables.
3. Update DB credentials in `config/db.php` if needed.
4. Open `index.php` and log in with one of the supported roles.

## 3) Documentation

- Functional documentation: `docs/FUNCTIONALITIES.md`
- File organization and routing map: `docs/STRUCTURE.md`
- New-user setup and usage guide: `docs/USER_GUIDE.md`

