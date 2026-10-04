<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

function ftpUpload($localFile, $remotePath) {
    $ch = curl_init();
    $fp = fopen($localFile, 'r');
    
    curl_setopt($ch, CURLOPT_URL, "ftp://ftpupload.net" . $remotePath);
    curl_setopt($ch, CURLOPT_USERPWD, "if0_43086073:XdRNePK250k1");
    curl_setopt($ch, CURLOPT_UPLOAD, 1);
    curl_setopt($ch, CURLOPT_INFILE, $fp);
    curl_setopt($ch, CURLOPT_INFILESIZE, filesize($localFile));
    curl_setopt($ch, CURLOPT_FTP_CREATE_MISSING_DIRS, true);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_TIMEOUT, 120);
    
    $result = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);
    fclose($fp);
    
    if ($result) {
        echo "OK: $localFile -> $remotePath\n";
        return true;
    } else {
        echo "FAIL: $localFile -> $remotePath ($error)\n";
        return false;
    }
}

$base = 'd:/PKK_KarangKedawung';

echo "Uploading SQL file...\n";
ftpUpload("$base/database_infinityfree.sql", "/htdocs/database_infinityfree.sql");
ftpUpload("$base/database_infinityfree.sql", "/htdocs/public/database_infinityfree.sql");

echo "\nUploading setup_db.php...\n";
ftpUpload("$base/public/setup_db.php", "/htdocs/setup_db.php");
ftpUpload("$base/public/setup_db.php", "/htdocs/public/setup_db.php");

echo "\nSELESAI!\n";
