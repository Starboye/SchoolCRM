<?php
require_once __DIR__ . '/includes/bootstrap.php';
require_permission($db,'can_view_analytics');
$pageTitle='Advanced Analytics';
$passRate=mysqli_fetch_assoc(mysqli_query($db,"SELECT ROUND(SUM(CASE WHEN (english+tamil+maths+science+social)>=175 THEN 1 ELSE 0 END)/NULLIF(COUNT(*),0)*100,2) pr FROM marks_new"));
$classRanks=mysqli_query($db,"SELECT s.standard,s.section, AVG(m.english+m.tamil+m.maths+m.science+m.social) avg_total FROM marks_new m JOIN student_info s ON s.id=m.id GROUP BY s.standard,s.section ORDER BY avg_total DESC");
$subjectHeat=mysqli_query($db,"SELECT AVG(english) eng, AVG(tamil) tam, AVG(maths) math, AVG(science) sci, AVG(social) soc FROM marks_new");
$risk=mysqli_query($db,"SELECT id, ROUND(SUM(CASE WHEN status=1 THEN 1 ELSE 0 END)/COUNT(*)*100,2) pct FROM attendance GROUP BY id HAVING pct < 75 ORDER BY pct ASC");
$monthly=mysqli_query($db,"SELECT DATE_FORMAT(date,'%Y-%m') ym, COUNT(*) recs, SUM(CASE WHEN status=0 THEN 1 ELSE 0 END) absents FROM attendance GROUP BY ym ORDER BY ym DESC LIMIT 12");
$heat=mysqli_fetch_assoc($subjectHeat);
include __DIR__.'/includes/header.php'; include __DIR__.'/includes/sidebar.php'; ?>
<main id="main" class="main"><div class="pagetitle"><h1>Advanced Analytics</h1></div>
<div class="row"><div class="col-lg-3"><div class="card"><div class="card-body"><h5 class="card-title">Pass %</h5><h3><?=e((string)($passRate['pr'] ?? '0'))?>%</h3></div></div></div>
<div class="col-lg-9"><div class="card"><div class="card-body"><h5 class="card-title">Subject Heatmap (Average)</h5><div class="row text-center"><div class="col">Eng<br><strong><?=e(number_format((float)($heat['eng']??0),1))?></strong></div><div class="col">Tam<br><strong><?=e(number_format((float)($heat['tam']??0),1))?></strong></div><div class="col">Math<br><strong><?=e(number_format((float)($heat['math']??0),1))?></strong></div><div class="col">Sci<br><strong><?=e(number_format((float)($heat['sci']??0),1))?></strong></div><div class="col">Soc<br><strong><?=e(number_format((float)($heat['soc']??0),1))?></strong></div></div></div></div></div></div>
<div class="row"><div class="col-lg-4"><div class="card"><div class="card-body"><h5 class="card-title">Class Rank Distribution</h5><ul><?php while($c=mysqli_fetch_assoc($classRanks)):?><li><?=e($c['standard'].$c['section'])?> - <?=e(number_format((float)$c['avg_total'],2))?></li><?php endwhile;?></ul></div></div></div>
<div class="col-lg-4"><div class="card"><div class="card-body"><h5 class="card-title">Attendance Risk Cohort</h5><ul><?php while($r=mysqli_fetch_assoc($risk)):?><li><?=e($r['id'])?> - <?=e((string)$r['pct'])?>%</li><?php endwhile;?></ul></div></div></div>
<div class="col-lg-4"><div class="card"><div class="card-body"><h5 class="card-title">Monthly Trend</h5><ul><?php while($m=mysqli_fetch_assoc($monthly)):?><li><?=e($m['ym'])?> : <?=e((string)$m['absents'])?> abs / <?=e((string)$m['recs'])?></li><?php endwhile;?></ul></div></div></div></div>
</main><?php include __DIR__.'/includes/footer.php'; ?>
