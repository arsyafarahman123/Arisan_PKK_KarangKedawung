# Automated Deploy Script for InfinityFree
$ftpUser = "if0_43086073"
$ftpPass = "XdRNePK250k1"
$ftpHost = "ftpupload.net"
$remoteBase = "ftp://${ftpHost}/htdocs"

Write-Host "Starting upload to InfinityFree ($remoteBase)..." -ForegroundColor Green

# Folders to upload
$folders = @("app", "bootstrap", "config", "database", "public", "resources", "routes", "storage", "vendor")
$rootFiles = @("artisan", "composer.json", ".htaccess", "database_infinityfree.sql")

# Upload Root Files
foreach ($file in $rootFiles) {
    if (Test-Path $file) {
        Write-Host "Uploading $file..." -ForegroundColor Cyan
        & curl.exe -s --user "${ftpUser}:${ftpPass}" --ftp-create-dirs -T "$file" "${remoteBase}/${file}"
    }
}

# Upload Folders
foreach ($folder in $folders) {
    if (Test-Path $folder) {
        Write-Host "Scanning $folder..." -ForegroundColor Yellow
        $items = Get-ChildItem -Path $folder -Recurse -File
        $count = $items.Count
        $i = 0
        foreach ($item in $items) {
            $i++
            $relPath = $item.FullName.Substring((Get-Location).Path.Length + 1).Replace("\", "/")
            if ($i % 50 -eq 0 -or $i -eq $count) {
                Write-Host "Uploading $folder ($i / $count): $relPath" -ForegroundColor Gray
            }
            & curl.exe -s --user "${ftpUser}:${ftpPass}" --ftp-create-dirs -T "$($item.FullName)" "${remoteBase}/${relPath}"
        }
    }
}

Write-Host "All files successfully uploaded to InfinityFree!" -ForegroundColor Green
