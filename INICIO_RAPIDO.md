# 🚀 Guía de Inicio Rápido para Frontend

**Lee esto primero si estás empezando a consumir la API**

---

## 📍 Lo Primero: URL y Configuración

```javascript
// 1. URL Base de la API
const API_URL = 'http://localhost:8000/api';

// 2. Headers para peticiones autenticadas
const headers = (token) => ({
  'Authorization': `Bearer ${token}`,
  'Content-Type': 'application/json'
});

// 3. Guardar token del login
localStorage.setItem('auth_token', token);
const token = localStorage.getItem('auth_token');
```

---

## 🔐 Paso 1: Login

**Frontend debe:**
1. Mostrar formulario con email y contraseña
2. Hacer petición POST a `/api/login`
3. Guardar token en localStorage
4. Redirigir a dashboard

```javascript
async function handleLogin(email, password) {
  const res = await fetch('http://localhost:8000/api/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });
  
  if (res.ok) {
    const { token } = await res.json();
    localStorage.setItem('auth_token', token);
    // Redirigir a dashboard
  } else {
    // Mostrar error
  }
}
```

---

## 📋 Paso 2: Listar Expedientes

Mostrar tabla con todos los expedientes:

```javascript
async function loadExpedientes() {
  const token = localStorage.getItem('auth_token');
  const res = await fetch('http://localhost:8000/api/expedientes', {
    headers: { 'Authorization': `Bearer ${token}` }
  });
  
  const expedientes = await res.json();
  // Mostrar en tabla
}
```

---

## 🔍 Paso 3: Buscar (Filtros)

Permitir búsqueda por titulo, estado, etc:

```javascript
async function searchExpedientes(filters) {
  const token = localStorage.getItem('auth_token');
  const params = new URLSearchParams();
  
  if (filters.titulo) params.append('titulo', filters.titulo);
  if (filters.estado) params.append('estado', filters.estado);
  if (filters.area_id) params.append('area_actual_id', filters.area_id);
  
  const res = await fetch(
    `http://localhost:8000/api/expedientes/buscar?${params}`,
    { headers: { 'Authorization': `Bearer ${token}` } }
  );
  
  return await res.json();
}
```

---

## ➕ Paso 4: Crear Expediente

Formulario con los campos requeridos:

```javascript
async function createExpediente(formData) {
  const token = localStorage.getItem('auth_token');
  const res = await fetch('http://localhost:8000/api/expedientes', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(formData)
  });
  
  if (res.ok) {
    const { expediente } = await res.json();
    // Mostrar éxito y redirigir
    return expediente;
  } else {
    const { errors } = await res.json();
    // Mostrar errores de validación
  }
}

// Uso:
const nuevoExp = await createExpediente({
  numero_expediente: 'EXP-001',
  titulo: 'Contrato',
  descripcion: '...',
  tipo_documento_id: 1,
  area_origen_id: 1,
  area_actual_id: 1,
  numero_folios: 25,
  estado: 'Activo',
  fecha_ingreso: '2026-05-20',
  tiempo_conservacion: '5 años'
});
```

---

## 👁️ Paso 5: Ver Detalle y Cambiar Estado

Mostrar detalles y permitir cambio de estado:

```javascript
async function getExpediente(id) {
  const token = localStorage.getItem('auth_token');
  const res = await fetch(`http://localhost:8000/api/expedientes/${id}`, {
    headers: { 'Authorization': `Bearer ${token}` }
  });
  return await res.json();
}

async function changeState(expedienteId, newState, observaciones) {
  const token = localStorage.getItem('auth_token');
  const res = await fetch(
    `http://localhost:8000/api/expedientes/${expedienteId}/estado`,
    {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ estado: newState, observaciones })
    }
  );
  
  if (res.ok) {
    // Mostrar éxito
  }
}
```

---

## 📄 Paso 6: Subir PDF

Input file para subir documento:

```javascript
async function uploadPDF(expedienteId, file) {
  const token = localStorage.getItem('auth_token');
  const formData = new FormData();
  formData.append('archivo', file);
  
  const res = await fetch(
    `http://localhost:8000/api/expedientes/${expedienteId}/archivos`,
    {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${token}` },
      body: formData
      // NOTA: NO incluir Content-Type cuando uses FormData
    }
  );
  
  if (res.ok) {
    const { archivo } = await res.json();
    // Mostrar confirmación
    return archivo;
  }
}

// HTML:
// <input type="file" id="pdfInput" accept=".pdf">
// <button onclick="uploadFile()">Subir</button>

function uploadFile() {
  const file = document.getElementById('pdfInput').files[0];
  const expedienteId = new URLSearchParams(window.location.search).get('id');
  uploadPDF(expedienteId, file);
}
```

---

## 🎯 Componentes Principales Necesarios

### 1. Login
- Email input
- Password input
- Botón "Iniciar Sesión"
- Manejo de errores

### 2. Navbar
- Mostrar usuario actual (`GET /me`)
- Botón Logout
- Links a secciones

### 3. Dashboard
- Tabla de expedientes
- Paginación (si hay muchos registros)
- Botón "Crear Expediente"

### 4. Buscador
- Input texto (por titulo)
- Select estado (Activo, Archivado, etc)
- Select área
- Botón buscar

### 5. Formulario Expediente (Crear/Editar)
- Número (único)
- Título
- Descripción
- Tipo documento (dropdown, `GET /tipos-documento`)
- Área origen (dropdown, `GET /areas`)
- Área actual (dropdown, `GET /areas`)
- Folios (número)
- Estado (dropdown)
- Fecha ingreso (date picker)
- Tiempo conservación (texto o select)

### 6. Detalle Expediente
- Mostrar todos los campos
- Botón "Cambiar estado"
- Botón "Editar"
- Sección de PDFs (listar y descargar)
- Botón "Subir PDF"

### 7. Alertas
- 3 secciones: VIGENTE, PRÓXIMO, ATRASADO
- Listar expedientes por cada sección
- Colores diferenciados

### 8. Administración de Áreas
- Tabla de áreas
- Formulario crear/editar
- Botón eliminar (con validación)

---

## 📊 Flujo de Datos Típico

```
LOGIN
  ↓
OBTENER TIPOS-DOCUMENTO Y ÁREAS (para formularios)
  ↓
LISTAR EXPEDIENTES (dashboard)
  ↓
[Usuario elige acción]
  ↓
  ├─ VER DETALLE → CAMBIAR ESTADO / SUBIR PDF / EDITAR
  ├─ CREAR NUEVO → POST /expedientes
  ├─ BUSCAR → GET /expedientes/buscar
  └─ VER ALERTAS → GET /expedientes/alertas
```

---

## 🎨 Estados y Colores Sugeridos

| Estado | Color | Significado |
|--------|-------|------------|
| Activo | 🟢 Verde | En uso |
| Archivado | ⚫ Gris | Archivado |
| Prestado | 🟡 Amarillo | Prestado |
| Pendiente transferencia | 🔵 Azul | Pendiente |
| VIGENTE (alerta) | 🟢 Verde | Sin riesgo |
| PRÓXIMO (alerta) | 🟡 Amarillo | Próximo a vencer |
| ATRASADO (alerta) | 🔴 Rojo | Vencido |

---

## 🔐 Seguridad: Qué Debes Hacer

1. ✅ Guardar token en localStorage después de login
2. ✅ Incluir token en TODOS los headers (excepto login)
3. ✅ Si recibés 401, limpiar localStorage y redirigir a login
4. ✅ NO almacenar contraseña nunca
5. ✅ NO mostrar errores de la API al usuario (sanitizar mensajes)

```javascript
// Interceptor para manejar 401
function setupAxiosInterceptors() {
  axios.interceptors.response.use(
    response => response,
    error => {
      if (error.response?.status === 401) {
        localStorage.removeItem('auth_token');
        window.location.href = '/login';
      }
      return Promise.reject(error);
    }
  );
}
```

---

## 🚨 Errores Comunes a Evitar

### ❌ NO hacer esto:
```javascript
// Falta token
fetch('http://localhost:8000/api/expedientes')

// Content-Type incorrecto en formulario
fetch(url, { 
  body: formData,
  headers: { 'Content-Type': 'multipart/form-data' }  // ❌
})

// Token no guardado
const token = null;  // ❌

// Mostrar error crudo al usuario
alert(error.response.data.errors.numero_expediente[0]);  // ❌
```

### ✅ HACER esto:
```javascript
// Con token
fetch('http://localhost:8000/api/expedientes', {
  headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` }
})

// FormData sin Content-Type
fetch(url, {
  method: 'POST',
  headers: { 'Authorization': `Bearer ${token}` },
  body: formData  // ✅ Browser lo hace automáticamente
})

// Obtener token
const token = localStorage.getItem('auth_token');  // ✅

// Mostrar error amigable
if (errors.numero_expediente) {
  showError('El número de expediente ya está en uso');  // ✅
}
```

---

## 📚 Documentos Relacionados

- **README.md** - Documentación técnica completa
- **FRONTEND_GUIDE.md** - Guía detallada para frontend
- **EJEMPLOS_PETICIONES.md** - Ejemplos en cURL y JavaScript

---

## 💬 Resumen

**Lo mínimo que necesitas:**

1. **Endpoint de login:** POST /api/login → guardar token
2. **Endpoint de expedientes:** GET /api/expedientes → mostrar lista
3. **Buscar:** GET /api/expedientes/buscar → filtrar
4. **Crear:** POST /api/expedientes → nuevo registro
5. **Cambiar estado:** PATCH /api/expedientes/{id}/estado → auditoría
6. **Subir PDF:** POST /api/expedientes/{id}/archivos → archivo
7. **Alertas:** GET /api/expedientes/alertas → agrupar por estado

**Con eso tienes 80% de la funcionalidad.**

---

¡Éxito con el frontend! 🚀
