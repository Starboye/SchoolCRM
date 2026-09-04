<?php
declare(strict_types=1);

/**
 * Application bootstrap: env, branding, URLs, session, password helpers.
 */
if (defined('APP_BOOTSTRAPPED')) {
    return;
}
define('APP_BOOTSTRAPPED', true);

$projectRoot = dirname(__DIR__);

$envFile = $projectRoot . DIRECTORY_SEPARATOR . '.env';
if (is_readable($envFile)) {
    foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
        $line = trim($line);
        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }
        if (!str_contains($line, '=')) {
            continue;
        }
        [$k, $v] = explode('=', $line, 2);
        $k = trim($k);
        $v = trim($v, " \t\"'");
        if ($k !== '' && getenv($k) === false) {
            putenv("$k=$v");
            $_ENV[$k] = $v;
        }
    }
}

function env_str(string $key, string $default = ''): string {
    $v = getenv($key);
    return $v === false ? $default : (string)$v;
}

function detect_app_base_path(string $projectRoot): string {
    $root = str_replace('\\', '/', (string)(realpath($projectRoot) ?: $projectRoot));
    $docRootRaw = (string)($_SERVER['DOCUMENT_ROOT'] ?? '');
    $docRoot = str_replace('\\', '/', (string)(realpath($docRootRaw) ?: $docRootRaw));
    $docRoot = rtrim($docRoot, '/');

    if ($docRoot !== '' && $root !== '') {
        $prefixLen = strlen($docRoot);
        $rootPrefix = substr($root, 0, $prefixLen);
        $underDocRoot = PHP_OS_FAMILY === 'Windows'
            ? strcasecmp($rootPrefix, $docRoot) === 0
            : $rootPrefix === $docRoot;
        if ($underDocRoot && strlen($root) > $prefixLen) {
            return rtrim(substr($root, $prefixLen), '/');
        }
    }

    $script = str_replace('\\', '/', (string)($_SERVER['SCRIPT_NAME'] ?? ''));
    $folder = basename($root);
    if ($script !== '' && $folder !== '') {
        if (preg_match('#^(.*?/' . preg_quote($folder, '#') . ')(?:/|$)#i', $script, $m)) {
            return rtrim($m[1], '/');
        }
    }

    return '';
}

function env_int(string $key, int $default): int {
    $v = getenv($key);
    return $v === false || $v === '' ? $default : (int)$v;
}

$docRoot = str_replace('\\', '/', (string)($_SERVER['DOCUMENT_ROOT'] ?? ''));
$root = str_replace('\\', '/', $projectRoot);
$detectedBase = detect_app_base_path($projectRoot);

define('APP_NAME', env_str('APP_NAME', 'CampusToday'));
define('APP_INITIALS', env_str('APP_INITIALS', 'CT'));
define('BRAND_LOGO_PATH', env_str('BRAND_LOGO_PATH', 'assets/img/campustoday/icon-48.png'));
define('BRAND_FAVICON_PATH', env_str('BRAND_FAVICON_PATH', 'assets/img/campustoday/icon-32.png'));
define('BRAND_LOGO_WIDE_PATH', env_str('BRAND_LOGO_WIDE_PATH', 'assets/img/campustoday/full-nav.png'));
define('BRAND_LOGO_LOGIN_PATH', env_str('BRAND_LOGO_LOGIN_PATH', 'assets/img/campustoday/logo-login.png'));
define('BRAND_BOOT_ICON_PATH', env_str('BRAND_BOOT_ICON_PATH', 'assets/img/campustoday/icon-512.png'));
$configuredBase = env_str('APP_BASE_PATH', '');
define('APP_BASE_PATH', rtrim($configuredBase !== '' ? $configuredBase : $detectedBase, '/'));

define('DB_HOST', env_str('DB_HOST', '127.0.0.1'));
define('DB_NAME', env_str('DB_NAME', 'asimos'));
define('DB_USER', env_str('DB_USER', 'root'));
define('DB_PASS', env_str('DB_PASS', ''));

if (!function_exists('e')) {
    function e(string $value): string {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }
}

function app_url(string $path = ''): string {
    $base = APP_BASE_PATH;
    $path = ltrim($path, '/');
    if ($base === '') {
        return '/' . $path;
    }
    return $base . '/' . $path;
}

function app_session_start(): void {
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    $cookiePath = APP_BASE_PATH === '' ? '/' : APP_BASE_PATH;
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $cookiePath,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();

    $timeoutMin = env_int('SESSION_TIMEOUT_MINUTES', 60);
    if ($timeoutMin > 0 && isset($_SESSION['_last_activity'])) {
        if (time() - (int)$_SESSION['_last_activity'] > $timeoutMin * 60) {
            $_SESSION = [];
            if (ini_get('session.use_cookies')) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool)$params['secure'], $params['httponly']);
            }
            session_destroy();
            header('Location: ' . app_url('index.php'));
            exit;
        }
    }
    $_SESSION['_last_activity'] = time();
}

function password_is_hashed(string $stored): bool {
    return str_starts_with($stored, '$2y$')
        || str_starts_with($stored, '$2a$')
        || str_starts_with($stored, '$argon2');
}

function app_password_hash(string $plain): string {
    return password_hash($plain, PASSWORD_DEFAULT);
}

function app_password_verify(string $plain, string $stored): bool {
    if ($stored === '') {
        return false;
    }
    if (password_is_hashed($stored)) {
        return password_verify($plain, $stored);
    }
    return hash_equals($stored, $plain);
}

function establish_user_session(array $row): void {
    $access = (int)$row['access'];
    $userId = (string)$row['id'];
    // Main portal uses id; legacy student/ portal used user_id — set both during transition.
    $_SESSION['id'] = $userId;
    $_SESSION['user_id'] = $userId;
    $_SESSION['name'] = (string)($row['name'] ?? '');
    $_SESSION['access'] = $access;
}

function role_home(int $access): string {
    return match ($access) {
        2 => app_url('admin/dashboard.php'),
        1 => app_url('teacher/dashboard.php'),
        default => app_url('studentDashboard.php'),
    };
}

function logout_url(): string {
    return app_url('logout.php');
}

function app_logout(): void {
    app_session_start();
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], (bool)$params['secure'], $params['httponly']);
    }
    session_destroy();
    header('Location: ' . app_url('index.php'));
    exit;
}

function require_login(?int $access = null): void {
    app_session_start();
    if (!isset($_SESSION['id'], $_SESSION['name'], $_SESSION['access'])) {
        header('Location: ' . app_url('index.php'));
        exit;
    }
    if ($access !== null && (int)$_SESSION['access'] !== $access) {
        http_response_code(403);
        echo 'Forbidden.';
        exit;
    }
}

function brand_asset_exists(string $relativePath): bool {
    $relativePath = ltrim(str_replace('\\', '/', trim($relativePath)), '/');
    if ($relativePath === '') {
        return false;
    }
    $root = dirname(__DIR__);
    $fs = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    return is_file($fs);
}

function brand_asset_url(string $relativePath, string $fallbackPath = ''): string {
    if ($relativePath !== '' && brand_asset_exists($relativePath)) {
        return app_url($relativePath);
    }
    if ($fallbackPath !== '' && brand_asset_exists($fallbackPath)) {
        return app_url($fallbackPath);
    }
    return '';
}

function brand_head_tags(): void {
    $faviconPath = BRAND_FAVICON_PATH !== '' ? BRAND_FAVICON_PATH : BRAND_LOGO_PATH;
    $faviconUrl = brand_asset_url($faviconPath, 'assets/img/brand-mark.svg');
    if ($faviconUrl === '') {
        $faviconUrl = app_url('assets/img/brand-mark.svg');
    }
    echo '<meta name="app-base-path" content="' . e(APP_BASE_PATH) . '">' . "\n";
    echo '<link rel="icon" href="' . e($faviconUrl) . '">' . "\n";
    echo '<link rel="stylesheet" href="' . e(app_url('assets/css/brand.css')) . '">' . "\n";
}

function portal_vendor_styles(): void {
    echo '<link href="' . e(app_url('assets/vendor/bootstrap/css/bootstrap.min.css')) . '" rel="stylesheet">' . "\n";
    echo '<link href="' . e(app_url('assets/vendor/bootstrap-icons/bootstrap-icons.css')) . '" rel="stylesheet">' . "\n";
    echo '<link href="' . e(app_url('assets/vendor/boxicons/css/boxicons.min.css')) . '" rel="stylesheet">' . "\n";
    echo '<link href="' . e(app_url('assets/vendor/remixicon/remixicon.css')) . '" rel="stylesheet">' . "\n";
    echo '<link href="' . e(app_url('assets/vendor/simple-datatables/style.css')) . '" rel="stylesheet">' . "\n";
    echo '<link href="' . e(app_url('assets/css/style.css')) . '" rel="stylesheet">' . "\n";
    echo '<link rel="stylesheet" href="' . e(app_url('assets/css/portal.css')) . '">' . "\n";
}

function portal_vendor_scripts(bool $withCharts = false): void {
    if ($withCharts) {
        echo '<script src="' . e(app_url('assets/vendor/apexcharts/apexcharts.min.js')) . '"></script>' . "\n";
        echo '<script src="' . e(app_url('assets/vendor/chart.js/chart.umd.js')) . '"></script>' . "\n";
        echo '<script src="' . e(app_url('assets/vendor/echarts/echarts.min.js')) . '"></script>' . "\n";
    }
    echo '<script src="' . e(app_url('assets/vendor/bootstrap/js/bootstrap.bundle.min.js')) . '"></script>' . "\n";
    echo '<script src="' . e(app_url('assets/vendor/simple-datatables/simple-datatables.js')) . '"></script>' . "\n";
    echo '<script src="' . e(app_url('assets/js/main.js')) . '"></script>' . "\n";
}

function profile_avatar_html(string $name, string $class = 'profile-avatar'): string {
    $initial = strtoupper(substr(trim($name), 0, 1) ?: '?');
    return '<span class="' . e($class) . '" aria-hidden="true">' . e($initial) . '</span>';
}

/** Resolve stored profile path to a public URL; falls back to default student avatar. */
function profile_image_url(?string $storedPath): string {
    $default = 'assets/img/default-student.svg';
    $storedPath = trim((string)$storedPath);
    if ($storedPath === '') {
        return app_url($default);
    }
    if (str_starts_with($storedPath, './')) {
        $storedPath = substr($storedPath, 2);
    }
    $storedPath = ltrim(str_replace('\\', '/', $storedPath), '/');
    $root = dirname(__DIR__);
    $fs = $root . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $storedPath);
    if (is_file($fs)) {
        return app_url($storedPath);
    }
    return app_url($default);
}

function profile_image_html(string $name, ?string $storedPath, string $class = 'profile-avatar', int $size = 0): string {
    $url = profile_image_url($storedPath);
    $classAttr = e($class);
    $style = $size > 0 ? ' style="width:' . $size . 'px;height:' . $size . 'px;object-fit:cover;"' : '';
    return '<img src="' . e($url) . '" alt="' . e($name) . '" class="' . $classAttr . ' rounded-circle"' . $style . '>';
}

function page_title(string $title): string {
    return e($title) . ' - ' . e(APP_NAME);
}

function brand_logo(string $href, string $variant = 'header'): void {
    $loginUrl = brand_asset_url(BRAND_LOGO_LOGIN_PATH);
    $iconUrl = brand_asset_url(BRAND_LOGO_PATH);
    $wideUrl = brand_asset_url(BRAND_LOGO_WIDE_PATH);

    if ($variant === 'login' && $loginUrl !== '') {
        echo '<a href="' . e($href) . '" class="logo d-flex align-items-center justify-content-center app-brand app-brand-login">';
        echo '<img src="' . e($loginUrl) . '" alt="' . e(APP_NAME) . '">';
        echo '</a>';
        return;
    }

    echo '<a href="' . e($href) . '" class="logo d-flex align-items-center app-brand">';
    if ($wideUrl !== '') {
        echo '<span class="app-brand-mark d-lg-none" role="img" aria-label="' . e(APP_NAME) . '">';
        if ($iconUrl !== '') {
            echo '<img src="' . e($iconUrl) . '" alt="">';
        } else {
            echo '<span class="app-brand-placeholder">' . e(APP_INITIALS) . '</span>';
        }
        echo '</span>';
        echo '<span class="app-brand-wide d-none d-lg-block" role="img" aria-label="' . e(APP_NAME) . '">';
        echo '<img src="' . e($wideUrl) . '" alt="' . e(APP_NAME) . '">';
        echo '</span>';
    } else {
        echo '<span class="app-brand-mark" role="img" aria-label="' . e(APP_NAME) . '">';
        if ($iconUrl !== '') {
            echo '<img src="' . e($iconUrl) . '" alt="">';
        } else {
            echo '<span class="app-brand-placeholder">' . e(APP_INITIALS) . '</span>';
        }
        echo '</span>';
        echo '<span class="d-none d-lg-block app-brand-name">' . e(APP_NAME) . '</span>';
    }
    echo '</a>';
}

function profile_signout_item(): void {
    echo '<li><a class="dropdown-item d-flex align-items-center" href="' . e(logout_url()) . '">';
    echo '<i class="bi bi-box-arrow-right"></i><span>Sign Out</span></a></li>';
}

/** One-time skeleton loader shown on the first page after successful login. */
function portal_boot_screen(): void {
    app_session_start();
    if (empty($_SESSION['portal_boot'])) {
        return;
    }
    unset($_SESSION['portal_boot']);

    $logoUrl = brand_asset_url(BRAND_BOOT_ICON_PATH, BRAND_LOGO_PATH);
    if ($logoUrl === '') {
        return;
    }

    echo '<div id="portal-boot" class="portal-boot" aria-live="polite" aria-busy="true" role="status">';
    echo '<div class="portal-boot-backdrop"></div>';
    echo '<div class="portal-boot-content">';
    echo '<div class="portal-boot-logo-wrap">';
    echo '<img class="portal-boot-logo" src="' . e($logoUrl) . '" alt="' . e(APP_NAME) . '">';
    echo '</div>';
    echo '<p class="portal-boot-text">Loading your portal&hellip;</p>';
    echo '<div class="portal-boot-layout">';
    echo '<div class="portal-boot-sidebar">';
    for ($i = 0; $i < 6; $i++) {
        echo '<div class="portal-boot-line"></div>';
    }
    echo '</div>';
    echo '<div class="portal-boot-main">';
    echo '<div class="portal-boot-card portal-boot-card-lg"></div>';
    echo '<div class="portal-boot-row">';
    echo '<div class="portal-boot-card portal-boot-card-sm"></div>';
    echo '<div class="portal-boot-card portal-boot-card-sm"></div>';
    echo '<div class="portal-boot-card portal-boot-card-sm"></div>';
    echo '</div>';
    echo '<div class="portal-boot-card portal-boot-card-md"></div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '</div>';
    echo '<script>(function(){var b=document.getElementById("portal-boot");if(!b)return;function hide(){b.classList.add("portal-boot-hide");setTimeout(function(){b.remove();},450);}var t=Date.now();function done(){var w=Math.max(0,1000-(Date.now()-t));setTimeout(hide,w);}if(document.readyState==="complete")done();else window.addEventListener("load",done);})();</script>';
}
