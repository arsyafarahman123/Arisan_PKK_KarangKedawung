<?php

$zip = new ZipArchive();
$zipFile = __DIR__ . '/deploy.zip';
if (file_exists($zipFile)) {
    unlink($zipFile);
}

if ($zip->open($zipFile, ZipArchive::CREATE) !== true) {
    echo "ERROR: Failed to create zip file\n";
    exit(1);
}

$folders = ['app', 'bootstrap', 'config', 'database', 'public', 'resources', 'routes', 'storage', 'vendor'];
$files = ['artisan', 'composer.json', '.htaccess', 'database_infinityfree.sql'];

foreach ($files as $f) {
    if (file_exists(__DIR__ . '/' . $f)) {
        $zip->addFile(__DIR__ . '/' . $f, $f);
    }
}
if (file_exists(__DIR__ . '/.env.production')) {
    $zip->addFile(__DIR__ . '/.env.production', '.env');
}

foreach ($folders as $dir) {
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator(__DIR__ . '/' . $dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::LEAVES_ONLY
    );
    foreach ($iterator as $file) {
        if (!$file->isDir()) {
            $filePath = $file->getRealPath();
            $relativePath = substr($filePath, strlen(__DIR__) + 1);
            $zip->addFile($filePath, str_replace('\\', '/', $relativePath));
        }
    }
}

$zip->close();
echo "SUCCESS: deploy.zip created successfully! Size: " . round(filesize($zipFile) / 1024 / 1024, 2) . " MB\n";
