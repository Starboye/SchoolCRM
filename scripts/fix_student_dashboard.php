<?php
$f = __DIR__ . '/../modules/dashboards/studentDashboard.php';
$c = file_get_contents($f);
$pattern = '/(<\?php include dirname\(__DIR__, 2\) \. \'\/partials\/sidebar\.php\'; \?>)\s*<main id="main" class="main">.*?<!-- End Sidebar-->\s*(<main id="main" class="main">)/s';
$fixed = preg_replace($pattern, '$1' . "\n\n" . '$2', $c, 1, $count);
if ($count !== 1) {
    fwrite(STDERR, "Pattern match count: $count\n");
    exit(1);
}
file_put_contents($f, $fixed);
echo "Fixed studentDashboard.php\n";
