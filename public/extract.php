<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 600);

echo "<h2>Extracting deployment package...</h2><pre>";

// Check multiple locations for the zip
$zipPaths = [
    __DIR__ . '/deploy_full.zip',
    dirname(__DIR__) . '/deploy_full.zip',
    __DIR__ . '/../deploy_full.zip',
];

$zipFile = null;
foreach ($zipPaths as $p) {
    echo "Checking: $p ... ";
    if (file_exists($p)) {
        echo "FOUND! (" . number_format(filesize($p)) . " bytes)\n";
        $zipFile = $p;
        break;
    } else {
        echo "not found\n";
    }
}

if (!$zipFile) {
    echo "\n✗ deploy_full.zip not found! Upload it first via FTP.\n";
    echo "\nListing files in " . __DIR__ . ":\n";
    $files = scandir(__DIR__);
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        $path = __DIR__ . '/' . $f;
        $size = is_file($path) ? number_format(filesize($path)) . ' bytes' : 'DIR';
        echo "  $f ($size)\n";
    }
    echo "\nListing files in " . dirname(__DIR__) . ":\n";
    $files = scandir(dirname(__DIR__));
    foreach ($files as $f) {
        if ($f === '.' || $f === '..') continue;
        $path = dirname(__DIR__) . '/' . $f;
        $size = is_file($path) ? number_format(filesize($path)) . ' bytes' : 'DIR';
        echo "  $f ($size)\n";
    }
    echo "</pre>";
    exit;
}

$zip = new ZipArchive;
$res = $zip->open($zipFile);

if ($res === TRUE) {
    $extractTo = dirname(__DIR__); // htdocs root
    echo "Extracting to: $extractTo\n";
    echo "Files in archive: " . $zip->numFiles . "\n\n";
    $zip->extractTo($extractTo);
    $zip->close();
    echo "\n✓ Extraction complete!\n";
    echo "Total files extracted: " . $zip->numFiles . "\n";
} else {
    echo "✗ Failed to open ZIP (error code: $res)\n";
}

echo "</pre>";
echo "<p><a href='/setup_db.php'>Next: Setup Database →</a></p>";
echo "<p><a href='/'>Go to website</a></p>";
