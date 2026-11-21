using Microsoft.VisualStudio.TestTools.UnitTesting;

namespace Martello.NfrTests;

/// <summary>
/// MARTELLO SRS NFR Birim Testleri — Grup 5 (NFR21 - NFR25)
/// Kaynak: Rümeysa Şevval Kandilci (12/10/2026)
/// Visual Studio 2022 Test Explorer (Test Gezgini) Entegrasyonu
/// </summary>
[TestClass]
[TestCategory("Grup5_Arsiv_VipIzolasyon_AntiSniping_Proxy_Anonim")]
public sealed class NfrGroup5Tests
{
    public TestContext TestContext { get; set; } = null!;

    [TestMethod]
    [Description("NFR21: Sona Eren Müzayedelerin Saklanma ve Arşivlenme Politikası (Hot->Cold, Wish < 3sn Rapor)")]
    public void NFR21_ArsivlemePolitikasi_HotColdVeHizliRapor()
    {
        PhpUnitRunnerHelper.RunPhpUnitMethod(
            TestContext,
            @"tests\NfrGroup5Test.php",
            "testNfr21AuctionArchiveRetentionAndFastColdReport",
            "NFR21",
            "Sona Eren Müzayedelerin Saklanma ve Arşivlenme Politikası");
    }

    [TestMethod]
    [Description("NFR22: VIP Müzayede Kaynak İzolasyonu (Dedicated Cluster, %0 Çakışma, HPA <= 5sn)")]
    public void NFR22_VipKaynakIzolasyonu_SifirCakisma()
    {
        PhpUnitRunnerHelper.RunPhpUnitMethod(
            TestContext,
            @"tests\NfrGroup5Test.php",
            "testNfr22VipResourceIsolationZeroCollision",
            "NFR22",
            "VIP Müzayede Kaynak İzolasyonu");
    }

    [TestMethod]
    [Description("NFR23 (Çekirdek Problem): Son Dakika Sniping Önleme (+30sn/+30sn/+10sn, Must <= 50ms)")]
    public void NFR23_AntiSniping_KademeliSureUzatma_CekirdekProblem()
    {
        PhpUnitRunnerHelper.RunPhpUnitMethod(
            TestContext,
            @"tests\NfrGroup5Test.php",
            "testNfr23AntiSnipingSteppedExtensionCoreProblem",
            "NFR23 *",
            "Son Dakika Sniping Önleme ve Süre Uzatma Kuralı (Çekirdek Problem)");
    }

    [TestMethod]
    [Description("NFR24 (Çekirdek Problem): Otomatik Teklif / Proxy Bidding (Must <= 100ms, Tavan Koruma)")]
    public void NFR24_ProxyBidding_OtomatikTeklif_CekirdekProblem()
    {
        PhpUnitRunnerHelper.RunPhpUnitMethod(
            TestContext,
            @"tests\NfrGroup5Test.php",
            "testNfr24ProxyBiddingAutomaticCounterBidCoreProblem",
            "NFR24 *",
            "Otomatik Teklif Yönetim Sistemi — Proxy Bidding (Çekirdek Problem)");
    }

    [TestMethod]
    [Description("NFR25: Anonim Katılımcı Kimlik Gizleme (%100 Maskeleme, < 10ms, Admin İhtilaf RBAC)")]
    public void NFR25_AnonimKatilimci_KimlikGizlemeVeRbac()
    {
        PhpUnitRunnerHelper.RunPhpUnitMethod(
            TestContext,
            @"tests\NfrGroup5Test.php",
            "testNfr25AnonymousBidderMaskingAndAdminDisputeReveal",
            "NFR25",
            "Anonim Katılımcı Kimlik Gizleme Sistemi");
    }
}
