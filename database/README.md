# Base de datos MecaQuick

## Estructura

| Archivo/Carpeta | Uso |
|-----------------|-----|
| `schema.sql` | Esquema v1.0 (3NF). Instalaciones nuevas. |
| `database.sql` | Esquema base legacy (antes de migraciones). |
| `migrations/` | Migraciones 001, 002 para BD legacy (database.sql + migraciones). |
| `versions/v0.1/schema.sql` | Esquema v0.1 consolidado (legacy completo). |
| `seeds.sql` | Usuarios iniciales (admin, instructor, aprendices). |

## Instalación nueva (v1.0)

```bash
mysql -u root -p mecaquick < database/schema.sql
mysql -u root -p mecaquick < database/seeds.sql
```

Ajuste `.env` con `DB_NAME`, `DB_USER`, `DB_PASS`.

## Instalación legacy (v0.1: database.sql + migrations)

```bash
mysql -u root -p mecaquick < database/database.sql
mysql -u root -p mecaquick < database/migrations/001_checklist_puntos_inspecciones.sql
mysql -u root -p mecaquick < database/migrations/002_modulo_recepcion.sql
mysql -u root -p mecaquick < database/migrations/002_inspeccion_ayudantes_tutor.sql
```

## Migración desde v0.1

Ejecutar el script de migración de datos (si existe) o importar datos manualmente.
El esquema v1.0 introduce tablas normalizadas: `roles_usuario`, `tipos_vehiculo`, `combustibles`, `estados_inspeccion`, `estados_punto`, `recepciones_orden_trabajo`, etc.
