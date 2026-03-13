-- Migración 003: estado de carrocería y nivel de combustible (recepción)
-- Requiere: BD con database.sql, migrations 001 y 002

ALTER TABLE checklist_datos
    ADD COLUMN carroceria_json TEXT NULL COMMENT 'JSON: daños por zona (golpe, rayón, abolladura)' AFTER nota_mantenimiento,
    ADD COLUMN nivel_combustible VARCHAR(10) NULL COMMENT '0, 1_4, 1_2, 3_4, 1' AFTER carroceria_json;
