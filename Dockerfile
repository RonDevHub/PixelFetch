FROM alpine:3.19

# Systemabhängigkeiten installieren und curl für API-Abrufe absichern
RUN apk add --no-cache curl \
    && a2enmod rewrite 2>/dev/null || true

# App-Ordner strukturieren
WORKDIR /var/www/html

# Dateien in den Container spiegeln
COPY . /var/www/html

# Apache-Konfiguration anpassen: Webleitung zeigt ausschließlich auf /public
RUN sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/httpd.conf 2>/dev/null || \
    sed -i 's|/var/www/html|/var/www/html/public|g' /etc/apache2/ssl.conf 2>/dev/null || true

# Schreibrechte für den Lazy Cache zuweisen
RUN mkdir -p /var/www/html/storage && chown -R www-data:www-data /var/www/html/storage

EXPOSE 80