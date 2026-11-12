# Recomendaciones de productos con Apriori

Para una explicación del algoritmo y de cómo se conecta todo el sistema,
consulta [README_FLUJO_APRIORI.md](./README_FLUJO_APRIORI.md).

1. Ejecuta las migraciones y crea datos de ejemplo desde la raíz de Laravel:
   `php artisan migrate --seed`.
   El seeder crea 15 restaurantes con menús distintos y pedidos de demostración
   entregados, organizados en grupos para que Apriori pueda encontrar asociaciones.
2. Instala las dependencias del microservicio y del ORM:
   `python3 -m venv .venv` y `./.venv/bin/pip install -r python/requirements.txt`.
   La persistencia usa SQLAlchemy ORM; no requiere pandas ni mlxtend.
3. Genera reglas Apriori sobre órdenes entregadas:
   `PYTHONPATH=python .venv/bin/python -m train_recommendations --min-support 0.02`.
   El umbral menor permite que el historial de demostración, que es pequeño,
   produzca suficientes reglas entre restaurantes.
   Opcionalmente configura `LARAVEL_DB_PATH` si SQLite no está en
   `database/database.sqlite`.
4. Inicia el servicio: `PYTHONPATH=python .venv/bin/python -m recommendation_api`. El endpoint
   `GET /api/recommendations?product_id=1` responde con hasta tres productos,
   incluso si pertenecen a otros restaurantes. El endpoint
   `GET /api/businesses/1/recommendations` responde con hasta tres restaurantes
   relacionados.

Las reglas de productos se entrenan por negocio: las cestas de un cliente se
agrupan dentro de cada restaurante, así las recomendaciones de una pizzería no
se mezclan con las de una hamburguesería. Solo se usan pedidos entregados; los
pedidos históricos sin `customer_id` se cuentan individualmente por pedido.
Las recomendaciones entre restaurantes siguen usando el historial entre
negocios de cada cliente. Después de entregar nuevos pedidos, vuelve a ejecutar
el entrenador para refrescar ambas tablas. Los pedidos históricos no se vinculan
automáticamente a clientes ni se infiere su dueño por nombre o teléfono.

Al abrir cualquier negocio, el menú muestra hasta tres recomendaciones de
productos generadas por Apriori para ese negocio. Un negocio nuevo muestra un
aviso hasta contar con compras entregadas que produzcan reglas; las sugerencias
no se inventan ni se copian de otro restaurante.

El envío gratis se calcula por pedido y restaurante: si el subtotal de productos
alcanza $300 MXN, se descuenta el costo de envío. No combina carritos ni pedidos
de varios restaurantes.

Para administrar el catálogo, entra a `/admin`, crea el negocio en **Negocios** y
después agrega sus productos en **Mi Menú**. El dueño solo puede asignar
productos a negocios que le pertenecen.

Las recomendaciones entre restaurantes requieren historial entregado del mismo
cliente en al menos dos negocios. El menú solo mostrará sugerencias cuando la
tabla de recomendaciones tenga reglas; después de registrar pedidos entregados
en distintos negocios, vuelve a ejecutar `python python/train_recommendations.py`.
Tras sembrar los datos de demostración, ejecuta también ese comando de entrenamiento
con `--min-support 0.02` para poblar las reglas y ver recomendaciones de restaurantes
en la página. Con más historial real, puedes volver al valor predeterminado `0.05`.

Para probar los accesos locales, el seeder crea `admin@example.test`,
`owner@example.test`, `rider@example.test` y `customer@example.test`; la
contraseña inicial es `password` para esas cuentas de demostración. No uses
estas credenciales en producción.
