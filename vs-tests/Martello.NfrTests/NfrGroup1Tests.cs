using Microsoft.VisualStudio.TestTools.UnitTesting;

namespace Martello.NfrTests;

/// <summary>
/// MARTELLO SRS NFR Birim Testleri — Grup 1 (NFR1 - NFR5)
/// Kaynak: Rümeysa Şevval Kandilci (12/10/2026)
/// Visual Studio 2022 Test Explorer (Test Gezgini) Entegrasyonu
/// </summary>
[TestClass]
[TestCategory("Grup1_Performans_Eszamanlilik_ACID")]
public sealed class NfrGroup1Tests
{
    public TestContext TestContext { get; set; } = null!;

    [TestMethod]
    [Description("NFR1: Teklif Gecikmesi — Must <= 300ms, Plan <= 200ms, Wish <= 100ms")]
    public void NFR01_TeklifGecikmesi_Must300msAlti()
    {
        PhpUnitRunnerHelper.RunPhpUnitMethod(
            TestContext,
            @"tests\NfrGroup1Test.php",
            "testNfr01BidLatencyWithinThresholds",
            "NFR1",
            "Teklif Gecikmesi (Bid Latency)");
    }

    [TestMethod]
    [Description("NFR2: Geri Sayım Senkronizasyonu — Must <= 150ms, Plan <= 50ms, Wish <= 10ms")]
    public void NFR02_GeriSayimSenkronizasyonu_Must150msAlti()
    {
        PhpUnitRunnerHelper.RunPhpUnitMethod(
            TestContext,
            @"tests\NfrGroup1Test.php",
            "testNfr02CountdownSynchronizationAcrossNetworks",
            "NFR2",
            "Geri Sayım Senkronizasyonu (Countdown Sync)");
    }

    [TestMethod]
    [Description("NFR3: Sayfa Yükleme Süresi — Must <= 2.0 sn, Plan <= 1.5 sn, Wish <= 1.0 sn")]
    public void NFR03_SayfaYuklemeSuresi_Must2SaniyeAlti()
    {
        PhpUnitRunnerHelper.RunPhpUnitMethod(
            TestContext,
            @"tests\NfrGroup1Test.php",
            "testNfr03PageLoadTimeWithinThresholds",
            "NFR3",
            "Sayfa Yükleme Süresi (Vitrin, Keşfet, Canlı Salon)");
    }

    [TestMethod]
    [Description("NFR4 (Çekirdek Problem *): Yarış Durumu ve Eşzamanlılık — %100 Bütünlük, Kilit < 20ms")]
    public void NFR04_YarisDurumuVeEszamanlilik_CekirdekProblem()
    {
        PhpUnitRunnerHelper.RunPhpUnitMethod(
            TestContext,
            @"tests\NfrGroup1Test.php",
            "testNfr04RaceConditionAndConcurrencyIntegrity",
            "NFR4 *",
            "Yarış Durumu Yönetimi ve Eşzamanlılık (Çekirdek Problem)");
    }

    [TestMethod]
    [Description("NFR5: Veritabanı Tutarlılığı (ACID) — %0 Tutarsızlık, Kilit+Onay <= 20ms")]
    public void NFR05_VeritabaniTutarliligi_AcidKapanis()
    {
        PhpUnitRunnerHelper.RunPhpUnitMethod(
            TestContext,
            @"tests\NfrGroup1Test.php",
            "testNfr05DatabaseConsistencyAndAcidCompliance",
            "NFR5",
            "Veritabanı Tutarlılığı (Atomicity, Consistency, Isolation, Durability)");
    }
}
