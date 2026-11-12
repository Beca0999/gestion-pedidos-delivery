# Pizarrón (Activity Board)

Resumen
-------
El `pizarrón` muestra la actividad constante de operaciones HTTP (POST, GET, PUT) en la aplicación para un rango reciente (por defecto últimos 5 minutos).

Arquitectura
-----------
- Se registra cada petición en la tabla `activity_logs` por medio del middleware `App\Http\Middleware\LogActivity`.
- Un endpoint API (`GET /api/pizarron/activity`) devuelve:
  - `counts`: conteo por método HTTP en el intervalo solicitado
  - `recent`: últimas 50 entradas con método, ruta, usuario e IP

Migración
---------
Se agregó la migración `database/migrations/2026_09_08_000001_create_activity_logs_table.php`.
Ejecuta:

```bash
php artisan migrate
```

Middleware
----------
El middleware `App\Http\Middleware\LogActivity` registra la petición y el código HTTP de respuesta.
Se añadió automáticamente al grupo `web` y `api` en `App\Providers\AppServiceProvider`.

API
---
- `GET /api/pizarron/activity?minutes=5`
  - `minutes` (opcional): número de minutos a considerar (por defecto 5)
  - Respuesta JSON:

```json
{
  "minutes": 5,
  "counts": {"GET": 12, "POST": 3, "PUT": 1},
  "recent": [ {"id":1, "method":"GET","path":"orders","user_id":null,"ip":"::1","created_at":"..."}, ... ]
}
```

Cómo probar localmente
----------------------
1. Ejecuta migraciones:

```bash
php artisan migrate
```

2. Simula tráfico en otra terminal para ver actividad (ejemplos):

```bash
# GET
curl -s http://127.0.0.1:8000/api/orders/count > /dev/null
# POST (ejemplo creando un pedido -- ajusta a tu API)
curl -s -X POST -d '{"_":"1"}' -H "Content-Type: application/json" http://127.0.0.1:8000/api/orders > /dev/null
# PUT (ejemplo)
curl -s -X PUT -d '{"_":"1"}' -H "Content-Type: application/json" http://127.0.0.1:8000/api/orders/1 > /dev/null
```

Validación y errores
--------------------
- Las acciones que crean pedidos validan campos obligatorios: `customer_name`, `phone`, `address`.
- Si se intenta crear un pedido sin esos campos la aplicación devolverá un error de validación. En la interfaz Livewire se muestra un mensaje legible en vez de un 500.


3. Consultar la API del pizarrón:

```bash
curl http://127.0.0.1:8000/api/pizarron/activity?minutes=1
```

Pasar a tiempo real
-------------------
Actualmente el sistema usa polling. Para tiempo real:
- Implementar broadcasting (Pusher o Redis + Socket server)
- Emitir eventos `OrderCreated`, `OrderUpdated` y escucharlos en el cliente

Notas
-----
- El middleware silencia errores de logging para evitar romper la petición original.
- Si prefieres no loggear todas las rutas, modifica `LogActivity` para filtrar por prefijos (`api/`, `orders`, etc.).
