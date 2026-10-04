<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('max_execution_time', 300);

$base = realpath('d:/PKK_KarangKedawung');
$skip = ['.git', 'node_modules', '.env', 'deploy_', 'upload_', 'fix_server', 'create_deploy', 'create_split', 'export_sql', 'database.sqlite', 'index_temp', '.htaccess_root', 'go.php', 'reupload_zip', 'database_infinityfree', 'make_part1', 'part1_files', 'test_single'];

$allFiles = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($base, RecursiveDirectoryIterator::SKIP_DOTS), RecursiveIteratorIterator::LEAVES_ONLY);
foreach ($it as $f) {
    if ($f->isDir()) continue;
    if ($f->isLink()) continue; // Skip symlinks!
    $realPath = $f->getRealPath();
    if ($realPath === false) continue; // Skip broken paths
    if (!is_file($realPath)) continue; // Must be actual file
    if (!is_readable($realPath)) continue; // Must be readable
    
    $rel = str_replace('\\', '/', substr($realPath, strlen($base) + 1));
    
    // Skip storage/app/public (it's a symlink target that causes issues)
    if (strpos($rel, 'storage/app/public') === 0) continue;
    
    $sk = false;
    foreach ($skip as $s) {
        if (strpos($rel, $s) === 0 || strpos(basename($rel), $s) === 0) { $sk = true; break; }
    }
    if ($sk) continue;
    
    $allFiles[] = ['real' => $realPath, 'rel' => $rel];
}

usort($allFiles, function($a, $b) { return strcmp($a['rel'], $b['rel']); });
echo "Total files: " . count($allFiles) . "\n";

// Split into 3 parts
$perPart = (int)ceil(count($allFiles) / 3);
$parts = array_chunk($allFiles, $perPart);

foreach ($parts as $i => $files) {
    $num = $i + 1;
    $zipPath = "$base/deploy_part{$num}.zip";
    @unlink($zipPath);
    
    $zip = new ZipArchive();
    if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
        echo "Cannot create part $num\n";
        continue;
    }
    
    foreach ($files as $f) {
        $zip->addFile($f['real'], $f['rel']);
    }
    
    if (!$zip->close()) {
        echo "Part $num: CLOSE FAILED\n";
        continue;
    }
    
    $size = filesize($zipPath);
    echo "Part $num: " . count($files) . " files, " . number_format($size) . " bytes (" . round($size/1024/1024, 1) . " MB)\n";
}

// Upload all parts
echo "\nUploading...\n";

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
        echo "  OK: " . basename($localFile) . " (" . number_format(filesize($localFile)) . " bytes)\n";
    } else {
        echo "  FAIL: " . basename($localFile) . " ($error)\n";
    }
}

for ($i = 1; $i <= count($parts); $i++) {
    $partFile = "$base/deploy_part{$i}.zip";
    if (file_exists($partFile)) {
        upload($partFile, "/htdocs/deploy_part{$i}.zip");
    }
}

// Upload go.php too
$goContent = '<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
ini_set("max_execution_time", 600);
ini_set("memory_limit", "256M");
echo "<html><body><h1>Deploying...</h1><pre>\n";

for ($i = 1; $i <= 5; $i++) {
    $z = __DIR__ . "/deploy_part{$i}.zip";
    if (!file_exists($z)) { if ($i == 1) echo "No parts found!\n"; break; }
    echo "Part $i: " . number_format(filesize($z)) . " bytes... ";
    $zip = new ZipArchive;
    if ($zip->open($z) === TRUE) {
        $n = $zip->numFiles;
        $zip->extractTo(__DIR__);
        $zip->close();
        unlink($z);
        echo "$n files OK\n";
    } else { echo "FAILED\n"; }
}

$ht = "<IfModule mod_rewrite.c>\n    RewriteEngine On\n    RewriteCond %{REQUEST_URI} !^/public/\n    RewriteRule ^(.*)$ public/\$1 [L]\n</IfModule>";
file_put_contents(__DIR__ . "/.htaccess", $ht);
echo ".htaccess OK\n";

if (!file_exists(__DIR__ . "/.env")) {
    file_put_contents(__DIR__ . "/.env", "APP_NAME=\"Arisan PKK\"\nAPP_ENV=production\nAPP_KEY=base64:xQ2IHXR3VuRwJKQulGQyx67yAsFbEZB1I4lOkJCnMHo=\nAPP_DEBUG=true\nAPP_URL=http://arisanku.rf.gd\n\nDB_CONNECTION=mysql\nDB_HOST=sql110.infinityfree.com\nDB_PORT=3306\nDB_DATABASE=if0_43086073_arisanku\nDB_USERNAME=if0_43086073\nDB_PASSWORD=XdRNePK250k1\n\nSESSION_DRIVER=file\nCACHE_STORE=file\nQUEUE_CONNECTION=sync\n");
    echo ".env OK\n";
}

foreach (["storage","storage/app","storage/app/public","storage/framework","storage/framework/sessions","storage/framework/views","storage/framework/cache","storage/framework/cache/data","storage/logs","bootstrap/cache"] as $d) {
    $p = __DIR__ . "/" . $d;
    if (!is_dir($p)) mkdir($p, 0755, true);
}
echo "Storage dirs OK\n";

$checks = ["artisan","public/index.php","vendor/autoload.php","bootstrap/app.php","config/app.php","routes/web.php"];
$ok = true;
foreach ($checks as $c) {
    $e = file_exists(__DIR__ . "/" . $c);
    echo ($e ? "OK" : "MISSING") . ": $c\n";
    if (!$e) $ok = false;
}

echo "\n" . ($ok ? "SUCCESS! Visit http://arisanku.rf.gd/" : "FAILED") . "\n";
echo "</pre></body></html>";
';

file_put_contents("$base/go.php", $goContent);
upload("$base/go.php", "/htdocs/go.php");

echo "\n=== ALL DONE ===\n";
echo "Open: http://arisanku.rf.gd/go.php\n";
