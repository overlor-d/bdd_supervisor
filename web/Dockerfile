# Utilise une image PHP avec Apache
FROM php:8.1-apache

# Active le module rewrite d'Apache
RUN a2enmod rewrite

# Installation de l'extension PDO MySQL
RUN docker-php-ext-install pdo_mysql

# Copie le code source dans le répertoire par défaut d'Apache
COPY src/ /var/www/html/

EXPOSE 80
