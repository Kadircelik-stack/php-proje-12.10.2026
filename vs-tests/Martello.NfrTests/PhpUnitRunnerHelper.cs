using System.Diagnostics;
using System.Text;
using Microsoft.VisualStudio.TestTools.UnitTesting;

namespace Martello.NfrTests;

/// <summary>
/// Visual Studio 2022 Test Explorer üzerinden Martello PHP projesinin
/// resmi PHPUnit 10.5.67 birim testlerini çalıştıran ve çıktılarını
/// Standard Output paneline aktaran yardımcı sınıf.
/// </summary>
internal static class PhpUnitRunnerHelper
{
    private static string ResolveProjectRoot()
    {
        var dir = new DirectoryInfo(AppContext.BaseDirectory);
        while (dir is not null)
        {
            if (File.Exists(Path.Combine(dir.FullName, "phpunit.xml")) &&
                File.Exists(Path.Combine(dir.FullName, "tools", "phpunit.phar")))
            {
                return dir.FullName;
            }
            dir = dir.Parent;
        }

        throw new DirectoryNotFoundException(
            "Martello proje kök dizini (phpunit.xml / tools/phpunit.phar) bulunamadı.");
    }

    private static string ResolvePhpBinary(string projectRoot)
    {
        string[] candidates =
        [
            @"C:\php\php.exe",
            Path.Combine(projectRoot, ".php-portable", "php.exe"),
            "php"
        ];

        foreach (var candidate in candidates)
        {
            if (candidate == "php" || File.Exists(candidate))
            {
                return candidate;
            }
        }

        return "php";
    }

    public static void RunPhpUnitMethod(
        TestContext testContext,
        string phpTestFileRelativePath,
        string phpMethodName,
        string nfrId,
        string nfrTitle)
    {
        string projectRoot = ResolveProjectRoot();
        string phpExe = ResolvePhpBinary(projectRoot);
        string phpUnitPhar = Path.Combine(projectRoot, "tools", "phpunit.phar");
        string phpTestFile = Path.Combine(projectRoot, phpTestFileRelativePath);

        var psi = new ProcessStartInfo
        {
            FileName = phpExe,
            Arguments = $"\"{phpUnitPhar}\" --testdox --filter \"{phpMethodName}\" \"{phpTestFile}\"",
            WorkingDirectory = projectRoot,
            RedirectStandardOutput = true,
            RedirectStandardError = true,
            UseShellExecute = false,
            CreateNoWindow = true,
            StandardOutputEncoding = Encoding.UTF8,
            StandardErrorEncoding = Encoding.UTF8
        };

        using var proc = Process.Start(psi)
            ?? throw new InvalidOperationException("PHPUnit süreci başlatılamadı.");

        string stdout = proc.StandardOutput.ReadToEnd();
        string stderr = proc.StandardError.ReadToEnd();
        proc.WaitForExit();

        testContext.WriteLine($"======================================================================");
        testContext.WriteLine($"SRS Gereksinimi : {nfrId} - {nfrTitle}");
        testContext.WriteLine($"Test Motoru     : PHPUnit 10.5.67 ({phpTestFileRelativePath}::{phpMethodName})");
        testContext.WriteLine($"======================================================================");
        testContext.WriteLine(stdout.Trim());

        if (!string.IsNullOrWhiteSpace(stderr))
        {
            testContext.WriteLine("--- STDERR ---");
            testContext.WriteLine(stderr.Trim());
        }

        Assert.AreEqual(
            0,
            proc.ExitCode,
            $"{nfrId} PHPUnit testi başarısız oldu (ExitCode={proc.ExitCode}).\n{stdout}\n{stderr}");

        StringAssert.Contains(
            stdout,
            "OK (",
            $"{nfrId} için PHPUnit 'OK' doğrulaması alınamadı.\n{stdout}");
    }
}
