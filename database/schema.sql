-- ===================================
-- MecaQuick - Esquema v1.0 (3NF normalizado)
-- Instalación nueva: ejecutar este archivo
-- Esquema legacy: database/versions/v0.1/schema.sql
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

-- FECHAS DISPONIBLES
CREATE TABLE fechas_disponibles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fecha DATE NOT NULL UNIQUE,
    max_cupos INT NOT NULL DEFAULT 4,
    activa TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- CLIENTES (expandido con datos recepción)
CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    documento VARCHAR(20) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100),
    email VARCHAR(150),
    telefono VARCHAR(20),
    celular VARCHAR(20),
    direccion TEXT,
    ciudad VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- TIPOS_VEHICULO
CREATE TABLE tipos_vehiculo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE,
    descripcion VARCHAR(100)
);

INSERT INTO tipos_vehiculo (nombre, descripcion) VALUES
('automovil', 'Vehículo particular 4 ruedas'),
('camioneta', 'SUV/Pickup'),
('moto', 'Motocicleta'),
('otro', 'Camión, bus, etc.');

-- COMBUSTIBLES
CREATE TABLE combustibles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(30) NOT NULL UNIQUE,
    descripcion VARCHAR(50)
);

INSERT INTO combustibles (nombre, descripcion) VALUES
('gasolina', 'Gasolina corriente/super'),
('diesel', 'Diésel/ACPM'),
('hibrido', 'Híbrido gasolina/eléctrico'),
('electrico', '100% eléctrico');

-- VEHÍCULOS
CREATE TABLE vehiculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    placa VARCHAR(10) NOT NULL UNIQUE,
    vin VARCHAR(50),
    numero_motor VARCHAR(50),
    marca VARCHAR(60),
    modelo VARCHAR(60),
    codigo_modelo VARCHAR(50),
    color VARCHAR(40),
    anio YEAR,
    numero_licencia_transito VARCHAR(30),
    tipo_vehiculo_id INT,
    combustible_id INT,
    cliente_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    FOREIGN KEY (tipo_vehiculo_id) REFERENCES tipos_vehiculo(id),
    FOREIGN KEY (combustible_id) REFERENCES combustibles(id)
);

-- ROLES USUARIO
CREATE TABLE roles_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre ENUM('admin','instructor','aprendiz','asesor_servicio') NOT NULL UNIQUE,
    descripcion VARCHAR(100)
);

INSERT INTO roles_usuario (nombre, descripcion) VALUES
('admin', 'Administrador sistema'),
('instructor', 'Instructor revisión / Mecánico líder'),
('aprendiz', 'Aprendiz SENA / Mecánico'),
('asesor_servicio', 'Asesor servicio Audi/otros');

-- USUARIOS SISTEMA
CREATE TABLE usuarios_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    rol_id INT NOT NULL,
    telefono VARCHAR(20),
    activo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (rol_id) REFERENCES roles_usuario(id)
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

-- ESTADOS INSPECCIÓN
CREATE TABLE estados_inspeccion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre ENUM('en_proceso','finalizada','aprobada') NOT NULL UNIQUE
);

INSERT INTO estados_inspeccion (nombre) VALUES
('en_proceso'), ('finalizada'), ('aprobada');

-- INSPECCIONES (cita_id NULL para módulo mantenimiento standalone; aprendiz_id NULL hasta asignación)
CREATE TABLE inspecciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    token VARCHAR(64) NOT NULL UNIQUE,
    cita_id INT NULL,
    aprendiz_id INT NULL,
    instructor_id INT NULL,
    estado_id INT NOT NULL,
    porcentaje_avance TINYINT DEFAULT 0,
    observaciones_finales TEXT,
    aprobado_instructor TINYINT(1) DEFAULT 0,
    informe_enviado TINYINT(1) DEFAULT 0,
    inicio_at TIMESTAMP NULL,
    fin_at TIMESTAMP NULL,
    FOREIGN KEY (cita_id) REFERENCES citas(id) ON DELETE SET NULL,
    FOREIGN KEY (aprendiz_id) REFERENCES usuarios_sistema(id),
    FOREIGN KEY (instructor_id) REFERENCES usuarios_sistema(id),
    FOREIGN KEY (estado_id) REFERENCES estados_inspeccion(id)
);

-- ESTADOS PUNTO
CREATE TABLE estados_punto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre ENUM('bueno','regular','malo','no_aplica') NOT NULL UNIQUE,
    descripcion VARCHAR(100)
);

INSERT INTO estados_punto (nombre, descripcion) VALUES
('bueno', 'Cumple norma'),
('regular', 'Requiere atención'),
('malo', 'Fuera servicio'),
('no_aplica', 'No corresponde');

-- PUNTOS CATÁLOGO
CREATE TABLE puntos_catalogo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_punto TINYINT NOT NULL,
    categoria VARCHAR(80),
    descripcion VARCHAR(200) NOT NULL,
    unidad_medida VARCHAR(40),
    activo TINYINT(1) DEFAULT 1,
    UNIQUE KEY uk_numero_categoria (numero_punto, categoria)
);

-- RESULTADOS PUNTOS
CREATE TABLE resultados_puntos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inspeccion_id INT NOT NULL,
    punto_id INT NOT NULL,
    valor_medido VARCHAR(100),
    estado_id INT NOT NULL,
    observacion TEXT,
    tiene_evidencia TINYINT(1) DEFAULT 0,
    registrado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inspeccion_id) REFERENCES inspecciones(id),
    FOREIGN KEY (punto_id) REFERENCES puntos_catalogo(id),
    FOREIGN KEY (estado_id) REFERENCES estados_punto(id),
    UNIQUE KEY uq_inspeccion_punto (inspeccion_id, punto_id)
);

-- TIPOS RECEPCIÓN
CREATE TABLE tipos_recepcion (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL UNIQUE
);

INSERT INTO tipos_recepcion (nombre) VALUES ('Ingreso Normal'), ('Recepción Activa');

-- RECEPCIONES ORDEN TRABAJO
CREATE TABLE recepciones_orden_trabajo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inspeccion_id INT NOT NULL UNIQUE,
    numero_ot VARCHAR(50) NOT NULL UNIQUE,
    fecha_apertura DATE NOT NULL,
    hora_apertura TIME,
    fecha_entrada DATE,
    hora_entrada TIME,
    fecha_cita DATE,
    hora_cita TIME,
    fecha_entrega_estimada DATE,
    kilometros_entrada INT,
    nivel_combustible VARCHAR(20),
    tipo_recepcion_id INT,
    requiere_taxi TINYINT(1) DEFAULT 0,
    requiere_prueba_ruta TINYINT(1) DEFAULT 0,
    costo_aproximado DECIMAL(12,2),
    observaciones_recepcion TEXT,
    nota_cliente TEXT,
    asesor_servicio_id INT,
    FOREIGN KEY (inspeccion_id) REFERENCES inspecciones(id),
    FOREIGN KEY (tipo_recepcion_id) REFERENCES tipos_recepcion(id),
    FOREIGN KEY (asesor_servicio_id) REFERENCES usuarios_sistema(id)
);

-- TALLERES
CREATE TABLE talleres (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL UNIQUE
);

INSERT INTO talleres (nombre) VALUES ('Taller Mecánica'), ('Taller Latoneria'), ('Taller Pintura');

-- ESTADOS ANOMALÍA
CREATE TABLE estados_anomalia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre ENUM('abierta','cerrada') NOT NULL UNIQUE
);

INSERT INTO estados_anomalia (nombre) VALUES ('abierta'), ('cerrada');

-- ANOMALÍAS REPORTADAS
CREATE TABLE anomalias_reportadas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    recepcion_id INT NOT NULL,
    numero_anomalia TINYINT NOT NULL,
    denominacion VARCHAR(100),
    taller_id INT,
    descripcion_cliente TEXT NOT NULL,
    estado_anomalia_id INT NOT NULL DEFAULT 1,
    fecha_alta DATE,
    FOREIGN KEY (recepcion_id) REFERENCES recepciones_orden_trabajo(id),
    FOREIGN KEY (taller_id) REFERENCES talleres(id),
    FOREIGN KEY (estado_anomalia_id) REFERENCES estados_anomalia(id),
    UNIQUE KEY uk_recepcion_numero (recepcion_id, numero_anomalia)
);

-- ESTADOS AUDI
CREATE TABLE estados_audi (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre ENUM('ok_realizado','no_ok','subsanada') NOT NULL UNIQUE
);

INSERT INTO estados_audi (nombre) VALUES
('ok_realizado'), ('no_ok'), ('subsanada');

-- AUDI PUNTOS CATÁLOGO
CREATE TABLE audi_puntos_catalogo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grupo VARCHAR(80) NOT NULL,
    descripcion VARCHAR(255) NOT NULL,
    unidad_medida VARCHAR(40),
    punto_catalogo_id INT NULL,
    activo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (punto_catalogo_id) REFERENCES puntos_catalogo(id),
    UNIQUE KEY uk_grupo_descripcion (grupo, descripcion)
);

-- CHECKLIST DATOS (cabecera del checklist técnico - compatibilidad con flujo actual)
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

-- AUDI CHECKLIST
CREATE TABLE audi_checklist (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inspeccion_id INT NOT NULL UNIQUE,
    num_orden VARCHAR(50),
    kilometraje INT,
    fecha_inspeccion DATE,
    km_prueba_salida INT,
    km_prueba_llegada INT,
    nota_mantenimiento TEXT,
    creado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inspeccion_id) REFERENCES inspecciones(id)
);

-- AUDI RESULTADOS PUNTOS
CREATE TABLE audi_resultados_puntos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    audi_checklist_id INT NOT NULL,
    punto_id INT NOT NULL,
    valor_medido VARCHAR(100),
    estado_audi_id INT NOT NULL,
    observacion TEXT,
    registrado_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (audi_checklist_id) REFERENCES audi_checklist(id),
    FOREIGN KEY (punto_id) REFERENCES audi_puntos_catalogo(id),
    FOREIGN KEY (estado_audi_id) REFERENCES estados_audi(id),
    UNIQUE KEY uq_audi_checklist_punto (audi_checklist_id, punto_id)
);

-- ÓRDENES REPUESTOS
CREATE TABLE ordenes_repuestos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inspeccion_id INT NOT NULL,
    numero_orden VARCHAR(50),
    numero_rombo VARCHAR(50),
    nit_cliente VARCHAR(20),
    total_repuestos DECIMAL(12,2) DEFAULT 0,
    fecha_creacion DATE,
    FOREIGN KEY (inspeccion_id) REFERENCES inspecciones(id)
);

-- ITEMS REPUESTOS
CREATE TABLE items_repuestos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    orden_repuestos_id INT NOT NULL,
    referencia VARCHAR(100),
    descripcion VARCHAR(255) NOT NULL,
    cantidad DECIMAL(10,2),
    precio_unitario DECIMAL(12,2),
    subtotal DECIMAL(12,2) GENERATED ALWAYS AS (cantidad * precio_unitario) STORED,
    FOREIGN KEY (orden_repuestos_id) REFERENCES ordenes_repuestos(id)
);

-- INSPECCIÓN AYUDANTES (integrantes del grupo de revisión)
CREATE TABLE inspeccion_ayudantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inspeccion_id INT NOT NULL,
    aprendiz_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inspeccion_id) REFERENCES inspecciones(id) ON DELETE CASCADE,
    FOREIGN KEY (aprendiz_id) REFERENCES usuarios_sistema(id) ON DELETE CASCADE,
    UNIQUE KEY uq_inspeccion_aprendiz (inspeccion_id, aprendiz_id)
);

-- EVIDENCIAS
CREATE TABLE evidencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    resultado_punto_id INT NOT NULL,
    ruta_archivo VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (resultado_punto_id) REFERENCES resultados_puntos(id)
);

-- DATOS INICIALES
INSERT INTO configuracion (clave, valor, descripcion, tipo) VALUES
('nombre_sistema', 'CTA SENA - Revisión Mecánica', 'Nombre del sistema', 'texto'),
('max_cupos_dia', '4', 'Máximo de vehículos por día', 'numero'),
('hora_inicio', '07:00', 'Hora de inicio de jornada', 'texto'),
('hora_fin', '11:30', 'Hora de cierre de jornada', 'texto'),
('smtp_host', 'smtp.gmail.com', 'Servidor SMTP', 'texto'),
('smtp_port', '587', 'Puerto SMTP', 'numero'),
('smtp_user', '', 'Usuario SMTP', 'texto'),
('smtp_pass', '', 'Contraseña SMTP', 'texto'),
('smtp_from', '', 'Correo remitente', 'texto'),
('sistema_activo', '1', 'Sistema habilitado', 'booleano'),
('zona_reunion', 'CTA Caquetá, vía al aeropuerto', 'Zona de encuentro para la cita', 'texto'),
('horario_atencion', '07:00 - 11:30', 'Horario de atención al público', 'texto');

INSERT INTO fechas_disponibles (fecha) VALUES ('2026-03-12'), ('2026-03-13');

-- PUNTOS CATÁLOGO (25 puntos técnicos)
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
