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

// Try to ensure public/uploads is a symlink to storage/app/public/uploads so it exists in the slug
$publicUploads = $base . 'public' . DIRECTORY_SEPARATOR . 'uploads';
$storageUploads = $base . 'storage' . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'uploads';

if (!file_exists($storageUploads)) {
    @mkdir($storageUploads, 0777, true);
}

// Create common upload subfolders used by the application
$subdirs = ['img', 'media', 'documents', 'business_logos', 'invoice_logos', 'UltimatePOS', 'temp'];
foreach ($subdirs as $sd) {
    $p = $storageUploads . DIRECTORY_SEPARATOR . $sd;
    if (!file_exists($p)) {
        @mkdir($p, 0777, true);
    }
    @chmod($p, 0777);
}

// If a non-symlink public/uploads exists, remove it so we can create a symlink
if (file_exists($publicUploads) && !is_link($publicUploads)) {
    // attempt recursive removal
    rrmdir_chmod($publicUploads, 0777);
    @rmdir($publicUploads);
}

if (!is_link($publicUploads)) {
    // Try native PHP symlink first (works on Unix during build)
    if (!@symlink($storageUploads, $publicUploads)) {
        // Fallback to executing ln -s (best-effort)
        @exec(sprintf('ln -sfn %s %s', escapeshellarg($storageUploads), escapeshellarg($publicUploads)));
    }
}

// Final chmod attempts
@chmod($storageUploads, 0777);
@chmod($publicUploads, 0777);

echo "Permission ensure script completed.\n";
exit(0);
