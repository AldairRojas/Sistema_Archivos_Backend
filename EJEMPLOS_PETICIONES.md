# 📚 Ejemplos de Peticiones HTTP

Este documento contiene ejemplos prácticos en cURL y JavaScript para todos los endpoints.

---

## 🔐 Autenticación

### Login (obtener token)
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "admin@test.com",
    "password": "password123"
  }'
```

**Respuesta:**
```json
{
  "message": "Inicio de sesión exitoso",
  "token": "1|ABC123XYZ...",
  "user": {
    "id": 1,
    "nombre": "Admin",
    "email": "admin@test.com",
    "rol": "admin"
  }
}
```

### JavaScript
```javascript
async function login(email, password) {
  const response = await fetch('http://localhost:8000/api/login', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email, password })
  });
  const data = await response.json();
  if (data.token) {
    localStorage.setItem('auth_token', data.token);
    return data;
  }
  throw new Error(data.message);
}
```

### Logout
```bash
TOKEN="1|ABC123XYZ..."
curl -X POST http://localhost:8000/api/logout \
  -H "Authorization: Bearer $TOKEN"
```

### Obtener usuario actual
```bash
TOKEN="1|ABC123XYZ..."
curl -X GET http://localhost:8000/api/me \
  -H "Authorization: Bearer $TOKEN"
```

---

## 📦 Expedientes

### Crear expediente
```bash
TOKEN="1|ABC123XYZ..."
curl -X POST http://localhost:8000/api/expedientes \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "numero_expediente": "EXP-001",
    "titulo": "Contrato de servicios",
    "descripcion": "Contrato de servicios municipales 2026",
    "tipo_documento_id": 1,
    "area_origen_id": 1,
    "area_actual_id": 1,
    "numero_folios": 25,
    "estado": "Activo",
    "fecha_ingreso": "2026-05-20",
    "tiempo_conservacion": "5 años"
  }'
```

### JavaScript
```javascript
async function createExpediente(data) {
  const token = localStorage.getItem('auth_token');
  const response = await fetch('http://localhost:8000/api/expedientes', {
    method: 'POST',
    headers: {
      'Authorization': `Bearer ${token}`,
      'Content-Type': 'application/json'
    },
    body: JSON.stringify(data)
  });
  return await response.json();
}

// Uso:
const nuevoExpediente = await createExpediente({
  numero_expediente: 'EXP-001',
  titulo: 'Contrato de servicios',
  // ... más campos
});
```

### Listar expedientes
```bash
TOKEN="1|ABC123XYZ..."
curl -X GET http://localhost:8000/api/expedientes \
  -H "Authorization: Bearer $TOKEN"
```

### Ver detalle de expediente
```bash
TOKEN="1|ABC123XYZ..."
curl -X GET http://localhost:8000/api/expedientes/1 \
  -H "Authorization: Bearer $TOKEN"
```

### Buscar expedientes (básico)
```bash
TOKEN="1|ABC123XYZ..."
curl -X GET "http://localhost:8000/api/expedientes/buscar?titulo=contrato" \
  -H "Authorization: Bearer $TOKEN"
```

### Buscar expedientes (avanzado)
```bash
TOKEN="1|ABC123XYZ..."
curl -X GET "http://localhost:8000/api/expedientes/buscar?numero=EXP&titulo=contrato&estado=Activo&area_actual_id=1&fecha_inicio=2026-01-01&fecha_fin=2026-12-31" \
  -H "Authorization: Bearer $TOKEN"
```

### JavaScript (búsqueda)
```javascript
async function searchExpedientes(filters) {
  const token = localStorage.getItem('auth_token');
  const params = new URLSearchParams(filters);
  const response = await fetch(
    `http://localhost:8000/api/expedientes/buscar?${params}`,
    {
      headers: { 'Authorization': `Bearer ${token}` }
    }
  );
  return await response.json();
}

// Uso:
const resultados = await searchExpedientes({
  titulo: 'contrato',
  estado: 'Activo',
  area_actual_id: 1
});
```

### Actualizar expediente
```bash
TOKEN="1|ABC123XYZ..."
curl -X PUT http://localhost:8000/api/expedientes/1 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "numero_expediente": "EXP-001",
    "titulo": "Contrato de servicios ACTUALIZADO",
    "descripcion": "Nueva descripción",
    "tipo_documento_id": 1,
    "area_origen_id": 1,
    "area_actual_id": 2,
    "numero_folios": 30,
    "fecha_ingreso": "2026-05-20",
    "tiempo_conservacion": "5 años"
  }'
```

### Cambiar estado
```bash
TOKEN="1|ABC123XYZ..."
curl -X PATCH http://localhost:8000/api/expedientes/1/estado \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "estado": "Archivado",
    "observaciones": "Expediente completado"
  }'
```

### JavaScript (cambiar estado)
```javascript
async function changeExpedienteState(id, newState, observaciones = '') {
  const token = localStorage.getItem('auth_token');
  const response = await fetch(
    `http://localhost:8000/api/expedientes/${id}/estado`,
    {
      method: 'PATCH',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({
        estado: newState,
        observaciones
      })
    }
  );
  return await response.json();
}
```

### Ver alertas de revisión
```bash
TOKEN="1|ABC123XYZ..."
curl -X GET http://localhost:8000/api/expedientes/alertas \
  -H "Authorization: Bearer $TOKEN"
```

**Respuesta:**
```json
{
  "VIGENTE": [
    {
      "id": 1,
      "numero_expediente": "EXP-001",
      "titulo": "Contrato",
      "estado": "Activo",
      "fecha_revision": "2031-05-20"
    }
  ],
  "PROXIMO": [
    {
      "id": 2,
      "numero_expediente": "EXP-002",
      "titulo": "Solicitud",
      "estado": "Activo",
      "fecha_revision": "2026-06-15"
    }
  ],
  "ATRASADO": []
}
```

---

## 📁 Archivos Digitales

### Subir PDF
```bash
TOKEN="1|ABC123XYZ..."
curl -X POST http://localhost:8000/api/expedientes/1/archivos \
  -H "Authorization: Bearer $TOKEN" \
  -F "archivo=@documento.pdf"
```

### JavaScript (subir PDF)
```javascript
async function uploadPDF(expedienteId, file) {
  const token = localStorage.getItem('auth_token');
  const formData = new FormData();
  formData.append('archivo', file);
  
  const response = await fetch(
    `http://localhost:8000/api/expedientes/${expedienteId}/archivos`,
    {
      method: 'POST',
      headers: { 'Authorization': `Bearer ${token}` },
      body: formData
    }
  );
  return await response.json();
}

// Uso en HTML:
// <input type="file" id="fileInput" accept=".pdf">
const fileInput = document.getElementById('fileInput');
const result = await uploadPDF(1, fileInput.files[0]);
```

### Listar PDFs de un expediente
```bash
TOKEN="1|ABC123XYZ..."
curl -X GET http://localhost:8000/api/expedientes/1/archivos \
  -H "Authorization: Bearer $TOKEN"
```

### Descargar PDF
```bash
TOKEN="1|ABC123XYZ..."
curl -X GET http://localhost:8000/api/expedientes/1/archivos/1 \
  -H "Authorization: Bearer $TOKEN" \
  -o documento.pdf
```

### JavaScript (descargar PDF)
```javascript
async function downloadPDF(expedienteId, archivoId, nombreArchivo) {
  const token = localStorage.getItem('auth_token');
  const response = await fetch(
    `http://localhost:8000/api/expedientes/${expedienteId}/archivos/${archivoId}`,
    { headers: { 'Authorization': `Bearer ${token}` } }
  );
  
  const blob = await response.blob();
  const url = window.URL.createObjectURL(blob);
  const link = document.createElement('a');
  link.href = url;
  link.download = nombreArchivo;
  link.click();
  window.URL.revokeObjectURL(url);
}
```

---

## 🏢 Áreas

### Listar áreas
```bash
TOKEN="1|ABC123XYZ..."
curl -X GET http://localhost:8000/api/areas \
  -H "Authorization: Bearer $TOKEN"
```

### Ver detalle de área
```bash
TOKEN="1|ABC123XYZ..."
curl -X GET http://localhost:8000/api/areas/1 \
  -H "Authorization: Bearer $TOKEN"
```

### Crear área
```bash
TOKEN="1|ABC123XYZ..."
curl -X POST http://localhost:8000/api/areas \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Tesorería",
    "descripcion": "Área de gestión financiera"
  }'
```

### Actualizar área
```bash
TOKEN="1|ABC123XYZ..."
curl -X PUT http://localhost:8000/api/areas/1 \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "nombre": "Tesorería Municipal",
    "descripcion": "Gestión financiera y presupuestaria"
  }'
```

### Desactivar área
```bash
TOKEN="1|ABC123XYZ..."
curl -X DELETE http://localhost:8000/api/areas/1 \
  -H "Authorization: Bearer $TOKEN"
```

---

## 📚 Listas

### Tipos de documento
```bash
TOKEN="1|ABC123XYZ..."
curl -X GET http://localhost:8000/api/tipos-documento \
  -H "Authorization: Bearer $TOKEN"
```

---

## 🎯 Flujo Completo: Ejemplo Práctico

### Paso 1: Login
```javascript
const loginRes = await fetch('http://localhost:8000/api/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    email: 'admin@test.com',
    password: 'password123'
  })
});

const { token } = await loginRes.json();
localStorage.setItem('auth_token', token);
```

### Paso 2: Obtener tipos de documento y áreas
```javascript
const getHeaders = () => ({
  'Authorization': `Bearer ${localStorage.getItem('auth_token')}`
});

const tiposRes = await fetch('http://localhost:8000/api/tipos-documento', {
  headers: getHeaders()
});
const tipos = await tiposRes.json();

const areasRes = await fetch('http://localhost:8000/api/areas', {
  headers: getHeaders()
});
const areas = await areasRes.json();

console.log('Tipos:', tipos);
console.log('Áreas:', areas);
```

### Paso 3: Crear expediente
```javascript
const createRes = await fetch('http://localhost:8000/api/expedientes', {
  method: 'POST',
  headers: {
    ...getHeaders(),
    'Content-Type': 'application/json'
  },
  body: JSON.stringify({
    numero_expediente: 'EXP-' + Date.now(),
    titulo: 'Contrato de servicios',
    descripcion: 'Contrato de servicios municipales',
    tipo_documento_id: tipos[0].id,
    area_origen_id: areas[0].id,
    area_actual_id: areas[0].id,
    numero_folios: 25,
    estado: 'Activo',
    fecha_ingreso: '2026-05-20',
    tiempo_conservacion: '5 años'
  })
});

const { expediente } = await createRes.json();
console.log('Expediente creado:', expediente);
```

### Paso 4: Subir PDF
```javascript
const fileInput = document.querySelector('input[type="file"]');
const formData = new FormData();
formData.append('archivo', fileInput.files[0]);

const uploadRes = await fetch(
  `http://localhost:8000/api/expedientes/${expediente.id}/archivos`,
  {
    method: 'POST',
    headers: { 'Authorization': `Bearer ${localStorage.getItem('auth_token')}` },
    body: formData
  }
);

const { archivo } = await uploadRes.json();
console.log('Archivo subido:', archivo);
```

### Paso 5: Cambiar estado
```javascript
const stateRes = await fetch(
  `http://localhost:8000/api/expedientes/${expediente.id}/estado`,
  {
    method: 'PATCH',
    headers: {
      ...getHeaders(),
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      estado: 'Archivado',
      observaciones: 'Completado y digitalizado'
    })
  }
);

const { estado_nuevo } = await stateRes.json();
console.log('Nuevo estado:', estado_nuevo);
```

---

## 🛠️ Cliente HTTP Recomendado

### Postman (para testing)
1. Descargar desde https://www.postman.com/
2. Crear una colección "Sistema de Expedientes"
3. Agregar carpetas por categoría (Auth, Expedientes, etc.)
4. Usar variables de entorno para el token

### Insomnia (alternativa)
1. Descargar desde https://insomnia.rest/
2. Similar a Postman pero más ligero

### Thunder Client (VS Code)
1. Extensión de VS Code
2. Más integrada al editor
3. Más ligera que Postman

---

## 🔧 Configuración de Postman

### Variables de Entorno
```json
{
  "base_url": "http://localhost:8000/api",
  "token": "1|ABC123XYZ..."
}
```

### Headers por defecto
```
Authorization: Bearer {{token}}
Content-Type: application/json
```

### Pre-request Script (para login)
```javascript
// Si es petición POST a /login, guardar token
if (pm.request.name === 'Login') {
  pm.test("Status is 200", () => {
    pm.response.to.have.status(200);
  });
}
```

### Post-request Script
```javascript
// Guardar token después de login
if (pm.response.code === 200 && pm.request.name === 'Login') {
  const data = pm.response.json();
  pm.environment.set('token', data.token);
}
```

---

## 📱 Interceptor Axios (para Vue/React)

```javascript
import axios from 'axios';

const api = axios.create({
  baseURL: 'http://localhost:8000/api'
});

// Request interceptor
api.interceptors.request.use(config => {
  const token = localStorage.getItem('auth_token');
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }
  return config;
});

// Response interceptor
api.interceptors.response.use(
  response => response,
  error => {
    if (error.response?.status === 401) {
      localStorage.removeItem('auth_token');
      window.location.href = '/login';
    }
    return Promise.reject(error);
  }
);

export default api;

// Uso:
// const data = await api.get('/expedientes');
// await api.post('/expedientes', {...});
```

---

**Última actualización:** 22-05-2026
