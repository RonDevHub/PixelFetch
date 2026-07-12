FROM alpine:3.18

# Systemabhängigkeiten, PHP 8.2 und Apache installieren
RUN apk add --no-cache \
    curl \
    apache2 \
    php82 \
    php82-apache2 \
    php82-curl \
    php82-json \
    php82-openssl \
    php82-mbstring \
    php82-phar \
    php82-dom \
    php82-xml \
    php82-xmlwriter \
    php82-ctype \
    && mkdir -p /run/apache2

# App-Ordner strukturieren
WORKDIR /var/www/html

# Dateien in den Container spiegeln
COPY . /var/www/html

# Apache-Konfiguration anpassen: DocumentRoot auf /public setzen und Module laden
RUN sed -i 's|"/var/www/localhost/htdocs"|"/var/www/html/public"|g' /etc/apache2/httpd.conf \
    && sed -i 's|AllowOverride None|AllowOverride All|g' /etc/apache2/httpd.conf \
    && sed -i 's|#LoadModule rewrite_module|LoadModule rewrite_module|g' /etc/apache2/httpd.conf

# Schreibrechte für den Lazy Cache an den Alpine Apache-User übergeben
RUN mkdir -p /var/www/html/storage && chown -R apache:apache /var/www/html/storage

EXPOSE 80

# Apache im Vordergrund starten
CMD ["/usr/sbin/httpd", "-D", "FOREGROUND"]