# RUNNING 🚀 - Delivery System (MVP)

Este sistema está diseñado como un **Producto Mínimo Viable (MVP)** enfocado en la agilidad y la simplicidad técnica, utilizando el ecosistema de Laravel con FilamentPHP y SQLite para eliminar la fricción de configuración y gestión de servidores.

## 🧠 Filosofía del Proyecto

### Arquitectura y Backend (Panel de Control)
El corazón del sistema reside en el Backend, construido con **FilamentPHP**. Al ser un entorno de administración "sin contraseña", se configura un middleware de autenticación nula o un usuario único persistente que permite el acceso directo al dashboard.

* **Gestión de Negocios y Productos:** Se implementan CRUDs para que el administrador pueda registrar múltiples establecimientos. Cada negocio tiene una relación de "uno a muchos" con sus productos.
* **Base de Datos Eficiente:** El uso de **SQLite** permite que todo el sistema viva en un solo archivo, facilitando la portabilidad y eliminando la necesidad de configurar motores pesados.
* **Flujo de Pedidos:** El backend incluye una vista de "Gestión de Pedidos" donde caen las solicitudes en tiempo real, permitiendo al administrador cambiar el estado (Pendiente, En Preparación, En Camino, Entregado).

### Frontend (Interfaz de Usuario)
El Frontend se concibe como una interfaz ligera y funcional, orientada a la toma de pedidos rápida sin registros complicados.

* **Levantamiento de Pedidos:** Consiste en un formulario reactivo (usando **Livewire**) donde el cliente selecciona un negocio, elige sus productos y completa sus datos básicos.
* **Sin Autenticación:** Para maximizar la tasa de conversión en este MVP, el cliente no necesita crear una cuenta. La identidad se maneja por cada pedido individual.

---

## 📖 Documentación de Apoyo
Para la puesta en marcha y operación, consulta las siguientes guías:

1. 🛠️ [**Instalación Técnica**](docs/INSTALL.md) - Requisitos y comandos iniciales.
2. 💳 [**Pasarela de Pagos**](docs/PAGOS.md) - Integración con Mercado Pago.
3. 👥 [**Manual de Roles**](docs/ROLES.md) - Operación para el Dueño y el Repartidor (Rider).



## 📱 Accesos Rápidos (Desarrollo)
* **Cliente:** `/`
* **Admin:** `/admin`
* **Repartidores:** `/rider`

---
Desarrollado para la máxima agilidad operativa. 🚀
