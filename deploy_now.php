<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 300);

$base = realpath('d:/PKK_KarangKedawung');
$skip = ['.git', 'node_modules', '.env', 'deploy_full', 'deploy_part', 'upload_', 'fix_server', 'create_deploy', 'export_sql', 'database.sqlite', 'index_temp', '.htaccess_root', 'go.php', 'reupload_zip', 'create_split', 'database_infinityfree'];

$allFiles = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::LEAVES_ONLY);
foreach ($it as $f) {
    if ($f->isDir()) continue;
    $rel = str_replace('\\', '/', substr($f->getRealPath(), strlen($base) + 1));
    $sk = false;
    foreach ($skip as $s) {
        if (strpos($rel, $s) === 0 || strpos(basename($rel), $s) === 0) { $sk = true; break; }
    }
    if ($sk) continue;
    $allFiles[] = ['real' => $f->getRealPath(), 'rel' => $rel, 'size' => $f->getSize()];
}

usort($allFiles, function($a, $b) { return strcmp($a['rel'], $b['rel']); });
echo "Total files: " . count($allFiles) . "\n";

// Split into 3 parts
$perPart = (int)ceil(count($allFiles) / 3);
$parts = array_chunk($allFiles, $perPart);

foreach ($parts as $i => $files) {
    $num = $i + 1;
    $zipPath = "$base/deploy_part{$num}.zip";
    if (file_exists($zipPath)) @unlink($zipPath);
    
    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        echo "Cannot create part $num\n";
        continue;
    }
    
    foreach ($files as $f) {
        $zip->addFile($f['real'], $f['rel']);
    }
    $zip->close();
    
    $size = filesize($zipPath);
    echo "Part $num: " . count($files) . " files, " . number_format($size) . " bytes (" . round($size/1024/1024, 1) . " MB)\n";
}

echo "\nNow uploading all parts + go.php...\n";

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
        CURLOPT_TIMEOUT => 300,
    ]);
    $result = curl_exec($ch);
    $error = curl_error($ch);
    fclose($fp);
    if ($result) {
        echo "  Uploaded: " . basename($localFile) . " (" . number_format(filesize($localFile)) . " bytes)\n";
        return true;
    } else {
        echo "  FAILED: " . basename($localFile) . " ($error)\n";
        return false;
    }
}

// Create updated go.php for multi-part extraction
$goContent = '<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set("max_execution_time", 600);
ini_set("memory_limit", "256M");
echo "<html><body><h1>Deploy</h1><pre>\n";

// Extract all parts
for ($i = 1; $i <= 5; $i++) {
    $zipPath = __DIR__ . "/deploy_part{$i}.zip";
    if (!file_exists($zipPath)) {
        echo "Part $i: not found (done)\n";
        break;
    }
    echo "Part $i: " . number_format(filesize($zipPath)) . " bytes... ";
    $zip = new ZipArchive;
    if ($zip->open($zipPath) === TRUE) {
        $zip->extractTo(__DIR__);
        $zip->close();
        echo $zip->numFiles . " files extracted\n";
        unlink($zipPath); // cleanup
    } else {
        echo "ERROR opening\n";
    }
}

// .htaccess in htdocs root (but we are IN htdocs, so put it here)
$ht = "<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_URI} !^/public/
    RewriteRule ^(.*)$ public/$1 [L]
</IfModule>";
file_put_contents(__DIR__ . "/.htaccess", $ht);
echo "\n.htaccess created\n";

// .env
if (!file_exists(__DIR__ . "/.env")) {
    $env = "APP_NAME=\"Arisan PKK\"\nAPP_ENV=production\nAPP_KEY=base64:xQ2IHXR3VuRwJKQulGQyx67yAsFbEZB1I4lOkJCnMHo=\nAPP_DEBUG=true\nAPP_URL=http://arisanku.rf.gd\n\nDB_CONNECTION=mysql\nDB_HOST=sql110.infinityfree.com\nDB_PORT=3306\nDB_DATABASE=if0_43086073_arisanku\nDB_USERNAME=if0_43086073\nDB_PASSWORD=XdRNePK250k1\n\nSESSION_DRIVER=file\nCACHE_STORE=file\nQUEUE_CONNECTION=sync\n";
    file_put_contents(__DIR__ . "/.env", $env);
    echo ".env created\n";
}

// Storage dirs
$dirs = ["storage","storage/app","storage/app/public","storage/framework","storage/framework/sessions","storage/framework/views","storage/framework/cache","storage/framework/cache/data","storage/logs","bootstrap/cache"];
foreach ($dirs as $d) {
    $p = __DIR__ . "/" . $d;
    if (!is_dir($p)) { mkdir($p, 0755, true); echo "Created: $d\n"; }
}

// Verify
echo "\nVerify:\n";
$checks = ["artisan","public/index.php","vendor/autoload.php","bootstrap/app.php","config/app.php","routes/web.php"];
$ok = true;
foreach ($checks as $c) {
    $exists = file_exists(__DIR__ . "/" . $c);
    echo ($exists ? "OK" : "MISSING") . ": $c\n";
    if (!$exists) $ok = false;
}

echo "\n" . ($ok ? "SUCCESS! Visit http://arisanku.rf.gd/" : "FAILED - files missing") . "\n";
echo "</pre></body></html>";
';

file_put_contents("$base/go.php", $goContent);

// Upload go.php
echo "\nUploading go.php...\n";
upload("$base/go.php", "/htdocs/go.php");

// Upload each part
for ($i = 1; $i <= 3; $i++) {
    $partFile = "$base/deploy_part{$i}.zip";
    if (file_exists($partFile)) {
        echo "\nUploading part $i...\n";
        upload($partFile, "/htdocs/deploy_part{$i}.zip");
    }
}

echo "\n=== ALL UPLOADED ===\n";
echo "Now visit: http://arisanku.rf.gd/go.php\n";
