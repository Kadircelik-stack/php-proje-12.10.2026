using Microsoft.VisualStudio.TestTools.UnitTesting;

namespace Martello.NfrTests;

/// <summary>
/// MARTELLO SRS NFR Birim Testleri — Grup 4 (NFR16 - NFR20)
/// Kaynak: Rümeysa Şevval Kandilci (12/10/2026)
/// Visual Studio 2022 Test Explorer (Test Gezgini) Entegrasyonu
/// </summary>
[TestClass]
[TestCategory("Grup4_OWASP_ZeroTrust_MFA_Parola_Bellek")]
public sealed class NfrGroup4Tests
{
    public TestContext TestContext { get; set; } = null!;

    [TestMethod]
    [Description("NFR16: Web Uygulama Güvenliği ve OWASP Top 10 Koruması (0 Kritik/Yüksek Zafiyet)")]
    public void NFR16_OwaspTop10_XssSqliCsrfSsrfKorumasi()
    {
        PhpUnitRunnerHelper.RunPhpUnitMethod(
            TestContext,
            @"tests\NfrGroup4Test.php",
            "testNfr16OwaspTop10Mitigation",
            "NFR16",
            "Web Uygulama Güvenliği ve OWASP Top 10 Koruması");
    }

    [TestMethod]
    [Description("NFR17: Sıfır Güven (Zero Trust) ve Servisler Arası mTLS / TLS 1.3 Şifreleme (< 1ms)")]
    public void NFR17_SifirGuven_MtlsVeTls13Sifreleme()
    {
        PhpUnitRunnerHelper.RunPhpUnitMethod(
            TestContext,
            @"tests\NfrGroup4Test.php",
            "testNfr17ZeroTrustAndServiceToServiceTls13",
            "NFR17",
            "Sıfır Güven Mimarisi ve Servisler Arası Şifreleme (TLS 1.3 / mTLS)");
    }

    [TestMethod]
    [Description("NFR18: Yönetim Paneli Güvenliği — RBAC & Donanım Tabanlı FIDO2/WebAuthn MFA (<= 150ms)")]
    public void NFR18_YonetimPaneli_RbacVeDonanimMfaFido2()
    {
        PhpUnitRunnerHelper.RunPhpUnitMethod(
            TestContext,
            @"tests\NfrGroup4Test.php",
            "testNfr18AdminSecurityRbacAndHardwareMfa",
            "NFR18",
            "Yönetim Paneli Güvenliği ve Donanım Tabanlı MFA (FIDO2/WebAuthn)");
    }

    [TestMethod]
    [Description("NFR19: Parola Güvenliği — Argon2id/bcrypt Benzersiz Salt & %0 Düz Metin (< 100ms)")]
    public void NFR19_ParolaGuvenligi_Argon2idBcryptHash()
    {
        PhpUnitRunnerHelper.RunPhpUnitMethod(
            TestContext,
            @"tests\NfrGroup4Test.php",
            "testNfr19PasswordSecurityAndCryptographicHash",
            "NFR19",
            "Parola Güvenliği ve Kriptografik Hash");
    }

    [TestMethod]
    [Description("NFR20: İstemci Bellek Yönetimi — Timer/Listener Temizliği ve 12 Saatlik Kararlı Bellek")]
    public void NFR20_IstemciBellekYonetimi_12SaatSizintiOnleme()
    {
        PhpUnitRunnerHelper.RunPhpUnitMethod(
            TestContext,
            @"tests\NfrGroup4Test.php",
            "testNfr20ClientMemoryManagementLongRunningSession",
            "NFR20",
            "Ürün Odaklı Müzayede İstemci Bellek Yönetimi");
    }
}
