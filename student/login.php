<?php
declare(strict_types=1);
require_once __DIR__ . '/../config/db.php';
session_start();

// If already logged in as student, go dashboard/homework
if (isset($_SESSION['user_id'], $_SESSION['access']) && (int)$_SESSION['access'] === 0) {
  header('Location: /student/homework.php'); exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $id = trim($_POST['id'] ?? '');
  $password = trim($_POST['password'] ?? '');
  if ($id === '' || $password === '') {
    $error = 'Enter ID and Password';
  } else {
    // Students have access=0 and login "id" equals student_info.id
    $sql = "SELECT id, name, password, access FROM user_login WHERE id = :id LIMIT 1";
    $stmt = db()->prepare($sql);
    $stmt->execute([':id' => $id]);
    $user = $stmt->fetch();
    if ($user && (string)$user['password'] === $password && (int)$user['access'] === 0) {
      $_SESSION['user_id'] = $user['id'];      // student id
      $_SESSION['name']    = $user['name'] ?? '';
      $_SESSION['access']  = (int)$user['access']; // 0=student
      header('Location: /student/homework.php'); exit;
    } else {
      $error = 'Invalid credentials or not a student account.';
    }
  }
}
?><!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Student Login</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="/assets/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-4">
      <div class="card shadow">
        <div class="card-body">
          <h4 class="mb-3 text-center">Student Login</h4>
          <?php if ($error): ?>
            <div class="alert alert-danger p-2"><?= htmlspecialchars($error) ?></div>
          <?php endif; ?>
          <form method="post" autocomplete="off">
            <div class="mb-3">
              <label class="form-label">Student ID</label>
              <input name="id" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Password</label>
              <input type="password" name="password" class="form-control" required>
            </div>
            <button class="btn btn-primary w-100">Login</button>
          </form>
          <p class="text-muted small mt-3">Use your Student ID (e.g., from <code>student_info.id</code>). </p>
        </div>
      </div>
    </div>
  </div>
</div>
</body></html>
