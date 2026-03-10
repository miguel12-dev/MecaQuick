-- Migración 001: módulo checklist técnico de vehículos (25 puntos)
-- Requiere: BD creada con database/database.sql
-- Ejecutar UNA SOLA VEZ. Si puntos_catalogo ya tiene datos, omitir el bloque INSERT final.

-- 1. Modificar inspecciones para checklist standalone
ALTER TABLE inspecciones
    ADD COLUMN token VARCHAR(64) UNIQUE NULL AFTER id,
    MODIFY COLUMN cita_id INT NULL,
    MODIFY COLUMN aprendiz_id INT NULL;

-- 2. Crear tabla checklist_datos (formato técnico vehículos)
DROP TABLE IF EXISTS checklist_datos;
CREATE TABLE checklist_datos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inspeccion_id INT NOT NULL UNIQUE,
    nombre_cliente VARCHAR(120) NOT NULL,
    cedula_nit VARCHAR(20) NOT NULL,
    telefono VARCHAR(15) NOT NULL,
    correo VARCHAR(150) NOT NULL,
    modelo_vehiculo VARCHAR(120) NOT NULL,
    placa VARCHAR(10) NOT NULL,
    kilometraje INT NOT NULL DEFAULT 0,
    fecha_ingreso DATE NOT NULL,
    hora_ingreso VARCHAR(10) NOT NULL,
    observaciones_generales TEXT,
    firma_tecnico VARCHAR(255),
    nombre_tecnico VARCHAR(120) NOT NULL,
    firma_cliente VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (inspeccion_id) REFERENCES inspecciones(id) ON DELETE CASCADE
);

-- 3. Eliminar tabla antigua si existe
DROP TABLE IF EXISTS checklist_registros;

-- 4. Poblar puntos_catalogo (25 puntos de revisión técnica)
-- Ejecutar solo si desea reemplazar los puntos existentes (se borran resultados previos).
SET SESSION FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE resultados_puntos;
TRUNCATE TABLE puntos_catalogo;
SET SESSION FOREIGN_KEY_CHECKS = 1;
INSERT INTO puntos_catalogo (numero_punto, categoria, descripcion, unidad_medida) VALUES
(1, 'Motor', 'Nivel y estado del aceite de motor', 'N/A'),
(2, 'Motor', 'Filtro de aceite', 'N/A'),
(3, 'Motor', 'Filtro de aire', 'N/A'),
(4, 'Motor', 'Filtro de combustible', 'N/A'),
(5, 'Motor', 'Fugas de aceite o refrigerante', 'N/A'),
(6, 'Motor', 'Estado de correas (accesorios/distribución)', 'N/A'),
(7, 'Motor', 'Nivel y estado del refrigerante', 'N/A'),
(8, 'Motor', 'Funcionamiento del electroventilador', 'N/A'),
(9, 'Eléctrico', 'Estado de la batería (voltaje y carga)', 'N/A'),
(10, 'Eléctrico', 'Sistema de carga (alternador)', 'N/A'),
(11, 'Eléctrico', 'Escaneo OBD (verificar DTC)', 'N/A'),
(12, 'Eléctrico', 'Funcionamiento de luces', 'N/A'),
(13, 'Eléctrico', 'Estado de fusibles y relés', 'N/A'),
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

-- 5. Crear tabla evidencias (referencia a resultados_puntos ya existente)
CREATE TABLE IF NOT EXISTS evidencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resultado_punto_id INT NOT NULL,
    ruta_archivo VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resultado_punto_id) REFERENCES resultados_puntos(id) ON DELETE CASCADE
);
