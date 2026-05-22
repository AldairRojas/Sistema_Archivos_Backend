# 📋 Sistema de Gestión de Archivos y Expedientes

Backend API REST en Laravel para gestión de archivos y expedientes documentales municipales.

**Versión:** 1.0  
**Estado:** ✅ Funcional (Sprints 1-4 completados)  
**Equipo:** Backend + Frontend

---

## 📑 Tabla de Contenidos

1. [Descripción General](#descripción-general)
2. [Stack Tecnológico](#stack-tecnológico)
3. [Requisitos](#requisitos)
4. [Instalación](#instalación)
5. [Autenticación](#autenticación)
6. [Sprints Implementados](#sprints-implementados)
7. [Endpoints Disponibles](#endpoints-disponibles)
8. [Modelos de Datos](#modelos-de-datos)
9. [Validaciones](#validaciones)
10. [Ejemplos de Uso](#ejemplos-de-uso)
11. [Códigos HTTP](#códigos-http)
12. [Estados de Expediente](#estados-de-expediente)
13. [Estructura](#estructura)
14. [Solución de Problemas](#solución-de-problemas)

---

## 📌 Descripción General

Sistema backend para digitalizar y gestionar expedientes documentales de forma segura. Permite:
- ✅ Registro y gestión de expedientes
- ✅ Control de estados (Activo, Archivado, Prestado, etc.)
- ✅ Subida y digitalización de archivos PDF
- ✅ Historial completo de cambios con auditoría
- ✅ Búsqueda avanzada con múltiples filtros
- ✅ Alertas de documentos próximos a revisión
- ✅ Administración de áreas municipales

**Base de datos:** Expedientes, Usuarios, Áreas, Tipos de documento, Archivos digitales, Historial de cambios

**Stack:**
- Laravel 12
- PHP 8.2+
- MariaDB/MySQL
- Laravel Sanctum (autenticación con tokens Bearer)
- Vite + Tailwind CSS

---

## ⚙️ Requisitos

- PHP >= 8.2
- Composer
- Node.js + npm (opcional, solo para assets)
- Laravel 12
- MariaDB/MySQL
- Git

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
```env
DB_CONNECTION=mariadb
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

### 6. (Opcional) Instalar dependencias JavaScript
```bash
npm install
npm run build
```

### 7. Iniciar servidor
```bash
php artisan serve
```

El servidor estará disponible en: **`http://localhost:8000`**

### 8. Crear usuario de prueba (opcional)
```bash
php artisan tinker
>>> $user = new \App\Models\User();
>>> $user->nombre = 'Admin';
>>> $user->email = 'admin@test.com';
>>> $user->password = Hash::make('password123');
>>> $user->rol = 'admin';
>>> $user->activo = 1;
>>> $user->save();
```

---

## 🔐 Autenticación

Todos los endpoints (excepto `/login`) requieren autenticación con **token Bearer**.

### Login
```http
POST /api/login
Content-Type: application/json

{
    "email": "archivo@jlo.gob.pe",
    "password": "password"
}
```

**Respuesta exitosa (200):**
```json
{
  "message": "Inicio de sesión exitoso",
  "token": "1|ABC123XYZ...",
  "user": {
    "id": 1,
    "nombre": "Usuario",
    "email": "archivo@jlo.gob.pe",
    "rol": "usuario"
  }
}
```

**Respuesta error (401):**
```json
{
  "message": "Correo o contraseña incorrectos"
}
```

### Usar token en peticiones
Agregar header `Authorization` a todas las peticiones protegidas:
```http
Authorization: Bearer 1|ABC123XYZ...
```

### Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

**Respuesta:**
```json
{
  "message": "Sesión cerrada correctamente"
}
```

### Obtener usuario actual
```http
GET /api/me
Authorization: Bearer {token}
```

**Respuesta:**
```json
{
  "user": {
    "id": 1,
    "nombre": "Usuario",
    "email": "archivo@jlo.gob.pe",
    "rol": "usuario"
  }
}
```

---

## ✨ Sprints Implementados

### ✅ Sprint 1 - CRUD de Expedientes (Completo)
**5 Historias de Usuario implementadas**
- Autenticación de usuarios (HU01)
- Cierre de sesión (HU18)
- Registro de expedientes (HU02)
- Visualización de expedientes (HU03)
- Búsqueda de expedientes (HU04)

### ✅ Sprint 2 - Actualización y Gestión de Estados (Completo)
**3 Historias de Usuario implementadas**
- Visualización detallada (HU05)
- Modificación de expedientes (HU06)
- Cambio de estado (HU07)

### ✅ Sprint 3 - Digitalización de Archivos PDF (Completo)
**3 Historias de Usuario implementadas**
- Subida de PDF (HU08)
- Asociación de archivos (HU09)
- Visualización de archivos (HU10)

### ✅ Sprint 4 - Administración de Áreas (Completo)
**3 Historias de Usuario implementadas**
- Registro de áreas (HU11)
- Consulta y modificación de áreas (HU12)
- Seguimiento de expedientes próximos a revisión (HU14)

### ⏳ Sprint 5 - Reportes Estadísticos (Futuro)
*No implementado en esta versión*

## 📡 Endpoints Disponibles (18 rutas)

### Autenticación (Públicas - sin token)
```http
POST   /api/login       → Login (obtener token)
```

### Autenticación (Protegidas - con token)
```http
POST   /api/logout      → Logout (cerrar sesión)
GET    /api/me          → Obtener usuario actual
```

### Listas para Formularios (Protegidas)
```http
GET    /api/areas               → Listar áreas disponibles
GET    /api/tipos-documento     → Listar tipos de documento
```

### Sprint 1 - Expedientes: CRUD
```http
POST   /api/expedientes         → Crear expediente
GET    /api/expedientes         → Listar expedientes
GET    /api/expedientes/{id}    → Ver detalle de expediente
GET    /api/expedientes/buscar  → Buscar con filtros avanzados
```

### Sprint 2 - Expedientes: Actualización y Estados
```http
PUT    /api/expedientes/{id}           → Actualizar expediente
PATCH  /api/expedientes/{id}/estado    → Cambiar estado + auditoría
```

### Sprint 3 - Archivos Digitales
```http
POST   /api/expedientes/{id}/archivos              → Subir PDF
GET    /api/expedientes/{id}/archivos              → Listar PDFs del expediente
GET    /api/expedientes/{id}/archivos/{archivo_id} → Descargar PDF
```

### Sprint 4 - Áreas
```http
GET    /api/areas               → Listar áreas
GET    /api/areas/{id}          → Ver detalle de área
POST   /api/areas               → Crear área
PUT    /api/areas/{id}          → Actualizar área
DELETE /api/areas/{id}          → Desactivar área
```

### Sprint 4 - Alertas de Revisión
```http
GET    /api/expedientes/alertas → Expedientes agrupados por alerta
```

## 📋 Modelos de Datos

### Expediente
```json
{
  "id": 1,
  "numero_expediente": "EXP-001",
  "titulo": "Contrato de servicios",
  "descripcion": "Contrato de servicios 2026",
  "tipo_documento": "Contrato",
  "area_origen": "Administración",
  "area_actual": "Administración",
  "numero_folios": 25,
  "estado": "Activo",
  "fecha_ingreso": "2026-05-20",
  "tiempo_conservacion": "5 años",
  "fecha_revision": "2031-05-20",
  "digitalizado": false,
  "created_at": "2026-05-22T10:30:00Z"
}
```

### Área
```json
{
  "id": 1,
  "nombre": "Administración",
  "descripcion": "Área administrativa central",
  "activo": 1,
  "created_at": "2026-05-22T10:00:00Z",
  "updated_at": "2026-05-22T10:00:00Z"
}
```

### Tipo Documento
```json
{
  "id": 1,
  "nombre": "Contrato",
  "descripcion": "Contratos varios",
  "activo": 1,
  "created_at": "2026-05-22T10:00:00Z"
}
```

### Archivo Digital
```json
{
  "id": 1,
  "expediente_id": 1,
  "usuario_id": 1,
  "nombre_original": "documento.pdf",
  "nombre_archivo": "1726934485_documento.pdf",
  "ruta_archivo": "expedientes/1/1726934485_documento.pdf",
  "tipo_mime": "application/pdf",
  "tamano_bytes": 250000,
  "uploaded_at": "2026-05-22T08:30:00Z"
}
```

### Historial de Estado
```json
{
  "id": 1,
  "expediente_id": 1,
  "estado_anterior": "Activo",
  "estado_nuevo": "Archivado",
  "usuario_id": 1,
  "usuario_nombre": "Admin",
  "fecha_cambio": "2026-05-22T11:30:00Z",
  "observaciones": "Por inactividad"
}
```

---

## ✅ Validaciones

### Crear/Actualizar Expediente
| Campo | Validación | Ejemplo |
|-------|-----------|---------|
| `numero_expediente` | Único, máx 50 caracteres, requerido | `EXP-001` |
| `titulo` | Requerido, máx 255 caracteres | `Contrato de servicios` |
| `descripcion` | Requerida, sin límite | `Descripción...` |
| `tipo_documento_id` | Requerido, debe existir | `1` |
| `area_origen_id` | Requerido, debe existir | `1` |
| `area_actual_id` | Requerido, debe existir | `1` |
| `numero_folios` | Requerido, entero, mínimo 1 | `25` |
| `estado` | Requerido, valores permitidos | `Activo` |
| `fecha_ingreso` | Requerida, no mayor a hoy | `2026-05-20` |
| `tiempo_conservacion` | Requerido, máx 50 caracteres | `5 años` o `Permanente` |

### Cambiar Estado
| Campo | Validación | Ejemplo |
|-------|-----------|---------|
| `estado` | Requerido, solo valores permitidos | `Archivado` |
| `observaciones` | Opcional, máx 500 caracteres | `Expediente completado` |

### Subir Archivo
| Campo | Validación | Ejemplo |
|-------|-----------|---------|
| `archivo` | Requerido, PDF, máx 50MB | `documento.pdf` |

### Crear/Actualizar Área
| Campo | Validación | Ejemplo |
|-------|-----------|---------|
| `nombre` | Requerido, único, máx 100 caracteres | `Administración` |
| `descripcion` | Requerida, máx 255 caracteres | `Área administrativa central` |

---

## 💡 Ejemplos de Uso

### Ejemplo 1: Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "archivo@jlo.gob.pe",
    "password": "password"
  }'
```

**Respuesta:**
```json
{
  "message": "Inicio de sesión exitoso",
  "token": "1|ABC123XYZ...",
  "user": {
    "id": 1,
    "nombre": "Usuario",
    "email": "archivo@jlo.gob.pe",
    "rol": "usuario"
  }
}
```

### Ejemplo 2: Crear Expediente
```bash
curl -X POST http://localhost:8000/api/expedientes \
  -H "Authorization: Bearer 1|ABC123XYZ..." \
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

### Ejemplo 3: Buscar Expedientes
```bash
curl -X GET "http://localhost:8000/api/expedientes/buscar?titulo=contrato&estado=Activo&area_actual_id=1" \
  -H "Authorization: Bearer 1|ABC123XYZ..."
```

**Parámetros de búsqueda:**
- `numero_expediente` - búsqueda parcial
- `titulo` - búsqueda parcial
- `area_actual_id` - filtro exacto
- `estado` - filtro exacto
- `tipo_documento_id` - filtro exacto
- `fecha_inicio` - fecha de inicio (YYYY-MM-DD)
- `fecha_fin` - fecha de fin (YYYY-MM-DD)

### Ejemplo 4: Cambiar Estado de Expediente
```bash
curl -X PATCH http://localhost:8000/api/expedientes/1/estado \
  -H "Authorization: Bearer 1|ABC123XYZ..." \
  -H "Content-Type: application/json" \
  -d '{
    "estado": "Archivado",
    "observaciones": "Expediente completado"
  }'
```

### Ejemplo 5: Subir PDF
```bash
curl -X POST http://localhost:8000/api/expedientes/1/archivos \
  -H "Authorization: Bearer 1|ABC123XYZ..." \
  -F "archivo=@documento.pdf"
```

O en **Postman:**
1. Método: **POST**
2. URL: `http://localhost:8000/api/expedientes/1/archivos`
3. Headers: `Authorization: Bearer 1|ABC123XYZ...`
4. Body → **form-data**:
   - Key: `archivo`
   - Type: `File`
   - Value: (selecciona archivo PDF)
5. Click **Send**

### Ejemplo 6: Listar Archivos del Expediente
```bash
curl -X GET http://localhost:8000/api/expedientes/1/archivos \
  -H "Authorization: Bearer 1|ABC123XYZ..."
```

### Ejemplo 7: Descargar Archivo
```bash
curl -X GET http://localhost:8000/api/expedientes/1/archivos/1 \
  -H "Authorization: Bearer 1|ABC123XYZ..." \
  -o documento.pdf
```

### Ejemplo 8: Ver Alertas de Revisión
```bash
curl -X GET http://localhost:8000/api/expedientes/alertas \
  -H "Authorization: Bearer 1|ABC123XYZ..."
```

**Respuesta:**
```json
{
  "VIGENTE": [
    { "id": 1, "numero_expediente": "EXP-001", ... }
  ],
  "PROXIMO": [
    { "id": 2, "numero_expediente": "EXP-002", ... }
  ],
  "ATRASADO": [
    { "id": 3, "numero_expediente": "EXP-003", ... }
  ]
}
```

### Ejemplo 9: Crear Área
```bash
curl -X POST http://localhost:8000/api/areas \
  -H "Authorization: Bearer 1|ABC123XYZ..." \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Tesorería",
    "descripcion": "Área de gestión financiera"
  }'
```

### Ejemplo 10: Listar Áreas
```bash
curl -X GET http://localhost:8000/api/areas \
  -H "Authorization: Bearer 1|ABC123XYZ..."
```

---

## 📊 Estados de Expediente

| Estado | Descripción | Permite cambios |
|--------|-------------|-----------------|
| `Activo` | Expediente en uso activo | Sí |
| `Archivado` | Expediente archivado | Sí |
| `Prestado` | En préstamo a otra área | Sí |
| `Pendiente transferencia` | Pendiente de transferencia | Sí |

---

## 🔍 Búsqueda Avanzada

El endpoint `GET /api/expedientes/buscar` acepta múltiples parámetros:

```http
GET /api/expedientes/buscar?numero=EXP&titulo=contrato&estado=Activo&area_actual_id=1&fecha_inicio=2026-01-01&fecha_fin=2026-12-31
```

**Respuesta:**
```json
{
  "data": [
    { "id": 1, "numero_expediente": "EXP-001", ... }
  ],
  "current_page": 1,
  "last_page": 1,
  "per_page": 10,
  "total": 1
}
```

---

## 🚨 Códigos HTTP

| Código | Significado | Ejemplo |
|--------|------------|---------|
| **200** | OK - Operación exitosa | Lectura de datos |
| **201** | Created - Recurso creado exitosamente | POST de expediente |
| **400** | Bad Request - Solicitud inválida | Formato incorrecto |
| **401** | Unauthorized - Sin autenticación/token inválido | Falta token |
| **404** | Not Found - Recurso no encontrado | ID inexistente |
| **422** | Unprocessable Entity - Validación fallida | Campos inválidos |
| **500** | Server Error - Error del servidor | Error interno |

---

## ⚠️ Respuestas de Error

### Error de Validación (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "numero_expediente": [
      "The numero_expediente has already been taken."
    ],
    "titulo": [
      "The titulo field is required."
    ]
  }
}
```

### Error de No Encontrado (404)
```json
{
  "message": "Expediente no encontrado"
}
```

### Error de Autenticación (401)
```json
{
  "message": "Unauthenticated."
}
```

---

## 📁 Estructura del Proyecto

```
backendArchivos/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       ├── AuthController.php           ✅
│   │       ├── ExpedienteController.php     ✅
│   │       ├── ArchivoDigitalController.php ✅
│   │       ├── AreaController.php           ✅
│   │       └── Controller.php
│   │
│   ├── Models/
│   │   ├── User.php                 ✅
│   │   ├── Expediente.php           ✅
│   │   ├── Area.php                 ✅
│   │   ├── TipoDocumento.php        ✅
│   │   ├── HistorialEstado.php      ✅
│   │   └── ArchivoDigital.php       ✅
│
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
│
├── routes/
│   ├── api.php          ✅ (18 rutas)
│   ├── web.php
│   └── console.php
│
├── storage/
│   └── app/public/expedientes/  ✅ (Almacenamiento de PDFs)
│
├── config/
│   ├── database.php
│   ├── sanctum.php
│   ├── filesystems.php
│   └── documentos.php
│
├── .env
├── .env.example
├── composer.json
├── README.md (este archivo)
└── vite.config.js
```

---

## 🛡️ Seguridad

- ✅ **Autenticación:** Laravel Sanctum con tokens Bearer
- ✅ **Validación de input:** Validaciones robustas en backend
- ✅ **Nombres únicos para archivos:** Previene sobrescrituras
- ✅ **Almacenamiento privado:** PDFs guardados fuera de web
- ✅ **Control de acceso:** Por usuario autenticado
- ✅ **Historial completo:** Auditoría de todos los cambios
- ✅ **Encriptación:** Contraseñas con bcrypt
- ✅ **CORS:** Configurado para frontend

---

## 🐛 Solución de Problemas

### "Unauthenticated" en peticiones
- ✅ Asegúrate de enviar el header `Authorization: Bearer {token}`
- ✅ Verifica que el token no esté expirado
- ✅ Comprueba que el usuario tenga `activo = 1`

### "The given data was invalid"
- ✅ Revisa los errores devueltos en el campo `errors`
- ✅ Valida los tipos de datos (número_expediente es único)
- ✅ Asegúrate que los IDs de relaciones existan

### Error al subir PDF
- ✅ Verifica que sea un archivo PDF válido
- ✅ Confirma que sea menor a 50MB
- ✅ Asegúrate usar `Content-Type: multipart/form-data`

### Expediente no encontrado (404)
- ✅ Verifica que el ID del expediente sea correcto
- ✅ Comprueba que no haya sido eliminado



## 📈 Próximos Pasos

### Sprint 5 (En desarrollo)
- [ ] Panel de control con estadísticas
- [ ] Reportes por área
- [ ] Reportes de digitalización
- [ ] Reportes por rango de fechas

---

**Versión:** 1.0  
**Última actualización:** 22-05-2026  
**Estado:** ✅ Funcional y listo para producción (Sprints 1-4)

