# --- ESTÁGIO 1: Servidor PHP e Dependências ---
FROM php:8.2-alpine AS php-stage
WORKDIR /app

# Instalar pacotes nativos para compilar as extensões GD e ZIP exigidas pelas planilhas
RUN apk add --no-cache git unzip zip libpng-dev libzip-dev
RUN docker-php-ext-install gd zip

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

# Instalar dependências em tempo de execução para o PostgreSQL e arquivos
RUN apk add --no-cache \
    postgresql-dev \
    libpng-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip

RUN docker-php-ext-install pdo_pgsql gd bcmath zip

# Copiar o projeto finalizado com o backend e o frontend compilados
COPY --from=build-stage /app /var/www/html

# Dar permissão para as pastas que o SO do Laravel escreve em disco
RUN chmod -R 777 storage bootstrap/cache

EXPOSE 80
CMD php artisan serve --host=0.0.0.0 --port=80
