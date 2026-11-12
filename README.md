# Running - Pedidos y delivery

Aplicación web para administrar negocios y menús, permitir pedidos de clientes,
gestionar entregas y recomendar productos y restaurantes usando reglas de
asociación Apriori. La aplicación principal está hecha con Laravel 12,
Livewire, Filament y SQLite. El entrenamiento de recomendaciones es un proceso
Python opcional que lee y escribe en la misma base SQLite.

## Funcionalidades

- Registro e inicio de sesión con roles de cliente, repartidor, propietario y
  administrador.
- Administración de negocios, productos y pedidos desde Filament.
- Carrito y pedidos asociados a una cuenta de cliente.
- Seguimiento del pedido y sugerencias de restaurantes.
- Selección del punto de entrega en el mapa y ubicación GPS del repartidor
  mientras tiene pedidos en camino. El repartidor debe autorizar compartir la
  ubicación desde su dispositivo.
- Verificación y recuperación de cuenta por correo cuando se configura un
  transportista SMTP.
- CAPTCHA Cloudflare Turnstile opcional.
- Entrenamiento Apriori para sugerencias de productos y restaurantes. Requiere
  volver a ejecutar el entrenador cuando haya nuevos pedidos entregados.

## Requisitos

- PHP `^8.2` y extensiones requeridas por Laravel/PDO SQLite.
- Composer.
- Node.js y npm para compilar los recursos del frontend.
- SQLite.
- Python 3 y pip solo para entrenar Apriori o ejecutar la API opcional.

Después de instalar dependencias, puedes comprobar los requisitos PHP con:

```bash
composer check-platform-reqs
php -m | grep -E 'pdo_sqlite|sqlite3'
```

## Instalación local

Clona el repositorio y entra al directorio:

```bash
git clone https://github.com/Beca0999/gestion-pedidos-delivery.git
cd gestion-pedidos-delivery
```

Instala dependencias PHP y JavaScript:

```bash
composer install
npm install
```

Prepara el entorno y el archivo SQLite:

```bash
cp .env.example .env
touch database/database.sqlite
php artisan key:generate
```

En `.env`, confirma que SQLite esté seleccionado:

```env
DB_CONNECTION=sqlite
```

Ejecuta las migraciones, crea los datos de demostración y genera el enlace de
almacenamiento para imágenes:

```bash
php artisan migrate --seed
php artisan storage:link
```

Compila los recursos y limpia cualquier configuración cacheada:

```bash
npm run build
php artisan optimize:clear
```

Inicia Laravel:

```bash
php artisan serve
```

Abre <http://127.0.0.1:8000>.

> Si ya tienes datos que quieres conservar, usa `php artisan migrate --seed`.
> Evita `php artisan migrate:fresh`: elimina todas las tablas y su información.

## Cuentas de demostración

El seeder crea estas cuentas locales con la contraseña inicial `password`:

| Rol | Correo |
|---|---|
| Administrador | `admin@example.test` |
| Propietario | `owner@example.test` |
| Repartidor | `rider@example.test` |
| Cliente | `customer@example.test` |

También crea datos de ejemplo para probar negocios, menús y pedidos. Estas
credenciales son únicamente para desarrollo: no las uses en un servidor público
ni en producción. Si el seeder se ejecuta en una base que ya tiene esos
usuarios, conserva las cuentas existentes.

## Mapa y seguimiento GPS

1. Inicia sesión con la cuenta de cliente, agrega productos y completa nombre,
   teléfono y dirección.
2. Marca el punto exacto de entrega en el mapa del formulario, o pulsa **Usar
   mi ubicación** y permite el acceso del navegador. La ubicación destino se
   guarda con el pedido.
3. En `/admin`, asigna el pedido a un repartidor y actualiza su estado a **En
   Camino**.
4. El repartidor inicia sesión y abre **Mis entregas** (`/rider`). Debe pulsar
   **Compartir GPS** y autorizar la ubicación. El cliente ve el GPS reciente y
   el destino en el seguimiento del pedido.
5. El repartidor puede pulsar **Detener** para dejar de compartir. La aplicación
   oculta la última ubicación si tiene más de dos minutos.

El mapa usa Leaflet y mosaicos de OpenStreetMap desde servicios públicos, sin
una llave de Google Maps. Se requiere conexión a internet para cargar esos
recursos. El navegador solo permite usar GPS en un contexto seguro: `localhost`
o una página HTTPS. Para probar desde otro dispositivo, usa una URL HTTPS
pública; no compartas un túnel temporal con personas no autorizadas.

## Configuración de correo

El correo es necesario para enviar enlaces reales de verificación y recuperación
de contraseña. Configura los valores `MAIL_*` en `.env`. No compartas ese
archivo ni guardes sus valores reales en el repositorio. Para Gmail:

1. Inicia sesión en la cuenta que enviará los correos y activa la verificación
   en dos pasos desde [Seguridad de la cuenta Google](https://myaccount.google.com/security).
2. En la misma cuenta crea una **Contraseña de aplicación**. Google solo permite
   esta opción si la verificación en dos pasos está activa.
3. Copia la contraseña de aplicación generada. No uses la contraseña normal de
   Gmail. Si Google la muestra separada en grupos, pégala sin espacios.
4. Completa las variables en el archivo `.env` local:

```env
MAIL_MAILER=smtp
MAIL_SCHEME=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=tu_direccion@gmail.com
MAIL_PASSWORD=tu_contrasena_de_aplicacion_de_google
MAIL_FROM_ADDRESS="${MAIL_USERNAME}"
MAIL_FROM_NAME="${APP_NAME}"
```

5. Guarda el archivo y limpia la configuración cacheada:

```bash
php artisan optimize:clear
```

6. Regístrate o solicita un nuevo correo de verificación/reset para probar el
   envío. Los mensajes enviados antes de cambiar la configuración no se vuelven
   a generar solos.

Si usas otro proveedor SMTP, sustituye host, puerto, esquema, usuario y
contraseña por los valores que indique ese proveedor.

En desarrollo local, el correo puede apuntar a `localhost`. Si abres el enlace
desde otro dispositivo, este debe usar una dirección HTTPS pública que alcance
tu aplicación. Un túnel temporal de Cloudflare puede servir para probar:

1. Inicia `php artisan serve` y deja esa terminal abierta.
2. En otra terminal ejecuta `cloudflared tunnel --url http://localhost:8000`.
3. Copia la URL `https://....trycloudflare.com` mostrada por el túnel en
   `APP_URL` dentro de `.env`.
4. Ejecuta `php artisan optimize:clear` y solicita un correo nuevo.

La URL rápida cambia cuando reinicias el túnel y deja de funcionar al cerrarlo;
los enlaces enviados anteriormente no se actualizan.

## CAPTCHA (Cloudflare Turnstile, opcional)

Turnstile está desactivado de forma predeterminada. Para activarlo:

1. Inicia sesión en Cloudflare y abre la sección **Turnstile** del panel.
2. Registra un widget para el nombre del proyecto y agrega los dominios donde
   lo vas a probar o publicar. Para una URL temporal, autoriza el hostname del
   túnel actual; tendrás que cambiarlo si Cloudflare asigna otro.
3. Selecciona el modo de widget deseado y crea el widget.
4. Copia **Site Key** y **Secret Key** en las variables indicadas abajo. La
   Site Key se usa en la página; la Secret Key solo debe quedar en el `.env`
   del servidor.
5. Limpia la configuración y prueba registro, inicio de sesión o recuperación
   de contraseña.

```env
TURNSTILE_ENABLED=true
TURNSTILE_SITE_KEY=pega_aqui_la_site_key
TURNSTILE_SECRET_KEY=pega_aqui_la_secret_key
```

No uses valores de ejemplo ni publiques la clave secreta. Si aún no tienes un
widget real, deja `TURNSTILE_ENABLED=false` y ambas claves vacías. Después de
editar el entorno, limpia la configuración:

```bash
php artisan optimize:clear
```

## Recomendaciones Apriori

El sitio Laravel consulta las tablas de recomendaciones directamente en SQLite;
no necesita iniciar Flask para mostrar sugerencias. Para instalar y correr el
entrenamiento:

```bash
python3 -m venv .venv
./.venv/bin/pip install -r python/requirements.txt
PYTHONPATH=python .venv/bin/python -m train_recommendations --min-support 0.02
```

El entrenador usa pedidos con estado `Entregado`, sus productos y clientes. Al
final guarda las reglas en `product_recommendations` y
`business_recommendations`. Los datos del seeder son sintéticos y solo sirven
para probar; no representan las preferencias de clientes reales. Después de
agregar pedidos entregados nuevos, vuelve a ejecutar el entrenamiento para
actualizar las reglas.

La explicación integral del algoritmo, las métricas y el recorrido desde SQLite
hasta la interfaz está en [python/README_FLUJO_APRIORI.md](python/README_FLUJO_APRIORI.md).
Los pasos y comandos de Python también están en
[python/README.md](python/README.md).

La API Flask de recomendaciones es opcional. Para iniciarla en otra terminal:

```bash
PYTHONPATH=python .venv/bin/python -m recommendation_api
```

Por defecto escucha en `http://127.0.0.1:5000`. Sus endpoints de consulta
incluyen:

- `GET /api/recommendations?product_id=1`
- `GET /api/businesses/1/recommendations`

Si la base SQLite está en otra ruta, configura `LARAVEL_DB_PATH` tanto para el
entrenador como para la API; asegúrate de que Laravel y Python lean la misma
base.

## Pagos con Mercado Pago (opcional)

Para permitir pagos con tarjeta en pruebas:

1. Crea una cuenta en [Mercado Pago Developers](https://www.mercadopago.com.mx/developers/panel)
   e inicia sesión.
2. Crea o selecciona una aplicación para Running.
3. Abre sus credenciales de **Prueba** y copia el **Access Token de prueba**.
   No confundas el token privado con una clave pública.
4. Guarda el token únicamente en tu `.env` local y activa el modo sandbox:

```env
MERCADOPAGO_ACCESS_TOKEN=pega_aqui_el_access_token_de_prueba
MERCADOPAGO_SANDBOX=true
```

5. Ejecuta `php artisan optimize:clear` y prueba con las cuentas/tarjetas de
   prueba que proporciona Mercado Pago.

No publiques el token ni lo pongas en código del frontend. En producción, usa
las credenciales de producción solo en el servidor, con `MERCADOPAGO_SANDBOX=false`
y una URL pública HTTPS configurada en `APP_URL`. Consulta
[docs/PAGOS.md](docs/PAGOS.md) para más información.

## Protección de configuración

El `.env` local está excluido por `.gitignore`; comparte solo `.env.example`,
que contiene nombres de variables sin secretos. Antes de publicar cambios,
comprueba que el archivo no esté agregado al índice:

```bash
git status --short
git check-ignore -v .env
```

No pegues valores de credenciales en README, capturas, mensajes de commit ni
issues públicos. Si una clave real se compartió por error, revócala y genera
una nueva desde el proveedor correspondiente.

## Consultar SQLite

La base de datos local es `database/database.sqlite`. Puedes abrir ese archivo
con DB Browser for SQLite, o usar la herramienta `sqlite3`:

```bash
sqlite3 database/database.sqlite
```

Consultas útiles dentro de SQLite:

```sql
.tables
.headers on
.mode column

SELECT id, customer_name, business_id, status, total FROM orders;
SELECT product_id, recommended_product_id, support, confidence, lift
FROM product_recommendations;
SELECT business_id, recommended_business_id, support, confidence, lift
FROM business_recommendations;
```

## Pruebas

Ejecuta las pruebas Laravel:

```bash
php artisan test
```

Pruebas del algoritmo Python:

```bash
PYTHONPATH=python .venv/bin/python -m unittest discover -s python -p 'test_*.py'
```

## Rutas principales

| Ruta | Uso |
|---|---|
| `/` | Tienda y lista de negocios |
| `/login` | Inicio de sesión |
| `/customer/register` | Registro de cliente |
| `/rastreo/{id}` | Seguimiento del pedido del cliente autenticado |
| `/rider` | Entregas y controles GPS del repartidor |
| `/admin` | Administración de negocios, productos y pedidos |

## Seguridad y datos locales

- No compartas el archivo `.env`, claves de correo, Mercado Pago o Turnstile.
- No publiques la contraseña de las cuentas de demostración.
- No uses datos sintéticos ni credenciales de desarrollo como configuración de
  producción.
- La ubicación GPS se comparte con autorización del repartidor y solo se
  considera actual durante un periodo breve.
