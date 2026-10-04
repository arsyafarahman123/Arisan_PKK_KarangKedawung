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
        return true;
    } else {
        echo "FAIL: " . basename($localFile) . " -> $remotePath ($error)\n";
        return false;
    }
}

$base = 'd:/PKK_KarangKedawung';

// Check if deploy_full.zip exists
if (!file_exists("$base/deploy_full.zip")) {
    echo "Creating deploy_full.zip...\n";
    $zip = new ZipArchive();
    $zip->open("$base/deploy_full.zip", ZipArchive::CREATE | ZipArchive::OVERWRITE);
    
    $rootPath = realpath($base);
    $skip = ['.git', 'node_modules', '.env', 'deploy_full.zip', 'upload_', 'fix_server', 'create_deploy', 'export_sql', 'database.sqlite', 'index_temp', '.htaccess_root', 'go.php'];
    
    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($rootPath, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );
    
    $count = 0;
    foreach ($iterator as $file) {
        $filePath = $file->getRealPath();
        $relativePath = str_replace('\\', '/', substr($filePath, strlen($rootPath) + 1));
        
        $shouldSkip = false;
        foreach ($skip as $s) {
            if (strpos($relativePath, $s) === 0 || strpos(basename($relativePath), $s) === 0) {
                $shouldSkip = true;
                break;
            }
        }
        if ($shouldSkip) continue;
        
        if ($file->isDir()) {
            $zip->addEmptyDir($relativePath);
        } else {
            $zip->addFile($filePath, $relativePath);
            $count++;
        }
    }
    $zip->close();
    echo "ZIP created: $count files, " . number_format(filesize("$base/deploy_full.zip")) . " bytes\n";
}

echo "Uploading deploy_full.zip...\n";
upload("$base/deploy_full.zip", "/htdocs/deploy_full.zip");
echo "DONE!\n";
