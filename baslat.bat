@echo off
chcp 65001 >nul
echo ============================================================================
echo MARTELLO ACIK ARTIRMA SITESI - PHP OTOMATIK BASLATICI (ZeroDay Ekibi)
echo ============================================================================

where php >nul 2>nul
if %ERRORLEVEL% EQU 0 (
    echo [OK] Sistemde PATH uzerinde PHP bulundu.
    echo Sunucu http://localhost:8000 adresinde baslatiliyor...
    start http://localhost:8000
    php -S localhost:8000 index.php
    goto :eof
)

if exist "C:\wamp64\bin\php\php8.2.29\php.exe" (
    echo [OK] WAMP PHP 8.2 bulundu. Sunucu baslatiliyor...
    start http://localhost:8000
    "C:\wamp64\bin\php\php8.2.29\php.exe" -S localhost:8000 index.php
    goto :eof
)

if exist "C:\wamp64\bin\php\php8.3.28\php.exe" (
    echo [OK] WAMP PHP 8.3 bulundu. Sunucu baslatiliyor...
    start http://localhost:8000
    "C:\wamp64\bin\php\php8.3.28\php.exe" -S localhost:8000 index.php
    goto :eof
)

if exist "C:\xampp\php\php.exe" (
    echo [OK] XAMPP PHP bulundu. Sunucu baslatiliyor...
    start http://localhost:8000
    "C:\xampp\php\php.exe" -S localhost:8000 index.php
    goto :eof
)

echo [BILGI] PHP bulunamadi, tarayici uzerinden canli onizleme aciliyor...
start kütüphaneyi_indir_canlı.html
