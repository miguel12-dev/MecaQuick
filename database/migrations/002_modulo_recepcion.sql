-- Migración 002: módulo de recepción del vehículo
-- Requiere: database.sql y migrations/001 ejecutados
-- Flujo: Cita (hoy) → Recepción → Checklist

-- 1. Completar clientes (campos del formulario recepción)
-- Si alguna columna ya existe, omitir ese ALTER
ALTER TABLE clientes ADD COLUMN direccion VARCHAR(200) NULL AFTER email;
ALTER TABLE clientes ADD COLUMN ciudad VARCHAR(80) NULL AFTER direccion;

-- 2. Completar vehiculos (campos del formulario recepción)
ALTER TABLE vehiculos ADD COLUMN vin VARCHAR(40) NULL AFTER placa;
ALTER TABLE vehiculos ADD COLUMN numero_motor VARCHAR(40) NULL AFTER vin;
ALTER TABLE vehiculos ADD COLUMN kilometraje INT NULL AFTER color;
ALTER TABLE vehiculos ADD COLUMN fecha_venta DATE NULL AFTER kilometraje;

-- 3. Tabla principal de recepción (vinculada a cita)
CREATE TABLE IF NOT EXISTS recepcion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cita_id INT NOT NULL UNIQUE,
    inspeccion_id INT NULL,
    kilometraje_recepcion INT NULL,
    -- Servicio anterior
    fecha_servicio_anterior DATE,
    or_numero VARCHAR(40),
    tipo_servicio_anterior VARCHAR(120),
    km_servicio_anterior INT,
    -- Campaña especial
    vehiculo_conducido_por ENUM('dueno','chofer','familiar','otro') DEFAULT 'dueno',
    -- Presupuesto estimado
    presupuesto_repuestos DECIMAL(12,2) DEFAULT 0,
    presupuesto_mano_obra DECIMAL(12,2) DEFAULT 0,
    presupuesto_total DECIMAL(12,2) DEFAULT 0,
    -- Método de pago
    metodo_pago ENUM('tarjeta_credito','efectivo','otro') DEFAULT 'efectivo',
    -- Accesorios: JSON {"gato":"si","llave_pernos":"no",...}
    accesorios_internos JSON,
    accesorios_externos JSON,
    -- Otros
    recibo_repuesto_cambiados TINYINT(1) DEFAULT 0,
    observaciones TEXT,
    defectos_carroceria TEXT,
    -- Firmas
    inventariado_por VARCHAR(120),
    inventariado_cc VARCHAR(20),
    firma_cliente VARCHAR(255),
    firma_cliente_cc VARCHAR(20),
    autorizacion_adicional DECIMAL(12,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cita_id) REFERENCES citas(id) ON DELETE CASCADE,
    FOREIGN KEY (inspeccion_id) REFERENCES inspecciones(id) ON DELETE SET NULL
);

-- 2. Índice para búsqueda por fecha (citas del día)
-- Las citas se filtran por fechas_disponibles.fecha = CURDATE()
