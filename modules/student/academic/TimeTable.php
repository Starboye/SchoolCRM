<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/portal.php';
require_once __DIR__ . '/../../includes/student_context.php';
require_once dirname(__DIR__, 3) . '/includes/timetable_helpers.php';
require_login(0);

extract(student_portal_context());

$conn = db_mysqli();
$studentStandard = (int)($row['standard'] ?? 0);
$studentSection  = (string)($row['section'] ?? '');
$academicYear    = tt_current_academic_year();
$dayLabels       = tt_day_labels();
$grid            = [];
$maxPeriod       = 0;
$workflowReady   = tt_tables_ready($conn);
$isPublished     = false;

if ($workflowReady && $studentStandard > 0 && $studentSection !== '') {
    $isPublished = tt_is_published($conn, $studentStandard, $studentSection, $academicYear);
    if ($isPublished) {
        $gridData = tt_grid_for_class($conn, $studentStandard, $studentSection, true, $academicYear);
        $grid = $gridData['grid'];
        $maxPeriod = $gridData['maxPeriod'];
    }
}

$pageTitle = 'Time Table';
student_layout_start($pageTitle);
?>

<main id="main" class="main">
  <div class="pagetitle">
    <h1>Time Table</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(app_url('studentDashboard.php')) ?>">Home</a></li>
        <li class="breadcrumb-item active">Time Table</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Class Timetable
          <?php if ($studentStandard > 0 && $studentSection !== ''): ?>
            — Class <?= e((string)$studentStandard) ?>-<?= e($studentSection) ?> (<?= e($academicYear) ?>)
          <?php endif; ?>
        </h5>

        <?php if (!$workflowReady): ?>
          <div class="alert alert-info mb-0">
            Timetable is not available online yet. Please contact your school office.
          </div>
        <?php elseif ($studentStandard <= 0 || $studentSection === ''): ?>
          <div class="alert alert-info mb-0">
            Your class is not set in your profile. Please contact the school office.
          </div>
        <?php elseif (!$isPublished): ?>
          <div class="alert alert-info mb-0">
            Your class timetable has not been published yet. Your class teacher will submit it for approval and it will appear here once the administrator approves it.
          </div>
        <?php elseif ($maxPeriod === 0): ?>
          <div class="alert alert-info mb-0">
            No periods have been scheduled for your class yet.
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>Period</th>
                  <?php foreach ($dayLabels as $label): ?>
                    <th><?= e($label) ?></th>
                  <?php endforeach; ?>
                </tr>
              </thead>
              <tbody>
                <?php for ($p = 1; $p <= $maxPeriod; $p++): ?>
                <tr>
                  <td><?= $p ?></td>
                  <?php for ($d = 1; $d <= 5; $d++): ?>
                    <td><?= e($grid[$p][$d] ?? '—') ?></td>
                  <?php endfor; ?>
                </tr>
                <?php endfor; ?>
              </tbody>
            </table>
          </div>
          <p class="small text-muted mb-0">Timetable is set by your class teacher and published by the school administration.</p>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>

<?php student_layout_end(); ?>
