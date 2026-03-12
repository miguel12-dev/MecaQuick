-- ===================================
-- MecaQuick - Esquema v0.1 (legacy)
-- Referencia y migración. Instalaciones nuevas usar database/schema.sql (v1.0)
-- Consolidado: database.sql + migrations 001, 002
-- ===================================

-- CONFIGURACIÓN
CREATE TABLE configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT NOT NULL,
    descripcion VARCHAR(255),
    tipo ENUM('texto','numero','booleano','json') DEFAULT 'texto',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT INTO configuracion (clave, valor, descripcion, tipo) VALUES
('nombre_sistema',   'CTA SENA - Revisión Mecánica',  'Nombre del sistema',           'texto'),
('max_cupos_dia',    '4',                              'Máximo de vehículos por día',  'numero'),
('hora_inicio',      '07:00',                          'Hora de inicio de jornada',    'texto'),
('hora_fin',         '11:30',                          'Hora de cierre de jornada',    'texto'),
('smtp_host',        'smtp.gmail.com',                 'Servidor SMTP',                'texto'),
('smtp_port',        '587',                            'Puerto SMTP',                  'numero'),
('smtp_user',        '',                               'Usuario SMTP',                 'texto'),
('smtp_pass',        '',                               'Contraseña SMTP',              'texto'),
('smtp_from',        '',                               'Correo remitente',             'texto'),
('sistema_activo',   '1',                              'Sistema habilitado',           'booleano'),
('zona_reunion',     'CTA Caquetá, vía al aeropuerto', 'Zona de encuentro para la cita', 'texto'),
('horario_atencion', '07:00 - 11:30',                  'Horario de atención al público', 'texto');

-- FECHAS DISPONIBLES
CREATE TABLE fechas_disponibles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL UNIQUE,
    max_cupos INT NOT NULL DEFAULT 4,
    activa TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

INSERT INTO fechas_disponibles (fecha) VALUES
('2026-03-12'), ('2026-03-13'), ('2026-03-17'),
('2026-03-18'), ('2026-03-19'), ('2026-03-20'),
('2026-03-24'), ('2026-03-25'), ('2026-03-26'), ('2026-03-27');

-- CLIENTES
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    documento VARCHAR(20) NOT NULL UNIQUE,
    telefono VARCHAR(20),
    email VARCHAR(150) NOT NULL,
    direccion VARCHAR(200) NULL,
    ciudad VARCHAR(80) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- VEHÍCULOS
CREATE TABLE vehiculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    placa VARCHAR(10) NOT NULL UNIQUE,
    vin VARCHAR(40) NULL,
    numero_motor VARCHAR(40) NULL,
    marca VARCHAR(60),
    modelo VARCHAR(60),
    anio YEAR,
    color VARCHAR(40),
    kilometraje INT NULL,
    fecha_venta DATE NULL,
    tipo_vehiculo ENUM('automovil','camioneta','otro') DEFAULT 'automovil',
    numero_licencia_transito VARCHAR(30),
    cliente_id INT NOT NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
);

-- CITAS
CREATE TABLE citas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) NOT NULL UNIQUE,
    fecha_id INT NOT NULL,
    vehiculo_id INT NOT NULL,
    estado ENUM('pendiente','confirmada','cancelada','reagendada','completada') DEFAULT 'pendiente',
    observaciones_cliente TEXT,
    correo_enviado TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fecha_id) REFERENCES fechas_disponibles(id),
    FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id)
);

-- USUARIOS (rol enum)
CREATE TABLE usuarios_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('admin','instructor','aprendiz') NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- PUNTOS CATÁLOGO
CREATE TABLE puntos_catalogo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_punto TINYINT NOT NULL,
    categoria VARCHAR(80),
    descripcion VARCHAR(200) NOT NULL,
    unidad_medida VARCHAR(40),
    activo TINYINT(1) DEFAULT 1
);

-- INSPECCIONES
CREATE TABLE inspecciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) UNIQUE NULL,
    cita_id INT NULL,
    aprendiz_id INT NULL,
    instructor_id INT NULL,
    estado ENUM('en_proceso','finalizada','aprobada') DEFAULT 'en_proceso',
    porcentaje_avance TINYINT DEFAULT 0,
    observaciones_finales TEXT,
    aprobado_instructor TINYINT(1) DEFAULT 0,
    informe_enviado TINYINT(1) DEFAULT 0,
    inicio_at TIMESTAMP NULL,
    fin_at TIMESTAMP NULL,
    FOREIGN KEY (cita_id) REFERENCES citas(id),
    FOREIGN KEY (aprendiz_id) REFERENCES usuarios_sistema(id),
    FOREIGN KEY (instructor_id) REFERENCES usuarios_sistema(id)
);

-- CHECKLIST DATOS
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

-- RESULTADOS PUNTOS (estado enum)
CREATE TABLE resultados_puntos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inspeccion_id INT NOT NULL,
    punto_id INT NOT NULL,
    valor_medido VARCHAR(100),
    estado ENUM('bueno','regular','malo','no_aplica') NOT NULL,
    observacion TEXT,
    tiene_evidencia TINYINT(1) DEFAULT 0,
    registrado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inspeccion_id) REFERENCES inspecciones(id),
    FOREIGN KEY (punto_id) REFERENCES puntos_catalogo(id),
    UNIQUE KEY uq_inspeccion_punto (inspeccion_id, punto_id)
);

-- EVIDENCIAS
CREATE TABLE evidencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resultado_punto_id INT NOT NULL,
    ruta_archivo VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resultado_punto_id) REFERENCES resultados_puntos(id) ON DELETE CASCADE
);

-- RECEPCIÓN (vinculada a cita)
CREATE TABLE recepcion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cita_id INT NOT NULL UNIQUE,
    inspeccion_id INT NULL,
    kilometraje_recepcion INT NULL,
    fecha_servicio_anterior DATE,
    or_numero VARCHAR(40),
    tipo_servicio_anterior VARCHAR(120),
    km_servicio_anterior INT,
    vehiculo_conducido_por ENUM('dueno','chofer','familiar','otro') DEFAULT 'dueno',
    presupuesto_repuestos DECIMAL(12,2) DEFAULT 0,
    presupuesto_mano_obra DECIMAL(12,2) DEFAULT 0,
    presupuesto_total DECIMAL(12,2) DEFAULT 0,
    metodo_pago ENUM('tarjeta_credito','efectivo','otro') DEFAULT 'efectivo',
    accesorios_internos JSON,
    accesorios_externos JSON,
    recibo_repuesto_cambiados TINYINT(1) DEFAULT 0,
    observaciones TEXT,
    defectos_carroceria TEXT,
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

-- INSPECCIÓN AYUDANTES
INSERT INTO configuracion (clave, valor, descripcion, tipo) VALUES
('tutor_mantenimiento_id', '', 'ID del instructor tutor del módulo de mantenimiento', 'numero')
ON DUPLICATE KEY UPDATE descripcion = VALUES(descripcion), tipo = VALUES(tipo);

CREATE TABLE inspeccion_ayudantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inspeccion_id INT NOT NULL,
    aprendiz_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inspeccion_id) REFERENCES inspecciones(id) ON DELETE CASCADE,
    FOREIGN KEY (aprendiz_id) REFERENCES usuarios_sistema(id) ON DELETE CASCADE,
    UNIQUE KEY uq_inspeccion_aprendiz (inspeccion_id, aprendiz_id)
);

-- PUNTOS CATÁLOGO (25 puntos)
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
