# Proyecto: Sistema de Gestión - Revisión Mecánica CTA SENA

## Contexto
Adjunto @database.sql con el esquema completo de la base de datos. 
Léelo antes de generar cualquier código.

## Stack
- PHP 8.1+ nativo (sin frameworks)
- MySQL 8 con PDO
- HTML/CSS/JS vanilla
- PHPMailer (incluido manualmente en /libs)

## Arquitectura MVC estricta
Estructura de carpetas:

/app/Controllers/
/app/Models/
/app/Views/
/app/Services/          ← MailService.php, PdfService.php
/core/                  ← Router.php, BaseController.php, BaseModel.php, Database.php
/config/                ← app.php, database.php
/public/                ← index.php (único punto de entrada), /assets, /uploads
.htaccess

Reglas:
- Todo el tráfico pasa por public/index.php via .htaccess
- BaseModel usa PDO con prepared statements siempre
- Los Controllers solo orquestan, la lógica va en Models/Services
- Las Views son PHP puro, sin lógica de negocio
- Credenciales y config sensible solo en /config, nunca hardcodeadas

## Sistema de Configuración
Existe la tabla `configuracion` (clave-valor en BD). 
Crear ConfiguracionModel con métodos estáticos get(clave) y set(clave, valor) 
con caché en propiedad estática para no repetir queries.
Toda configuración del sistema (SMTP, cupos, horarios) se lee desde ahí.

## Módulos a construir (en orden)

### MVP 1 — Registro público
1. Landing page con descripción del programa
2. Formulario de registro: nombre, apellido, documento, email, teléfono, 
   placa, marca, modelo, año del vehículo
3. Selección de fecha: mostrar solo fechas activas con cupos < max_cupos_dia
   (fechas llenas aparecen deshabilitadas visualmente, no ocultas)
4. Al confirmar cita: generar token único, guardar en BD, 
   enviar email SMTP con resumen de la cita via MailService
5. Página de éxito con datos del turno

### MVP 2 — Gestión interna (con login por roles)
Roles: admin, instructor, aprendiz

- Instructor: ver citas del día, asignar aprendiz a vehículo, 
  revisar progreso de inspección, aprobar y enviar informe final
- Aprendiz: ver vehículo asignado, registrar los 25 puntos 
  (mini formulario por punto: valor medido, estado, observación, foto)
  El porcentaje de avance = puntos_registrados / total_puntos * 100
- Admin: CRUD de fechas, gestión de configuración del sistema, 
  ver estadísticas básicas

## Estilo visual
- Responsive mobile-first
- Sin frameworks CSS externos, solo custom CSS

## Lo que NO debes hacer
- No usar frameworks PHP (Laravel, Symfony, etc.)
- No usar npm ni bundlers
- No generar lógica de negocio dentro de las Views
- No hardcodear ningún valor que esté en la tabla `configuracion`

## Por dónde empezar
Genera primero el core: .htaccess, public/index.php, core/Router.php, 
core/Database.php y core/BaseModel.php. 
Espera confirmación antes de continuar con los siguientes módulos.
