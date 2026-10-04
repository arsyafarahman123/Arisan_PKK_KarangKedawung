<?php

header('Content-Type: text/plain');
echo "=== InfinityFree Unzipper & Setup ===\n";

$zipFile = __DIR__ . '/deploy.zip';

if (!file_exists($zipFile)) {
    echo "ERROR: deploy.zip not found in " . __DIR__ . "\n";
    exit;
}

echo "Found deploy.zip (" . round(filesize($zipFile)/1024/1024, 2) . " MB). Extracting...\n";

$zip = new ZipArchive();
if ($zip->open($zipFile) === TRUE) {
    $zip->extractTo(__DIR__);
    $zip->close();
    echo "SUCCESS: Extraction completed!\n";
    
    // Copy .env if available
    if (file_exists(__DIR__ . '/.env.production') && !file_exists(__DIR__ . '/.env')) {
        copy(__DIR__ . '/.env.production', __DIR__ . '/.env');
        echo "Created .env configuration file.\n";
    }

    // Delete zip to save disk space
    unlink($zipFile);
    echo "Removed deploy.zip.\n";
    echo "Done! Website is ready to serve.\n";
} else {
    echo "ERROR: Failed to open zip archive.\n";
}
