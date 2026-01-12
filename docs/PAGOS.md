# Configuración de Mercado Pago 💳

Para que el botón de "Pagar con Tarjeta" funcione, se requiere una cuenta de Mercado Pago Developers.

## Pasos para obtener tus llaves:
1. Entra a [Mercado Pago Developers](https://www.mercadopago.com.mx/developers/panel).
2. Crea una "Aplicación" llamada **Running Delivery**.
3. En la sección **Credenciales de Producción**, copia el `Access Token`.
4. Pégalo en tu archivo `.env`:
   ```env
   MP_ACCESS_TOKEN=APP_USR-xxxx-xxxx
   ```

## Flujo del Dinero:
* Cuando el cliente paga, el dinero cae **directamente en la cuenta de Mercado Pago vinculada al Token**.
* El sistema genera una "Preferencia de Pago" que redirige al cliente a la pasarela segura de Mercado Pago.
* Al finalizar el pago, el cliente es devuelto a la pantalla de rastreo de su pedido.
