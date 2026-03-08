-- ===================================
-- MecaQuick - Schema base
-- Instalación nueva: ejecutar este archivo y luego migrations/001_checklist_puntos_inspecciones.sql
-- ===================================

-- ===================================
-- CONFIGURACIÓN DEL SISTEMA (clave-valor)
-- ===================================
CREATE TABLE configuracion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clave VARCHAR(100) NOT NULL UNIQUE,
    valor TEXT NOT NULL,
    descripcion VARCHAR(255),
    tipo ENUM('texto','numero','booleano','json') DEFAULT 'texto',
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Datos iniciales configurables desde el admin
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

-- ===================================
-- FECHAS DISPONIBLES (configurables)
-- ===================================
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

-- ===================================
-- CLIENTES (persona que se registra)
-- ===================================
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    documento VARCHAR(20) NOT NULL UNIQUE,
    telefono VARCHAR(20),
    email VARCHAR(150) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ===================================
-- VEHÍCULOS
-- ===================================
CREATE TABLE vehiculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    placa VARCHAR(10) NOT NULL UNIQUE,
    marca VARCHAR(60),
    modelo VARCHAR(60),
    anio YEAR,
    color VARCHAR(40),
    tipo_vehiculo ENUM('automovil','camioneta','otro') DEFAULT 'automovil',
    numero_licencia_transito VARCHAR(30),
    cliente_id INT NOT NULL,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
);

-- ===================================
-- CITAS
-- ===================================
CREATE TABLE citas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) NOT NULL UNIQUE,   -- para confirmación por email
    fecha_id INT NOT NULL,
    vehiculo_id INT NOT NULL,
    estado ENUM('pendiente','confirmada','cancelada','reagendada','completada') DEFAULT 'pendiente',
    observaciones_cliente TEXT,
    correo_enviado TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (fecha_id) REFERENCES fechas_disponibles(id),
    FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id)
);

-- ===================================
-- USUARIOS DEL SISTEMA (admin, instructor, aprendiz)
-- ===================================
CREATE TABLE usuarios_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol ENUM('admin','instructor','aprendiz') NOT NULL,
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ===================================
-- CATÁLOGO DE PUNTOS DE INSPECCIÓN
-- Los 34 puntos del checklist se cargan vía migrations/001_checklist_puntos_inspecciones.sql
-- ===================================
CREATE TABLE puntos_catalogo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_punto TINYINT NOT NULL,
    categoria VARCHAR(80),
    descripcion VARCHAR(200) NOT NULL,
    unidad_medida VARCHAR(40),        -- ej: mm, voltios, N/A
    activo TINYINT(1) DEFAULT 1
);

-- ===================================
-- INSPECCIONES (vincula cita + aprendiz)
-- Para checklist standalone: ejecutar migrations/001_checklist_puntos_inspecciones.sql
-- ===================================
CREATE TABLE inspecciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cita_id INT NOT NULL UNIQUE,
    aprendiz_id INT NOT NULL,
    instructor_id INT,
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

-- ===================================
-- RESULTADOS POR PUNTO
-- ===================================
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

-- ===================================
-- EVIDENCIAS FOTOGRÁFICAS
-- ===================================
CREATE TABLE evidencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resultado_punto_id INT NOT NULL,
    ruta_archivo VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resultado_punto_id) REFERENCES resultados_puntos(id)
);
