-- ===================================
-- Seeds: usuarios por rol (v1.0)
-- Ejecutar tras schema.sql
-- Contraseña común: admin123
-- ===================================

-- Administrador
INSERT INTO usuarios_sistema (nombre, email, password_hash, rol) VALUES
('Administrador', 'admin@mecanica.com', '$2y$12$j3.C9TDsuiG0oCUgei5/k.LXsIxHITHVlJcccFWAPha7bdkDNJkHq', 1);

-- Instructor / Mecánico líder
INSERT INTO usuarios_sistema (nombre, email, password_hash, rol) VALUES
('Instructor Líder', 'instructor@mecanica.com', '$2y$12$j3.C9TDsuiG0oCUgei5/k.LXsIxHITHVlJcccFWAPha7bdkDNJkHq', 2);

-- Aprendices / Mecánicos
INSERT INTO usuarios_sistema (nombre, email, password_hash, rol) VALUES
('Mecánico Aprendiz 1', 'aprendiz1@mecanica.com', '$2y$12$j3.C9TDsuiG0oCUgei5/k.LXsIxHITHVlJcccFWAPha7bdkDNJkHq', 3),
('Mecánico Aprendiz 2', 'aprendiz2@mecanica.com', '$2y$12$j3.C9TDsuiG0oCUgei5/k.LXsIxHITHVlJcccFWAPha7bdkDNJkHq', 3);