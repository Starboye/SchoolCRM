<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_permission($db,'can_manage_security');
$pageTitle='Security Controls'; $msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(($_POST['action']??'')==='policy'){
    foreach(['max_failed_attempts','session_timeout_minutes','password_min_length'] as $k){
      $v=trim($_POST[$k] ?? '');
      $stmt=mysqli_prepare($db,'REPLACE INTO security_policies (policy_key, policy_value) VALUES (?,?)'); mysqli_stmt_bind_param($stmt,'ss',$k,$v); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
    }
    $msg='Policies updated.';
  }
  if(($_POST['action']??'')==='force_reset'){
    $uid=trim($_POST['user_id'] ?? '');
    $stmt=mysqli_prepare($db,'REPLACE INTO user_security (user_id, force_password_reset, failed_attempts) VALUES (?,1,0)'); mysqli_stmt_bind_param($stmt,'s',$uid); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
    $msg='Forced password reset enabled.';
  }
}
$pol=[]; $rp=mysqli_query($db,'SELECT * FROM security_policies'); while($r=mysqli_fetch_assoc($rp)){$pol[$r['policy_key']]=$r['policy_value'];}
$logins=mysqli_query($db,'SELECT * FROM login_audit ORDER BY created_at DESC LIMIT 100');
$users=mysqli_query($db,'SELECT id,name FROM user_login ORDER BY name');
include __DIR__.'/includes/header.php'; include __DIR__.'/includes/sidebar.php'; ?>
<main id="main" class="main"><div class="pagetitle"><h1>Security Controls</h1></div><?php if($msg):?><div class="alert alert-success"><?=e($msg)?></div><?php endif;?>
<div class="card"><div class="card-body"><h5 class="card-title">Policies</h5><form method="post" class="row g-2"><input type="hidden" name="action" value="policy"><div class="col-md-3"><input class="form-control" name="max_failed_attempts" value="<?=e($pol['max_failed_attempts'] ?? '5')?>" placeholder="Max failed attempts"></div><div class="col-md-3"><input class="form-control" name="session_timeout_minutes" value="<?=e($pol['session_timeout_minutes'] ?? '60')?>" placeholder="Session timeout"></div><div class="col-md-3"><input class="form-control" name="password_min_length" value="<?=e($pol['password_min_length'] ?? '8')?>" placeholder="Password min len"></div><div class="col-md-2"><button class="btn btn-primary">Save</button></div></form></div></div>
<div class="card"><div class="card-body"><h5 class="card-title">Force Password Reset</h5><form method="post" class="row g-2"><input type="hidden" name="action" value="force_reset"><div class="col-md-4"><select class="form-control" name="user_id"><?php while($u=mysqli_fetch_assoc($users)):?><option value="<?=e($u['id'])?>"><?=e($u['name'].' ('.$u['id'].')')?></option><?php endwhile;?></select></div><div class="col-md-2"><button class="btn btn-warning">Force Reset</button></div></form></div></div>
<div class="card"><div class="card-body"><h5 class="card-title">Login History</h5><table class="table table-sm"><thead><tr><th>User</th><th>Status</th><th>IP</th><th>Time</th></tr></thead><tbody><?php while($l=mysqli_fetch_assoc($logins)):?><tr><td><?=e(($l['username'] ?? '').' / '.($l['user_id'] ?? '-'))?></td><td><?=e($l['status'])?></td><td><?=e($l['ip_address'])?></td><td><?=e($l['created_at'])?></td></tr><?php endwhile;?></tbody></table></div></div>
</main><?php include __DIR__.'/includes/footer.php'; ?>
