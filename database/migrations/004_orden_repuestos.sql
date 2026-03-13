-- Migración 004: módulo orden de repuestos
-- Requiere: BD con database.sql, migrations 001, 002, 003

CREATE TABLE IF NOT EXISTS orden_repuestos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inspeccion_id INT NULL,
    aprendiz_id INT NULL,
    cliente_nombre VARCHAR(120) NOT NULL,
    cliente_documento VARCHAR(40) NOT NULL,
    cliente_direccion VARCHAR(200),
    cliente_ciudad VARCHAR(80),
    cliente_telefono VARCHAR(30),
    cliente_celular VARCHAR(30),
    cliente_email VARCHAR(150),
    vin VARCHAR(40),
    no_motor VARCHAR(40),
    placa VARCHAR(20) NOT NULL,
    modelo VARCHAR(120),
    color VARCHAR(40),
    ano SMALLINT,
    fecha_entrada DATE NOT NULL,
    hora_entrada VARCHAR(10),
    fecha_prometida DATE,
    hora_prometida VARCHAR(10),
    km_mto INT,
    rep_gral VARCHAR(80),
    total DECIMAL(12,2) DEFAULT 0,
    firma_recepcionista VARCHAR(120),
    firma_cliente VARCHAR(120),
    cc_recepcionista VARCHAR(40),
    cc_cliente VARCHAR(40),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (inspeccion_id) REFERENCES inspecciones(id) ON DELETE SET NULL,
    FOREIGN KEY (aprendiz_id) REFERENCES usuarios_sistema(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS orden_repuestos_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orden_repuestos_id INT NOT NULL,
    referencia VARCHAR(80),
    descripcion VARCHAR(200) NOT NULL,
    cantidad_tiempo VARCHAR(40) NOT NULL,
    precio_unitario DECIMAL(12,2) NOT NULL DEFAULT 0,
    FOREIGN KEY (orden_repuestos_id) REFERENCES orden_repuestos(id) ON DELETE CASCADE
);
