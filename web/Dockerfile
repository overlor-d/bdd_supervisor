# Utilise une image PHP avec Apache
FROM php:8.1-apache

# Copie le code source dans le répertoire par défaut d'Apache
COPY src/ /var/www/html/

# (Optionnel) Active les modules PHP ou configure Apache si nécessaire
# RUN docker-php-ext-install mysqli pdo pdo_mysql

EXPOSE 80
