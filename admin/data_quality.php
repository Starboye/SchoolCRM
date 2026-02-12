<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_permission($db,'can_manage_data_quality');
$pageTitle='Data Quality Controls'; $msg='';
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='run_checks'){
  mysqli_query($db,'DELETE FROM data_quality_issues WHERE status="open"');
  $dupPhone = mysqli_query($db,"SELECT phone, GROUP_CONCAT(id) ids, COUNT(*) c FROM student_info WHERE phone IS NOT NULL AND phone<>'' GROUP BY phone HAVING c>1");
  while($d=mysqli_fetch_assoc($dupPhone)){
    $stmt=mysqli_prepare($db,'INSERT INTO data_quality_issues (issue_type,entity_type,entity_id,issue_details) VALUES (?,?,?,?)');
    $it='duplicate_phone'; $et='student_info'; $eid=$d['ids']; $det='Phone '.$d['phone'].' appears '.$d['c'].' times';
    mysqli_stmt_bind_param($stmt,'ssss',$it,$et,$eid,$det); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
  }
  $missing = mysqli_query($db,"SELECT id FROM student_info WHERE emailID IS NULL OR emailID='' OR fatherPhone IS NULL OR fatherPhone=''");
  while($m=mysqli_fetch_assoc($missing)){
    $stmt=mysqli_prepare($db,'INSERT INTO data_quality_issues (issue_type,entity_type,entity_id,issue_details) VALUES (?,?,?,?)');
    $it='missing_parent_or_email'; $et='student_info'; $eid=$m['id']; $det='Missing email or father phone';
    mysqli_stmt_bind_param($stmt,'ssss',$it,$et,$eid,$det); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
  }
  log_audit($db,'data_quality','run_checks','dataset',null,null,['ran'=>true]);
  $msg='Data quality checks completed.';
}
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='resolve'){
  $id=(int)$_POST['id']; $by=(string)$_SESSION['id'];
  $stmt=mysqli_prepare($db,'UPDATE data_quality_issues SET status="resolved", resolved_at=NOW(), resolved_by=? WHERE id=?'); mysqli_stmt_bind_param($stmt,'si',$by,$id); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
  $msg='Issue resolved.';
}
$issues=mysqli_query($db,'SELECT * FROM data_quality_issues ORDER BY status ASC, created_at DESC LIMIT 300');
include __DIR__.'/includes/header.php'; include __DIR__.'/includes/sidebar.php'; ?>
<main id="main" class="main"><div class="pagetitle"><h1>Data Quality</h1></div><?php if($msg):?><div class="alert alert-success"><?=e($msg)?></div><?php endif;?>
<div class="card"><div class="card-body"><h5 class="card-title">Validation Queue</h5><form method="post"><input type="hidden" name="action" value="run_checks"><button class="btn btn-primary">Run Checks</button></form>
<table class="table table-sm mt-3"><thead><tr><th>ID</th><th>Type</th><th>Entity</th><th>Details</th><th>Status</th><th>Action</th></tr></thead><tbody><?php while($i=mysqli_fetch_assoc($issues)):?><tr><td><?=e((string)$i['id'])?></td><td><?=e($i['issue_type'])?></td><td><?=e($i['entity_type'].'#'.$i['entity_id'])?></td><td><?=e($i['issue_details'])?></td><td><?=e($i['status'])?></td><td><?php if($i['status']==='open'):?><form method="post"><input type="hidden" name="action" value="resolve"><input type="hidden" name="id" value="<?=e((string)$i['id'])?>"><button class="btn btn-sm btn-success">Resolve</button></form><?php endif;?></td></tr><?php endwhile;?></tbody></table></div></div>
</main><?php include __DIR__.'/includes/footer.php'; ?>
