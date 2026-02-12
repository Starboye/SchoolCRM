<?php
require_once __DIR__ . '/includes/bootstrap.php';
$pageTitle='Approval Workflows';
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  require_permission($db,'can_manage_users');
  $id=(int)($_POST['id']??0); $action=$_POST['action']??''; $note=trim($_POST['note']??'');
  $status = $action==='approve' ? 'approved' : 'rejected';
  $stmt=mysqli_prepare($db,"UPDATE approval_requests SET status=?, reviewed_by=?, reviewed_at=NOW(), review_note=? WHERE id=? AND status='pending'");
  $by=(string)$_SESSION['id']; mysqli_stmt_bind_param($stmt,'sssi',$status,$by,$note,$id); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
  log_audit($db,'approval',$status,'approval_request',(string)$id,null,['note'=>$note]);
  $msg='Request updated.';
}
$list=mysqli_query($db,"SELECT * FROM approval_requests ORDER BY created_at DESC LIMIT 200");
include __DIR__.'/includes/header.php'; include __DIR__.'/includes/sidebar.php';
?>
<main id="main" class="main"><div class="pagetitle"><h1>Approvals</h1></div><?php if($msg):?><div class="alert alert-success"><?=e($msg)?></div><?php endif;?>
<div class="card"><div class="card-body"><h5 class="card-title">Requests</h5><table class="table table-sm table-striped"><thead><tr><th>ID</th><th>Module</th><th>Action</th><th>Entity</th><th>By</th><th>Status</th><th>Controls</th></tr></thead><tbody><?php while($r=mysqli_fetch_assoc($list)):?><tr><td><?=e((string)$r['id'])?></td><td><?=e($r['module'])?></td><td><?=e($r['action'])?></td><td><?=e($r['entity_type'].'#'.$r['entity_id'])?></td><td><?=e($r['requested_by'])?></td><td><?=e($r['status'])?></td><td><?php if($r['status']==='pending'):?><form method="post" class="d-flex gap-1"><input type="hidden" name="id" value="<?=e((string)$r['id'])?>"><input class="form-control form-control-sm" name="note" placeholder="note"><button class="btn btn-sm btn-success" name="action" value="approve">Approve</button><button class="btn btn-sm btn-danger" name="action" value="reject">Reject</button></form><?php endif;?></td></tr><?php endwhile;?></tbody></table></div></div></main><?php include __DIR__.'/includes/footer.php'; ?>
