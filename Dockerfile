FROM php:8.2-cli-alpine

LABEL maintainer="ZeroDay Ekibi"
LABEL description="MARTELLO Cevrimici Muzayede ve Ekspertiz Platformu"

# Calisma dizinini ayarla
WORKDIR /var/www/html

# Gerekli dosyalari kopyala
COPY . /var/www/html

# Storage izinlerini ayarla
RUN mkdir -p /var/www/html/storage/locks \
    && chmod -R 777 /var/www/html/storage

# Web sunucusu portunu ac
EXPOSE 8000

# Dahili PHP sunucusunu baslat
CMD ["php", "-S", "0.0.0.0:8000", "index.php"]
