@echo off
chcp 65001 >nul
title MARTELLO Acik Artirma ve Ekspertiz Platformu
echo ============================================================================
echo  MARTELLO ACIK ARTIRMA VE EKSPERTIZ PLATFORMU (ZeroDay Ekibi)
echo  IEEE Std 830 / 5 Rol Calisma Alani / JSON Veri Deposu
echo ============================================================================
echo.

cd /d "%~dp0"

:: Gerekli dizinleri olustur
if not exist "storage\locks" mkdir "storage\locks"

:: 1. Sistem PATH uzerinde PHP kontrolu
where php >nul 2>nul
if %ERRORLEVEL% EQU 0 (
    echo [OK] Sistemde PATH uzerinde PHP bulundu.
    echo Sunucu baslatiliyor: http://localhost:8000
    start http://localhost:8000
    php -S localhost:8000 index.php
    pause
    goto :eof
)

:: 2. Yerel tasinabilir (portable) PHP kontrolu
if exist ".php-portable\php.exe" (
    echo [OK] Tasinabilir PHP bulundu: .php-portable\php.exe
    echo Sunucu baslatiliyor: http://localhost:8000
    start http://localhost:8000
    ".php-portable\php.exe" -S localhost:8000 index.php
    pause
    goto :eof
)

:: 3. WAMP PHP surumleri kontrolu
for /d %%D in ("C:\wamp64\bin\php\php8*") do (
    if exist "%%D\php.exe" (
        echo [OK] WAMP PHP bulundu: %%D\php.exe
        echo Sunucu baslatiliyor: http://localhost:8000
        start http://localhost:8000
        "%%D\php.exe" -S localhost:8000 index.php
        goto :eof
    )
)

:: 4. XAMPP PHP kontrolu
if exist "C:\xampp\php\php.exe" (
    echo [OK] XAMPP PHP bulundu: C:\xampp\php\php.exe
    echo Sunucu baslatiliyor: http://localhost:8000
    start http://localhost:8000
    "C:\xampp\php\php.exe" -S localhost:8000 index.php
    goto :eof
)

:: 5. Laragon PHP kontrolu
for /d %%D in ("C:\laragon\bin\php\php-8*") do (
    if exist "%%D\php.exe" (
        echo [OK] Laragon PHP bulundu: %%D\php.exe
        echo Sunucu baslatiliyor: http://localhost:8000
        start http://localhost:8000
        "%%D\php.exe" -S localhost:8000 index.php
        goto :eof
    )
)

:: 6. C:\php veya Program Files PHP kontrolu
if exist "C:\php\php.exe" (
    echo [OK] C:\php\php.exe bulundu.
    echo Sunucu baslatiliyor: http://localhost:8000
    start http://localhost:8000
    "C:\php\php.exe" -S localhost:8000 index.php
    goto :eof
)

:: 7. Eger hicbir yerde PHP bulunamadiysa, otomatik Tasinabilir (Portable) PHP indirme secenegi sun
echo [BILGI] Bilgisayarinizda kurulu bir PHP bulunamadi.
echo.
echo Dilerseniz tek tusla resmi PHP 8.2 Windows paketini otomatik olarak indirip
echo bu klasor icine kurabiliriz (Herhangi bir kurulum/ayar gerektirmez).
echo.
set /p INDIR="Tasinabilir PHP otomatik indirilsin mi? (E/H) [Varsayilan: E]: "
if /i "%INDIR%"=="H" goto :manual_info

echo.
echo [1/2] PHP 8.2 paketi indiriliyor (Lutfen bekleyiniz, yaklasik 25 MB)...
powershell -Command "[Net.ServicePointManager]::SecurityProtocol = [Net.SecurityProtocolType]::Tls12; $url = 'https://windows.php.net/downloads/releases/archives/php-8.2.14-nts-Win32-vs16-x64.zip'; $dest = 'php-temp.zip'; try { Invoke-WebRequest -Uri $url -OutFile $dest -UseBasicParsing; Write-Host '[OK] Indirme tamamlandi.' } catch { Write-Host '[UYARI] Ana sunucuya baglanilamadi, yedek kaynaktan deneniyor...'; Invoke-WebRequest -Uri 'https://windows.php.net/downloads/releases/php-8.2.26-nts-Win32-vs16-x64.zip' -OutFile $dest -UseBasicParsing }"

if not exist "php-temp.zip" (
    echo [HATA] PHP zip paketi indirilemedi. Lutfen internet baglantinizi kontrol edin.
    goto :manual_info
)

echo [2/2] Zip dosyasi cikartiliyor (.php-portable)...
powershell -Command "Expand-Archive -Path 'php-temp.zip' -DestinationPath '.php-portable' -Force; Remove-Item 'php-temp.zip' -Force"

if exist ".php-portable\php.exe" (
    echo [BASARILI] Tasinabilir PHP hazirlandi!
    echo Sunucu baslatiliyor: http://localhost:8000
    start http://localhost:8000
    ".php-portable\php.exe" -S localhost:8000 index.php
    goto :eof
)

:manual_info
echo.
echo ============================================================================
echo MANUEL BASLATMA SECENEKLERI:
echo ============================================================================
echo 1. XAMPP veya WAMP kurulu ise, projeyi 'htdocs' veya 'www' klasorune tasiyin.
echo 2. Veya 'https://windows.php.net/download' adresinden PHP 8.2 indirip kurun.
echo 3. Veya Docker ile su komutu calistirin: docker compose up -d
echo ============================================================================
pause
