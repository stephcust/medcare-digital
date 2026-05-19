# --- ESTÁGIO 1: Compilar o Frontend (Vue 3 + Vite) ---
FROM node:18-alpine AS build-stage
WORKDIR /app
COPY package*.json ./
RUN npm install
COPY . .
RUN npm run build

# --- ESTÁGIO 2: Servidor de Produção PHP ---
FROM php:8.2-alpine
WORKDIR /var/www/html

# Instalar extensões necessárias do sistema para PostgreSQL e manipulação de arquivos
RUN apk add --no-cache \
    postgresql-dev \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    git \
    curl

RUN docker-php-ext-install pdo_pgsql pdo_mysql gd bcmath

# Instalar o Composer oficialmente dentro do container
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copiar todo o projeto para o container
COPY . .

# Copiar os arquivos compilados do Vue do Estágio 1 para a pasta pública do Laravel
COPY --from=build-stage /app/public/build /var/www/html/public/build

# Instalar as dependências do PHP otimizadas para produção
RUN composer install --no-dev --optimize-autoloader

# Dar permissão para o Laravel conseguir escrever nos logs e cache em disco
RUN chmod -R 777 storage bootstrap/cache

# Expor a porta padrão que o Render vai escutar
EXPOSE 80

# Comando para iniciar o servidor embutido do Laravel mapeando a porta do SO do Render
CMD php artisan serve --host=0.0.0.0 --port=80
