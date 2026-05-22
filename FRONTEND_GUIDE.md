# 🎨 Guía Rápida para Frontend

**Este documento Contiene toda la información necesaria para consumir la API.**

---

## 🚀 Inicio Rápido

### 1. URL Base
```
http://localhost:8000/api
```

### 2. Headers Requeridos
```javascript
// Para TODAS las peticiones (excepto login)
{
  "Authorization": "Bearer {token}",
  "Content-Type": "application/json"  // Excepto para subir archivos
}
```

### 3. Login
```javascript
// PRIMERA petición (sin token)
const response = await fetch('http://localhost:8000/api/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'admin@test.com',
    password: 'password123'
  })
});

const data = await response.json();
const token = data.token; // Guardar en localStorage
localStorage.setItem('auth_token', token);
```

---

## 📋 Endpoints por Funcionalidad

### 🔐 AUTENTICACIÓN

#### Login (Público - sin token)
```javascript
POST /login
Body: { email: string, password: string }
Response: { message, token, user: { id, nombre, email, rol } }
```

#### Logout (Protegido)
```javascript
POST /logout
Response: { message: "Sesión cerrada correctamente" }
```

#### Obtener Usuario Actual (Protegido)
```javascript
GET /me
Response: { user: { id, nombre, email, rol } }
```

---

### 📦 EXPEDIENTES

#### Crear Expediente (Protegido)
```javascript
POST /expedientes
Body: {
  numero_expediente: "EXP-001",
  titulo: "Contrato de servicios",
  descripcion: "Contrato de servicios 2026",
  tipo_documento_id: 1,
  area_origen_id: 1,
  area_actual_id: 1,
  numero_folios: 25,
  estado: "Activo",
  fecha_ingreso: "2026-05-20",
  tiempo_conservacion: "5 años"  // o "Permanente"
}
Response: { message, expediente: {...} }
```

#### Listar Expedientes (Protegido)
```javascript
GET /expedientes
Response: [{ id, numero_expediente, titulo, estado, ... }, ...]
```

#### Ver Detalle de Expediente (Protegido)
```javascript
GET /expedientes/{id}
Response: { 
  id, numero_expediente, titulo, descripcion, 
  tipo_documento, area_origen, area_actual, 
  numero_folios, estado, fecha_ingreso, 
  tiempo_conservacion, fecha_revision, 
  digitalizado, created_at 
}
```

#### Buscar Expedientes (Protegido)
```javascript
GET /expedientes/buscar?numero=EXP&titulo=contrato&estado=Activo&area_actual_id=1
Query params:
  - numero_expediente: string (búsqueda parcial)
  - titulo: string (búsqueda parcial)
  - area_actual_id: number
  - estado: string
  - tipo_documento_id: number
  - fecha_inicio: YYYY-MM-DD
  - fecha_fin: YYYY-MM-DD

Response: {
  data: [{ ... }, ...],
  current_page: 1,
  last_page: 1,
  per_page: 10,
  total: 1
}
```

#### Actualizar Expediente (Protegido)
```javascript
PUT /expedientes/{id}
Body: { campos a actualizar (excepto estado) }
Response: { message, expediente: {...} }
```

#### Cambiar Estado (Protegido)
```javascript
PATCH /expedientes/{id}/estado
Body: {
  estado: "Archivado",  // Activo, Archivado, Prestado, Pendiente transferencia
  observaciones: "Motivo del cambio (opcional)"
}
Response: { message, estado_anterior, estado_nuevo }
```

#### Ver Alertas de Revisión (Protegido)
```javascript
GET /expedientes/alertas
Response: {
  VIGENTE: [{ ... }, ...],
  PROXIMO: [{ ... }, ...],
  ATRASADO: [{ ... }, ...]
}
```

---

### 📁 ARCHIVOS DIGITALES

#### Subir PDF (Protegido)
```javascript
POST /expedientes/{id}/archivos
Body: FormData (multipart/form-data)
  - archivo: File (solo PDF, máx 50MB)

Response: {
  message: "Archivo subido correctamente",
  archivo: {
    id, expediente_id, nombre_original, nombre_archivo,
    ruta_archivo, tipo_mime, tamano_bytes, uploaded_at
  }
}

// Con fetch:
const formData = new FormData();
formData.append('archivo', fileInput.files[0]);

fetch(`http://localhost:8000/api/expedientes/${id}/archivos`, {
  method: 'POST',
  headers: { 'Authorization': `Bearer ${token}` },  // NO incluir Content-Type
  body: formData
});
```

#### Listar PDFs del Expediente (Protegido)
```javascript
GET /expedientes/{id}/archivos
Response: {
  archivos: [
    {
      id, expediente_id, nombre_original, nombre_archivo,
      ruta_archivo, tipo_mime, tamano_bytes, uploaded_at
    },
    ...
  ]
}
```

#### Descargar PDF (Protegido)
```javascript
GET /expedientes/{id}/archivos/{archivo_id}
Response: File (PDF descargado)

// En navegador, simplemente haz una petición GET con el token
window.location.href = `http://localhost:8000/api/expedientes/${id}/archivos/${archivo_id}?token=${token}`;
// O con fetch y blob:
fetch(...).then(r => r.blob()).then(blob => {
  const url = window.URL.createObjectURL(blob);
  const a = document.createElement('a');
  a.href = url;
  a.download = 'documento.pdf';
  a.click();
});
```

---

### 🏢 ÁREAS

#### Listar Áreas (Protegido)
```javascript
GET /areas
Response: [
  { id, nombre, descripcion, activo, created_at, updated_at },
  ...
]
```

#### Ver Detalle de Área (Protegido)
```javascript
GET /areas/{id}
Response: { id, nombre, descripcion, activo, ... }
```

#### Crear Área (Protegido)
```javascript
POST /areas
Body: {
  nombre: "Tesorería",
  descripcion: "Área de gestión financiera"
}
Response: { message, area: {...} }
```

#### Actualizar Área (Protegido)
```javascript
PUT /areas/{id}
Body: { nombre: string, descripcion: string }
Response: { message, area: {...} }
```

#### Desactivar Área (Protegido)
```javascript
DELETE /areas/{id}
Response: { message: "Área desactivada correctamente" }
// Nota: No elimina, solo marca como inactiva. No permite si tiene expedientes.
```

---

### 📚 LISTAS PARA FORMULARIOS

#### Tipos de Documento (Protegido)
```javascript
GET /tipos-documento
Response: [
  { id, nombre, descripcion, activo },
  ...
]
```

#### Áreas (Protegido)
```javascript
GET /areas
Response: [...]  // Ver sección de Áreas
```

---

## 🔴 Estados de Expediente

Los únicos estados permitidos son:
- `Activo` - Expediente en uso
- `Archivado` - Archivado
- `Prestado` - En préstamo a otra área
- `Pendiente transferencia` - Pendiente de transferencia

---

## 📊 Ejemplo Completo: Flujo de Usuario

```javascript
// 1. LOGIN
const loginRes = await fetch('http://localhost:8000/api/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({ email: 'admin@test.com', password: 'password123' })
});
const { token } = await loginRes.json();
localStorage.setItem('auth_token', token);

// 2. LISTAR EXPEDIENTES
const header = { 'Authorization': `Bearer ${token}` };
const expedientesRes = await fetch('http://localhost:8000/api/expedientes', { headers: header });
const expedientes = await expedientesRes.json();

// 3. VER DETALLE
const detalleRes = await fetch(`http://localhost:8000/api/expedientes/1`, { headers: header });
const expediente = await detalleRes.json();

// 4. CAMBIAR ESTADO
const estadoRes = await fetch(`http://localhost:8000/api/expedientes/1/estado`, {
  method: 'PATCH',
  headers: { ...header, 'Content-Type': 'application/json' },
  body: JSON.stringify({ estado: 'Archivado', observaciones: 'Completado' })
});
const { estado_nuevo } = await estadoRes.json();

// 5. SUBIR PDF
const formData = new FormData();
formData.append('archivo', fileInput.files[0]);
const uploadRes = await fetch(`http://localhost:8000/api/expedientes/1/archivos`, {
  method: 'POST',
  headers: { 'Authorization': `Bearer ${token}` },
  body: formData
});
const { archivo } = await uploadRes.json();

// 6. LISTAR PDFs
const archivosRes = await fetch(`http://localhost:8000/api/expedientes/1/archivos`, { headers: header });
const { archivos } = await archivosRes.json();
```

---

## ⚠️ Errores Comunes

### "Unauthenticated"
```json
{ "message": "Unauthenticated." }
```
**Solución:** Verifica que:
- El token esté correcto
- Se esté enviando en el header `Authorization: Bearer {token}`
- El token no esté expirado (elimina localStorage si es necesario)

### "The given data was invalid"
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "numero_expediente": ["The numero_expediente has already been taken."]
  }
}
```
**Solución:** Revisa el campo `errors` para ver qué validación falló

### "Expediente no encontrado"
```json
{ "message": "Expediente no encontrado" }
```
**Solución:** Verifica que el ID sea correcto

### Error al subir PDF
```json
{ "message": "The archivo must be a file of type: pdf." }
```
**Solución:**
- Verifica que sea un PDF válido
- Que sea menor a 50MB
- Que uses `Content-Type: multipart/form-data` (automático con FormData)
- **NO incluyas** el header `Content-Type` cuando uses FormData

---

## 🛠️ Utilidades para Frontend

### Helper: Realizar petición con token
```javascript
const apiCall = (endpoint, method = 'GET', body = null) => {
  const token = localStorage.getItem('auth_token');
  const options = {
    method,
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    }
  };
  
  if (body) options.body = JSON.stringify(body);
  
  return fetch(`http://localhost:8000/api${endpoint}`, options)
    .then(r => r.json());
};

// Uso:
const expedientes = await apiCall('/expedientes');
const nuevoExp = await apiCall('/expedientes', 'POST', { ... });
```

### Helper: Upload de archivo
```javascript
const uploadFile = (endpoint, fileInput) => {
  const token = localStorage.getItem('auth_token');
  const formData = new FormData();
  formData.append('archivo', fileInput.files[0]);
  
  return fetch(`http://localhost:8000/api${endpoint}`, {
    method: 'POST',
    headers: { 'Authorization': `Bearer ${token}` },
    body: formData
  }).then(r => r.json());
};

// Uso:
const resultado = await uploadFile(`/expedientes/1/archivos`, fileInput);
```

---

## 📈 Validaciones a Implementar en Frontend

| Campo | Validación |
|-------|-----------|
| `numero_expediente` | Único, máx 50 caracteres |
| `titulo` | Máx 255 caracteres |
| `descripcion` | Sin límite |
| `numero_folios` | Entero positivo |
| `estado` | Uno de: Activo, Archivado, Prestado, Pendiente transferencia |
| `fecha_ingreso` | Fecha, no mayor a hoy |
| `tiempo_conservacion` | Máx 50 caracteres |
| `nombre (área)` | Único, máx 100 caracteres |
| `descripcion (área)` | Máx 255 caracteres |
| `archivo (PDF)` | PDF válido, máx 50MB |

---

## 🎯 Flujos Principales

### Flujo 1: Crear y Digitalizar Expediente
1. Login → obtener token
2. Obtener listados (tipos, áreas)
3. Crear expediente
4. Subir PDF
5. Verificar que `digitalizado = true`

### Flujo 2: Buscar y Cambiar Estado
1. Buscar expedientes con filtros
2. Ver detalle
3. Cambiar estado
4. Ver historial de cambios (en detalle)

### Flujo 3: Ver Alertas
1. Obtener alertas (GET /expedientes/alertas)
2. Mostrar expedientes por categoría: VIGENTE, PROXIMO, ATRASADO
3. Permitir acciones desde alertas

### Flujo 4: Administrar Áreas
1. Listar áreas
2. Crear/editar área
3. Desactivar área (si no tiene expedientes)

---

## 📱 Variables de Ambiente Sugeridas

```javascript
// frontend/.env
VITE_API_URL=http://localhost:8000/api
VITE_APP_NAME=Sistema de Gestión de Expedientes
```

```javascript
// frontend/config.js
export const API_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';
export const getToken = () => localStorage.getItem('auth_token');
export const setToken = (token) => localStorage.setItem('auth_token', token);
export const removeToken = () => localStorage.removeItem('auth_token');
```

---

## 🚨 Notas Importantes

1. **Token en localStorage:** Guarda el token y úsalo en todas las peticiones
2. **Content-Type en uploads:** NO incluyas header `Content-Type` cuando uses FormData
3. **CORS:** El backend permite solicitudes desde cualquier origen
4. **Fechas:** Usa formato `YYYY-MM-DD`
5. **Estados de alerta:**
   - `VIGENTE`: Sin fecha de revisión o > 30 días restantes
   - `PROXIMO`: 0-30 días hasta fecha de revisión
   - `ATRASADO`: Fecha de revisión pasada

---

## 📞 Contacto

Si encuentras problemas con los endpoints o necesitas aclaraciones, revisa:
1. Este documento (FRONTEND_GUIDE.md)
2. README.md principal
3. Los ejemplos en los headers de cada endpoint

---

**Última actualización:** 22-05-2026  
**Estado:** ✅ API Completa y Funcional
