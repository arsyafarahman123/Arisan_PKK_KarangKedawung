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
        CURLOPT_TIMEOUT => 120,
    ]);
    $result = curl_exec($ch);
    $error = curl_error($ch);
    fclose($fp);
    if ($result) {
        echo "OK: " . basename($localFile) . " -> $remotePath\n";
    } else {
        echo "FAIL: " . basename($localFile) . " -> $remotePath ($error)\n";
    }
}

function ftpDelete($remotePath) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "ftp://ftpupload.net",
        CURLOPT_USERPWD => "if0_43086073:XdRNePK250k1",
        CURLOPT_QUOTE => ["DELE $remotePath"],
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_NOBODY => true,
    ]);
    $result = curl_exec($ch);
    $error = curl_error($ch);
    if ($result) {
        echo "DELETED: $remotePath\n";
    } else {
        echo "Delete failed (may not exist): $remotePath\n";
    }
}

$base = 'd:/PKK_KarangKedawung';

echo "=== Fixing server ===\n\n";

// Step 1: Remove broken .htaccess
echo "[1] Removing broken .htaccess...\n";
ftpDelete("/htdocs/.htaccess");

// Step 2: Upload go.php
echo "\n[2] Uploading go.php...\n";
upload("$base/go.php", "/htdocs/go.php");

// Step 3: Upload simple index.html as fallback
$indexHtml = '<html><body><h1>Arisan PKK</h1><p>Setting up... <a href="/go.php">Click here to setup</a></p></body></html>';
file_put_contents("$base/index_temp.html", $indexHtml);
upload("$base/index_temp.html", "/htdocs/index.html");

echo "\n=== DONE ===\n";
echo "Now open: http://arisanku.rf.gd/go.php\n";
