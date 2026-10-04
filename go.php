<?php
error_reporting(E_ALL);
ini_set("display_errors",1);
ini_set("max_execution_time",600);
ini_set("memory_limit","256M");
echo "<html><body><h1>Deploying...</h1><pre>\n";
$found = 0;
for ($i=1; $i<=20; $i++) {
    $z = __DIR__."/deploy_part{$i}.zip";
    if (!file_exists($z)) break;
    $found++;
    echo "Part $i: ".number_format(filesize($z))." bytes... ";
    $zip = new ZipArchive;
    if ($zip->open($z)===TRUE) {
        $n=$zip->numFiles;
        $zip->extractTo(__DIR__);
        $zip->close();
        unlink($z);
        echo "$n files OK\n";
    } else { echo "FAILED\n"; }
}
if ($found==0) echo "No ZIP parts found!\n";

file_put_contents(__DIR__."/.htaccess","<IfModule mod_rewrite.c>\n    RewriteEngine On\n    RewriteCond %{REQUEST_URI} !^/public/\n    RewriteRule ^(.*)$ public/\$1 [L]\n</IfModule>");
echo ".htaccess OK\n";

if (!file_exists(__DIR__."/.env")) {
    file_put_contents(__DIR__."/.env","APP_NAME=\"Arisan PKK\"\nAPP_ENV=production\nAPP_KEY=base64:xQ2IHXR3VuRwJKQulGQyx67yAsFbEZB1I4lOkJCnMHo=\nAPP_DEBUG=true\nAPP_URL=http://arisanku.rf.gd\n\nDB_CONNECTION=mysql\nDB_HOST=sql110.infinityfree.com\nDB_PORT=3306\nDB_DATABASE=if0_43086073_arisanku\nDB_USERNAME=if0_43086073\nDB_PASSWORD=XdRNePK250k1\n\nSESSION_DRIVER=file\nCACHE_STORE=file\nQUEUE_CONNECTION=sync\n");
    echo ".env OK\n";
}

foreach(["storage","storage/app","storage/app/public","storage/framework","storage/framework/sessions","storage/framework/views","storage/framework/cache","storage/framework/cache/data","storage/logs","bootstrap/cache"] as $d) {
    $p=__DIR__."/".$d;
    if(!is_dir($p))mkdir($p,0755,true);
}
echo "Storage OK\n";

$ok=true;
foreach(["artisan","public/index.php","vendor/autoload.php","bootstrap/app.php","config/app.php","routes/web.php"] as $c) {
    $e=file_exists(__DIR__."/".$c);
    echo ($e?"OK":"MISSING").": $c\n";
    if(!$e)$ok=false;
}
echo "\n".($ok?"SUCCESS! Visit http://arisanku.rf.gd/":"FAILED")."\n";
echo "</pre></body></html>";
