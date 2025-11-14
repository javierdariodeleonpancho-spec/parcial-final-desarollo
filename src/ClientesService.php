<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/database.php';

/**
 * SOAP actions that handle client persistence in SQLite.
 */
class ClientesService
{
    /**
     * Returns a client identified by its "clave".
     *
     * @param array|stdClass $request
     * @return array
     */
    public function obtenerCliente($request): array
    {
        $clave = $this->extractString($request, 'clave');

        if ($clave === null || $clave === '') {
            return [
                'Cliente' => null,
                'Resultado' => [
                    'exito' => false,
                    'mensaje' => 'La clave del cliente es obligatoria.'
                ],
            ];
        }

        $pdo = Database::getConnection();
        $stmt = $pdo->prepare('SELECT clave, nombre, direccion, telefono, fecha_registro FROM TB_CLIENTE WHERE clave = :clave');
        $stmt->execute([':clave' => $clave]);
        $row = $stmt->fetch();

        if ($row === false) {
            return [
                'Cliente' => null,
                'Resultado' => [
                    'exito' => false,
                    'mensaje' => 'No se encontró ningún cliente con la clave indicada.'
                ],
            ];
        }

        return [
            'Cliente' => $row,
            'Resultado' => [
                'exito' => true,
                'mensaje' => 'Cliente obtenido correctamente.'
            ],
        ];
    }

    /**
     * Inserts a new client into the database.
     *
     * @param array|stdClass $request
     * @return array
     */
    public function guardarCliente($request): array
    {
        $datos = $this->normalizeCliente($request);
        $validacion = $this->validarDatosCliente($datos);

        if ($validacion !== null) {
            return ['Resultado' => $validacion];
        }

        $pdo = Database::getConnection();

        $existe = $pdo->prepare('SELECT COUNT(1) FROM TB_CLIENTE WHERE clave = :clave');
        $existe->execute([':clave' => $datos['clave']]);

        if ((int) $existe->fetchColumn() > 0) {
            return [
                'Resultado' => [
                    'exito' => false,
                    'mensaje' => 'Ya existe un cliente con la clave proporcionada.'
                ],
            ];
        }

        $insert = $pdo->prepare(
            'INSERT INTO TB_CLIENTE (clave, nombre, direccion, telefono) VALUES (:clave, :nombre, :direccion, :telefono)'
        );

        $insert->execute([
            ':clave' => $datos['clave'],
            ':nombre' => $datos['nombre'],
            ':direccion' => $datos['direccion'],
            ':telefono' => $datos['telefono'],
        ]);

        return [
            'Resultado' => [
                'exito' => true,
                'mensaje' => 'Cliente guardado correctamente.'
            ],
        ];
    }

    /**
     * Updates an existing client.
     *
     * @param array|stdClass $request
     * @return array
     */
    public function modificarCliente($request): array
    {
        $datos = $this->normalizeCliente($request);
        $validacion = $this->validarDatosCliente($datos);

        if ($validacion !== null) {
            return ['Resultado' => $validacion];
        }

        $pdo = Database::getConnection();

        $update = $pdo->prepare(
            'UPDATE TB_CLIENTE SET nombre = :nombre, direccion = :direccion, telefono = :telefono WHERE clave = :clave'
        );

        $update->execute([
            ':clave' => $datos['clave'],
            ':nombre' => $datos['nombre'],
            ':direccion' => $datos['direccion'],
            ':telefono' => $datos['telefono'],
        ]);

        if ($update->rowCount() === 0) {
            return [
                'Resultado' => [
                    'exito' => false,
                    'mensaje' => 'No existe un cliente con la clave proporcionada.'
                ],
            ];
        }

        return [
            'Resultado' => [
                'exito' => true,
                'mensaje' => 'Cliente modificado correctamente.'
            ],
        ];
    }

    /**
     * Obtains and normalizes the client data from the request payload.
     *
     * @param array|stdClass $request
     * @return array{
     *     clave: string|null,
     *     nombre: string|null,
     *     direccion: string|null,
     *     telefono: string|null
     * }
     */
    private function normalizeCliente($request): array
    {
        return [
            'clave' => $this->extractString($request, 'clave'),
            'nombre' => $this->extractString($request, 'nombre'),
            'direccion' => $this->extractString($request, 'direccion'),
            'telefono' => $this->extractString($request, 'telefono'),
        ];
    }

    /**
     * Validates required client fields.
     *
     * @param array $datos
     * @return array|null
     */
    private function validarDatosCliente(array $datos): ?array
    {
        if ($datos['clave'] === null || $datos['clave'] === '') {
            return [
                'exito' => false,
                'mensaje' => 'La clave del cliente es obligatoria.'
            ];
        }

        if ($datos['nombre'] === null || $datos['nombre'] === '') {
            return [
                'exito' => false,
                'mensaje' => 'El nombre del cliente es obligatorio.'
            ];
        }

        return null;
    }

    /**
     * Safely extracts a string from arrays or stdClass instances.
     *
     * @param array|stdClass $data
     * @param string $key
     * @return string|null
     */
    private function extractString($data, string $key): ?string
    {
        if (is_array($data) && array_key_exists($key, $data)) {
            $value = $data[$key];
        } elseif (is_object($data) && property_exists($data, $key)) {
            $value = $data->$key;
        } else {
            return null;
        }

        if ($value === null) {
            return null;
        }

        return trim((string) $value);
    }
}
