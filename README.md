# MultiBD - Web Service Multi-Base de Datos

API RESTful desarrollada con Laravel que implementa conexiones con tres motores de base de datos diferentes:

- **MySQL** (Relacional)
- **MongoDB** (NoSQL - Documentos)
- **Redis** (Clave-Valor)

## 📋 Requisitos Previos

- PHP 8.3 o superior
- Composer
- WAMP Server (o equivalente con MySQL)
- MongoDB Server
- Redis Server
- Extensión PHP MongoDB (`php_mongodb.dll`)

## 🚀 Instalación

### 1. Clonar el repositorio

```bash
git clone <url-del-repositorio>
cd multiBD
```

### 2. Instalar dependencias de PHP

```bash
composer install
```

### 3. Configurar el archivo de entorno

Copiar el archivo de ejemplo y configurar las variables:

```bash
cp .env.example .env
```

Editar `.env` con los siguientes valores:

```env
# Configuración de la aplicación
APP_NAME=MultiBD
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=America/Mexico_City
APP_LOCALE=es

# MySQL (Relacional)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=multibd
DB_USERNAME=root
DB_PASSWORD=

# MongoDB (NoSQL)
MONGODB_HOST=127.0.0.1
MONGODB_PORT=27017
MONGODB_DATABASE=multibd_mongo
MONGODB_USERNAME=
MONGODB_PASSWORD=

# Redis (Clave-Valor)
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
```

### 4. Generar clave de aplicación

```bash
php artisan key:generate
```

### 5. Ejecutar migraciones

```bash
php artisan migrate
```

### 6. Instalar extensión MongoDB en PHP (si no está instalada)

1. Descargar la DLL desde: https://pecl.php.net/package/mongodb
2. Seleccionar **PHP 8.3 Thread Safe (TS) x64**
3. Extraer `php_mongodb.dll` a `C:\wamp64\bin\php\php8.3.x\ext\`
4. Agregar en `php.ini`:
   ```ini
   extension=mongodb
   ```
5. Reiniciar WAMP Server

### 7. Iniciar los servicios necesarios

- **WAMP Server** (MySQL)
- **MongoDB**: `mongod`
- **Redis**: `redis-server`

## 🏃 Ejecución

Iniciar el servidor de desarrollo:

```bash
php artisan serve
```

La API estará disponible en: `http://localhost:8000/api`

## 📚 Estructura de la API

### Bases de Datos y Recursos

| Base de Datos | Tipo | Recursos |
|---------------|------|----------|
| MySQL | Relacional | Categorías, Productos, Clientes, Órdenes |
| MongoDB | NoSQL (Documentos) | Logs, Comentarios |
| Redis | Clave-Valor | Configuraciones, Sesiones |

## 🔗 Endpoints de la API

### Información General

```
GET /api
```

Retorna información sobre la API y los endpoints disponibles.

---

### 📦 MySQL - Recursos Relacionales

#### Categorías

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/categorias` | Listar todas las categorías |
| POST | `/api/categorias` | Crear una categoría |
| GET | `/api/categorias/{id}` | Obtener una categoría |
| PATCH | `/api/categorias/{id}` | Actualizar una categoría |
| DELETE | `/api/categorias/{id}` | Eliminar una categoría (lógico) |
| PATCH | `/api/categorias/{id}/restaurar` | Restaurar una categoría |

**Ejemplo - Crear Categoría:**
```json
POST /api/categorias
{
    "nombre": "Electrónicos",
    "descripcion": "Productos electrónicos y tecnología",
    "activo": true
}
```

#### Productos

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/productos` | Listar todos los productos |
| POST | `/api/productos` | Crear un producto |
| GET | `/api/productos/{id}` | Obtener un producto |
| PATCH | `/api/productos/{id}` | Actualizar un producto |
| DELETE | `/api/productos/{id}` | Eliminar un producto (lógico) |
| PATCH | `/api/productos/{id}/restaurar` | Restaurar un producto |

**Ejemplo - Crear Producto:**
```json
POST /api/productos
{
    "nombre": "Laptop HP",
    "descripcion": "Laptop HP 15 pulgadas",
    "precio": 15999.99,
    "stock": 10,
    "sku": "LAP-HP-001",
    "categoria_id": 1,
    "activo": true
}
```

**Parámetros de consulta:**
- `buscar`: Buscar por nombre o SKU
- `categoria_id`: Filtrar por categoría
- `con_stock`: Solo productos con stock (`true`)
- `precio_min`, `precio_max`: Rango de precios
- `ordenar_por`, `orden`: Ordenamiento

#### Clientes

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/clientes` | Listar todos los clientes |
| POST | `/api/clientes` | Crear un cliente |
| GET | `/api/clientes/{id}` | Obtener un cliente |
| PATCH | `/api/clientes/{id}` | Actualizar un cliente |
| DELETE | `/api/clientes/{id}` | Eliminar un cliente (lógico) |
| PATCH | `/api/clientes/{id}/restaurar` | Restaurar un cliente |

**Ejemplo - Crear Cliente:**
```json
POST /api/clientes
{
    "nombre": "Juan",
    "apellido": "Pérez",
    "email": "juan.perez@email.com",
    "telefono": "5551234567",
    "direccion": "Calle Principal 123",
    "ciudad": "Ciudad de México",
    "estado": "CDMX",
    "codigo_postal": "06600"
}
```

#### Órdenes

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/ordenes` | Listar todas las órdenes |
| GET | `/api/ordenes/estados` | Obtener estados disponibles |
| POST | `/api/ordenes` | Crear una orden |
| GET | `/api/ordenes/{id}` | Obtener una orden |
| PATCH | `/api/ordenes/{id}` | Actualizar una orden |
| DELETE | `/api/ordenes/{id}` | Eliminar una orden (lógico) |
| PATCH | `/api/ordenes/{id}/restaurar` | Restaurar una orden |

**Ejemplo - Crear Orden:**
```json
POST /api/ordenes
{
    "cliente_id": 1,
    "subtotal": 15999.99,
    "impuestos": 2560.00,
    "total": 18559.99,
    "estado": "pendiente",
    "notas": "Envío express"
}
```

**Estados disponibles:** `pendiente`, `procesando`, `enviado`, `entregado`, `cancelado`

---

### 🍃 MongoDB - Recursos NoSQL

#### Logs

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/logs` | Listar todos los logs |
| GET | `/api/logs/acciones` | Obtener acciones disponibles |
| POST | `/api/logs` | Crear un log |
| GET | `/api/logs/{id}` | Obtener un log |
| PATCH | `/api/logs/{id}` | Actualizar un log |
| DELETE | `/api/logs/{id}` | Eliminar un log (lógico) |
| PATCH | `/api/logs/{id}/restaurar` | Restaurar un log |

**Ejemplo - Crear Log:**
```json
POST /api/logs
{
    "accion": "crear",
    "entidad": "productos",
    "entidad_id": "1",
    "usuario_id": 1,
    "datos_nuevos": {"nombre": "Producto X"}
}
```

**Acciones disponibles:** `crear`, `actualizar`, `eliminar`, `login`, `logout`

#### Comentarios

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/comentarios` | Listar todos los comentarios |
| GET | `/api/comentarios/promedio/{entidad}/{id}` | Obtener promedio de calificaciones |
| POST | `/api/comentarios` | Crear un comentario |
| GET | `/api/comentarios/{id}` | Obtener un comentario |
| PATCH | `/api/comentarios/{id}` | Actualizar un comentario |
| DELETE | `/api/comentarios/{id}` | Eliminar un comentario (lógico) |
| PATCH | `/api/comentarios/{id}/restaurar` | Restaurar un comentario |

**Ejemplo - Crear Comentario:**
```json
POST /api/comentarios
{
    "contenido": "Excelente producto, muy recomendado",
    "entidad": "productos",
    "entidad_id": "1",
    "usuario_nombre": "Juan Pérez",
    "calificacion": 5,
    "metadata": {"verificado": true}
}
```

---

### 🔴 Redis - Recursos Clave-Valor

#### Configuraciones

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/configuraciones` | Listar todas las configuraciones |
| GET | `/api/configuraciones/eliminadas` | Listar configuraciones eliminadas |
| POST | `/api/configuraciones` | Crear una configuración |
| GET | `/api/configuraciones/{clave}` | Obtener una configuración |
| PATCH | `/api/configuraciones/{clave}` | Actualizar una configuración |
| DELETE | `/api/configuraciones/{clave}` | Eliminar una configuración (lógico) |
| PATCH | `/api/configuraciones/{clave}/restaurar` | Restaurar una configuración |

**Ejemplo - Crear Configuración:**
```json
POST /api/configuraciones
{
    "clave": "app_mantenimiento",
    "valor": false,
    "descripcion": "Modo de mantenimiento de la aplicación",
    "tipo": "boolean"
}
```

**Tipos disponibles:** `string`, `integer`, `boolean`, `json`, `array`

#### Sesiones Cache

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/sesiones` | Listar todas las sesiones |
| GET | `/api/sesiones/eliminadas` | Listar sesiones eliminadas |
| GET | `/api/sesiones/usuario/{id}` | Listar sesiones de un usuario |
| POST | `/api/sesiones` | Crear una sesión |
| GET | `/api/sesiones/{id}` | Obtener una sesión |
| PATCH | `/api/sesiones/{id}` | Actualizar una sesión |
| DELETE | `/api/sesiones/{id}` | Eliminar una sesión (lógico) |
| PATCH | `/api/sesiones/{id}/restaurar` | Restaurar una sesión |

**Ejemplo - Crear Sesión:**
```json
POST /api/sesiones
{
    "usuario_id": 1,
    "datos": {
        "carrito": [],
        "preferencias": {"tema": "oscuro"}
    }
}
```

---

## 🗑️ Borrado Lógico

Todos los recursos implementan **borrado lógico** (soft delete). Esto significa que:

- Al eliminar un registro con `DELETE`, no se borra físicamente
- Se marca con `activo: false` y `deleted_at: timestamp`
- Los registros eliminados no aparecen en las consultas por defecto
- Se pueden restaurar usando el endpoint `/restaurar`
- Para ver registros eliminados, usar `?incluir_inactivos=true`

## 📄 Respuestas de la API

### Respuesta Exitosa

```json
{
    "success": true,
    "data": { ... },
    "message": "Operación exitosa"
}
```

### Respuesta de Error

```json
{
    "success": false,
    "message": "Mensaje de error",
    "errors": { ... }
}
```

### Códigos HTTP

| Código | Descripción |
|--------|-------------|
| 200 | OK - Operación exitosa |
| 201 | Created - Recurso creado |
| 404 | Not Found - Recurso no encontrado |
| 422 | Unprocessable Entity - Error de validación |
| 500 | Server Error - Error del servidor |

## 🧪 Probar la API

### Con cURL

```bash
# Listar categorías
curl -X GET http://localhost:8000/api/categorias

# Crear categoría
curl -X POST http://localhost:8000/api/categorias \
  -H "Content-Type: application/json" \
  -d '{"nombre":"Electrónicos","descripcion":"Productos electrónicos"}'
```

### Con Postman

1. Importar la colección de endpoints
2. Configurar la variable de entorno `base_url` como `http://localhost:8000/api`

## 📁 Estructura del Proyecto

```
multiBD/
├── app/
│   ├── Http/Controllers/Api/     # Controladores de la API
│   ├── Models/                   # Modelos (MySQL y MongoDB)
│   └── Services/                 # Servicios (Redis)
├── config/
│   └── database.php              # Configuración de bases de datos
├── database/
│   └── migrations/               # Migraciones de MySQL
├── routes/
│   └── api.php                   # Rutas de la API
└── .env                          # Variables de entorno
```

## 👨‍💻 Autor

Proyecto desarrollado para la materia de Bases de Datos.

## 📝 Licencia

Este proyecto es de uso académico.
