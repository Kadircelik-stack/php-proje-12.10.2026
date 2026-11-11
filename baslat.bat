@echo off
chcp 65001 >nul
echo ============================================================================
echo MARTELLO ACIK ARTIRMA SITESI - PHP & XAMPP OTOMATIK BASLATICI (ZeroDay)
echo ============================================================================
where php >nul 2>nul
if %ERRORLEVEL% EQU 0 (
    echo [OK] Sistemde PHP bulundu. Yerlesik sunucu http://localhost:8000 adresinde baslatiliyor...
    start http://localhost:8000
    php -S localhost:8000 index.php
    goto :eof
)
if exist "C:\xampp\php\php.exe" (
    echo [OK] Windows XAMPP PHP bulundu. Sunucu http://localhost:8000 adresinde baslatiliyor...
    start http://localhost:8000
    "C:\xampp\php\php.exe" -S localhost:8000 index.php
    goto :eof
)
echo [BILGI] PHP bulunamadi, tarayici uzerinden canli onizleme aciliyor...
start kütüphaneyi_indir_canlı.html
