<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_permission($db, 'can_manage_delegation');
$pageTitle = 'RBAC Permissions';
$msg='';

if ($_SERVER['REQUEST_METHOD']==='POST') {
  if (($_POST['action'] ?? '')==='assign_role') {
    $uid = trim($_POST['user_id'] ?? '');
    $rid = (int)($_POST['role_id'] ?? 0);
    $stmt = mysqli_prepare($db, 'INSERT IGNORE INTO user_roles (user_id, role_id, assigned_by) VALUES (?, ?, ?)');
    $by = (string)$_SESSION['id'];
    mysqli_stmt_bind_param($stmt, 'sis', $uid, $rid, $by);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    log_audit($db,'rbac','assign_role','user',$uid,null,['role_id'=>$rid]);
    $msg='Role assigned.';
  }
  if (($_POST['action'] ?? '')==='detach_role') {
    $uid = trim($_POST['user_id'] ?? '');
    $rid = (int)($_POST['role_id'] ?? 0);
    $stmt = mysqli_prepare($db, 'DELETE FROM user_roles WHERE user_id=? AND role_id=?');
    mysqli_stmt_bind_param($stmt, 'si', $uid, $rid);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    log_audit($db,'rbac','detach_role','user',$uid,['role_id'=>$rid],null);
    $msg='Role detached.';
  }
}

$roles = mysqli_query($db, 'SELECT * FROM roles ORDER BY role_name');
$users = mysqli_query($db, 'SELECT id,name,access FROM user_login ORDER BY name');
$map = mysqli_query($db, 'SELECT ur.user_id, r.role_name, ur.role_id FROM user_roles ur JOIN roles r ON r.role_id=ur.role_id ORDER BY ur.user_id');
include __DIR__.'/includes/header.php'; include __DIR__.'/includes/sidebar.php';
?>
<main id="main" class="main"><div class="pagetitle"><h1>RBAC</h1></div>
<?php if($msg):?><div class="alert alert-success"><?=e($msg)?></div><?php endif;?>
<div class="card"><div class="card-body"><h5 class="card-title">Assign Role</h5>
<form method="post" class="row g-2"><input type="hidden" name="action" value="assign_role">
<div class="col-md-4"><select class="form-control" name="user_id" required><option value="">User</option><?php while($u=mysqli_fetch_assoc($users)):?><option value="<?=e($u['id'])?>"><?=e($u['name'].' ('.$u['id'].')')?></option><?php endwhile;?></select></div>
<div class="col-md-4"><select class="form-control" name="role_id" required><option value="">Role</option><?php mysqli_data_seek($roles,0); while($r=mysqli_fetch_assoc($roles)):?><option value="<?=e((string)$r['role_id'])?>"><?=e($r['role_name'])?></option><?php endwhile;?></select></div>
<div class="col-md-2"><button class="btn btn-primary w-100">Assign</button></div></form></div></div>
<div class="card"><div class="card-body"><h5 class="card-title">Current Delegations</h5><table class="table table-sm"><thead><tr><th>User ID</th><th>Role</th><th>Action</th></tr></thead><tbody><?php while($m=mysqli_fetch_assoc($map)):?><tr><td><?=e($m['user_id'])?></td><td><?=e($m['role_name'])?></td><td><form method="post"><input type="hidden" name="action" value="detach_role"><input type="hidden" name="user_id" value="<?=e($m['user_id'])?>"><input type="hidden" name="role_id" value="<?=e((string)$m['role_id'])?>"><button class="btn btn-sm btn-outline-danger">Remove</button></form></td></tr><?php endwhile;?></tbody></table></div></div>
</main><?php include __DIR__.'/includes/footer.php'; ?>
