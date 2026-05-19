# Sistema Web de Gestión de Archivos y Digitalización Documental

## Tecnologías
- PHP 8.0
- Laravel 11
- MySQL / MariaDB
- Laravel Sanctum (autenticación)

## Requisitos
- XAMPP (Apache + MySQL)
- PHP 8.0
- Composer

## Instalación
1. Clonar el repositorio
git clone https://github.com/AldairRojas/Sistema_Archivos_MDJLO.git

2. Instalar dependencias
composer install

3. Copiar el archivo de configuración
copy .env.example .env
    
4. Generar la clave de la aplicación
php artisan key:generate

5. Configurar la base de datos en el archivo .env
DB_CONNECTION=mariadb
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=archivos
DB_USERNAME=root
DB_PASSWORD=

6. Importar la base de datos
Importar el archivo archivos.sql en phpMyAdmin

7. Ejecutar migraciones
php artisan migrate

8. Iniciar el servidor
php artisan serve

## Rutas API - Sprint 1
| Método | Ruta | Descripción |
|--------|------|-------------|
| POST | /api/login | Iniciar sesión |
| POST | /api/logout | Cerrar sesión |
| GET | /api/me | Usuario autenticado |
| POST | /api/expedientes | Registrar expediente |
| GET | /api/expedientes | Listar expedientes |
| GET | /api/expedientes/{id} | Ver expediente |
| GET | /api/expedientes/buscar | Buscar expedientes |

## Credenciales de prueba
- Email: admin@jlo.gob.pe
- Password: password

