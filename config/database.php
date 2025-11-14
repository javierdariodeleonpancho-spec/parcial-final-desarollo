<?php

declare(strict_types=1);

/**
 * Simple PDO wrapper for the SQLite database used by the SOAP service.
 */
class Database
{
    /** @var string absolute path to the SQLite database file */
    private static string $databasePath = __DIR__ . '/../database/clientes.db';

    /**
     * Creates (if necessary) and returns a PDO connection to the SQLite database.
     *
     * @return PDO
     */
    public static function getConnection(): PDO
    {
        self::ensureStorageDirectory();

        $pdo = new PDO('sqlite:' . self::$databasePath);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

        self::ensureSchema($pdo);

        return $pdo;
    }

    /**
     * Ensures the directory for the SQLite database exists.
     */
    private static function ensureStorageDirectory(): void
    {
        $directory = dirname(self::$databasePath);
        if (!is_dir($directory)) {
            mkdir($directory, 0775, true);
        }
    }

    /**
     * Creates the required tables when they are missing.
     */
    private static function ensureSchema(PDO $pdo): void
    {
        $pdo->exec(
            'CREATE TABLE IF NOT EXISTS TB_CLIENTE (
                clave TEXT PRIMARY KEY,
                nombre TEXT NOT NULL,
                direccion TEXT,
                telefono TEXT,
                fecha_registro TEXT NOT NULL DEFAULT (datetime("now"))
            )'
        );
    }
}
