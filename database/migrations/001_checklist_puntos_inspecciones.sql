-- Migración 001: módulo checklist de mantenimiento
-- Requiere: BD creada con database/database.sql
-- Ejecutar UNA SOLA VEZ. Si puntos_catalogo ya tiene datos, omitir el bloque INSERT final.

-- 1. Modificar inspecciones para checklist standalone
ALTER TABLE inspecciones
    ADD COLUMN token VARCHAR(64) UNIQUE NULL AFTER id,
    MODIFY COLUMN cita_id INT NULL,
    MODIFY COLUMN aprendiz_id INT NULL;

-- 2. Crear tabla checklist_datos
CREATE TABLE IF NOT EXISTS checklist_datos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inspeccion_id INT NOT NULL UNIQUE,
    numero_orden VARCHAR(40) NOT NULL,
    tipo_comercial_codigo VARCHAR(40),
    matricula VARCHAR(20) NOT NULL,
    matriculacion DATE,
    bastidor VARCHAR(40) NOT NULL,
    ldm VARCHAR(20),
    djka VARCHAR(20),
    kilometraje INT NOT NULL DEFAULT 0,
    asesor VARCHAR(120) NOT NULL,
    tipo_comercial_modelo VARCHAR(120),
    ldc VARCHAR(20),
    vhn VARCHAR(20),
    ano_modelo SMALLINT,
    fecha_servicio DATE NOT NULL,
    tipo_inspeccion VARCHAR(80),
    km_salida INT,
    km_llegada INT,
    observaciones TEXT,
    nota_mantenimiento TEXT,
    fecha_firma_responsable DATE,
    fecha_firma_control DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (inspeccion_id) REFERENCES inspecciones(id) ON DELETE CASCADE
);

-- 3. Eliminar tabla antigua si existe
DROP TABLE IF EXISTS checklist_registros;

-- 4. Poblar puntos_catalogo (checklist técnico estándar 25 puntos)
-- Ejecutar solo si puntos_catalogo está vacío; si ya tiene datos, omitir este bloque.
INSERT INTO puntos_catalogo (numero_punto, categoria, descripcion, unidad_medida) VALUES
(1, 'Motor', 'Nivel y estado del aceite de motor', 'N/A'),
(2, 'Motor', 'Filtro de aceite', 'N/A'),
(3, 'Motor', 'Filtro de aire', 'N/A'),
(4, 'Motor', 'Filtro de combustible', 'N/A'),
(5, 'Motor', 'Fugas de aceite o refrigerante', 'N/A'),
(6, 'Motor', 'Estado de correas (accesorios/distribución)', 'N/A'),
(7, 'Motor', 'Nivel y estado del refrigerante', 'N/A'),
(8, 'Motor', 'Funcionamiento del electroventilador', 'N/A'),
(9, 'Electricidad', 'Estado de la batería (voltaje y carga)', 'N/A'),
(10, 'Electricidad', 'Sistema de carga (alternador)', 'N/A'),
(11, 'Electricidad', 'Escaneo OBD (verificar DTC)', 'N/A'),
(12, 'Electricidad', 'Funcionamiento de luces', 'N/A'),
(13, 'Electricidad', 'Estado de fusibles y relés', 'N/A'),
(14, 'Frenos', 'Espesor de pastillas de freno', 'N/A'),
(15, 'Frenos', 'Estado de discos/campanas', 'N/A'),
(16, 'Frenos', 'Nivel y estado del líquido de frenos', 'N/A'),
(17, 'Frenos', 'Funcionamiento freno de estacionamiento', 'N/A'),
(18, 'Suspensión', 'Estado de amortiguadores', 'N/A'),
(19, 'Suspensión', 'Rótulas y terminales de dirección', 'N/A'),
(20, 'Suspensión', 'Bujes y brazos de suspensión', 'N/A'),
(21, 'Suspensión', 'Alineación y balanceo', 'N/A'),
(22, 'Neumáticos', 'Estado y presión de neumáticos', 'N/A'),
(23, 'Transmisión', 'Nivel de aceite de caja', 'N/A'),
(24, 'Transmisión', 'Funcionamiento embrague/convertidor', 'N/A'),
(25, 'Climatización', 'Sistema de aire acondicionado', 'N/A');
