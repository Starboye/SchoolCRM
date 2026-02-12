# Codebase Structure and Rearrangement Map

## Overview
This document explains how files are organized by functionality and where to add new code.

## High-level layout

- **Role modules**
  - `admin/`: administrator console and controls.
  - `teacher/`: teacher operations.
  - `student/`: student-facing portal.
- **Cross-cutting modules**
  - `modules/common/`: shared account-level pages.
  - `modules/communication/`: announcement/homework communication pages.
  - `modules/student/academic/`: student academic data pages.
  - `modules/dashboards/`: legacy dashboard pages.
- **System infrastructure**
  - `backoffice/`: login/logout and credential/session handling.
  - `config/`: environment/database configuration.
  - `middleware/`: authorization checks.
  - `forms/`: submission handlers and AJAX endpoints.
  - `assets/`: CSS/JS/vendor and static resources.

## Rearranged root files

The following root-level files were moved into `modules/` and replaced with compatibility wrappers:

| Old root path | New location |
|---|---|
| `adminDashboard.php` | `modules/dashboards/adminDashboard.php` |
| `studentDashboard.php` | `modules/dashboards/studentDashboard.php` |
| `teachersDashboard.php` | `modules/dashboards/teachersDashboard.php` |
| `FeeDetails.php` | `modules/student/academic/FeeDetails.php` |
| `TimeTable.php` | `modules/student/academic/TimeTable.php` |
| `reportCard.php` | `modules/student/academic/reportCard.php` |
| `reportCard_pdf.php` | `modules/student/academic/reportCard_pdf.php` |
| `announcements.php` | `modules/communication/announcements.php` |
| `homework.php` | `modules/communication/homework.php` |
| `changePassword.php` | `modules/common/account/changePassword.php` |
| `logout.php` | `modules/common/account/logout.php` |
| `users-profile.php` | `modules/common/account/users-profile.php` |

## Where to add new code

- New **student-specific** screens: `student/` or `modules/student/academic/`.
- New **teacher-specific** screens: `teacher/`.
- New **admin governance/reporting** screens: `admin/`.
- Shared **authentication/account/session** behavior: `backoffice/` + `modules/common/account/`.
- Shared **API/form endpoints**: `forms/`.
- Shared **UI libraries and scripts**: `assets/`.

## Recommended naming rules

- Use snake_case for endpoint files (`update_attendance_ajax.php`).
- Keep role-specific pages in their role folders.
- Keep root directory reserved for entry points (e.g., `index.php`) and wrappers only.

