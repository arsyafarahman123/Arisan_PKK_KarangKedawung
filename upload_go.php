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

// Make sure ZIP exists
if (!file_exists("$base/deploy_full.zip")) {
    echo "Creating deploy_full.zip first...\n";
    exec("php $base/reupload_zip.php");
}

echo "=== Upload go.php + ZIP ===\n\n";

// Upload go.php
echo "[1/2] go.php...\n";
upload("$base/go.php", "/htdocs/go.php");

// Upload ZIP
echo "\n[2/2] deploy_full.zip (" . number_format(filesize("$base/deploy_full.zip")) . " bytes)...\n";
upload("$base/deploy_full.zip", "/htdocs/deploy_full.zip");

echo "\n=== DONE ===\n";
