using Microsoft.VisualStudio.TestTools.UnitTesting;

namespace Martello.NfrTests;

/// <summary>
/// MARTELLO SRS NFR Birim Testleri — Grup 2 (NFR6 - NFR10)
/// Kaynak: Rümeysa Şevval Kandilci (12/10/2026)
/// Visual Studio 2022 Test Explorer (Test Gezgini) Entegrasyonu
/// </summary>
[TestClass]
[TestCategory("Grup2_Kapasite_Erisilebilirlik_Guvenlik")]
public sealed class NfrGroup2Tests
{
    public TestContext TestContext { get; set; } = null!;

    [TestMethod]
    [Description("NFR6: Katılımcı Kapasitesi 50.000 Sınırı — Must <= 200ms, Plan <= 100ms Kontenjan Dolu")]
    public void NFR06_KatilimciKapasitesi_50000Siniri()
    {
        PhpUnitRunnerHelper.RunPhpUnitMethod(
            TestContext,
            @"tests\NfrGroup2Test.php",
            "testNfr06ParticipantCapacityLimit50000",
            "NFR6",
            "Katılımcı Kapasitesi ve Eşzamanlı Erişim Sınırı (50.000)");
    }

    [TestMethod]
    [Description("NFR7: Kesintisiz Çalışma ve Yüksek Erişilebilirlik — Must >= %99.9, Plan >= %99.95, Wish >= %99.99")]
    public void NFR07_YuksekErisilebilirlik_HealthCheckVeSla()
    {
        PhpUnitRunnerHelper.RunPhpUnitMethod(
            TestContext,
            @"tests\NfrGroup2Test.php",
            "testNfr07HighAvailabilityAndHealthCheck",
            "NFR7",
            "Kesintisiz Çalışma ve Yüksek Erişilebilirlik");
    }

    [TestMethod]
    [Description("NFR8: Felaket Kurtarma — RPO = 0, Must RTO <= 30dk, Plan <= 15dk, Wish <= 5dk")]
    public void NFR08_FelaketKurtarma_SifirRpoVeFailover()
    {
        PhpUnitRunnerHelper.RunPhpUnitMethod(
            TestContext,
            @"tests\NfrGroup2Test.php",
            "testNfr08DisasterRecoveryZeroRpoAndRto",
            "NFR8",
            "Felaket Kurtarma ve Veri Kurtarma Hedefleri (RPO=0 / RTO)");
    }

    [TestMethod]
    [Description("NFR9: Ödeme Güvenliği — PCI-DSS Tokenizasyon, 0 Düz Metin Kart, 3D Secure & Wish < 1.5sn")]
    public void NFR09_OdemeGuvenligi_PciDssVe3DSecure()
    {
        PhpUnitRunnerHelper.RunPhpUnitMethod(
            TestContext,
            @"tests\NfrGroup2Test.php",
            "testNfr09PaymentSecurityPciDssAnd3DSecure",
            "NFR9",
            "Ödeme Güvenliği ve Finansal Veri Koruma (PCI-DSS / 3DS)");
    }

    [TestMethod]
    [Description("NFR10: Sahtecilik ve Bot Önleme — Must >= %99 Engel, < %0.5 FP, Plan CAPTCHA < 50ms")]
    public void NFR10_SahtecilikVeBotOnleme_WafVeDavranissalAnalitik()
    {
        PhpUnitRunnerHelper.RunPhpUnitMethod(
            TestContext,
            @"tests\NfrGroup2Test.php",
            "testNfr10AntiFraudBotPreventionAndLowFalsePositive",
            "NFR10",
            "Sahtecilik ve Bot Önleme (WAF / Rate Limit / CAPTCHA)");
    }
}
