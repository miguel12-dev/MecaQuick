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

-- 4. Poblar puntos_catalogo (lista Audi mantenimiento)
-- Ejecutar solo si puntos_catalogo está vacío; si ya tiene datos, omitir este bloque.
INSERT INTO puntos_catalogo (numero_punto, categoria, descripcion, unidad_medida) VALUES
(1, 'Preparación', 'Historial modificaciones, Mantenimiento a la milésima: consultar', 'N/A'),
(2, 'Exterior', 'Bocina: Comprobar el funcionamiento', 'N/A'),
(3, 'Interior', 'Cuadro de instrumentos: comprobar testigos', 'N/A'),
(4, 'Interior', 'Iluminación de la guantera, del habitáculo y luz de lectura: comprobar funcionamiento', 'N/A'),
(5, 'Exterior', 'Alumbrado de circulación y de marcha atrás, luces de freno, luz de posición, luz de matrícula, intermitentes e intermitentes de emergencia: comprobar funcionamiento', 'N/A'),
(6, 'Exterior', 'Lavacristales: comprobar el campo de proyección y ajustar si es necesario', 'N/A'),
(7, 'Exterior', 'Escobillas limpiacristales: comprobar posibles daños', 'N/A'),
(8, 'Exterior', 'Faros: comprobar el reglaje', 'N/A'),
(9, 'Interior', 'Filtro del habitáculo: sustituir', 'N/A'),
(10, 'Interior', 'Cinturones de seguridad: comprobar lengüeta del cinturón, cierre del cinturón y comportamiento del bloqueo del enrollador automático del cinturón', 'N/A'),
(11, 'Exterior', 'Carrocería: comprobar la pintura del vehículo con las puertas y capós/portón abiertos y de los bajos con respecto a daños y corrosión', 'N/A'),
(12, 'Exterior', 'Triángulo de preseñalización: comprobar si está', 'N/A'),
(13, 'Exterior', 'Botiquín de primeros auxilios: comprobar fecha de caducidad y anotarla', 'MM/AAAA'),
(14, 'Exterior', 'Neumático rueda de repuesto: comprobar presión y corregir si es necesario', 'N/A'),
(15, 'Interior', 'Luz del maletero: comprobar el funcionamiento', 'N/A'),
(16, 'Exterior', 'Bisagras con retenedores de puerta separados: limpiar y engrasar', 'N/A'),
(17, 'Mecánica', 'Sistema de refrigeración: comprobar el nivel de anticongelante y refrigerante, y corregir si es necesario', 'N/A'),
(18, 'Mecánica', 'Aceite de motor: sustituir el filtro', 'N/A'),
(19, 'Neumáticos', 'Neumáticos eje delantero: comprobar presiones y corregir si es necesario', 'N/A'),
(20, 'Neumáticos', 'Neumáticos eje trasero: comprobar presiones de inflado y corregir si es necesario', 'N/A'),
(21, 'Mecánica', 'Aceite de motor: vaciar', 'N/A'),
(22, 'Neumáticos', 'Neumático delantero izquierdo: comprobar estado, sentido de giro, desgaste del dibujo y profundidad del perfil y anotar', 'mm'),
(23, 'Neumáticos', 'Neumático trasero izquierdo: comprobar estado, sentido de giro, desgaste del dibujo y profundidad del perfil y anotar', 'mm'),
(24, 'Neumáticos', 'Neumático trasero derecho: comprobar estado, sentido de giro, desgaste del dibujo y profundidad del perfil y anotar', 'mm'),
(25, 'Neumáticos', 'Neumático delantero derecho: comprobar estado, sentido de giro, desgaste del dibujo y profundidad del perfil y anotar', 'mm'),
(26, 'Frenos', 'Pastillas/zapatas de freno: comprobar el grosor', 'N/A'),
(27, 'Mecánica', 'Motor, caja de cambios, grupo final y dirección: comprobar estanqueidad y posibles daños', 'N/A'),
(28, 'Mecánica', 'Componentes del eje delantero y trasero: comprobar el juego, la fijación, los fuelles guardapolvo y en cuanto a daños', 'N/A'),
(29, 'Frenos', 'Sistema de frenos: comprobar el estado de los tubos flexibles y la integridad de las caperuzas de los tornillos de purga', 'N/A'),
(30, 'Bajos', 'Bajos del vehículo (carenados, guardabarros, largueros inferiores y tuberías): comprobar con respecto a daños y fijación correcta', 'N/A'),
(31, 'Mecánica', 'Aceite de motor: cargar - norma del aceite VW 508 00 (0W-20) - Capacidad', 'Litros'),
(32, 'Mecánica', 'Filtro de aire: sustituir el cartucho, limpiar la carcasa', 'N/A'),
(33, 'Neumáticos', 'Sistema de control de la presión de los neumáticos: guardar los valores de las presiones modificados', 'N/A'),
(34, 'Final', 'Indicador de intervalos de servicio: reiniciar la Inspección con Servicio de cambio de aceite - Recorrido de prueba: realizar', 'N/A');
