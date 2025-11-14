-- Script de creación para la base de datos de clientes.
-- Puede ejecutarse con "sqlite3 clientes.db < database/schema.sql".

CREATE TABLE IF NOT EXISTS TB_CLIENTE (
    clave TEXT PRIMARY KEY,
    nombre TEXT NOT NULL,
    direccion TEXT,
    telefono TEXT,
    fecha_registro TEXT NOT NULL DEFAULT (datetime('now'))
);
