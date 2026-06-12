#!/usr/bin/env bash
set -euo pipefail
PORT="${PORT:-80}"
sed -i "s/^Listen .*/Listen ${PORT}/" /etc/apache2/ports.conf
sed -i "s/<VirtualHost \*:80>/<VirtualHost *:${PORT}>/" /etc/apache2/sites-enabled/000-default.conf
mkdir -p /var/www/html/uploads && chmod -R 777 /var/www/html/uploads
exec apache2-foreground
