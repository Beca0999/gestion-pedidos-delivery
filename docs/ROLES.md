# Manual de Roles en RUNNING 🚀

## 1. El Administrador / Dueño 🔑
Es el encargado de gestionar la logística desde `/admin`.
* **Acceso:** Usuario y contraseña creados con `php artisan make:filament-user`.
* **Funciones:**
    * Crear Negocios y Productos (con fotos).
    * **Asignación:** Debe cambiar el estatus de la orden a "En Preparación" y luego a "En Camino", eligiendo a un repartidor de la lista.
    * **Comunicación:** Botón directo para enviar ticket por WhatsApp al cliente o instrucciones al repartidor.

## 2. El Repartidor (Rider) 🛵
No necesita usuario/contraseña de Laravel, usa un acceso rápido por número de teléfono.
* **Acceso:** `/rider`. El código de acceso es su **número de teléfono** registrado por el Admin.
* **Funciones:**
    * Ver **únicamente** los pedidos que tiene asignados.
    * Botón de **Mapa** para abrir Google Maps con las coordenadas GPS que fijó el cliente.
    * Botón de **Llamar** para contactar al cliente.
    * Botón **Entregado** para finalizar el pedido y desaparecer la burbuja de rastreo.
