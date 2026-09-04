# Demo login credentials

Run the demo seed (truncates existing data and inserts fresh mock records):

```powershell
c:\xampp\php\php.exe scripts\seed_demo_data.php
```

**Password for every demo user:** `Demo@2026`

## Admin

| Username | Role | Name |
|----------|------|------|
| `admin` | Admin (2) | Lakshmi Sundaram |

## Teachers

| Username | Subject | Name |
|----------|---------|------|
| `priya.ramachandran` | English (class teacher 8-A) | Priya Ramachandran |
| `karthik.venkatesh` | Maths (class teacher 8-B) | Karthik Venkatesh |
| `ananya.iyer` | Science | Ananya Iyer |
| `farhan.mohammed` | Social | Mohammed Farhan |
| `divya.nair` | Tamil | Divya Nair |
| `ramesh.babu` | Computer Science (class teacher 9-A) | Ramesh Babu |

## Sample students

| Username | Class | Name |
|----------|-------|------|
| `aditya.krishnan` | 8-A | Aditya Krishnan |
| `meera.subramanian` | 8-A | Meera Subramanian |
| `anbu.selvam` | 8-B | Anbu Selvam |
| `dinesh.kumar` | 9-A | Dinesh Kumar |

## What is seeded

- **32 students** across classes 8-A (12), 8-B (10), 9-A (10) — South Indian names
- **6 teachers** + **1 admin** — all password `Demo@2026`
- **RBAC:** permissions, roles, Super Admin for `admin`
- **Fees:** Term 1–3 per class with paid/unpaid/partial status
- **Timetables:** approved class timetables (Mon–Fri, 6 periods)
- **Marks:** `marks` + `marks_new` for Terms 1–3; `marks_master` + `marks_details` per class exam
- **Assessments:** Unit 1, Unit 2, Mid-Term per subject (student dashboard Class Test table)
- **Homework:** 5 days × all subjects × all classes
- **Notifications** + templates + scheduled notification
- **Attendance** (last 10 school days)
- **Exams & exam_windows** for admin/teacher exam features
- **approval_requests** (pending timetable), **audit_logs**, **login_audit**, **data_quality_issues**, **marks_revisions**

Academic year: **2026-27** | School location: **Chennai**

## Notes

- Login uses the **username** column (`user_login.name`), not the ID.
- Select the correct role radio button on the login page (Student / Teacher / Admin).
- `scripts/truncate_asimos.sql` only clears data; use `seed_demo_data.php` for the full demo reset.
