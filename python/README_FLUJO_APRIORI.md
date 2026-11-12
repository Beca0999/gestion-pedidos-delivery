# Flujo de recomendaciones con Apriori

Esta guía explica cómo se conectan los pedidos de Running con el entrenamiento
Apriori, las tablas de recomendaciones y las sugerencias que aparecen en la
aplicación. Está pensada como introducción para el equipo; para instalar y
ejecutar los comandos rápidamente, consulta [README.md](./README.md).

## Resumen del flujo

```text
Pedidos + productos en SQLite
              │
              ▼
train_recommendations.py
  1. Lee pedidos entregados
  2. Agrupa compras en canastas
  3. Calcula reglas A → B con Apriori
  4. Reemplaza las reglas anteriores
              │
              ▼
product_recommendations
business_recommendations
              │
              ▼
Servicios Laravel consultan SQLite
              │
              ▼
Livewire muestra sugerencias en el menú y la página principal
```

El entrenamiento es por lotes: **no se recalcula automáticamente** cada vez que
se confirma o entrega un pedido. Después de acumular pedidos entregados hay que
volver a ejecutar el comando de entrenamiento.

## Qué datos usa

La fuente de datos es la misma base SQLite de Laravel, normalmente
`database/database.sqlite`.

| Tabla | Uso en el algoritmo |
|---|---|
| `orders` | Pedido, cliente, negocio y estado. Solo cuentan filas con `status = 'Entregado'`. |
| `order_product` | Relaciona cada pedido con los productos que compró. |
| `products` | Proporciona el producto y el negocio al que pertenece. |
| `businesses` | Catálogo de negocios usado al mostrar recomendaciones. |

El algoritmo trabaja con **IDs**, no con nombres, precios, direcciones,
calificaciones ni categorías. Las cantidades tampoco ponderan la recomendación:
una canasta es un conjunto, así que comprar cuatro unidades de un producto
cuenta igual que comprar una.

### Canastas de productos

En `load_product_baskets` (`train_recommendations.py`) se leen los productos de
pedidos entregados. La clave de cada canasta combina:

- el negocio;
- el cliente, si el pedido tiene `customer_id`;
- o el ID del pedido cuando no hay cliente asociado.

Esto agrupa el historial entregado de un mismo cliente **dentro de un mismo
negocio**. Así no se mezclan los productos de diferentes menús. Los pedidos sin
cliente no se asocian a una persona por teléfono, nombre o correo: cada pedido
anónimo se mantiene por separado.

### Canastas de negocios

En `load_business_baskets` se agrupan negocios por cliente, usando todos sus
pedidos entregados. Por ejemplo, si un cliente ya tuvo pedidos entregados en
los negocios 4 y 9, su canasta es `{4, 9}`. Si no tiene `customer_id`, ese
pedido forma una canasta independiente; por tanto, un pedido anónimo no crea
una asociación con otros negocios.

## Cómo calcula Apriori las reglas

La función `mine_pair_rules` recibe canastas como estas:

```text
Productos:  {hamburguesa, papas}, {hamburguesa, refresco}, {hamburguesa, papas}
Negocios:   {negocio 4, negocio 9}, {negocio 4}, {negocio 4, negocio 9}
```

El algoritmo cuenta primero cada elemento y luego los conjuntos frecuentes.
Para una regla `A → B` calcula:

- **Soporte:** proporción de canastas que contienen A y B.
  `canastas con A y B / total de canastas`.
- **Confianza:** proporción de canastas con A que también contienen B.
  `canastas con A y B / canastas con A`.
- **Lift:** confianza dividida entre la proporción de canastas que contienen B.
  `confianza / soporte de B`. Un lift mayor que 1 indica que B aparece junto
  con A más frecuentemente de lo que su popularidad general sugeriría.

Por cada par frecuente `{A, B}` se consideran ambas reglas (`A → B` y `B → A`):
la confianza puede ser diferente en cada dirección. Los umbrales por defecto
están en `MIN_SUPPORT` (0.05) y `MIN_CONFIDENCE` (0.10); pueden cambiarse desde
la terminal. En la base de demostración se usa con frecuencia soporte `0.02`
porque hay menos historial.

**Alcance actual:** el código explora candidatos de tamaño creciente, pero
solo convierte conjuntos de exactamente dos elementos en reglas y solo
persisten reglas de un antecedente y un consecuente. No genera reglas como
`A y B → C`.

## Cómo se entrena y escribe en SQLite

Desde la raíz del proyecto:

```bash
PYTHONPATH=python .venv/bin/python -m train_recommendations --min-support 0.02
```

Si aún no se instalaron los paquetes de Python, la preparación está explicada
en [README.md](./README.md). El comando también permite definir `--min-confidence`
y `--database`. Si se usa otra ruta de base de datos, Laravel debe leer esa
misma base para que aparezcan las recomendaciones.

`train()` crea las tablas de salida si todavía no existen, calcula por separado
las reglas de productos y de negocios, y reemplaza las recomendaciones
anteriores dentro de una transacción:

- `product_recommendations`: `product_id` (antecedente),
  `recommended_product_id` (consecuente), soporte, confianza y lift.
- `business_recommendations`: `business_id` (antecedente),
  `recommended_business_id` (consecuente), soporte, confianza y lift.

El entrenador borra los resultados previos de esas tablas y guarda el resultado
completo nuevo. No borra pedidos, productos ni negocios. Al terminar imprime
cuántas reglas guardó de cada tipo.

Se pueden inspeccionar las reglas con SQLite:

```sql
SELECT
    source.name AS producto,
    recommended.name AS recomendado,
    round(rule.support, 3) AS soporte,
    round(rule.confidence, 3) AS confianza,
    round(rule.lift, 3) AS lift
FROM product_recommendations AS rule
JOIN products AS source ON source.id = rule.product_id
JOIN products AS recommended ON recommended.id = rule.recommended_product_id
ORDER BY rule.confidence DESC, rule.lift DESC;
```

## Cómo Laravel las convierte en sugerencias

El sitio Laravel consulta las tablas de SQLite directamente; no necesita que el
servidor Flask esté ejecutándose para mostrar estas sugerencias:

1. En [QuickOrder.php](../app/Livewire/QuickOrder.php), al agregar productos a
   la bolsa o quitarlos, se consultan recomendaciones asociadas con los
   productos que quedan seleccionados. Las sugerencias aparecen junto al menú
   cuando hay reglas disponibles.
2. [ProductRecommendationService.php](../app/Services/ProductRecommendationService.php)
   consulta `product_recommendations`, ordena las reglas por confianza y lift,
   descarta productos ya agregados y limita la lista a tres sugerencias por
   defecto. En el menú Livewire, los productos recomendados deben pertenecer al
   mismo negocio que los productos de origen.
3. [BusinessRecommendationService.php](../app/Services/BusinessRecommendationService.php)
   lee `business_recommendations` para un negocio. En la página principal, si el
   cliente tiene un pedido entregado, se toma su negocio entregado más reciente
   y se buscan negocios asociados. Si no hay reglas para mostrar, la página
   puede presentar los negocios populares como alternativa; esa lista es un
   fallback por cantidad de pedidos entregados, no una regla Apriori.
4. Los controladores de recomendaciones de Laravel exponen esos mismos
   servicios mediante rutas API. También existe una API Flask en
   [recommendation_api.py](./recommendation_api.py), que lee las tablas de
   recomendaciones de la base configurada; es una interfaz opcional, separada
   de la lectura que hace el sitio.

## Datos de demostración y significado de las sugerencias

[DatabaseSeeder.php](../database/seeders/DatabaseSeeder.php) crea negocios,
productos y pedidos sintéticos con estado `Entregado`. Las cestas están
organizadas para que ciertas combinaciones aparezcan repetidamente y se puedan
probar las reglas; algunos grupos de clientes también tienen pedidos en varios
negocios para permitir recomendaciones entre negocios.

Por lo tanto, las sugerencias que aparecen después de sembrar son una
**demostración del cálculo**, no evidencia de preferencias reales. Apriori
detecta coocurrencias en el historial disponible. No sabe si dos productos
son parecidos, si un negocio está cerca, si el usuario calificó algo bien o si
una asociación es causal.

Para observar recomendaciones basadas en clientes reales, hacen falta pedidos
correctamente asociados a `customer_id`, con sus productos en `order_product`
y con estado `Entregado`. Luego se vuelve a correr el entrenamiento. Si la
tabla de reglas queda vacía, revisa que los pedidos entregados existan, que
tengan productos y que los umbrales sean razonables para el volumen de datos.

## Pruebas y archivos relacionados

- `python/test_train_recommendations.py`: prueba los cálculos de soporte,
  confianza y lift, y verifica que las reglas se guarden por separado.
- `python/recommendation_models.py`: define los modelos SQLAlchemy para las
  tablas de entrada y salida.
- `python/recommendation_database.py`: configura la conexión SQLite y sus
  claves foráneas.
- `database/seeders/DatabaseSeeder.php`: prepara los datos de demostración.
- `app/Services/ProductRecommendationService.php` y
  `app/Services/BusinessRecommendationService.php`: consultan las reglas desde
  Laravel.
- `app/Livewire/QuickOrder.php`: conecta los servicios con la interfaz de la
  tienda.

Si cambian los nombres de las tablas/columnas, los filtros de pedidos o la
forma de agrupar canastas, hay que revisar en conjunto el entrenador Python,
los modelos SQLAlchemy, los servicios Laravel y sus pruebas.
