# 📦 API Zapatería

API RESTful desarrollada con **Laravel** y conectada a **PostgreSQL** en **Supabase**, diseñada para gestionar inventario, ventas y apartados de una zapatería.

---

## 🚀 Características

- Backend en **Laravel 10+**
- Base de datos **PostgreSQL** (hosteada en Supabase)
- Arquitectura RESTful
- Migraciones y seeders para carga inicial de datos
- Controladores y rutas optimizados para API
- Configuración lista para entornos **local** y **producción**

---

## 📋 Requisitos previos

Antes de instalar el proyecto asegúrate de tener:

- PHP >= 8.2
- Composer >= 2.x
- Node.js >= 18.x y npm >= 9.x
- PostgreSQL (o cuenta en [Supabase](https://supabase.com))
- Git

---

## ⚙️ Instalación

1. **Clonar el repositorio**
   ```bash
   git clone https://github.com/TU_USUARIO/api-zapateria.git
   cd api-zapateria

2. **Instalar dependencias**
   ```bash
   composer install (php)
   npm install (node)

3. **Configurar variables de entorno**
   - cp .env.example .env

4. **Edita las variables con tus credenciales**
   - DB_CONNECTION=pgsql
   - DB_HOST=xxxxxxxx.supabase.co
   - DB_PORT=5432
   - DB_DATABASE=nombre_base
   - DB_USERNAME=usuario
   - DB_PASSWORD=contraseña
  
5. **Genera la clave de la aplicacion**
   - php artisan key: generate
  
6. **Ejecuta las migraciones**
   - php artisan migrate
  
7. **Levanta el servidor**
   - php artisan serve
  
8. **Estructura de carpetas**
   api-zapateria/
    │── app/              # Controladores, modelos y lógica de negocio
    │── bootstrap/        # Carga inicial de Laravel
    │── config/           # Configuraciones del proyecto
    │── database/         # Migraciones y seeders
    │── public/           # Archivos públicos (index.php)
    │── resources/        # Vistas y assets
    │── routes/           # Definición de rutas
    │── storage/          # Logs, caché, sesiones
    │── tests/            # Pruebas automatizadas
    └── vendor/           # Dependencias de Composer

 9. **Comandos utiles**
     # Ejecutar migraciones y seeders
    php artisan migrate --seed

    # Limpiar caché de Laravel
    php artisan cache:clear

    # Ejecutar pruebas
    php artisan test

     
