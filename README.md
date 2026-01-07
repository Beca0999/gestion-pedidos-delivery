# gestion-pedidos-delivery
MVP de sistema de pedidos tipo delivery usando Laravel, FilamentPHP y SQLite.

#  gestion-pedidos-delivery – Laravel + FilamentPHP

Este proyecto es un **Producto Mínimo Viable (MVP)** diseñado para la gestión de pedidos tipo delivery, priorizando la **agilidad de desarrollo**, la **simplicidad técnica** y la **mínima fricción de configuración**.

El sistema está construido sobre el ecosistema de **Laravel**, utilizando **FilamentPHP** como panel administrativo y **SQLite** como motor de base de datos.

---

## 🎯 Objetivo del Proyecto

Crear una plataforma funcional que permita:

- Registrar múltiples negocios y sus productos
- Recibir pedidos sin autenticación de clientes
- Gestionar el estado de los pedidos desde un panel administrativo
- Ejecutar el sistema de forma local sin configuraciones complejas

Este MVP está pensado para validación temprana del flujo de pedidos, no como un sistema de producción a gran escala.

---

## 🏗️ Arquitectura General

### Backend (Panel de Control)

El backend es el núcleo del sistema y está construido con **FilamentPHP**.

Características principales:

- Acceso directo al dashboard (sin sistema de contraseñas tradicional)
- CRUD de Negocios
- CRUD de Productos asociados a cada negocio (relación uno a muchos)
- Gestión de pedidos (Deliverys) con estados:
  - Pendiente
  - En Camino
  - Entregado

### Base de Datos

- **SQLite**
- Base de datos contenida en un solo archivo
- Alta portabilidad y cero configuración adicional

---

## 🧑‍💻 Frontend (Cliente)

El frontend se concibe como una interfaz ligera orientada a la toma rápida de pedidos.

Características:

- Formulario reactivo usando **Livewire**
- Selección de negocio y productos
- Captura de datos básicos del cliente:
  - Nombre
  - Dirección
  - Teléfono
- No requiere autenticación
- Cada pedido se registra de forma independiente

---

## 🛠️ Requisitos del Entorno

Para ejecutar este proyecto necesitas tener instalado:

- **Git**
- **Composer**
- **Laravel Herd**

> Herd proporciona PHP y servidor web preconfigurado, alineado con el objetivo de eliminar fricción técnica en este MVP.

---

## 🚀 Flujo General del Sistema

1. El administrador registra negocios y productos desde el panel.
2. El cliente accede al formulario de pedidos.
3. El pedido se guarda directamente en la base de datos.
4. El administrador visualiza y actualiza el estado del pedido en tiempo real.

---

## 📌 Estado del Proyecto

Este proyecto se encuentra en fase inicial de desarrollo como MVP.

---

## 📄 Licencia

Uso académico y de portafolio.
