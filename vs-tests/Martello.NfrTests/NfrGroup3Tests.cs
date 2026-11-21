using Microsoft.VisualStudio.TestTools.UnitTesting;

namespace Martello.NfrTests;

/// <summary>
/// MARTELLO SRS NFR Birim Testleri — Grup 3 (NFR11 - NFR15)
/// Kaynak: Rümeysa Şevval Kandilci (12/10/2026)
/// Visual Studio 2022 Test Explorer (Test Gezgini) Entegrasyonu
/// </summary>
[TestClass]
[TestCategory("Grup3_Denetim_KVKK_Sozlesme_VIP")]
public sealed class NfrGroup3Tests
{
    public TestContext TestContext { get; set; } = null!;

    [TestMethod]
    [Description("NFR11: Denetim İzi, WORM Senkronizasyonu ve SHA-256 Değiştirilemezlik")]
    public void NFR11_DenetimIzi_WormVeHashZinciri()
    {
        PhpUnitRunnerHelper.RunPhpUnitMethod(
            TestContext,
            @"tests\NfrGroup3Test.php",
            "testNfr11AuditTrailWormAndHashChainIntegrity",
            "NFR11",
            "Denetim İzi ve İzlenebilirlik (WORM & SHA-256)");
    }

    [TestMethod]
    [Description("NFR12: KVKK / GDPR AES-256-GCM Sütun Şifreleme ve Unutulma Hakkı")]
    public void NFR12_KvkkGdpr_Aes256VeUnutulmaHakki()
    {
        PhpUnitRunnerHelper.RunPhpUnitMethod(
            TestContext,
            @"tests\NfrGroup3Test.php",
            "testNfr12KvkkGdprEncryptionAndRightToErasure",
            "NFR12",
            "Kişisel Verilerin Korunması ve Gizlilik Uyumluluğu (KVKK/GDPR)");
    }

    [TestMethod]
    [Description("NFR13: Sözleşme Sürüm Zorunluluğu ve İnkar Edilemezlik Hash Zinciri")]
    public void NFR13_SozlesmeUyumlulugu_NonRepudiation()
    {
        PhpUnitRunnerHelper.RunPhpUnitMethod(
            TestContext,
            @"tests\NfrGroup3Test.php",
            "testNfr13ContractComplianceVersionEnforcementAndNonRepudiation",
            "NFR13",
            "Sözleşme ve Şartlar Uyumluluğu (Sürüm & Hash Zinciri)");
    }

    [TestMethod]
    [Description("NFR14: VIP Müzayede Yetki ve Tier Kontrolü — In-Memory Cache < 3ms & %100 Doğruluk")]
    public void NFR14_VipYetkiVeTierKontrolu_InMemoryCache()
    {
        PhpUnitRunnerHelper.RunPhpUnitMethod(
            TestContext,
            @"tests\NfrGroup3Test.php",
            "testNfr14VipTierAccessControlAndInMemoryCache",
            "NFR14",
            "VIP Müzayede Yetki ve Tier Kontrolü");
    }

    [TestMethod]
    [Description("NFR15: VIP WAF, DDoS ve Bot Koruması — >= %99.95 Engel, < 5ms Filtreleme")]
    public void NFR15_VipWafDdosVeBotKorumasi()
    {
        PhpUnitRunnerHelper.RunPhpUnitMethod(
            TestContext,
            @"tests\NfrGroup3Test.php",
            "testNfr15VipWafDdosAndBotProtection",
            "NFR15",
            "VIP Müzayede WAF ve Bot/DDoS Koruması");
    }
}
