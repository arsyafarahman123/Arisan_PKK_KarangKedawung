<?php

echo "Connecting to ftpupload.net...\n";
$ftp = @ftp_connect('ftpupload.net', 21, 30);
if (!$ftp) {
    echo "ERROR: Failed to connect to ftpupload.net\n";
    exit(1);
}

echo "Logging in as if0_43086073...\n";
$login = @ftp_login($ftp, 'if0_43086073', 'XdRNePK250k1');
if (!$login) {
    echo "ERROR: FTP login failed. Please check credentials.\n";
    ftp_close($ftp);
    exit(1);
}

echo "SUCCESS: Connected and logged in!\n";
ftp_pasv($ftp, true);

$list = ftp_nlist($ftp, '.');
echo "Root directory contents: " . implode(', ', $list) . "\n";

if (in_array('htdocs', $list) || in_array('./htdocs', $list) || in_array('/htdocs', $list)) {
    echo "htdocs directory found!\n";
    $htdocsList = ftp_nlist($ftp, 'htdocs');
    echo "htdocs contents: " . implode(', ', (array)$htdocsList) . "\n";
}

ftp_close($ftp);
