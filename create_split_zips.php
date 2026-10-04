<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 600);
ini_set('memory_limit', '512M');

$base = realpath('d:/PKK_KarangKedawung');
$maxSize = 9 * 1024 * 1024; // 9MB per zip

echo "=== Creating split ZIPs ===\n\n";

// Collect all files
$skip = ['.git', 'node_modules', '.env', 'deploy_full.zip', 'deploy_part', 'upload_', 'fix_server', 'create_deploy', 'export_sql', 'database.sqlite', 'index_temp', '.htaccess_root', 'go.php', 'reupload_zip', 'upload_go', 'upload_sql', 'upload_all'];

$allFiles = [];
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($base, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::LEAVES_ONLY
);

foreach ($iterator as $file) {
    if ($file->isDir()) continue;
    $filePath = $file->getRealPath();
    $relativePath = str_replace('\\', '/', substr($filePath, strlen($base) + 1));
    
    $shouldSkip = false;
    foreach ($skip as $s) {
        if (strpos($relativePath, $s) === 0 || strpos(basename($relativePath), $s) === 0) {
            $shouldSkip = true;
            break;
        }
    }
    if ($shouldSkip) continue;
    
    $allFiles[] = [
        'real' => $filePath,
        'rel' => $relativePath,
        'size' => $file->getSize()
    ];
}

echo "Total files: " . count($allFiles) . "\n";

// Sort so vendor files are together
usort($allFiles, function($a, $b) {
    return strcmp($a['rel'], $b['rel']);
});

// Split into chunks
$parts = [];
$currentPart = [];
$currentSize = 0;
$partNum = 1;

foreach ($allFiles as $f) {
    // Estimate compressed size (rough: 40% of original for code files)
    $estSize = (int)($f['size'] * 0.4);
    
    if ($currentSize + $estSize > $maxSize && count($currentPart) > 0) {
        $parts[$partNum] = $currentPart;
        $partNum++;
        $currentPart = [];
        $currentSize = 0;
    }
    
    $currentPart[] = $f;
    $currentSize += $estSize;
}
if (count($currentPart) > 0) {
    $parts[$partNum] = $currentPart;
}

echo "Split into " . count($parts) . " parts\n\n";

// Create ZIPs
foreach ($parts as $num => $files) {
    $zipPath = "$base/deploy_part{$num}.zip";
    $zip = new ZipArchive();
    $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
    
    foreach ($files as $f) {
        $zip->addFile($f['real'], $f['rel']);
    }
    
    $zip->close();
    $size = filesize($zipPath);
    echo "Part $num: " . count($files) . " files, " . number_format($size) . " bytes (" . round($size/1024/1024, 1) . "MB)\n";
    
    if ($size > 10 * 1024 * 1024) {
        echo "  WARNING: Part $num is over 10MB! Need to split further.\n";
    }
}

echo "\nDone creating ZIPs!\n";
