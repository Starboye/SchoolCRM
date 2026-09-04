<?php
/** Batch-patch student module pages to shared layout */
$files = [
    ['path' => 'modules/communication/homework.php', 'title' => 'Homework', 'depth' => 2],
    ['path' => 'modules/communication/announcements.php', 'title' => 'Announcements', 'depth' => 2],
    ['path' => 'modules/student/academic/FeeDetails.php', 'title' => 'Fee Status', 'depth' => 3],
    ['path' => 'modules/student/academic/TimeTable.php', 'title' => 'Time Table', 'depth' => 3],
    ['path' => 'modules/student/academic/reportCard.php', 'title' => 'Report Card', 'depth' => 3],
    ['path' => 'modules/common/account/changePassword.php', 'title' => 'Change Password', 'depth' => 3],
];

$root = dirname(__DIR__);

foreach ($files as $meta) {
    $f = $root . '/' . $meta['path'];
    $c = file_get_contents($f);
    $depth = (int)$meta['depth'];
    $partials = "dirname(__DIR__, $depth) . '/partials'";

    // Inject context require after portal require if missing
    if (!str_contains($c, 'student_context.php')) {
        $c = preg_replace(
            '/(require_login\(0\);)/',
            "$1\n\nrequire_once __DIR__ . '/../includes/student_context.php';\nextract(student_portal_context());",
            $c,
            1
        );
        // fix depth for communication vs academic
        if (str_contains($meta['path'], 'student/academic') || str_contains($meta['path'], 'common/account')) {
            $c = str_replace("require_once __DIR__ . '/../includes/student_context.php';", "require_once __DIR__ . '/../../includes/student_context.php';", $c);
        }
    }

    $start = strpos($c, '<!DOCTYPE html>');
    $sidebarNeedle = "include dirname(__DIR__, $depth) . '/partials/sidebar.php'";
    $headerEnd = strpos($c, $sidebarNeedle);
    if ($start === false || $headerEnd === false) {
        echo "SKIP layout: {$meta['path']}\n";
        continue;
    }

    $title = $meta['title'];
    $insert = "<?php\n\$pageTitle = " . var_export($title, true) . ";\n"
        . "student_layout_start(\$pageTitle);\n?>\n";

    $c = substr($c, 0, $start) . $insert . substr($c, $headerEnd);

    // Remove duplicate sidebar include line
    $c = str_replace($sidebarNeedle . '; ?>', '', $c);
    $c = str_replace($sidebarNeedle . ";", '', $c);

    // Replace footer block
    if (preg_match('/\s*<footer id="footer"/', $c)) {
        $c = preg_replace('/\s*<footer id="footer"[\s\S]*$/', "\n<?php student_layout_end(); ?>\n", $c);
    } elseif (preg_match('/\s*<!-- Vendor JS Files -->/', $c)) {
        $c = preg_replace('/\s*<!-- Vendor JS Files -->[\s\S]*$/', "\n<?php student_layout_end(); ?>\n", $c);
    }

    file_put_contents($f, $c);
    echo "Patched {$meta['path']}\n";
}

echo "Done.\n";
