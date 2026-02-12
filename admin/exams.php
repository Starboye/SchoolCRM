<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_permission($db,'can_manage_exams');
$pageTitle='Exam Lifecycle'; $msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $action=$_POST['action']??'';
  if($action==='create_window'){
    $name=$_POST['exam_name']; $s=$_POST['starts_on']; $e=$_POST['ends_on']; $by=(string)$_SESSION['id'];
    $stmt=mysqli_prepare($db,'INSERT INTO exam_windows (exam_name,starts_on,ends_on,created_by) VALUES (?,?,?,?)'); mysqli_stmt_bind_param($stmt,'ssss',$name,$s,$e,$by); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
    log_audit($db,'exam','create_window','exam_window',null,null,['name'=>$name]); $msg='Exam window created.';
  }
  if($action==='toggle_lock'){
    $id=(int)$_POST['id']; $stmt=mysqli_prepare($db,'UPDATE exam_windows SET marks_entry_locked = 1-marks_entry_locked WHERE id=?'); mysqli_stmt_bind_param($stmt,'i',$id); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt); $msg='Lock updated.';
  }
  if($action==='publish'){
    $id=(int)$_POST['id']; $stmt=mysqli_prepare($db,'UPDATE exam_windows SET marks_published=1, marks_entry_locked=1 WHERE id=?'); mysqli_stmt_bind_param($stmt,'i',$id); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt); $msg='Results published/frozen.';
  }
}
$windows=mysqli_query($db,'SELECT * FROM exam_windows ORDER BY starts_on DESC');
$revisions=mysqli_query($db,'SELECT * FROM marks_revisions ORDER BY created_at DESC LIMIT 100');
include __DIR__.'/includes/header.php'; include __DIR__.'/includes/sidebar.php'; ?>
<main id="main" class="main"><div class="pagetitle"><h1>Exam Lifecycle</h1></div><?php if($msg):?><div class="alert alert-success"><?=e($msg)?></div><?php endif;?>
<div class="card"><div class="card-body"><h5 class="card-title">Create Exam Window</h5><form method="post" class="row g-2"><input type="hidden" name="action" value="create_window"><div class="col-md-3"><input class="form-control" name="exam_name" placeholder="Exam name" required></div><div class="col-md-2"><input type="date" class="form-control" name="starts_on" required></div><div class="col-md-2"><input type="date" class="form-control" name="ends_on" required></div><div class="col-md-2"><button class="btn btn-primary">Create</button></div></form></div></div>
<div class="card"><div class="card-body"><h5 class="card-title">Exam Windows</h5><table class="table table-sm"><thead><tr><th>Name</th><th>Dates</th><th>Entry Lock</th><th>Published</th><th>Actions</th></tr></thead><tbody><?php while($w=mysqli_fetch_assoc($windows)):?><tr><td><?=e($w['exam_name'])?></td><td><?=e($w['starts_on'].' to '.$w['ends_on'])?></td><td><?=e((string)$w['marks_entry_locked'])?></td><td><?=e((string)$w['marks_published'])?></td><td><form method="post" class="d-inline"><input type="hidden" name="id" value="<?=e((string)$w['id'])?>"><button class="btn btn-sm btn-warning" name="action" value="toggle_lock">Toggle Lock</button></form> <form method="post" class="d-inline"><input type="hidden" name="id" value="<?=e((string)$w['id'])?>"><button class="btn btn-sm btn-success" name="action" value="publish">Publish</button></form></td></tr><?php endwhile;?></tbody></table></div></div>
<div class="card"><div class="card-body"><h5 class="card-title">Marks Revision Log</h5><table class="table table-sm"><thead><tr><th>Student</th><th>Test</th><th>By</th><th>When</th></tr></thead><tbody><?php while($r=mysqli_fetch_assoc($revisions)):?><tr><td><?=e($r['student_id'])?></td><td><?=e($r['test_name'])?></td><td><?=e($r['changed_by'])?></td><td><?=e($r['created_at'])?></td></tr><?php endwhile;?></tbody></table></div></div>
</main><?php include __DIR__.'/includes/footer.php'; ?>
