<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_permission($db,'can_delete_attendance');
$pageTitle='Attendance Governance'; $msg='';
$date=$_GET['date'] ?? date('Y-m-d');
if($_SERVER['REQUEST_METHOD']==='POST'){
  if(($_POST['action']??'')==='lock_day'){
    $reason=trim($_POST['lock_reason']??''); $by=(string)$_SESSION['id'];
    $stmt=mysqli_prepare($db,'REPLACE INTO attendance_day_lock (lock_date,is_locked,lock_reason,locked_by) VALUES (?,1,?,?)');
    mysqli_stmt_bind_param($stmt,'sss',$date,$reason,$by); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
    log_audit($db,'attendance_governance','lock_day','attendance_date',$date,null,['reason'=>$reason]);
    $msg='Attendance day locked.';
  }
}
$unmarked=mysqli_query($db,"SELECT tsa.standard, tsa.section, tsa.teacher_id
FROM teacher_subject_allocation tsa
LEFT JOIN attendance a ON a.date='".mysqli_real_escape_string($db,$date)."' AND a.teacher_id=tsa.teacher_id
GROUP BY tsa.standard, tsa.section, tsa.teacher_id HAVING COUNT(a.id)=0");
$completion=mysqli_query($db,"SELECT teacher_id, COUNT(*) cnt FROM attendance WHERE date='".mysqli_real_escape_string($db,$date)."' GROUP BY teacher_id ORDER BY cnt DESC");
$spike=mysqli_query($db,"SELECT date, SUM(CASE WHEN status=0 THEN 1 ELSE 0 END) absents, COUNT(*) total FROM attendance GROUP BY date ORDER BY absents DESC LIMIT 10");
$lock=mysqli_fetch_assoc(mysqli_query($db,"SELECT * FROM attendance_day_lock WHERE lock_date='".mysqli_real_escape_string($db,$date)."' LIMIT 1"));
include __DIR__.'/includes/header.php'; include __DIR__.'/includes/sidebar.php'; ?>
<main id="main" class="main"><div class="pagetitle"><h1>Attendance Governance</h1></div><?php if($msg):?><div class="alert alert-success"><?=e($msg)?></div><?php endif;?>
<div class="card"><div class="card-body"><h5 class="card-title">Day Control</h5><form method="get" class="row g-2"><div class="col-md-3"><input type="date" class="form-control" name="date" value="<?=e($date)?>"></div><div class="col-md-2"><button class="btn btn-primary">Load</button></div></form>
<form method="post" class="row g-2 mt-2"><input type="hidden" name="action" value="lock_day"><div class="col-md-5"><input class="form-control" name="lock_reason" placeholder="Lock reason"></div><div class="col-md-2"><button class="btn btn-warning">Lock Day</button></div></form>
<?php if($lock):?><p class="mt-2 text-danger">Locked by <?=e($lock['locked_by'])?>: <?=e($lock['lock_reason'])?></p><?php endif;?></div></div>
<div class="row"><div class="col-lg-4"><div class="card"><div class="card-body"><h5 class="card-title">Unmarked Classes</h5><ul><?php while($u=mysqli_fetch_assoc($unmarked)):?><li><?=e($u['teacher_id'].' - '.$u['standard'].$u['section'])?></li><?php endwhile;?></ul></div></div></div>
<div class="col-lg-4"><div class="card"><div class="card-body"><h5 class="card-title">Teacher Completion</h5><ul><?php while($c=mysqli_fetch_assoc($completion)):?><li><?=e($c['teacher_id'])?> : <?=e((string)$c['cnt'])?></li><?php endwhile;?></ul></div></div></div>
<div class="col-lg-4"><div class="card"><div class="card-body"><h5 class="card-title">Absence Spikes</h5><ul><?php while($s=mysqli_fetch_assoc($spike)):?><li><?=e($s['date'])?> - <?=e((string)$s['absents'])?>/<?=e((string)$s['total'])?></li><?php endwhile;?></ul></div></div></div></div>
</main><?php include __DIR__.'/includes/footer.php'; ?>
