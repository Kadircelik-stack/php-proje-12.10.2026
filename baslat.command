#!/bin/bash
cd "$(dirname "$0")"
echo "=== MARTELLO ACIK ARTIRMA SITESI BASLATILIYOR (http://localhost:8000) ==="
php -S localhost:8000 index.php
