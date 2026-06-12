FROM php:8.2-apache

# Habilita o mod_rewrite do Apache (importante para URLs e rotas)
RUN a2enmod rewrite

# Instala bibliotecas do sistema necessárias
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libzip-dev \
    libpq-dev \
    zip \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# pdo_mysql (local) | pdo_pgsql (Supabase) | gd | zip
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql pdo_pgsql mysqli zip

# Instala o Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Define a pasta raiz do servidor
WORKDIR /var/www/html

# Copia os arquivos do projeto
COPY . .

# Cria pasta de uploads e instala dependências do Composer
RUN mkdir -p uploads \
    && chmod -R 777 uploads \
    && composer install --no-interaction --prefer-dist --optimize-autoloader || true

# Permite .htaccess sobrescrever configurações do Apache
RUN sed -i 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Ajusta permissões
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/uploads

EXPOSE 80

COPY scripts/render-start.sh /usr/local/bin/render-start.sh
RUN chmod +x /usr/local/bin/render-start.sh

CMD ["/usr/local/bin/render-start.sh"]