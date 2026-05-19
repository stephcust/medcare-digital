# --- ESTÁGIO 1: Servidor PHP e Dependências ---
FROM php:8.2-alpine AS php-stage
WORKDIR /app

# Instalar dependências para rodar o Composer e compilar a extensão GD exigida pelo Excel
RUN apk add --no-cache git unzip zip libpng-dev
RUN docker-php-ext-install gd

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar os arquivos de configuração do PHP e rodar o install
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader

# Copiar o resto do projeto e gerar o autoload otimizado
COPY . .
RUN composer dump-autoload --no-dev --optimize

# --- ESTÁGIO 2: Compilar o Frontend (Vue 3 + Vite) ---
FROM node:18-alpine AS build-stage
WORKDIR /app
COPY --from=php-stage /app /app
RUN npm install
RUN npm run build

# --- ESTÁGIO 3: Servidor de Produção Final (SO Limpo) ---
FROM php:8.2-alpine
WORKDIR /var/www/html

# Instalar extensões necessárias do sistema para PostgreSQL, GD e manipulação de arquivos
RUN apk add --no-cache \
    postgresql-dev \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip

RUN docker-php-ext-install pdo_pgsql gd bcmath

# Copiar o projeto finalizado com o backend e o frontend compilados
COPY --from=build-stage /app /var/www/html

# Dar permissão para as pastas que o SO do Laravel escreve em disco
RUN chmod -R 777 storage bootstrap/cache

EXPOSE 80
CMD php artisan serve --host=0.0.0.0 --port=80
