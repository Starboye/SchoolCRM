<?php
$f = __DIR__ . '/../modules/dashboards/studentDashboard.php';
$c = file_get_contents($f);

$start = strpos($c, '<!DOCTYPE html>');
$headerEnd = strpos($c, "<?php include dirname(__DIR__, 2) . '/partials/sidebar.php'; ?>");
if ($start === false || $headerEnd === false) {
    fwrite(STDERR, "Could not find layout markers\n");
    exit(1);
}

$before = substr($c, 0, $start);
$after = substr($c, $headerEnd);

$insert = "<?php\n\$pageTitle = 'Dashboard';\n\$withCharts = true;\n"
    . "include dirname(__DIR__, 2) . '/partials/student_head.php';\n"
    . "include dirname(__DIR__, 2) . '/partials/header.php';\n?>\n";

$fixed = $before . $insert . $after;

$footerStart = strpos($fixed, '  <!-- ======= Footer ======= -->');
if ($footerStart !== false) {
    $fixed = substr($fixed, 0, $footerStart) . "<?php include dirname(__DIR__, 2) . '/partials/student_footer.php'; ?>\n";
}

file_put_contents($f, $fixed);
echo "Patched studentDashboard.php\n";
