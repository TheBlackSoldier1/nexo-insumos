# Nexo · Gestión de insumos informáticos

Aplicación modular en PHP, Laravel 13 y SQLite. Interfaz en español, sin compilación de JavaScript y sin servicios externos para funcionar.

## Inicio rapido en localhost (Windows y Git Bash)

Requisitos: PHP **8.4.1 o superior de 64 bits**, Composer y Git. PHP debe tener habilitadas las extensiones PDO SQLite, mbstring, DOM, XML, fileinfo y OpenSSL. No necesitas MySQL, Node ni npm.

1. Descomprime el ZIP. Abre la carpeta `insumos` con Git Bash (clic derecho → Open Git Bash here).
2. Comprueba las herramientas:

```bash
php -v
composer --version
git --version
```

3. Instala y prepara el proyecto. Ejecuta cada comando solo si el anterior termino sin errores:

```bash
composer install
php preparar-local.php
php artisan key:generate
php artisan migrate
```

`key:generate` se ejecuta solo en la primera instalacion. El preparador no reemplaza `.env` ni una base existente. Manten `DB_CONNECTION=sqlite` en `.env`. La base se guarda en `database/database.sqlite`.

4. Para probar con 8 productos, 3 ubicaciones y 13 movimientos de ejemplo, ejecuta:

```bash
php artisan db:seed --class=DemoSeeder
```

Este paso es opcional. Se omite si ya hay productos o ubicaciones.

5. Inicia la aplicacion:

```bash
php artisan serve --host=127.0.0.1 --port=8127
```

6. Abre **http://127.0.0.1:8127** (tambien puedes usar http://localhost:8127) en tu navegador. Deja Git Bash abierto mientras utilizas la aplicacion. Para detenerla, presiona `Ctrl+C`.

En los siguientes usos basta con ejecutar el comando de inicio o hacer doble clic en `INICIAR.bat`. No repitas la instalacion ni generes una nueva clave.

## Recorrido de prueba

1. **Ubicaciones:** crea `Bodega prueba` y `Oficina prueba`.
2. **Inventario → Nuevo producto:** registra `Teclado de prueba`, SKU `PRUEBA-001`, categoria `Perifericos`, minimo `3` y costo `10000`. El producto comienza con cero unidades.
3. **Movimientos → Registrar movimiento:** selecciona el producto, tipo `Entrada`, destino `Bodega prueba`, cantidad `10`, fecha actual y tu nombre como responsable.
4. Registra un **Traslado** de `4` unidades desde `Bodega prueba` hacia `Oficina prueba`. En Inventario veras `6` en bodega y `4` en oficina: el total sigue siendo `10`.
5. Registra una **Salida** de `2` unidades desde `Oficina prueba`. Quedaran `6` en bodega, `2` en oficina y `8` en total.
6. En **Inventario**, abre el enlace de movimientos del producto: veras tres operaciones y un traslado, con fecha, origen y destino.
7. En **Reportes**, selecciona `Historial de movimientos`, el producto y, si lo necesitas, fechas. Descarga PDF, Excel, CSV o JSON. Para existencias actuales, selecciona `Inventario por ubicacion`.
8. Intenta trasladar `20` unidades desde la bodega: se rechazara sin modificar existencias. Un producto con stock no puede darse de baja hasta registrar su salida.

**Historial de cambios** muestra las altas, ediciones y bajas administrativas. Los movimientos tienen su propio historial. El dashboard resume el inventario actual y alerta cuando el stock total es menor o igual al minimo.

## Subir a tu GitHub con Git Bash

El ZIP contiene el codigo fuente, `composer.lock` y estas instrucciones. No contiene claves, `.env`, `vendor`, bases SQLite ni datos personales. `.gitignore` mantiene esos archivos fuera de Git aunque hayas ejecutado la aplicacion.

1. En GitHub, crea un repositorio vacio llamado, por ejemplo, `nexo-insumos`. Elige su visibilidad. **No agregues README, .gitignore ni licencia desde GitHub**, porque este proyecto ya trae archivos.
2. Desde Git Bash, dentro de la carpeta `insumos`, ejecuta:

```bash
git init -b main
git add .
git status
```

3. Revisa la lista de archivos preparados. No debe aparecer `.env`, `vendor/` ni `database/database.sqlite`. Crea el primer commit:

```bash
git commit -m "Proyecto Nexo: inventario Laravel y SQLite"
```

Si Git solicita tu identidad, configura solo este repositorio y repite el commit, sustituyendo los valores de ejemplo:

```bash
git config user.name "TU NOMBRE"
git config user.email "TU CORREO DE GITHUB"
```

4. Copia la URL HTTPS del repositorio que acabas de crear. **Reemplaza `TU_USUARIO` y el nombre del repositorio** en el siguiente comando:

```bash
git remote add origin https://github.com/TU_USUARIO/nexo-insumos.git
git push -u origin main
```

Git puede abrir el navegador para autenticarte. Usa el flujo de inicio de sesion de GitHub/Git Credential Manager; no escribas tokens en el codigo ni los incluyas en la URL del repositorio.

5. Actualiza la pagina del repositorio en GitHub: deberias ver el codigo y este README. GitHub aloja el codigo; **GitHub Pages no ejecuta PHP/Laravel**. La prueba se ejecuta en localhost siguiendo las instrucciones anteriores.

Para cambios posteriores:

```bash
git add .
git diff --cached --stat
git commit -m "Describe aqui el cambio"
git push
```

## Solucion de problemas

- **`php` o `composer` no se reconoce:** instala la herramienta y agrega su carpeta al PATH; cierra y vuelve a abrir Git Bash. Comprueba `php -v` y `composer --version`.
- **PHP incompatible:** este proyecto y sus dependencias requieren PHP 8.4.1 o superior. Algunas instalaciones antiguas de XAMPP incluyen una version menor; revisa la version que usa Git Bash.
- **`could not find driver`:** ejecuta `php --ini`, abre el `php.ini` indicado y habilita `extension=pdo_sqlite`. Verifica con `php -m`.
- **Falta `vendor/autoload.php`:** ejecuta `composer install` dentro de `insumos`.
- **Falta clave de aplicacion:** en una instalacion nueva ejecuta `php artisan key:generate`.
- **`no such table`:** ejecuta `php artisan migrate`.
- **Puerto ocupado:** usa `php artisan serve --host=127.0.0.1 --port=8128` y abre http://127.0.0.1:8128.
- **Error 419 al guardar:** recarga la pagina para renovar la sesion y usa siempre el mismo host (localhost o 127.0.0.1).
- **`remote origin already exists`:** consulta `git remote -v`. Si la URL es incorrecta, cambiala con `git remote set-url origin URL_CORRECTA`.
- **Push rechazado por historial remoto:** no uses `--force`. Si el repositorio tiene archivos o trabajo previo, integra ese historial antes de subir. Los comandos de esta guia asumen un repositorio nuevo y vacio.

## Funciones

- Dashboard con productos activos, unidades, movimientos, alertas y distribución por categoría.
- Alta, consulta, edición y baja lógica de productos. SKU único y stock mínimo.
- CRUD de ubicaciones; las ubicaciones con existencias o historial no se eliminan.
- Entradas, salidas y traslados parciales, con cantidad, origen, destino, fecha, responsable y nota.
- Transacciones SQLite y descuento condicionado al stock disponible para evitar saldos negativos.
- Historial inmutable desde la interfaz, incluyendo productos dados de baja. Conteos de movimientos y traslados por producto.
- Auditoría de altas, ediciones y bajas de productos y ubicaciones.
- Exportación real a PDF, Excel XLSX, CSV y JSON. Inventario por ubicación o movimientos filtrados por producto y fechas.
- Fechas en America/Santiago; costo unitario expresado en CLP.

## Organización

- `app/Models`: productos, ubicaciones, existencias y movimientos.
- `app/Services/InventoryService.php`: lógica transaccional de existencias.
- `app/Services/XlsxExporter.php`: generación de libros Excel con celdas de texto seguras.
- `app/Http/Controllers/InventoryController.php`: formularios, filtros y auditoría.
- `app/Http/Controllers/ReportController.php`: consultas y descargas de reportes.
- `resources/views`: vistas Blade y componentes parciales.
- `public/app.css`, `public/app.js`: interfaz responsive sin dependencias remotas.
- `database/migrations`: esquema SQLite versionado.
- `tests/Feature/InventoryTest.php`: pruebas de integridad y reportes.

## Pruebas

```powershell
php artisan test
```

Las pruebas usan SQLite en memoria y no modifican los datos de uso local.

## Operación y alcance

Versión de uso local individual, sin autenticación ni roles. El nombre del responsable se ingresa manualmente y no acredita identidad. Antes de exponerla a Internet o a múltiples usuarios se debe añadir autenticación, autorización y política de respaldo. El servidor de desarrollo escucha únicamente en 127.0.0.1.

Los movimientos no se editan ni eliminan: un error se corrige con un movimiento compensatorio y una nota explicativa. La fecha de registro se conserva en `created_at` además de la fecha de operación. No se reconstruyen saldos históricos para validar movimientos retroactivos: el límite se valida contra la disponibilidad actual.

Para respaldar, detén la aplicación y copia `database/database.sqlite` junto con cualquier archivo `-wal` o `-shm` que exista. Las exportaciones son reportes y no sustituyen una copia completa de la base. SQLite es adecuado para este uso local y volúmenes moderados; para muchas escrituras concurrentes se debe evaluar una base de servidor.
