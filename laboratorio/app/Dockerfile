FROM php:8.3-apache
# ping para la página de "diagnóstico de red" (inyección de comandos)
RUN apt-get update && apt-get install -y --no-install-recommends iputils-ping && rm -rf /var/lib/apt/lists/*
COPY . /var/www/html/
