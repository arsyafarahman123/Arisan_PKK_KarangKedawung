<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function upload($localFile, $remotePath) {
    $ch = curl_init();
    $fp = fopen($localFile, 'r');
    curl_setopt_array($ch, [
        CURLOPT_URL => "ftp://ftpupload.net" . $remotePath,
        CURLOPT_USERPWD => "if0_43086073:XdRNePK250k1",
        CURLOPT_UPLOAD => 1,
        CURLOPT_INFILE => $fp,
        CURLOPT_INFILESIZE => filesize($localFile),
        CURLOPT_FTP_CREATE_MISSING_DIRS => true,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_TIMEOUT => 600,
    ]);
    $result = curl_exec($ch);
    $error = curl_error($ch);
    fclose($fp);
    if ($result) {
        echo "OK: " . basename($localFile) . " -> $remotePath (" . number_format(filesize($localFile)) . " bytes)\n";
    } else {
        echo "FAIL: " . basename($localFile) . " -> $remotePath ($error)\n";
    }
}

$base = 'd:/PKK_KarangKedawung';

echo "=== Uploading files to InfinityFree ===\n\n";

echo "[1/4] setup_db.php...\n";
upload("$base/public/setup_db.php", "/htdocs/setup_db.php");
upload("$base/public/setup_db.php", "/htdocs/public/setup_db.php");

echo "\n[2/4] extract.php...\n";
upload("$base/public/extract.php", "/htdocs/extract.php");
upload("$base/public/extract.php", "/htdocs/public/extract.php");

echo "\n[3/4] deploy_full.zip...\n";
if (file_exists("$base/deploy_full.zip")) {
    upload("$base/deploy_full.zip", "/htdocs/deploy_full.zip");
} else {
    echo "deploy_full.zip NOT FOUND locally! Creating it first...\n";
    // Re-create the zip
    echo "Creating deployment ZIP...\n";
    $zipPath = "$base/deploy_full.zip";
    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        die("Cannot create ZIP\n");
    }
    
    $rootPath = realpath($base);
    $skip = ['.git', 'node_modules', 'vendor/bin', '.env', 'deploy_full.zip', 'upload_sql.php', 'upload_all.php', 'create_deploy_zip.php', 'database.sqlite', 'export_sql.php'];
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($rootPath, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    $count = 0;
    foreach ($iterator as $file) {
        $filePath = $file->getRealPath();
        $relativePath = substr($filePath, strlen($rootPath) + 1);
        $relativePathUnix = str_replace('\\', '/', $relativePath);
        
        // Skip unwanted
        $shouldSkip = false;
        foreach ($skip as $s) {
            if (strpos($relativePathUnix, $s) === 0) {
                $shouldSkip = true;
                break;
            }
        }
        if ($shouldSkip) continue;
        
        if ($file->isDir()) {
            $zip->addEmptyDir($relativePathUnix);
        } else {
            $zip->addFile($filePath, $relativePathUnix);
            $count++;
        }
    }
    
    $zip->close();
    echo "ZIP created with $count files (" . number_format(filesize($zipPath)) . " bytes)\n";
    upload($zipPath, "/htdocs/deploy_full.zip");
}

echo "\n[4/4] .htaccess & .env...\n";
// Create .htaccess for root
$htaccess = '<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>';
file_put_contents("$base/.htaccess_root", $htaccess);
upload("$base/.htaccess_root", "/htdocs/.htaccess");

// Upload .env
if (file_exists("$base/.env.production")) {
    upload("$base/.env.production", "/htdocs/.env");
} 

echo "\n=== ALL DONE ===\n";
