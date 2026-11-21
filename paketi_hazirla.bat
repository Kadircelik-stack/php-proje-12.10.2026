@echo off
chcp 65001 >nul
title MARTELLO Proje Paketleyici (Export ZIP)
echo ============================================================================
echo  MARTELLO PROJE PAKETLEYICI (Baska Bilgisayara Tasima Icin ZIP Olusturucu)
echo ============================================================================
echo.

cd /d "%~dp0"

echo [1/2] Proje dosyalari hazirlaniyor (.git ve tests haric tutuluyor)...
set OUTPUT_ZIP=%USERPROFILE%\Desktop\MARTELLO_PROJE_PAKETI.zip
if exist "MARTELLO_PROJE_PAKETI.zip" del /f /q "MARTELLO_PROJE_PAKETI.zip"

powershell -NoProfile -Command "
$source = Get-Location;
$zipName = 'MARTELLO_PROJE_PAKETI.zip';
$tempDir = Join-Path $env:TEMP ('martello_export_' + [System.Guid]::NewGuid().ToString());

Write-Host 'Gecici aktarma dizini:' $tempDir;
New-Item -ItemType Directory -Path $tempDir -Force | Out-Null;

# Dosyalari kopyala (.git, tests, zip dosyalari haric)
Get-ChildItem -Path $source -Recurse | Where-Object {
    $_.FullName -notmatch '\\\.git($|\\)' -and
    $_.FullName -notmatch '\\tests($|\\)' -and
    $_.FullName -notmatch '\\\.php-portable($|\\)' -and
    $_.Name -notmatch '\.zip$'
} | ForEach-Object {
    $relPath = $_.FullName.Substring($source.Path.Length + 1);
    $destPath = Join-Path $tempDir $relPath;
    if ($_.PSIsContainer) {
        New-Item -ItemType Directory -Path $destPath -Force | Out-Null;
    } else {
        $parent = Split-Path $destPath -Parent;
        if (-not (Test-Path $parent)) { New-Item -ItemType Directory -Path $parent -Force | Out-Null }
        Copy-Item -Path $_.FullName -Destination $destPath -Force;
    }
};

Write-Host '[2/2] ZIP arsivi sikistiriliyor: MARTELLO_PROJE_PAKETI.zip';
Compress-Archive -Path (Join-Path $tempDir '*') -DestinationPath (Join-Path $source $zipName) -Force;
Remove-Item -Path $tempDir -Recurse -Force;

Write-Host '';
Write-Host '============================================================================' -ForegroundColor Green;
Write-Host ' [BASARILI] MARTELLO_PROJE_PAKETI.zip basariyla olusturuldu!' -ForegroundColor Green;
Write-Host (' Konum: ' + (Join-Path $source $zipName)) -ForegroundColor Cyan;
Write-Host ' Bu ZIP dosyasini USB bellege atabilir veya diger bilgisayara gonderebilirsiniz.' -ForegroundColor Yellow;
Write-Host ' Diger bilgisayarda ZIPten cikarip baslat.bat dosyasina cift tiklamaniz yeterlidir.' -ForegroundColor Yellow;
Write-Host '============================================================================' -ForegroundColor Green;
"

pause
