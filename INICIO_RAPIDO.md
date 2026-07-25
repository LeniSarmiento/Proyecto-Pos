# 🚀 Guía Rápida de Inicio - Punto de Venta Arquitec

Sigue estos pasos para tener el sistema funcionando en menos de 10 minutos.

---

## ✅ Paso 1: Verificar XAMPP

1. Abre el panel de control de XAMPP
2. Inicia **Apache**
3. Verifica que PHP esté en la versión 8.0 o superior

---

## ✅ Paso 2: Configurar Supabase (5 minutos)

### 2.1 Crear Cuenta y Proyecto

1. Ve a https://supabase.com
2. Regístrate con GitHub o email
3. Click en **"New Project"**
4. Completa:
   - **Name**: `punto-venta-arquitec`
   - **Database Password**: (guárdala en un lugar seguro)
   - **Region**: Elige la más cercana a tu ubicación
5. Click en **"Create new project"** (toma 2-3 minutos)

### 2.2 Obtener Credenciales

1. Una vez creado el proyecto, ve a **Settings** (engranaje en la barra lateral)
2. Click en **API**
3. Copia estos 3 valores:
   ```
   Project URL: https://xxxxx.supabase.co
   anon public: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
   service_role: eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9... (¡MANTENER EN SECRETO!)
   ```

### 2.3 Ejecutar Script SQL

1. En la barra lateral, click en **SQL Editor**
2. Click en **"New Query"**
3. Abre el archivo `database/init.sql` de tu proyecto
4. Copia TODO el contenido y pégalo en el editor de Supabase
5. Click en **"Run"** o presiona `Ctrl + Enter`
6. Deberías ver: `SUCCESS. Rows affected: 0` (o similar)

**Verificación:**
- En la barra lateral, ve a **Table Editor**
- Deberías ver las tablas: `profiles`, `products`, `customers`, `sales`, `sale_items`, `cash_register`

---

## ✅ Paso 3: Configurar Archivo .env (2 minutos)

1. En tu carpeta del proyecto `C:\xampp\htdocs\punto-venta-arquitec\`
2. Abre el archivo `.env` con un editor de texto (Bloc de notas, VS Code, etc.)
3. Reemplaza los valores vacíos con tus credenciales de Supabase:

```env
SUPABASE_URL="https://TU-PROJECT-ID.supabase.co"
SUPABASE_ANON_KEY="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.TU-ANON-KEY"
SUPABASE_SERVICE_KEY="eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.TU-SERVICE-KEY"
APP_NAME="Punto de Venta Arquitec"
APP_URL="http://localhost/punto-venta-arquitec"
```

**Importante:**
- Las comillas dobles son obligatorias
- No dejar espacios antes o después del `=`
- Guardar el archivo después de editar

---

## ✅ Paso 4: Crear Usuario Admin (2 minutos)

### Opción A: Desde Supabase Dashboard

1. En Supabase, ve a **Authentication** (barra lateral)
2. Click en **"Add User"** (esquina superior derecha)
3. Selecciona **"Create user manually"**
4. Completa:
   - **Email**: `admin@tu-negocio.com`
   - **Password**: `Admin123!` (o la que quieras)
   - **Auto Confirm User**: ✅ Activar
5. Click en **"Create user"**

### Opción B: Desde el Código (Temporal)

Crea un archivo `temp_register.php` en la raíz:

```php
<?php
require_once 'config/supabase.php';

$result = $supabase->signUp('admin@ejemplo.com', 'Admin123!', 'Administrador');
echo json_encode($result, JSON_PRETTY_PRINT);
```

Ejecútalo en el navegador: `http://localhost/punto-venta-arquitec/temp_register.php`

**Elimina el archivo después de usarlo.**

---

## ✅ Paso 5: Probar el Sistema (1 minuto)

1. Abre tu navegador
2. Ve a: `http://localhost/punto-venta-arquitec`
3. Deberías ver la página principal con el buscador de productos
4. Click en **"Ingresar"** (esquina superior derecha)
5. Ingresa las credenciales del usuario creado
6. Si todo está correcto, verás el **Dashboard**

---

## 🔧 Solución de Problemas Comunes

### Error: "Configura las variables de entorno de Supabase"

**Causa:** El archivo `.env` no está configurado o no se encuentra

**Solución:**
1. Verifica que el archivo `.env` exista en la raíz del proyecto
2. Asegúrate de que las variables estén entre comillas dobles
3. Reinicia Apache en XAMPP

### Error: "Credenciales inválidas" al login

**Causa:** El usuario no existe o la contraseña es incorrecta

**Solución:**
1. Verifica en Supabase → Authentication → Users que el usuario exista
2. Asegúrate de que el usuario esté confirmado (check verde en "Email confirmed")
3. Intenta restablecer la contraseña

### Error: "Error al obtener productos"

**Causa:** Las políticas RLS no están configuradas correctamente

**Solución:**
1. En Supabase, ve a **SQL Editor**
2. Ejecuta nuevamente el script `database/init.sql`
3. Verifica en **Authentication → Policies** que las políticas existan

### Error: "No se pudo conectar con el servidor"

**Causa:** PHP no tiene habilitada la extensión cURL

**Solución:**
1. Abre `C:\xampp\php\php.ini`
2. Busca la línea `;extension=curl`
3. Quita el `;` del inicio: `extension=curl`
4. Reinicia Apache

### La página se ve sin estilos

**Causa:** La ruta del CSS no es correcta

**Solución:**
1. Verifica que `assets/css/styles.css` exista
2. Limpia el caché del navegador (`Ctrl + F5`)
3. Revisa la consola del navegador (F12) para ver errores

---

## 📱 Primeros Pasos en el Sistema

### 1. Agregar Productos de Prueba

En Supabase → SQL Editor, ejecuta:

```sql
INSERT INTO public.products (name, description, sku, price, cost, stock, category, is_active) VALUES
('Adobo Arequipeño', 'Tradicional plato de domingo con panes', 'PLATO-001', 25.00, 15.00, 50, 'Platos', true),
('Chicharrón', 'Cerdo frito con yuca y salsa criolla', 'PLATO-002', 28.00, 18.00, 40, 'Platos', true),
('Rocoto Relleno', 'Rocoto relleno de carne y queso', 'PLATO-003', 18.00, 10.00, 30, 'Platos', true),
('Chicha de Guiñapo', 'Bebida tradicional arequipeña', 'BEBIDA-001', 5.00, 2.00, 100, 'Bebidas', true),
('Inka Kola', 'Gaseosa de 500ml', 'BEBIDA-002', 3.00, 2.00, 200, 'Bebidas', true);
```

### 2. Realizar Primera Venta

1. Ve al **Punto de Venta** (menú lateral)
2. Busca un producto por nombre
3. Haz clic en la tarjeta del producto (se agregará al carrito)
4. Click en el botón **"🛒 Carrito"** (arriba a la derecha)
5. Selecciona método de pago: **Efectivo**
6. Click en **"✅ Procesar Venta"**
7. ¡Listo! La venta quedó registrada

### 3. Ver Ventas Realizadas

1. En el **Dashboard**, baja hasta "Ventas Recientes"
2. Deberías ver la venta que acabas de registrar
3. El stock del producto se actualizó automáticamente

---

## 🎯 Siguientes Pasos Recomendados

1. **Personalizar el logo**: Reemplaza el SVG en `index.php` y `dashboard.php`
2. **Configurar métodos de pago**: Edita las opciones en `pages/pos.php`
3. **Agregar más productos**: Usa el Table Editor de Supabase o crea un CRUD
4. **Configurar impresora**: Investiga cómo imprimir desde navegador a impresora térmica
5. **Poner en producción**: Sube el proyecto a un hosting con HTTPS

---

## 📞 Soporte

Si tienes problemas:

1. Revisa la consola del navegador (F12) para ver errores de JavaScript
2. Revisa los logs de Apache en `C:\xampp\apache\logs\error.log`
3. Verifica que tu conexión a internet esté activa (necesario para Supabase)
4. Consulta la documentación completa en `README.md`

---

## 🎉 ¡Listo!

Tu sistema de Punto de Venta Arquitec está configurado y funcionando.

**Disfruta de tu nuevo sistema POS moderno y profesional.** 🚀