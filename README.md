# 🏪 Punto de Venta Arquitec

Sistema moderno de punto de venta (POS) desarrollado con **PHP 8+** y **Supabase** (PostgreSQL, Auth, Storage). Diseñado para negocios que buscan una solución rápida, segura y visualmente atractiva.

![Estado](https://img.shields.io/badge/estado-en%20desarrollo-blue)
![PHP](https://img.shields.io/badge/PHP-8+-777BB4?logo=php)
![Supabase](https://img.shields.io/badge/Supabase-PostgreSQL-3ECF8E?logo=supabase)

---

## 📑 Tabla de Contenidos

- [Características](#-características)
- [Requisitos](#-requisitos)
- [Instalación](#-instalación)
- [Configuración](#-configuración)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Uso](#-uso)
- [API Endpoints](#-api-endpoints)
- [Base de Datos](#-base-de-datos)
- [Tecnologías](#-tecnologías)
- [Licencia](#-licencia)

---

## ✨ Características

### Frontend
- ✅ Diseño **mobile-first** moderno y responsive
- ✅ Interfaz intuitiva tipo aplicación nativa
- ✅ Carrito de compras en tiempo real
- ✅ Búsqueda de productos instantánea
- ✅ Notificaciones toast para feedback
- ✅ Animaciones suaves y micro-interacciones
- ✅ Modo oscuro/listo para personalización

### Backend
- ✅ Autenticación segura con **Supabase Auth**
- ✅ Base de datos **PostgreSQL** en la nube
- ✅ Row Level Security (RLS) para protección de datos
- ✅ API RESTful con PHP
- ✅ Gestión de stock automática
- ✅ Control de caja y sesiones
- ✅ Reportes y estadísticas

### Funcionalidades POS
- ✅ Punto de venta rápido y eficiente
- ✅ Múltiples métodos de pago (efectivo, tarjeta, Yape/Plin)
- ✅ Generación de números de venta únicos
- ✅ Cálculo automático de IGV (18%)
- ✅ Gestión de clientes
- ✅ Control de inventario en tiempo real
- ✅ Historial de ventas

---

## 📋 Requisitos

### Servidor Local (Desarrollo)
- **XAMPP** o **WAMP** con:
  - PHP 8.0 o superior
  - Apache con `mod_rewrite` habilitado
  - Extensiones: `curl`, `json`, `mbstring`

### Producción
- Servidor web con PHP 8+
- Acceso a internet (para conectar con Supabase)
- SSL recomendado (HTTPS)

### Supabase
- Cuenta gratuita en [Supabase](https://supabase.com)
- Proyecto creado

---

## 🚀 Instalación

### Paso 1: Clonar/Descargar el Proyecto

```bash
# Copia la carpeta a tu htdocs de XAMPP
cp -r punto-venta-arquitec C:\xampp\htdocs\
```

### Paso 2: Configurar Supabase

1. Ve a [Supabase](https://supabase.com) y crea una cuenta
2. Crea un nuevo proyecto
3. Espera a que se provisione la base de datos
4. Ve a **Settings → API** y copia:
   - `Project URL`
   - `anon public` key
   - `service_role` key (mantener en secreto)

### Paso 3: Ejecutar Script SQL

1. En tu proyecto de Supabase, ve a **SQL Editor**
2. Copia y pega el contenido de `database/init.sql`
3. Ejecuta el script

Esto creará:
- Tablas: `profiles`, `products`, `customers`, `sales`, `sale_items`, `cash_register`
- Políticas de seguridad RLS
- Triggers automáticos
- Datos de ejemplo

### Paso 4: Configurar Variables de Entorno

1. Renombra `.env.example` a `.env` en la raíz del proyecto
2. Edita el archivo `.env` con tus credenciales:

```env
SUPABASE_URL="https://tu-project-id.supabase.co"
SUPABASE_ANON_KEY="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
SUPABASE_SERVICE_KEY="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9..."
APP_NAME="Punto de Venta Arquitec"
APP_URL="http://localhost/punto-venta-arquitec"
```

### Paso 5: Crear Usuario Admin

1. Ve a **Authentication → Users** en Supabase
2. Haz clic en **Add User**
3. Registra un usuario con email y contraseña
4. Ese usuario será el administrador inicial

### Paso 6: Acceder al Sistema

1. Abre tu navegador y ve a:
   ```
   http://localhost/punto-venta-arquitec
   ```
2. Inicia sesión con el usuario creado

---

## ⚙️ Configuración

### Estructura de Archivos

```
punto-venta-arquitec/
├── .env                      # Variables de entorno (NO subir a Git)
├── .env.example              # Plantilla de variables
├── index.php                 # Punto de entrada principal
├── dashboard.php             # Panel de administración
├── config/
│   └── supabase.php          # Cliente de Supabase y helpers
├── database/
│   └── init.sql              # Script de base de datos
├── pages/
│   ├── pos.php               # Página de punto de venta
│   └── login.php             # Página de login
├── actions/
│   ├── get_products.php      # Endpoint: obtener productos
│   ├── create_sale.php       # Endpoint: crear venta
│   ├── login.php             # Endpoint: autenticar
│   └── logout.php            # Endpoint: cerrar sesión
├── assets/
│   ├── css/
│   │   └── styles.css        # Estilos principales
│   ├── js/
│   │   └── main.js           # Lógica frontend
│   └── img/
│       └── placeholder.png   # Imagen por defecto
└── uploads/                  # Archivos subidos (imágenes)
```

---

## 📖 Uso

### Punto de Venta (POS)

1. **Buscar Productos**: Usa la barra de búsqueda para filtrar por nombre o SKU
2. **Agregar al Carrito**: Haz clic en cualquier tarjeta de producto
3. **Revisar Carrito**: Click en el botón "🛒 Carrito" para ver el resumen
4. **Seleccionar Método de Pago**: Efectivo, tarjeta, transferencia o Yape/Plin
5. **Procesar Venta**: Click en "Procesar Venta" para finalizar

### Dashboard

- **Estadísticas**: Visualiza ventas del día, mes y productos con stock bajo
- **Ventas Recientes**: Consulta las últimas transacciones
- **Gestión**: Navega por el menú lateral para acceder a productos, clientes, reportes

### Agregar Productos

Para agregar productos, usa el SQL Editor de Supabase:

```sql
INSERT INTO public.products (name, description, sku, price, cost, stock, category)
VALUES (
    'Producto Ejemplo',
    'Descripción del producto',
    'SKU-001',
    25.00,
    15.00,
    100,
    'General'
);
```

O crea una página de administración en `dashboard.php?page=products`.

---

## 🔌 API Endpoints

Todos los endpoints están en la carpeta `actions/` y retornan JSON.

### Autenticación

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/actions/login.php` | Iniciar sesión |
| POST | `/actions/logout.php` | Cerrar sesión |

**Ejemplo Login:**
```json
POST /actions/login.php
{
  "email": "admin@ejemplo.com",
  "password": "contraseña123"
}

Response:
{
  "success": true,
  "access_token": "eyJhbGciOiJIUzI1NiIs...",
  "user": {
    "id": "uuid",
    "name": "Administrador",
    "email": "admin@ejemplo.com",
    "role": "admin"
  }
}
```

### Productos

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| GET | `/actions/get_products.php` | Listar productos activos |

**Response:**
```json
{
  "success": true,
  "data": [
    {
      "id": "uuid",
      "name": "Producto A",
      "price": 25.00,
      "stock": 100,
      "image_url": "..."
    }
  ],
  "count": 10
}
```

### Ventas

| Método | Endpoint | Descripción |
|--------|----------|-------------|
| POST | `/actions/create_sale.php` | Registrar nueva venta |

**Ejemplo:**
```json
POST /actions/create_sale.php
{
  "items": [
    {
      "id": "uuid-producto",
      "name": "Producto A",
      "price": 25.00,
      "quantity": 2,
      "stock": 100
    }
  ],
  "payment_method": "cash",
  "notes": "Venta al contado"
}
```

---

## 🗄️ Base de Datos

### Tablas Principales

#### `profiles`
Información extendida de usuarios autenticados.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | UUID | FK a auth.users |
| name | VARCHAR | Nombre completo |
| email | VARCHAR | Correo electrónico |
| role | VARCHAR | admin, vendedor, cajero |
| avatar_url | TEXT | URL de foto de perfil |

#### `products`
Catálogo de productos.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | UUID | Identificador único |
| name | VARCHAR | Nombre del producto |
| sku | VARCHAR | Código único |
| price | DECIMAL | Precio de venta |
| cost | DECIMAL | Costo de compra |
| stock | INTEGER | Cantidad disponible |
| min_stock | INTEGER | Stock mínimo para alerta |
| category | VARCHAR | Categoría |
| is_active | BOOLEAN | Estado del producto |

#### `sales`
Cabecera de ventas.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | UUID | Identificador único |
| sale_number | VARCHAR | Número único de venta |
| customer_id | UUID | FK a customers |
| user_id | UUID | FK a profiles (vendedor) |
| subtotal | DECIMAL | Subtotal antes de impuestos |
| tax | DECIMAL | IGV (18%) |
| discount | DECIMAL | Descuentos aplicados |
| total | DECIMAL | Total final |
| payment_method | VARCHAR | cash, card, transfer, yape |
| payment_status | VARCHAR | paid, pending, cancelled |

#### `sale_items`
Detalle de productos por venta.

| Columna | Tipo | Descripción |
|---------|------|-------------|
| id | UUID | Identificador único |
| sale_id | UUID | FK a sales |
| product_id | UUID | FK a products |
| quantity | INTEGER | Cantidad vendida |
| price | DECIMAL | Precio unitario |
| subtotal | DECIMAL | Subtotal del item |

---

## 🛡️ Seguridad

### Row Level Security (RLS)

Las políticas implementadas aseguran que:

- ✅ Solo usuarios autenticados pueden acceder a datos
- ✅ Solo administradores pueden crear/eliminar productos
- ✅ Cada usuario puede gestionar su propia caja
- ✅ Todos los vendedores pueden registrar ventas

### Mejores Prácticas

1. **Nunca expongas** la `SUPABASE_SERVICE_KEY` en el frontend
2. Usa siempre **HTTPS** en producción
3. Valida datos tanto en frontend como en backend
4. Mantén el archivo `.env` fuera del control de versiones

---

## 🎨 Personalización

### Colores

Edita las variables CSS en `assets/css/styles.css`:

```css
:root {
    --color-primary: #2563eb;    /* Azul corporativo */
    --color-accent: #f59e0b;     /* Color de destaque */
    /* ... más variables */
}
```

### Logo

Reemplaza el SVG en el header por tu logo:

```php
<!-- En index.php y dashboard.php -->
<a href="index.php" class="brand">
    <img src="assets/img/logo.png" alt="<?= APP_NAME ?>">
    <span><?= APP_NAME ?></span>
</a>
```

---

## 📊 Roadmap

- [ ] Página de gestión de productos (CRUD completo)
- [ ] Página de gestión de clientes
- [ ] Reportes de ventas por fecha
- [ ] Exportación a PDF/Excel
- [ ] Integración con impresoras térmicas
- [ ] Modo offline con PWA
- [ ] Multi-sucursal
- [ ] Notificaciones de stock bajo por email

---

## 🤝 Soporte

Para issues o preguntas:
- Revisa la documentación en `database/init.sql`
- Verifica que las variables de entorno estén correctas
- Asegúrate de que RLS esté habilitado en Supabase

---

## 📄 Licencia

Este proyecto es de código abierto y puede ser utilizado libremente para fines personales y comerciales.

---

## 👨‍💻 Desarrollado por

**Arquitec** - Sistema de Punto de Venta Moderno

Hecho con ❤️ usando PHP y Supabase