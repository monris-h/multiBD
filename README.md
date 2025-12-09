# MultiBD - Sistema Multi-Base de Datos

API RESTful desarrollada con Laravel 12 que implementa conexiones simultáneas con tres motores de base de datos diferentes.

## Tecnologías Implementadas

| Motor | Tipo | Propósito |
|-------|------|-----------|
| MySQL | Relacional | Datos estructurados con relaciones |
| MongoDB | NoSQL (Documentos) | Datos flexibles y logs |
| Redis | Clave-Valor | Cache y sesiones en memoria |

---

## Tabla de Contenidos

1. [Requisitos del Sistema](#requisitos-del-sistema)
2. [Instalación](#instalación)
3. [Configuración](#configuración)
4. [Ejecución](#ejecución)
5. [Estructura de la API](#estructura-de-la-api)
6. [Endpoints](#endpoints)
7. [Borrado Lógico](#borrado-lógico)
8. [Respuestas de la API](#respuestas-de-la-api)
9. [Pruebas](#pruebas)
10. [Estructura del Proyecto](#estructura-del-proyecto)

---

## Requisitos del Sistema

- PHP 8.3 o superior
- Composer 2.x
- MySQL 8.0 o superior
- MongoDB 6.0 o superior
- Redis 7.0 o superior (o Memurai en Windows)
- Extensión PHP MongoDB (`php_mongodb.dll`)
- Extensión PHP Redis o cliente Predis

---

## Instalación

### 1. Clonar el repositorio

```bash
git clone <url-del-repositorio>
cd multiBD
```

### 2. Instalar dependencias

```bash
composer install
npm install
```

### 3. Configurar el entorno

```bash
cp .env.example .env
php artisan key:generate
```

### 4. Ejecutar migraciones

```bash
php artisan migrate
```

### 5. Compilar assets (opcional)

```bash
npm run build
```

---

## Configuración

Editar el archivo `.env` con los siguientes parámetros:

### Aplicación

```env
APP_NAME=MultiBD
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000
APP_TIMEZONE=America/Mexico_City
APP_LOCALE=es
```

### MySQL (Base de datos relacional)

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=multibd
DB_USERNAME=root
DB_PASSWORD=
```

### MongoDB (Base de datos NoSQL)

```env
MONGODB_HOST=127.0.0.1
MONGODB_PORT=27017
MONGODB_DATABASE=multibd_mongo
MONGODB_USERNAME=
MONGODB_PASSWORD=
```

### Redis (Almacenamiento clave-valor)

```env
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
REDIS_DB=0
```

### Instalación de la extensión MongoDB en PHP

1. Descargar desde: https://pecl.php.net/package/mongodb
2. Seleccionar la versión correspondiente a PHP 8.3 Thread Safe (TS) x64
3. Copiar `php_mongodb.dll` al directorio de extensiones de PHP
4. Agregar en `php.ini`:
   ```ini
   extension=mongodb
   ```
5. Reiniciar el servidor web

---

## Ejecución

### Iniciar servicios requeridos

Asegurarse de que los siguientes servicios estén activos:

- MySQL Server
- MongoDB Server (`mongod`)
- Redis Server (`redis-server` o Memurai)

### Iniciar el servidor de desarrollo

```bash
php artisan serve
```

La aplicación estará disponible en:
- **Interfaz Web**: http://localhost:8000
- **API REST**: http://localhost:8000/api

---

## Estructura de la API

### Recursos por Base de Datos

| Base de Datos | Recursos | Descripción |
|---------------|----------|-------------|
| MySQL | Categorías, Productos, Clientes, Órdenes | Datos transaccionales con relaciones |
| MongoDB | Logs, Comentarios | Documentos flexibles y auditoría |
| Redis | Configuraciones, Sesiones | Datos en memoria de acceso rápido |

---

## Endpoints

### Información General

```
GET /api
```

Retorna información sobre la API y los endpoints disponibles.

---

### MySQL - Recursos Relacionales

#### Categorías

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/categorias` | Listar todas las categorías |
| POST | `/api/categorias` | Crear una categoría |
| GET | `/api/categorias/{id}` | Obtener una categoría |
| PATCH | `/api/categorias/{id}` | Actualizar una categoría |
| DELETE | `/api/categorias/{id}` | Eliminar una categoría (borrado lógico) |
| PATCH | `/api/categorias/{id}/restaurar` | Restaurar una categoría |

Ejemplo de creación:
```json
POST /api/categorias
{
    "nombre": "Electrónicos",
    "descripcion": "Productos electrónicos y tecnología"
}
```

#### Productos

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/productos` | Listar todos los productos |
| POST | `/api/productos` | Crear un producto |
| GET | `/api/productos/{id}` | Obtener un producto |
| PATCH | `/api/productos/{id}` | Actualizar un producto |
| DELETE | `/api/productos/{id}` | Eliminar un producto (borrado lógico) |
| PATCH | `/api/productos/{id}/restaurar` | Restaurar un producto |

Ejemplo de creación:
```json
POST /api/productos
{
    "nombre": "Laptop HP",
    "descripcion": "Laptop HP 15 pulgadas",
    "precio": 15999.99,
    "stock": 10,
    "categoria_id": 1
}
```

Parámetros de consulta disponibles:
- `buscar`: Búsqueda por nombre
- `categoria_id`: Filtrar por categoría
- `con_stock`: Solo productos con stock disponible
- `precio_min`, `precio_max`: Rango de precios

#### Clientes

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/clientes` | Listar todos los clientes |
| POST | `/api/clientes` | Crear un cliente |
| GET | `/api/clientes/{id}` | Obtener un cliente |
| PATCH | `/api/clientes/{id}` | Actualizar un cliente |
| DELETE | `/api/clientes/{id}` | Eliminar un cliente (borrado lógico) |
| PATCH | `/api/clientes/{id}/restaurar` | Restaurar un cliente |

Ejemplo de creación:
```json
POST /api/clientes
{
    "nombre": "Juan Pérez",
    "email": "juan.perez@email.com",
    "telefono": "5551234567",
    "direccion": "Calle Principal 123"
}
```

#### Órdenes

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/ordenes` | Listar todas las órdenes |
| GET | `/api/ordenes/estados` | Obtener estados disponibles |
| POST | `/api/ordenes` | Crear una orden |
| GET | `/api/ordenes/{id}` | Obtener una orden con productos |
| PATCH | `/api/ordenes/{id}` | Actualizar una orden |
| DELETE | `/api/ordenes/{id}` | Eliminar una orden (borrado lógico) |
| PATCH | `/api/ordenes/{id}/restaurar` | Restaurar una orden |

Estados disponibles: `pendiente`, `procesando`, `completada`, `cancelada`

---

### MongoDB - Recursos NoSQL

#### Logs

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/logs` | Listar todos los logs |
| GET | `/api/logs/acciones` | Obtener acciones disponibles |
| POST | `/api/logs` | Crear un log |
| GET | `/api/logs/{id}` | Obtener un log |
| PATCH | `/api/logs/{id}` | Actualizar un log |
| DELETE | `/api/logs/{id}` | Eliminar un log (borrado lógico) |
| PATCH | `/api/logs/{id}/restaurar` | Restaurar un log |

Acciones disponibles: `crear`, `actualizar`, `eliminar`, `restaurar`

#### Comentarios

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/comentarios` | Listar todos los comentarios |
| GET | `/api/comentarios/promedio/{entidad}/{id}` | Obtener promedio de calificaciones |
| POST | `/api/comentarios` | Crear un comentario |
| GET | `/api/comentarios/{id}` | Obtener un comentario |
| PATCH | `/api/comentarios/{id}` | Actualizar un comentario |
| DELETE | `/api/comentarios/{id}` | Eliminar un comentario (borrado lógico) |
| PATCH | `/api/comentarios/{id}/restaurar` | Restaurar un comentario |

---

### Redis - Recursos Clave-Valor

#### Configuraciones

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/configuraciones` | Listar todas las configuraciones |
| GET | `/api/configuraciones/eliminadas` | Listar configuraciones eliminadas |
| POST | `/api/configuraciones` | Crear una configuración |
| GET | `/api/configuraciones/{clave}` | Obtener una configuración |
| PATCH | `/api/configuraciones/{clave}` | Actualizar una configuración |
| DELETE | `/api/configuraciones/{clave}` | Eliminar una configuración (borrado lógico) |
| PATCH | `/api/configuraciones/{clave}/restaurar` | Restaurar una configuración |

Tipos de valor disponibles: `string`, `integer`, `boolean`, `json`

#### Sesiones

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/api/sesiones` | Listar todas las sesiones |
| GET | `/api/sesiones/eliminadas` | Listar sesiones eliminadas |
| POST | `/api/sesiones` | Crear una sesión |
| GET | `/api/sesiones/{id}` | Obtener una sesión |
| PATCH | `/api/sesiones/{id}` | Actualizar una sesión |
| DELETE | `/api/sesiones/{id}` | Eliminar una sesión (borrado lógico) |
| PATCH | `/api/sesiones/{id}/restaurar` | Restaurar una sesión |

---

## Borrado Lógico

Todos los recursos implementan **borrado lógico** (soft delete):

- Al ejecutar `DELETE`, el registro no se elimina físicamente de la base de datos
- Se marca con un campo `deleted_at` con la fecha de eliminación
- Los registros eliminados no aparecen en las consultas estándar
- Se pueden restaurar mediante el endpoint `/restaurar`
- Para incluir registros eliminados en consultas, usar `?incluir_inactivos=true`

---

## Respuestas de la API

### Estructura de respuesta exitosa

```json
{
    "success": true,
    "data": { },
    "message": "Operación completada exitosamente"
}
```

### Estructura de respuesta de error

```json
{
    "success": false,
    "message": "Descripción del error",
    "errors": { }
}
```

### Códigos de estado HTTP

| Código | Descripción |
|--------|-------------|
| 200 | OK - Solicitud procesada correctamente |
| 201 | Created - Recurso creado exitosamente |
| 404 | Not Found - Recurso no encontrado |
| 422 | Unprocessable Entity - Error de validación |
| 500 | Internal Server Error - Error del servidor |

---

## Pruebas

### Usando cURL

```bash
# Listar categorías
curl -X GET http://localhost:8000/api/categorias

# Crear una categoría
curl -X POST http://localhost:8000/api/categorias \
  -H "Content-Type: application/json" \
  -d '{"nombre":"Electrónicos","descripcion":"Productos electrónicos"}'

# Obtener una categoría
curl -X GET http://localhost:8000/api/categorias/1

# Actualizar una categoría
curl -X PATCH http://localhost:8000/api/categorias/1 \
  -H "Content-Type: application/json" \
  -d '{"nombre":"Electrónica"}'

# Eliminar una categoría
curl -X DELETE http://localhost:8000/api/categorias/1

# Restaurar una categoría
curl -X PATCH http://localhost:8000/api/categorias/1/restaurar
```

### Usando Postman

1. Importar la colección de endpoints
2. Configurar la variable `base_url` como `http://localhost:8000/api`
3. Ejecutar las solicitudes según sea necesario

---

## Estructura del Proyecto

```
multiBD/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Api/                 # Controladores de la API REST
│   ├── Livewire/                    # Componentes Livewire (interfaz web)
│   ├── Models/                      # Modelos Eloquent (MySQL y MongoDB)
│   └── Services/                    # Servicios para Redis
├── config/
│   └── database.php                 # Configuración de conexiones
├── database/
│   └── migrations/                  # Migraciones de MySQL
├── resources/
│   └── views/
│       ├── components/layouts/      # Layouts de la aplicación
│       └── livewire/                # Vistas de componentes Livewire
├── routes/
│   ├── api.php                      # Rutas de la API REST
│   └── web.php                      # Rutas de la interfaz web
└── .env                             # Variables de entorno
```

---

## Interfaz Web

La aplicación incluye una interfaz web desarrollada con Livewire que permite:

- Visualizar estadísticas de todas las bases de datos
- Gestionar recursos de MySQL (CRUD completo)
- Consultar logs y comentarios de MongoDB
- Administrar configuraciones y sesiones de Redis
- Restaurar registros eliminados

Acceder a la interfaz en: http://localhost:8000

---

## Consideraciones Técnicas

### MySQL
- Utiliza Eloquent ORM para el mapeo objeto-relacional
- Implementa SoftDeletes para borrado lógico
- Relaciones definidas: Categorías -> Productos, Clientes -> Órdenes, Órdenes <-> Productos

### MongoDB
- Utiliza el paquete `mongodb/laravel-mongodb`
- Los documentos tienen estructura flexible
- El campo `_id` es de tipo ObjectId

### Redis
- Utiliza el cliente Predis
- Los datos se almacenan como JSON serializado
- Soporta TTL (tiempo de vida) para sesiones

---

## Autor

Proyecto desarrollado para la materia de Bases de Datos.

## Licencia

Este proyecto es de uso académico.
