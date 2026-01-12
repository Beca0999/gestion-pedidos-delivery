# Guía Detallada de Instalación 🛠️

Esta guía te ayudará a configurar **Running 🚀** paso a paso.

## Requisitos Previos
* PHP 8.1 o superior
* Composer
* Node.js & NPM
* SQLite (o MySQL si prefieres)

## Pasos Técnicos

### 1. Clonar y Preparar
```bash
git clone https://github.com/TU_USUARIO/gestion-pedidos-delivery.git
cd gestion-pedidos-delivery
composer install
npm install && npm run build
```

### 2. Variables de Entorno
Copia el archivo `.env`:
```bash
cp .env.example .env
php artisan key:generate
```

Configura tu Token de Mercado Pago:
```env
MP_ACCESS_TOKEN=tu_token_aqui
```

### 3. Base de Datos
```bash
touch database/database.sqlite
php artisan migrate --seed
```

### 4. Crear Usuario Administrador
Para entrar a `/admin`, ejecuta:
```bash
php artisan make:filament-user
```
