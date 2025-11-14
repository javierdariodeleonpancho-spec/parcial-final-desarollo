<?php

declare(strict_types=1);

require_once __DIR__ . '/lib/nusoapmime.php';

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$baseUrl = rtrim($scheme . '://' . $host . $scriptDir, '/');
$wsdlUrl = $baseUrl . '/wsCliente.php?wsdl';

$client = new nusoap_client($wsdlUrl, true);
$error = $client->getError();
$mensaje = '';
$estado = null;
$clienteBuscado = null;
$formData = [
    'clave' => '',
    'nombre' => '',
    'direccion' => '',
    'telefono' => '',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $error === null) {
    $accion = $_POST['accion'] ?? '';
    $estado = null;
    $clienteBuscado = null;
    $formData = [
        'clave' => trim($_POST['clave'] ?? ''),
        'nombre' => trim($_POST['nombre'] ?? ''),
        'direccion' => trim($_POST['direccion'] ?? ''),
        'telefono' => trim($_POST['telefono'] ?? ''),
    ];

    switch ($accion) {
        case 'guardar':
            $respuesta = $client->call('guardarCliente', $formData);
            procesarResultado($respuesta);
            break;
        case 'modificar':
            $respuesta = $client->call('modificarCliente', $formData);
            procesarResultado($respuesta);
            break;
        case 'buscar':
            $respuesta = $client->call('obtenerCliente', ['clave' => $formData['clave']]);
            if ($client->fault) {
                $mensaje = 'Error en la petición: ' . ($client->fault['faultstring'] ?? '');
            } elseif (($err = $client->getError()) !== null) {
                $mensaje = 'Error de comunicación: ' . $err;
            } elseif (is_array($respuesta)) {
                $estado = $respuesta['Resultado'] ?? null;
                if (!empty($respuesta['Cliente'])) {
                    $clienteBuscado = $respuesta['Cliente'];
                    $formData = [
                        'clave' => $clienteBuscado['clave'] ?? '',
                        'nombre' => $clienteBuscado['nombre'] ?? '',
                        'direccion' => $clienteBuscado['direccion'] ?? '',
                        'telefono' => $clienteBuscado['telefono'] ?? '',
                    ];
                }
                $mensaje = $estado['mensaje'] ?? '';
            }
            break;
    }
}

if ($error !== null && $mensaje === '') {
    $mensaje = 'No fue posible inicializar el cliente SOAP: ' . $error;
}

function procesarResultado(?array $respuesta): void
{
    global $client, $mensaje, $estado;

    if ($client->fault) {
        $mensaje = 'Error en la petición: ' . ($client->fault['faultstring'] ?? '');
        return;
    }

    if (($err = $client->getError()) !== null) {
        $mensaje = 'Error de comunicación: ' . $err;
        return;
    }

    if (is_array($respuesta) && isset($respuesta['Resultado'])) {
        $estado = $respuesta['Resultado'];
        $mensaje = $estado['mensaje'] ?? '';
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Super Ventas Los Ingenieros - Clientes</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background-color: #f5f6fa;
            margin: 0;
            padding: 0;
            color: #333;
        }
        header {
            background-color: #34495e;
            color: #fff;
            padding: 20px;
            text-align: center;
        }
        main {
            max-width: 980px;
            margin: 30px auto;
            background-color: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
        h2 {
            margin-top: 0;
        }
        fieldset {
            border: 1px solid #dcdfe6;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 25px;
        }
        legend {
            padding: 0 10px;
            font-weight: bold;
            color: #2c3e50;
        }
        label {
            display: block;
            margin-bottom: 6px;
            font-weight: bold;
        }
        input[type="text"] {
            width: 100%;
            padding: 8px 12px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .acciones {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        button {
            border: none;
            padding: 10px 18px;
            border-radius: 4px;
            cursor: pointer;
            font-weight: bold;
            color: #fff;
        }
        button[name="accion"][value="guardar"] { background-color: #27ae60; }
        button[name="accion"][value="modificar"] { background-color: #2980b9; }
        button[name="accion"][value="buscar"] { background-color: #8e44ad; }
        button:hover { opacity: 0.9; }
        .mensaje {
            padding: 12px 18px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .mensaje.exito { background-color: #eafaf1; color: #1e8449; border: 1px solid #27ae60; }
        .mensaje.error { background-color: #fdecea; color: #c0392b; border: 1px solid #e74c3c; }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        th, td {
            border: 1px solid #dcdfe6;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #ecf0f1;
        }
        .wsdl-info {
            font-size: 0.9em;
            margin-bottom: 20px;
            color: #555;
        }
    </style>
</head>
<body>
<header>
    <h1>Super Ventas Los Ingenieros</h1>
    <p>Control de Clientes mediante SOAP</p>
</header>
<main>
    <h2>Agregar Clientes</h2>
    <p class="wsdl-info">Servicio web: <a href="<?= htmlspecialchars($wsdlUrl, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($wsdlUrl, ENT_QUOTES, 'UTF-8') ?></a></p>
    <?php if ($mensaje !== ''): ?>
        <?php $clase = ($estado['exito'] ?? false) ? 'exito' : 'error'; ?>
        <div class="mensaje <?= $clase ?>"><?= htmlspecialchars($mensaje, ENT_QUOTES, 'UTF-8') ?></div>
    <?php endif; ?>

    <form method="post">
        <fieldset>
            <legend>Datos del Cliente</legend>
            <label for="clave">Clave</label>
            <input type="text" id="clave" name="clave" value="<?= htmlspecialchars($formData['clave'], ENT_QUOTES, 'UTF-8') ?>" required>

            <label for="nombre">Nombre</label>
            <input type="text" id="nombre" name="nombre" value="<?= htmlspecialchars($formData['nombre'], ENT_QUOTES, 'UTF-8') ?>" required>

            <label for="direccion">Dirección</label>
            <input type="text" id="direccion" name="direccion" value="<?= htmlspecialchars($formData['direccion'], ENT_QUOTES, 'UTF-8') ?>">

            <label for="telefono">Teléfono</label>
            <input type="text" id="telefono" name="telefono" value="<?= htmlspecialchars($formData['telefono'], ENT_QUOTES, 'UTF-8') ?>">

            <div class="acciones">
                <button type="submit" name="accion" value="guardar">Guardar Cliente</button>
                <button type="submit" name="accion" value="modificar">Modificar Cliente</button>
                <button type="submit" name="accion" value="buscar">Buscar Cliente</button>
            </div>
        </fieldset>
    </form>

    <?php if ($clienteBuscado !== null): ?>
        <fieldset>
            <legend>Resultado de la búsqueda</legend>
            <table>
                <thead>
                    <tr>
                        <th>Clave</th>
                        <th>Nombre</th>
                        <th>Dirección</th>
                        <th>Teléfono</th>
                        <th>Fecha de registro</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?= htmlspecialchars($clienteBuscado['clave'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($clienteBuscado['nombre'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($clienteBuscado['direccion'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($clienteBuscado['telefono'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($clienteBuscado['fecha_registro'] ?? '', ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                </tbody>
            </table>
        </fieldset>
    <?php endif; ?>
</main>
</body>
</html>
