# Restructure Notes (What Changed and Why)

This document captures the major restructuring work that was done, including compatibility behavior and documentation locations.

## 1) What was changed

The project was reorganized to group files by functionality and user role:

- **Admin functionality** in `admin/`
- **Teacher functionality** in `teacher/`
- **Student functionality** in `student/`
- **Cross-cutting legacy pages** moved into `modules/` subfolders:
  - `modules/dashboards/`
  - `modules/student/academic/`
  - `modules/communication/`
  - `modules/common/account/`

A set of root files (legacy endpoints) were converted into lightweight wrappers that load their new module paths.

---

## 2) Legacy compatibility behavior

To avoid breaking old links and existing bookmarks:

- Root wrappers were preserved for legacy routes (for example `adminDashboard.php`, `reportCard.php`, `FeeDetails.php`, etc.).
- Legacy admin backoffice entrypoints were added:
  - `backoffice/adminDashBoard.php`
  - `backoffice/adminDashboard.php`

These legacy backoffice files now load the current admin dashboard implementation to maintain backward compatibility.

---

## 3) Why this was done

- Improve discoverability and maintenance by role/domain ownership.
- Reduce confusion caused by many large root-level files.
- Keep old URLs functional while introducing cleaner structure.
- Provide clear onboarding documentation for new users and contributors.

---

## 4) Where the full documentation is now

Start with the documentation hub:
- `docs/README.md`

Main detailed project documentation:
- `docs/PROJECT_DOCUMENTATION.md`

Supporting docs:
- `docs/FUNCTIONALITIES.md`
- `docs/STRUCTURE.md`
- `docs/USER_GUIDE.md`

---

## 5) Quick reference: old-to-new direction

Examples of wrapper-to-module mapping:

- `adminDashboard.php` → `modules/dashboards/adminDashboard.php`
- `studentDashboard.php` → `modules/dashboards/studentDashboard.php`
- `teachersDashboard.php` → `modules/dashboards/teachersDashboard.php`
- `FeeDetails.php` → `modules/student/academic/FeeDetails.php`
- `TimeTable.php` → `modules/student/academic/TimeTable.php`
- `reportCard.php` → `modules/student/academic/reportCard.php`
- `reportCard_pdf.php` → `modules/student/academic/reportCard_pdf.php`
- `announcements.php` → `modules/communication/announcements.php`
- `homework.php` → `modules/communication/homework.php`
- `changePassword.php` → `modules/common/account/changePassword.php`
- `logout.php` → `modules/common/account/logout.php`
- `users-profile.php` → `modules/common/account/users-profile.php`

