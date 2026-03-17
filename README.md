# MecaQuick

Sistema de gestión para revisión mecánica de vehículos (CTA SENA). Permite el registro público de citas, el checklist de mantenimiento por aprendices y la supervisión en tiempo real por instructores.

## Requisitos

- PHP 8.1 o superior
- MySQL 8
- Servidor web (Apache con mod_rewrite o servidor embebido de PHP)

## Instalación

1. Clonar o copiar el proyecto en el directorio deseado.

2. Crear el archivo `.env` en la raíz del proyecto con las variables de base de datos:

   ```
   DB_HOST=localhost
   DB_PORT=3306
   DB_NAME=mecaquick
   DB_CHARSET=utf8mb4
   DB_USER=tu_usuario
   DB_PASS=tu_contraseña
   ```

   Opcional: `APP_DEBUG=1` para mostrar errores en pantalla (no usar en producción).

3. Crear la base de datos y ejecutar los scripts SQL en este orden:
   - `database/database.sql` (esquema base y datos iniciales)
   - `database/migrations/001_checklist_puntos_inspecciones.sql` (módulo checklist)
   - `database/migrations/002_inspeccion_ayudantes_tutor.sql`
   - `database/migrations/003_recepcion_carroceria_combustible.sql`
   - `database/migrations/004_ordenes_repuestos.sql` (orden de repuestos)

4. Punto de entrada: configurar el servidor para que apunte a la carpeta `public/`.
   - **Apache**: DocumentRoot o alias hacia `public/`; el `.htaccess` de la raíz redirige todo a `public/index.php`.
   - **Servidor embebido PHP**: desde la raíz del proyecto:
     ```bash
     php -S localhost:7000 -t public
     ```
     La aplicación quedará en `http://localhost:7000`.

## Configuración

### Variables de entorno (`.env`)

| Variable   | Descripción              | Ejemplo    |
|-----------|--------------------------|------------|
| DB_HOST   | Servidor MySQL           | localhost  |
| DB_PORT   | Puerto MySQL             | 3306       |
| DB_NAME   | Nombre de la base de datos | mecaquick |
| DB_USER   | Usuario MySQL            | —          |
| DB_PASS   | Contraseña MySQL         | —          |
| APP_DEBUG | Mostrar errores (0/1)     | 0          |

### Configuración en base de datos (tabla `configuracion`)

La aplicación lee la configuración desde la tabla `configuracion` (clave-valor). Claves principales:

| Clave             | Uso                                      |
|-------------------|------------------------------------------|
| nombre_sistema    | Nombre mostrado en la aplicación         |
| max_cupos_dia     | Máximo de vehículos por día              |
| hora_inicio / hora_fin | Horario de jornada                  |
| smtp_host, smtp_port, smtp_user, smtp_pass, smtp_from | Envío de correo (citas, credenciales) |
| sistema_activo    | Habilitar o desactivar el sistema       |
| zona_reunion      | Lugar de encuentro para la cita          |
| horario_atencion  | Texto de horario al público              |

Los valores se pueden cambiar desde el panel de administración (si está implementado) o directamente en la base de datos.

## Uso del aplicativo

### Público (sin sesión)

- **Inicio**: descripción del programa y enlace para solicitar cita.
- **Solicitar cita**: formulario con datos del cliente, vehículo y selección de fecha. Las fechas con cupo disponible se muestran; al confirmar se envía un correo con el resumen (si SMTP está configurado).
- **Checklist de vehículos** (`/checklist`): formulario de checklist de mantenimiento por pasos; el avance se guarda con un token.

### Con sesión (admin, instructor, aprendiz)

- **Login**: `/login` con email y contraseña de usuario del sistema.
- **Panel**: `/dashboard` según el rol.

| Rol        | Acceso principal                                                                 |
|-----------|------------------------------------------------------------------------------------|
| **Admin** | Gestión de instructores y aprendices (crear usuarios, envío de credenciales por correo). Acceso al checklist y al panel de revisiones. |
| **Instructor** | Panel de revisiones (`/checklist/panel`): filtro por día, listado de revisiones activas (placa, encargado, hora inicio, porcentaje). Detalle de cada revisión con tabla de puntos y evidencias, con actualización automática (polling). |
| **Aprendiz** | Checklist de vehículos para realizar o continuar revisiones.                        |

### Panel de revisiones (instructor / admin)

1. Ir a **Panel de revisiones** desde el menú o el dashboard.
2. Elegir una **fecha** en el desplegable (solo se muestran fechas con al menos una revisión).
3. Ver la tabla del día: placa, encargado (líder del grupo), hora de inicio, porcentaje de avance.
4. Pulsar **Ver revisión** para abrir el detalle: cabecera y tabla de puntos ya enviados con evidencias. La vista se actualiza en segundo plano cada unos segundos.

## Estructura del proyecto

```
/config          Configuración (app.php, database.php) y variables .env
/core            Router, BaseController, BaseModel, Database
/app
  /Controllers   Lógica de control (Home, Auth, Dashboard, Usuarios, Checklist)
  /Models        Acceso a datos (Configuracion, Cita, Checklist, Inspeccion, etc.)
  /Views         Vistas PHP por módulo
  /Services      MailService, AuthService
/database        database.sql y migraciones
/public          index.php (entrada única), /assets (CSS, JS, img), /uploads
/libs            PHPMailer (incluido manualmente)
```

Todo el tráfico HTTP pasa por `public/index.php`. Las rutas se resuelven por convención (ej.: `/checklist/panel` → `ChecklistController::panel()`).

## Notas

- No se usan frameworks PHP ni npm; el frontend es HTML/CSS/JS vanilla.
- Credenciales y datos sensibles solo en `.env` y en la tabla `configuracion`; no se hardcodean en el código.
- El encargado de cada revisión en el panel es el aprendiz que inicia o crea el checklist; si no está asignado, se muestra "Sin asignar" o el asesor de los datos del checklist.
