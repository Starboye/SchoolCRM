<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/portal.php';
require_once __DIR__ . '/../../includes/student_context.php';
require_once dirname(__DIR__, 3) . '/includes/fee_helpers.php';
require_login(0);

extract(student_portal_context());

$conn = db_mysqli();
$studentStandard = (int)($row['standard'] ?? 0);
$studentSection  = (string)($row['section'] ?? '');
$academicYear    = fee_current_academic_year();
$feeRows         = [];

if (fee_tables_ready($conn) && $studentStandard > 0 && $studentSection !== '') {
    $feeRows = fee_rows_for_student($conn, $id, $studentStandard, $studentSection, $academicYear);
}

$pageTitle = 'Fee Status';
student_layout_start($pageTitle);
?>

<main id="main" class="main">
  <div class="pagetitle">
    <h1>Fee Status</h1>
    <nav>
      <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= e(app_url('studentDashboard.php')) ?>">Home</a></li>
        <li class="breadcrumb-item active">Fee Status</li>
      </ol>
    </nav>
  </div>

  <section class="section">
    <div class="card">
      <div class="card-body">
        <h5 class="card-title">Academic year <?= e($academicYear) ?>
          <?php if ($studentStandard > 0 && $studentSection !== ''): ?>
            — Class <?= e((string)$studentStandard) ?>-<?= e($studentSection) ?>
          <?php endif; ?>
        </h5>

        <?php if (!fee_tables_ready($conn)): ?>
          <div class="alert alert-info mb-0">
            Fee information is not available online yet. Please contact the school office.
          </div>
        <?php elseif (!$feeRows): ?>
          <div class="alert alert-info mb-0">
            No fee structure has been set for your class yet. Your administrator will publish fee details here when ready.
          </div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-bordered table-hover align-middle">
              <thead class="table-light">
                <tr>
                  <th>Term</th>
                  <th>Term Fee</th>
                  <th>Special Fee</th>
                  <th>Tuition Fee</th>
                  <th>Lab Fee</th>
                  <th>Total Fee</th>
                  <th>Status</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($feeRows as $fee): ?>
                  <?php
                    $status = (string)$fee['payment_status'];
                    $badge = fee_status_badge_class($status);
                  ?>
                  <tr>
                    <td><?= e((string)$fee['term_label']) ?></td>
                    <td><?= e(fee_format_amount($fee['term_fee'])) ?></td>
                    <td><?= e(fee_format_amount($fee['special_fee'])) ?></td>
                    <td><?= e(fee_format_amount($fee['tuition_fee'])) ?></td>
                    <td><?= e(fee_format_amount($fee['lab_fee'])) ?></td>
                    <td><strong><?= e(fee_format_amount($fee['total_fee'])) ?></strong></td>
                    <td>
                      <span class="badge bg-<?= e($badge) ?>"><?= e(ucfirst($status)) ?></span>
                      <?php if ($status === 'partial' && (float)$fee['amount_paid'] > 0): ?>
                        <span class="small text-muted d-block">Paid: <?= e(fee_format_amount($fee['amount_paid'])) ?></span>
                      <?php endif; ?>
                      <?php if (!empty($fee['paid_on'])): ?>
                        <span class="small text-muted d-block">On: <?= e((string)$fee['paid_on']) ?></span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <p class="small text-muted mb-0">Fee amounts are set by the school administration. For payment queries, contact the school office.</p>
        <?php endif; ?>
      </div>
    </div>
  </section>
</main>

<?php student_layout_end(); ?>
