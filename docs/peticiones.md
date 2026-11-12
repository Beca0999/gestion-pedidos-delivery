# Peticiones (Endpoints) del proyecto

Este documento lista las peticiones (endpoints) disponibles en la aplicación, ejemplos de payload/respuesta y un resumen tipo CRUD por recurso.

---

## Resumen rápido de recursos y CRUD

- `orders` — Sí tiene flujo CRUD en el panel de administración (Filament). En frontend público: CREATE (QuickOrder), READ (rastreo por id). UPDATE/DELETE se realizan desde Filament.
- `businesses` — CRUD en Filament (admin).
- `products` — CRUD en Filament (admin).
- `riders` — CRUD en Filament (admin).
- `activity_logs` — Solo lectura vía API (`/pizarron/activity`) para el pizarrón.

---

## Rutas públicas / web (principales)

- `GET /`
  - Descripción: Página principal (Livewire `QuickOrder`).
  - Acceso: público

- `GET /rastreo/{id}`
  - Descripción: Página de rastreo para un pedido (Livewire `OrderTracking`).
  - Acceso: público

- `GET /rider`
  - Descripción: Panel de rider (Livewire `RiderPanel`).
  - Acceso: según configuración (normalmente protegido)

- `GET /pizarron/activity`
  - Descripción: Alias web del endpoint de actividad (devuelve conteos por método y últimas entradas).
  - Query params: `minutes` (opcional, por defecto `5`)
  - Ejemplo respuesta:

```json
{
  "minutes": 5,
  "counts": {"GET": 12, "POST": 3, "PUT": 1},
  "recent": [ {"id":1, "method":"GET","path":"orders","user_id":null,"ip":"::1","created_at":"..."}, ... ]
}
```

---

## Rutas API

- `GET /api/orders/count`
  - Descripción: Devuelve el número total de órdenes.
  - Ejemplo respuesta:

```json
{ "count": 42 }
```

- `GET /api/pizarron/activity`
  - Descripción: Endpoint para el pizarrón que devuelve actividad reciente (mismos datos que `/pizarron/activity`).
  - Query params: `minutes` (opcional)

---

## Rutas generadas por Livewire (internas)

- `POST /livewire/update` — Livewire usa este endpoint para sincronizar componente y realizar acciones (por ejemplo `saveOrder`).
- `POST /livewire/upload-file` — subida de archivos por Livewire.

Nota: Estas rutas no son una API REST tradicional — requieren payloads formateados por Livewire.

---

## Ejemplos concretos de peticiones

1) Crear pedido (desde UI Livewire `QuickOrder`)

- Método: POST (a través de Livewire `livewire/update` internamente)
- Parámetros esperados (según validación en `QuickOrder`):
  - `customer_name` (string, obligatorio)
  - `phone` (string, obligatorio)
  - `address` (string, obligatorio)
  - `selectedBusiness` (business_id)
  - `selectedProducts` (array)
  - `payment_method` (string)

Si quieres probarlo desde cURL fuera de Livewire, puedes insertar directamente en BD (no recomendado) o crear una ruta API adicional que acepte JSON y llame a la misma lógica.

2) Consultar conteos para el pizarrón

```bash
curl 'http://127.0.0.1:8000/pizarron/activity?minutes=5'
```

3) Conteo de órdenes

```bash
curl 'http://127.0.0.1:8000/api/orders/count'
```

---

## Comportamiento CRUD por recurso (detallado)

- Orders
  - Create:
    - UI público: Livewire `QuickOrder::saveOrder()` crea la orden en DB.
    - Admin: Filament panel permite crear.
  - Read:
    - UI público: `/rastreo/{id}` muestra seguimiento.
    - Admin: Filament lista y muestra detalles.
  - Update:
    - Admin: Filament permite editar estado, asignar rider, etc.
    - No hay endpoints públicos para update directo (se actualiza desde panel o jobs).
  - Delete:
    - Admin: Filament (si está habilitado) permite borrar.

- Businesses / Products / Riders
  - CRUD completo a través del panel Filament (`/admin/...`) — requieren autenticación de administrador.

- ActivityLogs
  - Create: se registran automáticamente mediante `App\Http\Middleware\LogActivity` para grupos `web` y `api`.
  - Read: `GET /pizarron/activity` (resumen por método y últimas entradas).
  - Update/Delete: no aplican (histórico), aunque se podría añadir en Filament si se desea.

---

## Notas de implementación y recomendaciones

- Livewire maneja la mayor parte de la interacción del frontend, por lo que muchas acciones se envían a `POST /livewire/update` y son responsabilidad del componente.
- Si necesitas una API REST completa (por ejemplo para integraciones externas), propongo crear controladores API para `orders` que expongan endpoints JSON RESTful (`GET /api/orders`, `POST /api/orders`, `GET /api/orders/{id}`, `PUT /api/orders/{id}`, `DELETE /api/orders/{id}`) reutilizando la lógica existente.
- Para el pizarrón en tiempo real: usar broadcasting (Pusher o Redis + Socket server + Laravel Echo) y emitir eventos `OrderCreated`, `OrderUpdated`.

---

Si quieres que genere automáticamente los controladores API RESTful para `orders` (y rutas, validaciones y tests), dime y lo creo. También puedo agregar ejemplos curl más detallados para cada endpoint.
