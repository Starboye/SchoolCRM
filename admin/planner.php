<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_permission($db,'can_manage_planner');
$pageTitle='Timetable & Workload Planner'; $msg='';
if($_SERVER['REQUEST_METHOD']==='POST'){
  $teacher=$_POST['teacher_id']; $std=(int)$_POST['standard']; $sec=$_POST['section']; $sub=$_POST['subject_name']; $day=(int)$_POST['day_of_week']; $period=(int)$_POST['period_no'];
  $conf=mysqli_fetch_assoc(mysqli_query($db,"SELECT id FROM timetable_slots WHERE teacher_id='".mysqli_real_escape_string($db,$teacher)."' AND day_of_week=$day AND period_no=$period LIMIT 1"));
  if($conf){$msg='Conflict: teacher already allocated for this slot.';} else {
    $stmt=mysqli_prepare($db,'INSERT INTO timetable_slots (teacher_id,standard,section,subject_name,day_of_week,period_no,created_by) VALUES (?,?,?,?,?,?,?)');
    $by=(string)$_SESSION['id']; mysqli_stmt_bind_param($stmt,'sissiis',$teacher,$std,$sec,$sub,$day,$period,$by); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
    log_audit($db,'planner','create_slot','timetable_slot',null,null,['teacher'=>$teacher,'day'=>$day,'period'=>$period]);
    $msg='Slot added.';
  }
}
$teachers=mysqli_query($db,"SELECT id,name FROM user_login WHERE access=1 ORDER BY name");
$slots=mysqli_query($db,"SELECT teacher_id, day_of_week, period_no, standard, section, subject_name FROM timetable_slots ORDER BY day_of_week, period_no");
$load=mysqli_query($db,"SELECT teacher_id, COUNT(*) c FROM timetable_slots GROUP BY teacher_id ORDER BY c DESC");
include __DIR__.'/includes/header.php'; include __DIR__.'/includes/sidebar.php'; ?>
<main id="main" class="main"><div class="pagetitle"><h1>Planner</h1></div><?php if($msg):?><div class="alert alert-info"><?=e($msg)?></div><?php endif;?>
<div class="card"><div class="card-body"><h5 class="card-title">Add Slot</h5><form method="post" class="row g-2"><div class="col-md-3"><select class="form-control" name="teacher_id"><?php while($t=mysqli_fetch_assoc($teachers)):?><option value="<?=e($t['id'])?>"><?=e($t['name'])?></option><?php endwhile;?></select></div><div class="col-md-1"><input class="form-control" name="standard" placeholder="Std" required></div><div class="col-md-1"><input class="form-control" name="section" placeholder="Sec" required></div><div class="col-md-2"><input class="form-control" name="subject_name" placeholder="Subject" required></div><div class="col-md-1"><input class="form-control" name="day_of_week" placeholder="1-7" required></div><div class="col-md-1"><input class="form-control" name="period_no" placeholder="Period" required></div><div class="col-md-2"><button class="btn btn-primary">Add</button></div></form></div></div>
<div class="row"><div class="col-lg-8"><div class="card"><div class="card-body"><h5 class="card-title">Slots</h5><table class="table table-sm"><thead><tr><th>Teacher</th><th>Day</th><th>Period</th><th>Class</th><th>Subject</th></tr></thead><tbody><?php while($s=mysqli_fetch_assoc($slots)):?><tr><td><?=e($s['teacher_id'])?></td><td><?=e((string)$s['day_of_week'])?></td><td><?=e((string)$s['period_no'])?></td><td><?=e($s['standard'].'-'.$s['section'])?></td><td><?=e($s['subject_name'])?></td></tr><?php endwhile;?></tbody></table></div></div></div><div class="col-lg-4"><div class="card"><div class="card-body"><h5 class="card-title">Workload</h5><ul class="list-group"><?php while($l=mysqli_fetch_assoc($load)):?><li class="list-group-item d-flex justify-content-between"><span><?=e($l['teacher_id'])?></span><strong><?=e((string)$l['c'])?></strong></li><?php endwhile;?></ul></div></div></div></div>
</main><?php include __DIR__.'/includes/footer.php'; ?>
