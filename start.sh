#!/usr/bin/env bash
# ============================================================================
# MARTELLO AÇIK ARTIRMA VE EKSPERTİZ PLATFORMU — ÇALIŞTIRMA BETİĞİ (macOS & Linux)
# ============================================================================

set -e
DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$DIR"

echo "============================================================================"
echo " MARTELLO ACIK ARTIRMA VE EKSPERTIZ PLATFORMU (ZeroDay Ekibi)"
echo "============================================================================"

# Storage dizinini ve kilit klasorunu hazirla
mkdir -p storage/locks
chmod -R 777 storage 2>/dev/null || true

# PHP kontrolu
if command -v php >/dev/null 2>&1; then
    PHP_VER=$(php -r "echo PHP_VERSION;")
    echo "[OK] Sistemde PHP bulundu: v$PHP_VER"
    echo "Sunucu http://localhost:8000 adresinde baslatiliyor..."
    
    # Tarayicida otomatik ac
    if command -v xdg-open >/dev/null 2>&1; then
        xdg-open http://localhost:8000 &
    elif command -v open >/dev/null 2>&1; then
        open http://localhost:8000 &
    fi

    php -S 0.0.0.0:8000 index.php
else
    echo "[HATA] Sistemde PHP bulunamadi."
    echo ""
    echo "Lutfen terminalden PHP 8.2+ kurunuz:"
    echo "  - Ubuntu/Debian : sudo apt update && sudo apt install -y php-cli"
    echo "  - macOS (Homebrew): brew install php"
    echo ""
    echo "Veya Docker ile aninda baslatabilirsiniz:"
    echo "  docker compose up -d"
    echo ""
    exit 1
fi
