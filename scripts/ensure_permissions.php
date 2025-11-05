<?php
// Ensure required runtime directories exist and have writable permissions.
// This script is intended to run during composer post-install on Linux-based
// hosts (Heroku) to create storage and uploads directories and set permissions.

$base = __DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR;
$paths = [
    $base . 'storage',
    $base . 'storage' . DIRECTORY_SEPARATOR . 'app',
    $base . 'storage' . DIRECTORY_SEPARATOR . 'framework',
    $base . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'views',
    $base . 'storage' . DIRECTORY_SEPARATOR . 'framework' . DIRECTORY_SEPARATOR . 'cache',
    $base . 'storage' . DIRECTORY_SEPARATOR . 'logs',
    $base . 'bootstrap' . DIRECTORY_SEPARATOR . 'cache',
    $base . 'public' . DIRECTORY_SEPARATOR . 'uploads',
];

function rrmdir_chmod($dir, $mode = 0777)
{
    if (!is_dir($dir)) {
        return false;
    }
    $objects = scandir($dir);
    foreach ($objects as $object) {
        if ($object == '.' || $object == '..') continue;
        $path = $dir . DIRECTORY_SEPARATOR . $object;
        if (is_dir($path)) {
            rrmdir_chmod($path, $mode);
        } else {
            @chmod($path, $mode);
        }
    }
    @chmod($dir, $mode);
    return true;
}

foreach ($paths as $p) {
    if (!is_dir($p)) {
        if (!@mkdir($p, 0777, true)) {
            echo "Warning: Could not create dir: $p\n";
        } else {
            echo "Created: $p\n";
        }
    }
    // Try to set permissive permission
    if (!@chmod($p, 0777)) {
        // chmod may fail on some filesystems (e.g., Windows), so warn but continue
        echo "Warning: chmod failed for $p (this may be fine on some platforms)\n";
    }
    // Recursively chmod
    rrmdir_chmod($p, 0777);
}

// Ensure storage symlink for public (if intended) is created via artisan storage:link normally.

echo "Permission ensure script completed.\n";
exit(0);
