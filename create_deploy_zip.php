<?php
/**
 * Create deployment ZIP for InfinityFree
 * Includes all necessary Laravel files
 */

set_time_limit(600);
$startTime = microtime(true);

$basePath = __DIR__;
$zipName = $basePath . '/deploy_full.zip';

// Delete existing zip
if (file_exists($zipName)) {
    unlink($zipName);
}

$zip = new ZipArchive();
if ($zip->open($zipName, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    die("Cannot create ZIP file\n");
}

// Directories to include
$includeDirs = ['app', 'bootstrap', 'config', 'database', 'public', 'resources', 'routes', 'storage', 'vendor'];

// Files to include from root
$includeFiles = [
    'artisan',
    'composer.json',
    'composer.lock',
    '.htaccess',
    '.env.production',
    'database_infinityfree.sql',
];

// Directories/files to skip
$skipPatterns = [
    '.git',
    'node_modules',
    '.phpunit.result.cache',
    'storage/logs/*.log',
    'storage/framework/cache/data',
    'storage/framework/sessions',
    'storage/framework/views',
];

function shouldSkip($path) {
    global $skipPatterns;
    $normalized = str_replace('\\', '/', $path);
    
    foreach ($skipPatterns as $pattern) {
        if (strpos($normalized, $pattern) !== false) {
            return true;
        }
    }
    return false;
}

$fileCount = 0;

// Add root files
foreach ($includeFiles as $file) {
    $fullPath = $basePath . '/' . $file;
    if (file_exists($fullPath)) {
        $zip->addFile($fullPath, $file);
        $fileCount++;
        echo "Added: $file\n";
    }
}

// Add directories recursively
foreach ($includeDirs as $dir) {
    $dirPath = $basePath . '/' . $dir;
    if (!is_dir($dirPath)) {
        echo "SKIP (not found): $dir\n";
        continue;
    }
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dirPath, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    $dirFileCount = 0;
    foreach ($iterator as $item) {
        $relativePath = $dir . '/' . $iterator->getSubPathname();
        $relativePath = str_replace('\\', '/', $relativePath);
        
        if (shouldSkip($relativePath)) {
            continue;
        }
        
        if ($item->isFile()) {
            $zip->addFile($item->getPathname(), $relativePath);
            $dirFileCount++;
            $fileCount++;
        } elseif ($item->isDir()) {
            $zip->addEmptyDir($relativePath);
        }
    }
    echo "Added: $dir/ ($dirFileCount files)\n";
}

// Ensure storage directories exist in ZIP
$storageDirs = [
    'storage/app/public',
    'storage/framework/cache',
    'storage/framework/sessions', 
    'storage/framework/views',
    'storage/logs',
];
foreach ($storageDirs as $sdir) {
    $zip->addEmptyDir($sdir);
    // Add .gitignore to keep directories
    $zip->addFromString($sdir . '/.gitignore', "*\n!.gitignore\n");
}

$zip->close();

$elapsed = round(microtime(true) - $startTime, 2);
$sizeMB = round(filesize($zipName) / 1024 / 1024, 2);

echo "\n========================================\n";
echo "ZIP created: deploy_full.zip\n";
echo "Files: $fileCount | Size: {$sizeMB} MB | Time: {$elapsed}s\n";
echo "========================================\n";
