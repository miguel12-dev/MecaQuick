-- Migración 004: módulo orden de repuestos
-- Requiere: BD con database.sql, migrations 001, 002, 003

CREATE TABLE IF NOT EXISTS ordenes_repuestos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inspeccion_id INT NOT NULL UNIQUE,
    cliente_nombre VARCHAR(120),
    cliente_documento VARCHAR(40),
    cliente_direccion VARCHAR(200),
    cliente_ciudad VARCHAR(80),
    cliente_telefono VARCHAR(20),
    cliente_celular VARCHAR(20),
    cliente_email VARCHAR(100),
    vin VARCHAR(40),
    numero_motor VARCHAR(40),
    placa VARCHAR(20),
    modelo VARCHAR(120),
    color VARCHAR(40),
    ano SMALLINT,
    fecha_entrada DATE,
    hora_entrada VARCHAR(10),
    fecha_prometida DATE,
    hora_prometida VARCHAR(10),
    mto_km INT,
    rep_gral VARCHAR(80),
    total DECIMAL(12, 2) DEFAULT 0,
    firma_recepcionista VARCHAR(120),
    firma_cliente VARCHAR(120),
    cc_recepcionista VARCHAR(40),
    cc_cliente VARCHAR(40),
    estado ENUM('pendiente', 'completada') DEFAULT 'pendiente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (inspeccion_id) REFERENCES inspecciones(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS orden_repuestos_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orden_id INT NOT NULL,
    referencia VARCHAR(80),
    descripcion VARCHAR(200),
    cant_tiempo VARCHAR(40),
    precio DECIMAL(12, 2),
    FOREIGN KEY (orden_id) REFERENCES ordenes_repuestos(id) ON DELETE CASCADE
);
