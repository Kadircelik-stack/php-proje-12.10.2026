@echo off
chcp 65001 >nul
title Martello - PHPUnit 10.5.67 Unit Test Ortami
cd /d "%~dp0"
echo ================================================================================
echo  MARTELLO - RESMI PHPUnit 10.5.67 UNIT TEST ORTAMI
echo ================================================================================
echo.
C:\php\php.exe tools\phpunit.phar --testdox --colors=always --testdox-html tests\phpunit-report.html
echo.
echo ================================================================================
echo  Resmi PHPUnit HTML Raporu: tests\phpunit-report.html
echo  Tekrar calistirmak icin: C:\php\php.exe tools\phpunit.phar --testdox
echo ================================================================================
pause
