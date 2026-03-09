-- Migración 002: tutor del módulo de mantenimiento y ayudantes por inspección
-- Requiere: BD con database.sql y migrations/001_checklist_puntos_inspecciones.sql

-- 1. Configuración: ID del instructor tutor del módulo de mantenimiento
INSERT INTO configuracion (clave, valor, descripcion, tipo) VALUES
('tutor_mantenimiento_id', '', 'ID del instructor tutor del módulo de mantenimiento', 'numero')
ON DUPLICATE KEY UPDATE descripcion = VALUES(descripcion), tipo = VALUES(tipo);

-- 2. Tabla de ayudantes por inspección (integrantes del grupo de revisión)
CREATE TABLE IF NOT EXISTS inspeccion_ayudantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    inspeccion_id INT NOT NULL,
    aprendiz_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (inspeccion_id) REFERENCES inspecciones(id) ON DELETE CASCADE,
    FOREIGN KEY (aprendiz_id) REFERENCES usuarios_sistema(id) ON DELETE CASCADE,
    UNIQUE KEY uq_inspeccion_aprendiz (inspeccion_id, aprendiz_id)
);
