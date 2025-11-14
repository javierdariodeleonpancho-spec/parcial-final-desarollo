# Super Ventas Los Ingenieros - Servicio SOAP de Clientes

Este proyecto implementa el servicio web solicitado para administrar clientes utilizando SQLite y SOAP con una interfaz basada en NuSOAP.

## Contenido

- `wsCliente.php`: Servicio SOAP que expone los métodos `obtenerCliente`, `guardarCliente` y `modificarCliente`.
- `consumoSOAP.php`: Página que consume el servicio SOAP y ofrece la interfaz gráfica para trabajar con los clientes.
- `src/ClientesService.php`: Lógica de negocio que opera contra la base de datos SQLite.
- `config/database.php`: Utilidad para conectarse a la base de datos y crear la tabla necesaria.
- `lib/nusoap.php`: Cargador que busca la librería oficial de NuSOAP y, si no está disponible, utiliza un adaptador ligero.
- `lib/nusoapmime.php`: Punto de entrada compatible con `nusoapmime.php` para soportar distribuciones completas de NuSOAP.
- `wsdl/wsCliente.wsdl`: Plantilla WSDL que el servicio utiliza para exponer su contrato.
- `database/schema.sql`: Script de creación de la base de datos.

## Requisitos

- PHP 8.1 o superior con la extensión `soap` habilitada (XAMPP ya la incluye por defecto, pero debe estar activada en `php.ini`).
- SQLite3 (incluido con PHP).

## Instalación y uso en XAMPP

1. Copie todo el contenido del proyecto dentro de una carpeta dentro de `htdocs`, por ejemplo `htdocs/super-ventas`.
2. Desde la línea de comandos, ubíquese en esa carpeta y ejecute:

   ```bash
   sqlite3 database/clientes.db < database/schema.sql
   ```

   Esto creará el archivo de base de datos con la tabla `TB_CLIENTE`.

3. Abra `http://localhost/super-ventas/consumoSOAP.php` en el navegador. Allí podrá:
   - Guardar un cliente nuevo.
   - Modificar un cliente existente.
   - Buscar un cliente por su clave.

   La página mostrará mensajes de confirmación o error según corresponda.

4. El WSDL del servicio se encuentra en `http://localhost/super-ventas/wsCliente.php?wsdl`.

### Uso de la librería NuSOAP proporcionada por la cátedra

Si cuenta con la carpeta que contiene los archivos originales `nusoap.php` y `nusoapmime.php`, cópiela dentro de `lib/vendor/` (por ejemplo `lib/vendor/nusoap.php` y `lib/vendor/nusoapmime.php`).

El cargador incluido detectará automáticamente esos archivos y los utilizará en lugar del adaptador integrado, asegurando compatibilidad total con la implementación solicitada.

## Notas

- El archivo `database/clientes.db` no se incluye en el repositorio. Se genera automáticamente al ejecutar el script SQL o la primera vez que se invoque algún método del servicio.
- La tabla `TB_CLIENTE` contiene los campos `clave`, `nombre`, `direccion`, `telefono` y `fecha_registro`.
- En caso de realizar cambios en la ruta del proyecto, asegúrese de que la URL mostrada en la página coincida con la del servicio SOAP.
