FROM php:8.2-apache

RUN docker-php-ext-install mysqli pdo_mysql

RUN a2enmod rewrite

RUN sed -i '/<Directory \/var\/www\/>/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/apache2/apache2.conf

RUN echo "PassEnv DB_HOST DB_PORT DB_USER DB_PASS DB_NAME APP_URL MIDTRANS_SERVER_KEY MIDTRANS_CLIENT_KEY MIDTRANS_IS_PRODUCTION RAJAONGKIR_API_KEY RAJAONGKIR_ORIGIN_CITY_ID CLOUDINARY_CLOUD_NAME CLOUDINARY_API_KEY CLOUDINARY_API_SECRET SMTP_HOST SMTP_PORT SMTP_USER SMTP_PASS GOOGLE_CLIENT_ID GOOGLE_CLIENT_SECRET GOOGLE_REDIRECT_URI PORT" >> /etc/apache2/apache2.conf

WORKDIR /var/www/html

COPY . /var/www/html/

EXPOSE 80

CMD ["sh", "-c", "php -r 'echo \"Extracting env vars...\\n\"; $fp = fopen(\"/var/www/html/.env\", \"w\"); foreach (getenv() as $k => $v) { if (preg_match(\"/^(APP|DB|MIDTRANS|RAJAONGKIR|CLOUDINARY|SMTP|GOOGLE|PORT)/\", $k)) { fwrite($fp, \"$k=\\\"\".addcslashes($v, \"\\\"\\\\\").\"\\\"\\n\"); } } fclose($fp);' && sed -i \"s/Listen 80/Listen ${PORT:-80}/g\" /etc/apache2/ports.conf && sed -i \"s/<VirtualHost \\*:80>/<VirtualHost *:${PORT:-80}>/g\" /etc/apache2/sites-available/000-default.conf && apache2-foreground"]
