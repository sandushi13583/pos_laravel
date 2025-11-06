<?php
// Lightweight check script to run on Heroku dynos.
// Usage: php scripts/check_uploads.php

echo "Checking upload paths...\n";
function out($k, $v){ echo sprintf("% -30s %s\n", $k, $v); }

$out = [];
$out['is_writable public/uploads'] = is_writable('public/uploads') ? '1' : '0';
$out['is_writable public/uploads/img'] = is_writable('public/uploads/img') ? '1' : '0';
$out['public/uploads exists'] = file_exists('public/uploads') ? '1' : '0';
$out['public/uploads is_link'] = is_link('public/uploads') ? '1' : '0';
$out['public/uploads link_target'] = is_link('public/uploads') ? readlink('public/uploads') : 'n/a';
$out['storage/uploads exists'] = file_exists('storage/app/public/uploads') ? '1' : '0';

if (function_exists('posix_getuid')) {
    $out['uid'] = posix_getuid();
    $out['gid'] = posix_getgid();
} else {
    $out['uid'] = 'posix_unavailable';
    $out['gid'] = 'posix_unavailable';
}

foreach ($out as $k => $v) {
    out($k, $v);
}

// Attempt a quick write test (non-destructive)
$testFile = 'public/uploads/heroku_check_' . uniqid() . '.txt';
$w = @file_put_contents($testFile, "ok");
$outWrite = $w ? 'write_ok' : 'write_fail';
out('quick_write ' . $testFile, $outWrite);
if ($w) {
    @unlink($testFile);
}

echo "Done.\n";
