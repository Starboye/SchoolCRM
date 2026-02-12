<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_permission($db,'can_manage_notifications');
$pageTitle='Notification Control Center'; $msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $action=$_POST['action']??'';
  if($action==='template'){
    $name=trim($_POST['name']??''); $body=trim($_POST['body']??''); $by=(string)$_SESSION['id'];
    $stmt=mysqli_prepare($db,'INSERT INTO notification_templates (name,body,created_by) VALUES (?,?,?)'); mysqli_stmt_bind_param($stmt,'sss',$name,$body,$by); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt); $msg='Template saved.';
  }
  if($action==='schedule'){
    $templateId=(int)($_POST['template_id']??0); $targetType=$_POST['target_type']; $targetValue=trim($_POST['target_value']??''); $message=trim($_POST['message']??''); $at=$_POST['scheduled_at']; $by=(string)$_SESSION['id'];
    $stmt=mysqli_prepare($db,'INSERT INTO scheduled_notifications (template_id,target_type,target_value,message,scheduled_at,created_by) VALUES (?,?,?,?,?,?)');
    mysqli_stmt_bind_param($stmt,'isssss',$templateId,$targetType,$targetValue,$message,$at,$by); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt); $msg='Notification scheduled.';
  }
}
$templates=mysqli_query($db,'SELECT * FROM notification_templates ORDER BY created_at DESC');
$schedules=mysqli_query($db,'SELECT * FROM scheduled_notifications ORDER BY scheduled_at DESC LIMIT 100');
include __DIR__.'/includes/header.php'; include __DIR__.'/includes/sidebar.php'; ?>
<main id="main" class="main"><div class="pagetitle"><h1>Notification Center</h1></div><?php if($msg):?><div class="alert alert-success"><?=e($msg)?></div><?php endif;?>
<div class="row"><div class="col-lg-6"><div class="card"><div class="card-body"><h5 class="card-title">Templates</h5><form method="post" class="row g-2"><input type="hidden" name="action" value="template"><div class="col-12"><input class="form-control" name="name" placeholder="Template name" required></div><div class="col-12"><textarea class="form-control" name="body" rows="3" placeholder="Message" required></textarea></div><div class="col-4"><button class="btn btn-primary">Save</button></div></form><ul class="mt-3"><?php while($t=mysqli_fetch_assoc($templates)):?><li><strong><?=e($t['name'])?></strong>: <?=e($t['body'])?></li><?php endwhile;?></ul></div></div></div>
<div class="col-lg-6"><div class="card"><div class="card-body"><h5 class="card-title">Schedule Broadcast</h5><form method="post" class="row g-2"><input type="hidden" name="action" value="schedule"><div class="col-6"><input class="form-control" name="template_id" placeholder="Template ID (optional)"></div><div class="col-6"><input class="form-control" name="target_type" placeholder="student/teacher/class/all"></div><div class="col-12"><input class="form-control" name="target_value" placeholder="Target value"></div><div class="col-12"><textarea class="form-control" name="message" rows="2" placeholder="Message"></textarea></div><div class="col-8"><input type="datetime-local" class="form-control" name="scheduled_at" required></div><div class="col-4"><button class="btn btn-success">Schedule</button></div></form>
<table class="table table-sm mt-3"><thead><tr><th>ID</th><th>Target</th><th>When</th><th>Status</th></tr></thead><tbody><?php while($s=mysqli_fetch_assoc($schedules)):?><tr><td><?=e((string)$s['id'])?></td><td><?=e($s['target_type'].'/'.$s['target_value'])?></td><td><?=e($s['scheduled_at'])?></td><td><?=e($s['status'])?></td></tr><?php endwhile;?></tbody></table></div></div></div></div>
</main><?php include __DIR__.'/includes/footer.php'; ?>
