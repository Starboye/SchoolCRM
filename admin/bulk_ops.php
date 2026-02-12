<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_permission($db,'can_manage_users');
$pageTitle='Bulk Operations';
$msg='';
if($_SERVER['REQUEST_METHOD']==='POST' && isset($_FILES['csv'])){
  $type=$_POST['bulk_type'] ?? 'students';
  $tmp=$_FILES['csv']['tmp_name'];
  if(($h=fopen($tmp,'r'))){
    $count=0; $head=fgetcsv($h);
    while(($row=fgetcsv($h))!==false){
      if($type==='students' && count($row)>=4){
        [$id,$name,$std,$sec]=$row;
        $stmt=mysqli_prepare($db,'INSERT INTO student_info (id,name,standard,section) VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE name=VALUES(name),standard=VALUES(standard),section=VALUES(section)');
        mysqli_stmt_bind_param($stmt,'ssis',$id,$name,$std,$sec); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
        $count++;
      }
      if($type==='allocation' && count($row)>=4){
        [$tid,$sub,$std,$sec]=$row;
        $stmt=mysqli_prepare($db,'INSERT INTO teacher_subject_allocation (teacher_id,subject_name,standard,section) VALUES (?,?,?,?)');
        mysqli_stmt_bind_param($stmt,'ssis',$tid,$sub,$std,$sec); mysqli_stmt_execute($stmt); mysqli_stmt_close($stmt);
        $count++;
      }
    }
    fclose($h);
    log_audit($db,'bulk_ops','upload',$type,null,null,['count'=>$count]);
    $msg="Bulk import complete: $count rows";
  }
}
include __DIR__.'/includes/header.php'; include __DIR__.'/includes/sidebar.php'; ?>
<main id="main" class="main"><div class="pagetitle"><h1>Bulk Operations</h1></div><?php if($msg):?><div class="alert alert-success"><?=e($msg)?></div><?php endif;?>
<div class="card"><div class="card-body"><h5 class="card-title">CSV Upload</h5>
<form method="post" enctype="multipart/form-data" class="row g-2"><div class="col-md-3"><select class="form-control" name="bulk_type"><option value="students">Students (id,name,standard,section)</option><option value="allocation">Allocation (teacher_id,subject_name,standard,section)</option></select></div><div class="col-md-4"><input type="file" class="form-control" name="csv" required></div><div class="col-md-2"><button class="btn btn-primary">Upload</button></div></form>
</div></div></main><?php include __DIR__.'/includes/footer.php'; ?>
