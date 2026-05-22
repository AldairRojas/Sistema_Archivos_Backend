# 📋 Sistema de Gestión de Archivos y Expedientes

Backend API REST en Laravel para gestión de archivos y expedientes documentales.

---

## 📑 Tabla de Contenidos

1. [Descripción General](#descripción-general)
2. [Requisitos](#requisitos)
3. [Instalación](#instalación)
4. [Autenticación](#autenticación)
5. [Sprints](#sprints)
6. [Endpoints](#endpoints)
7. [Ejemplos](#ejemplos)
8. [Estructura](#estructura)

---

## 📌 Descripción General

Sistema backend para digitalizar y gestionar expedientes documentales de forma segura. Permite:
- ✅ Registro y gestión de expedientes
- ✅ Control de estados (Activo, Archivado, Prestado, etc.)
- ✅ Subida y digitalización de archivos PDF
- ✅ Historial completo de cambios
- ✅ Auditoría de accesos

**Stack:**
- Laravel 12
- PHP 8.2+
- SQLite/MySQL
- Laravel Sanctum (autenticación API)
- Vite + Tailwind CSS (frontend)

---

## ⚙️ Requisitos

- PHP >= 8.2
- Composer
- Node.js + npm
- Laravel 12
- MySQL o SQLite

---

## 🚀 Instalación

### 1. Clonar repositorio
```bash
git clone <repositorio>
cd backendArchivos
```

### 2. Instalar dependencias PHP
```bash
composer install
```

### 3. Configurar .env
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Configurar base de datos en .env
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=archivos
DB_USERNAME=root
DB_PASSWORD=
```

### 5. Ejecutar migraciones
```bash
php artisan migrate
```

### 6. Instalar dependencias JavaScript
```bash
npm install
npm run build
```

### 7. Iniciar servidor
```bash
php artisan serve
```

El servidor estará disponible en: `http://localhost:8000`

---

## 🔐 Autenticación

Todos los endpoints (excepto `/login`) requieren autenticación con token Bearer.

### Login
```
POST /api/login
Content-Type: application/json

{
  "email": "usuario@ejemplo.com",
  "password": "password123"
}
```

**Respuesta:**
```json
{
  "message": "Inicio de sesión exitoso",
  "token": "1|ABC123XYZ...",
  "user": {
    "id": 1,
    "nombre": "Juan Pérez",
    "email": "usuario@ejemplo.com",
    "rol": "admin"
  }
}
```

### Usar token en peticiones
Agregar header a todas las peticiones:
```
Authorization: Bearer 1|ABC123XYZ...
```

### Logout
```
POST /api/logout
Authorization: Bearer {token}
```

### Obtener usuario actual
```
GET /api/me
Authorization: Bearer {token}
```

---

## 📦 Sprints Implementados

### ✅ Sprint 1 - CRUD de Expedientes

**Objetivo:** Crear, listar y consultar expedientes

#### Funcionalidades (HU01-HU04)

| ID | Título | Descripción |
|-----|---------|-------------|
| HU01 | Registro de expedientes | Crear expedientes con validaciones |
| HU02 | Listado de expedientes | Ver todos los expedientes |
| HU03 | Consulta de expediente | Ver detalle de un expediente |
| HU04 | Búsqueda de expedientes | Buscar con filtros |

#### Endpoints Sprint 1
```
POST   /api/expedientes              → Crear expediente
GET    /api/expedientes              → Listar todos
GET    /api/expedientes/{id}         → Ver detalle
GET    /api/expedientes/buscar       → Buscar con filtros
GET    /api/areas                    → Listar áreas disponibles
GET    /api/tipos-documento          → Listar tipos de documento
```

#### Campos de expediente
```json
{
  "numero_expediente": "EXP-001",
  "titulo": "Título del expediente",
  "descripcion": "Descripción detallada",
  "tipo_documento_id": 1,
  "area_origen_id": 1,
  "area_actual_id": 1,
  "numero_folios": 50,
  "estado": "Activo",
  "fecha_ingreso": "2026-05-20",
  "tiempo_conservacion": "5 años"
}
```

---

### ✅ Sprint 2 - Actualización y Gestión de Estados

**Objetivo:** Modificar expedientes y gestionar cambios de estado

#### Funcionalidades (HU05-HU07)

| ID | Título | Descripción |
|-----|---------|-------------|
| HU05 | Visualización detallada | Ver expediente con historial de cambios |
| HU06 | Modificación | Actualizar datos del expediente |
| HU07 | Cambio de estado | Cambiar estado + registra auditoría |

#### Endpoints Sprint 2
```
PUT    /api/expedientes/{id}         → Actualizar expediente
PATCH  /api/expedientes/{id}/estado  → Cambiar estado
GET    /api/expedientes/{id}         → Ver + historial completo
```

#### Estados permitidos
- `Activo` - Expediente en uso
- `Archivado` - Expediente archivado
- `Prestado` - En préstamo a otra área
- `Pendiente transferencia` - Pendiente de transferencia

#### Cambiar estado de expediente
```json
{
  "estado": "Archivado",
  "observaciones": "Razón del cambio (opcional)"
}
```

#### Respuesta con historial
```json
{
  "message": "Estado actualizado correctamente",
  "estado_anterior": "Activo",
  "estado_nuevo": "Archivado"
}
```

#### Visualizar historial (GET /expedientes/{id})
```json
{
  "expediente": {...},
  "historial": [
    {
      "id": 1,
      "estado_anterior": "Activo",
      "estado_nuevo": "Archivado",
      "observaciones": "Por inactividad",
      "fecha_cambio": "2026-05-22T08:00:00Z",
      "usuario": "Juan Pérez"
    }
  ]
}
```

---

### ✅ Sprint 3 - Digitalización de Archivos PDF

**Objetivo:** Subir, gestionar y descargar archivos PDF asociados a expedientes

#### Funcionalidades (HU08-HU10)

| ID | Título | Descripción |
|-----|---------|-------------|
| HU08 | Subida de PDF | Subir archivos validando formato y tamaño |
| HU09 | Asociación | Vincular PDF con expediente + marcar digitalizado |
| HU10 | Visualización | Ver y descargar archivos asociados |

#### Endpoints Sprint 3
```
POST   /api/expedientes/{id}/archivos              → Subir PDF
GET    /api/expedientes/{id}/archivos              → Listar archivos
GET    /api/expedientes/{id}/archivos/{archivo_id} → Descargar archivo
```

#### Subir archivo PDF
```
Content-Type: multipart/form-data

Form data:
- Key: "archivo"
- Type: File
- Value: [selecciona archivo PDF]
```

#### Validaciones de archivos
- ✅ Solo PDF (`application/pdf`)
- ✅ Tamaño máximo: 50MB
- ✅ Nombre único (timestamp + nombre original)
- ✅ Almacenamiento seguro en `storage/app/public/`

#### Respuesta subida exitosa (201)
```json
{
  "message": "Archivo subido correctamente",
  "archivo": {
    "id": 1,
    "expediente_id": 1,
    "nombre_original": "documento.pdf",
    "nombre_archivo": "1726934485_documento.pdf",
    "ruta_archivo": "expedientes/1/1726934485_documento.pdf",
    "tipo_mime": "application/pdf",
    "tamano_bytes": 250000,
    "uploaded_at": "2026-05-22T08:30:00Z"
  }
}
```

#### Listar archivos del expediente (200)
```json
{
  "archivos": [
    {
      "id": 1,
      "expediente_id": 1,
      "nombre_original": "documento.pdf",
      "nombre_archivo": "1726934485_documento.pdf",
      "ruta_archivo": "expedientes/1/1726934485_documento.pdf",
      "tipo_mime": "application/pdf",
      "tamano_bytes": 250000,
      "uploaded_at": "2026-05-22T08:30:00Z"
    }
  ]
}
```

#### Acciones automáticas al subir PDF
- ✅ Se vincula automáticamente al expediente
- ✅ Se marca `digitalizado = true` en el expediente
- ✅ Se genera nombre único con timestamp
- ✅ Se guarda en disco (privado)
- ✅ Se registra en auditoría

---

## 📡 Endpoints Completos

### Autenticación
```
POST   /api/login       → Login (obtener token)
POST   /api/logout      → Logout (cerrar sesión)
GET    /api/me          → Obtener usuario actual
```

### Listados
```
GET    /api/areas               → Áreas disponibles
GET    /api/tipos-documento     → Tipos de documento
```

### Sprint 1 - Expedientes (CRUD)
```
POST   /api/expedientes         → Crear expediente
GET    /api/expedientes         → Listar expedientes
GET    /api/expedientes/{id}    → Ver expediente
GET    /api/expedientes/buscar  → Buscar expedientes
```

### Sprint 2 - Actualización y Estados
```
PUT    /api/expedientes/{id}           → Actualizar expediente
PATCH  /api/expedientes/{id}/estado    → Cambiar estado
```

### Sprint 3 - Archivos Digitales
```
POST   /api/expedientes/{id}/archivos              → Subir PDF
GET    /api/expedientes/{id}/archivos              → Listar PDFs
GET    /api/expedientes/{id}/archivos/{archivo_id} → Descargar PDF
```

---

## 💡 Ejemplos de Uso

### Ejemplo 1: Crear expediente (Sprint 1)
```bash
curl -X POST http://localhost:8000/api/expedientes \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "numero_expediente": "EXP-001",
    "titulo": "Contrato de servicios",
    "descripcion": "Contrato de servicios 2026",
    "tipo_documento_id": 1,
    "area_origen_id": 1,
    "area_actual_id": 1,
    "numero_folios": 25,
    "estado": "Activo",
    "fecha_ingreso": "2026-05-20",
    "tiempo_conservacion": "5 años"
  }'
```

### Ejemplo 2: Cambiar estado (Sprint 2)
```bash
curl -X PATCH http://localhost:8000/api/expedientes/1/estado \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "estado": "Archivado",
    "observaciones": "Expediente completado"
  }'
```

### Ejemplo 3: Subir PDF (Sprint 3 - con Postman)
1. Método: **POST**
2. URL: `http://localhost:8000/api/expedientes/1/archivos`
3. Headers: `Authorization: Bearer {token}`
4. Body → **form-data**:
   - Key: `archivo`
   - Type: `File`
   - Value: (selecciona archivo PDF)
5. Click **Send**

### Ejemplo 4: Listar archivos del expediente (Sprint 3)
```bash
curl -X GET http://localhost:8000/api/expedientes/1/archivos \
  -H "Authorization: Bearer {token}"
```

### Ejemplo 5: Descargar archivo (Sprint 3)
```bash
curl -X GET http://localhost:8000/api/expedientes/1/archivos/1 \
  -H "Authorization: Bearer {token}" \
  -o documento.pdf
```

---

## 📁 Estructura del Proyecto

```
backendArchivos/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── AuthController.php           ← Autenticación
│   │       ├── ExpedienteController.php     ← Sprint 1 y 2
│   │       └── ArchivoDigitalController.php ← Sprint 3
│   ├── Models/
│   │   ├── User.php                         ← Usuarios
│   │   ├── Expediente.php                   ← Expedientes
│   │   ├── Area.php                         ← Áreas
│   │   ├── TipoDocumento.php                ← Tipos documento
│   │   ├── HistorialEstado.php              ← Historial
│   │   └── ArchivoDigital.php               ← Archivos
│   └── Providers/
│
├── database/
│   ├── migrations/
│   │   ├── create_usuarios_table.php
│   │   ├── create_expedientes_table.php
│   │   ├── create_areas_table.php
│   │   ├── create_tipos_documento_table.php
│   │   ├── create_historial_estados_table.php
│   │   └── create_archivos_digitales_table.php
│   └── seeders/
│
├── routes/
│   ├── api.php          ← Todas las rutas API
│   └── web.php
│
├── storage/
│   └── app/
│       └── public/
│           └── expedientes/  ← Almacenamiento de PDFs
│
├── config/
│   ├── app.php
│   ├── database.php
│   ├── filesystems.php
│   └── cors.php
│
├── .env.example
├── composer.json
├── package.json
└── README.md
```

---

## 🗄️ Modelos y Relaciones

### User (Usuarios)
```php
- hasMany(HistorialEstado)
- hasMany(ArchivoDigital)
```

### Expediente (Expedientes)
```php
- belongsTo(TipoDocumento)
- belongsTo(Area) as areaOrigen
- belongsTo(Area) as areaActual
- hasMany(HistorialEstado)
- hasMany(ArchivoDigital)
```

### ArchivoDigital (Archivos)
```php
- belongsTo(Expediente)
- belongsTo(User) as usuario
```

### HistorialEstado (Historial)
```php
- belongsTo(Expediente)
- belongsTo(User)
```

### Area (Áreas)
```php
- hasMany(Expediente) as areaOrigen
- hasMany(Expediente) as areaActual
```

### TipoDocumento (Tipos)
```php
- hasMany(Expediente)
```

---

## ✅ Validaciones

### Crear/Actualizar Expediente
- `numero_expediente` - único, máx 50 caracteres, requerido
- `titulo` - requerido, máx 255 caracteres
- `descripcion` - requerida, máx 5000 caracteres
- `tipo_documento_id` - requerido, debe existir
- `area_origen_id` - requerido, debe existir
- `area_actual_id` - requerido, debe existir
- `numero_folios` - requerido, entero, mínimo 1
- `estado` - solo: Activo, Archivado, Prestado, Pendiente transferencia
- `fecha_ingreso` - requerida, formato date, no mayor a hoy
- `tiempo_conservacion` - requerido, máx 50 caracteres

### Cambiar Estado
- `estado` - requerido, solo valores permitidos
- `observaciones` - opcional, máx 500 caracteres

### Subir Archivo
- `archivo` - requerido, PDF, máx 50MB
- Solo formato PDF (application/pdf) permitido

---

## 🔍 Búsqueda de Expedientes

El endpoint `GET /api/expedientes/buscar` acepta parámetros query:

```
GET /api/expedientes/buscar?numero=EXP&titulo=contrato&estado=Activo
```

Parámetros disponibles:
- `numero` - Búsqueda en número de expediente
- `titulo` - Búsqueda en título
- `estado` - Filtrar por estado
- `area` - Filtrar por área actual
- `desde` - Fecha inicio (fecha_ingreso)
- `hasta` - Fecha fin (fecha_ingreso)

---

## 📊 Estados de Expediente

| Estado | Descripción | Permite cambios |
|--------|-------------|-----------------|
| Activo | Expediente en uso activo | Sí |
| Archivado | Expediente archivado | Sí |
| Prestado | En préstamo a otra área | Sí |
| Pendiente transferencia | Pendiente de transferencia | Sí |

---

## 🛡️ Seguridad

- ✅ Autenticación con Laravel Sanctum (tokens)
- ✅ Validación de input en backend
- ✅ Nombres únicos para archivos (previene sobrescritura)
- ✅ Almacenamiento privado de PDFs
- ✅ Control de acceso por usuario autenticado
- ✅ Historial completo de cambios (auditoría)
- ✅ Encriptación de contraseñas (bcrypt)

---

## 📝 Códigos HTTP

| Código | Significado |
|--------|------------|
| 200 | OK - Operación exitosa |
| 201 | Created - Recurso creado exitosamente |
| 400 | Bad Request - Solicitud inválida |
| 401 | Unauthorized - Sin autenticación/token inválido |
| 404 | Not Found - Recurso no encontrado |
| 422 | Unprocessable Entity - Validación fallida |
| 500 | Server Error - Error del servidor |

---

## 📈 Resumen de Sprints

```
✅ Sprint 1 (Completo): CRUD de expedientes
   - Crear, listar, consultar y buscar expedientes

✅ Sprint 2 (Completo): Actualización y estados
   - Modificar expedientes
   - Cambiar estado con historial
   - Ver historial de cambios

✅ Sprint 3 (Completo): Digitalización
   - Subir archivos PDF
   - Listar archivos por expediente
   - Descargar archivos
   - Marcar expediente como digitalizado
```

---

## 🚀 Próximas Fases (No Implementadas)

### Sprint 4 (Futuro)
- [ ] Búsqueda avanzada con filtros
- [ ] Reportes y estadísticas
- [ ] Exportación de datos

### Sprint 5 (Futuro)
- [ ] Búsqueda rápida mejorada
- [ ] OCR de documentos
- [ ] Clasificación automática

---


## 📄 Licencia

MIT License - 2026

---

**Versión:** 1.0  
**Última actualización:** 22-05-2026  
**Equipo:** Desarrollo Backend  
**Estado:** ✅ Funcional (Sprint 1, 2, 3 completados)