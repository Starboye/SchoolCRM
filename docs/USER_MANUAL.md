# School CRM — User Manual

Welcome to **CampusToday**. This guide helps students, teachers, and administrators use the system day to day.

---

## 1. Introduction

CampusToday is an online portal for your school. It lets you:

- View homework and announcements
- Check attendance and marks
- Download report cards
- Manage students and classes (admin)

There are **three roles**:

| Role | Who uses it |
|------|-------------|
| **Student** | Pupils view their own data |
| **Teacher** | Staff mark attendance, assign homework, enter marks |
| **Admin** | School office manages users, planner, notifications, security |

---

## 2. Getting Started

### 2.1 Open the website

1. Open your web browser (Chrome, Edge, or Firefox).
2. Go to the URL your school gave you (for example: `https://yourschool.com/SchoolCRM/`).
3. You will see the **login page**.

### 2.2 Log in

1. Enter your **username** (this is your login name, not always your email).
2. Enter your **password**.
3. Select your role using the radio buttons:
   - **Student** — if you are a pupil
   - **Teacher** — if you are teaching staff
   - **Admin** — if you are school administration
4. Click **Login**.

**Important:** You must pick the role that matches your account. If you choose the wrong role, login will fail even with the correct password.

### 2.3 First-time tips

- Usernames and passwords are set by your school administrator.
- After login you are taken to your role’s home dashboard.
- Use **Sign Out** when you finish, especially on shared computers.

---

## 3. Student Guide

After logging in as **Student**, you land on the **Dashboard**.

### 3.1 Menu items (left sidebar)

| Menu | What it does |
|------|----------------|
| **Dashboard** | Overview: attendance summary, recent marks, notifications |
| **Fee Status** | Fee information (contact school office if not shown online) |
| **Time Table** | Your class weekly timetable |
| **Report Card** | View marks by term (Term 1, 2, 3) |
| **Home Work** | Daily homework by subject; use arrows to change date |
| **Announcements** | Messages from teachers and school |
| **Change Password** | Update your password |

### 3.2 View homework

1. Click **Home Work** in the sidebar.
2. Use the **date arrows** to move between days.
3. Read homework listed for each subject.

### 3.3 View report card

1. Click **Report Card**.
2. Choose a **term** (Term 1, Term 2, or Term 3) if a selector is shown.
3. Review subject marks and totals.
4. To download a PDF: click the **Download PDF** or print link on the report card page.

### 3.4 Profile and password

1. Click your **name** in the top-right corner.
2. Choose **My Profile** to see your details.
3. Choose **Change Password** (or use the sidebar link):
   - Enter your **current password**
   - Enter a **new password** (at least 6 characters)
   - Confirm the new password
   - Click **Update Password**

### 3.5 Sign out

1. Click your name (top right).
2. Click **Sign Out**.

---

## 4. Teacher Guide

After logging in as **Teacher**, you land on the **Teacher Dashboard**.

### 4.1 Menu items

| Menu | What it does |
|------|----------------|
| **Dashboard** | Summary: homework count, attendance today, quick actions |
| **Attendance** | Mark present/absent by date and session |
| **Add Homework** | Assign homework to a class |
| **Enter Marks** | Record test marks for students |
| **Manage Marks** | Review marks you have entered |
| **Add Announcement** | Send a notice to students |
| **Student Lookup** | View a student’s profile, marks, attendance |
| **Exam Timetable** | Your teaching timetable from the school planner |
| **Profile** | Your teacher profile |

### 4.2 Mark attendance

1. Open **Attendance**.
2. Pick the **date**.
3. Pick the **session** (Morning, Afternoon, or Evening).
4. Click **Load Students**.
5. For each student, click **P** (Present) or **A** (Absent).
6. Status updates immediately.

### 4.3 Add homework

1. Open **Add Homework**.
2. Select **class** and **section**.
3. Enter **subject**, **title**, and **description**.
4. Set the **date**.
5. Click **Save** or **Submit**.

### 4.4 Enter marks

1. Open **Enter Marks** (Add Marks).
2. Select **class** and **section**.
3. Enter the **exam name** (e.g. Term 1, Unit Test).
4. Choose **Entire Class** or **Single Student**.
5. Enter marks for each subject (obtained and total).
6. Click **Save Marks**.
7. Open **Manage Marks** to verify saved results.

### 4.5 Send an announcement

1. Open **Add Announcement**.
2. Write your message.
3. Choose who receives it (class or individuals as offered).
4. Submit.

### 4.6 Sign out

Use **Sign Out** from the top-right profile menu.

---

## 5. Admin Guide

After logging in as **Admin**, you land on the **Admin Dashboard**.

### 5.1 Main menu areas

| Menu | Purpose |
|------|---------|
| **Dashboard** | School-wide overview |
| **Students** | Add, edit, view student records |
| **Teachers** | Manage teacher accounts and allocations |
| **Attendance** | View and manage attendance records |
| **Homework** | Oversee homework across classes |
| **Marks** | Manage consolidated marks (`marks_new` for report cards) |
| **RBAC** | Roles and permissions for admin users |
| **Approvals** | Review items needing approval |
| **Bulk Ops** | Import or bulk-update data |
| **Planner** | Build class timetables (`timetable_slots`) |
| **Attendance Gov** | Lock or govern attendance days |
| **Exam Lifecycle** | Configure exam windows |
| **Notification Ctr** | School-wide notifications |
| **Analytics** | Reports and charts |
| **Security** | Login audit, account security |
| **Data Quality** | Find and fix data issues |
| **Delegation** | Temporary access delegation |

### 5.2 Typical admin tasks

**Add a student**

1. Go to **Students**.
2. Click add/new.
3. Fill in details and save.
4. Ensure a matching **user_login** account exists with access = Student (0).

**Set up timetable**

1. Go to **Planner**.
2. Assign subjects, teachers, periods, and days for each class.
3. Students and teachers will see timetables after slots are saved.

**Send school notification**

1. Go to **Notification Ctr**.
2. Create a new notification.
3. Choose audience (all, class, or specific users).

---

## 6. Common Tasks

### Reset or change password

**Students:** Sidebar → **Change Password**, or profile menu → **Change Password**.

**Teachers/Admins using profile form:** Profile page → change password section (submits to the password update handler).

You need your **current password**. If you forgot it, contact your school administrator.

### Login fails

1. Check **username** and **password** (caps lock off).
2. Confirm you selected the **correct role** (Student / Teacher / Admin).
3. Try again after a minute.
4. Contact admin if the account may be locked or missing.

### Sign out

Always use **Sign Out** from the profile menu. Closing the browser tab may not end your session immediately.

---

## 7. Troubleshooting

| Problem | What to try |
|---------|-------------|
| **“Session expired” or sent to login** | Your session timed out. Log in again. |
| **Wrong page after login** | You may have picked the wrong role. Log out and select the correct one. |
| **Page blank or old layout** | Clear browser cache (Ctrl+F5) or try another browser. |
| **Sidebar missing** | Refresh the page. If it persists, contact admin. |
| **Report card shows no marks** | Marks may not be entered for that term yet. Ask your teacher. |
| **Fees not shown** | Online fees may not be configured. Contact the school office. |
| **403 Forbidden** | Your account cannot access that page. Use the correct role or ask admin. |

---

## 8. Screen Reference — Menu by Role

### Student

1. Dashboard  
2. Fee Status  
3. Time Table  
4. Report Card  
5. Home Work  
6. Announcements  
7. Change Password  
8. Sign Out (profile menu)

### Teacher

1. Dashboard  
2. Attendance  
3. Add Homework  
4. Enter Marks  
5. Manage Marks  
6. Add Announcement  
7. Student Lookup  
8. Exam Timetable  
9. Profile  
10. Sign Out

### Admin

1. Dashboard  
2. Students  
3. Teachers  
4. Attendance  
5. Homework  
6. Marks  
7. RBAC  
8. Approvals  
9. Bulk Ops  
10. Planner  
11. Attendance Gov  
12. Exam Lifecycle  
13. Notification Ctr  
14. Analytics  
15. Security  
16. Data Quality  
17. Delegation  
18. Sign Out

---

*For technical support, contact your school IT administrator.*
