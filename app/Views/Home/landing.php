<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($titulo ?? 'MecaQuick') ?></title>
    <link rel="icon" type="image/png" href="/assets/img/logo_sena.png">
    <link rel="stylesheet" href="/assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
</head>
<body class="page">
    <?php require ROOT_PATH . '/app/Views/partials/header.php'; ?>

    <main>
        <section class="home-hero" aria-label="Presentación">
            
            <h1 class="home-hero__title">
                Gestiona tu revisión mecánica de forma <span class="home-hero__highlight">rápida</span>
            </h1>
            <p class="home-hero__desc">
                MecaQuick es la plataforma que permite agendar tu cita de revisión, elegir fecha según cupos disponibles y recibir la confirmación con horario y punto de encuentro por correo. Diseñada para simplificar el proceso.
            </p>
            <div class="home-hero__ctas">
                <a href="/home/formulario" class="btn btn--primary">Solicitar cita</a>
                <a href="/login" class="btn btn--secondary">Iniciar sesión</a>
            </div>
            <div class="home-features">
                <div class="home-feature">
                    <span class="home-feature__icon" aria-hidden="true">✓</span>
                    <span class="home-feature__text">Citas por cupos diarios</span>
                </div>
                <div class="home-feature">
                    <span class="home-feature__icon" aria-hidden="true">✓</span>
                    <span class="home-feature__text">Confirmación por correo</span>
                </div>
                <div class="home-feature">
                    <span class="home-feature__icon" aria-hidden="true">✓</span>
                    <span class="home-feature__text">Horario y punto de encuentro</span>
                </div>
                <div class="home-feature">
                    <span class="home-feature__icon" aria-hidden="true">✓</span>
                    <span class="home-feature__text">Token de confirmación</span>
                </div>
            </div>
        </section>

        <div class="home-content">
            <section class="home-content__section">
                <h2>¿Qué es MecaQuick?</h2>
                <p>
                    Es el sistema oficial del Centro de Tecnología Automotriz del SENA para solicitar y gestionar citas de revisión mecánica de vehículos. Permite elegir una fecha según los cupos disponibles y recibir por correo el horario, la zona de reunión (CTA Caquetá, vía al aeropuerto) y un token de confirmación.
                </p>
            </section>

            <section class="home-content__section">
                <h2>¿Cómo funciona?</h2>
                <p>
                    El aplicativo centraliza todo el proceso: registro del propietario y del vehículo, selección de fecha y confirmación. Los cupos por día son limitados; cada cita queda registrada con fecha y hora. Al confirmar, se envía un correo con el resumen del turno y el token que debes presentar el día de la revisión.
                </p>
            </section>

            <section class="home-content__section">
                <h2>Pasos para solicitar tu cita</h2>
                <ul>
                    <li><strong>Registro:</strong> Completa el formulario con tus datos y los del vehículo (placa y marca obligatorios; modelo opcional).</li>
                    <li><strong>Fecha:</strong> Elige una fecha disponible en el calendario; las sin cupos aparecen deshabilitadas.</li>
                    <li><strong>Confirmación:</strong> Recibirás por correo el horario de atención, la zona de reunión y tu token.</li>
                    <li><strong>Día de la cita:</strong> Asiste al centro en la fecha y horario indicados con tu token o el correo de confirmación.</li>
                </ul>
            </section>

            <section class="home-content__section">
                <h2>Recomendaciones</h2>
                <p>
                    Solicita tu cita con anticipación; los cupos son limitados por día. Si una fecha no tiene disponibilidad, elige otra. Para más información o dudas sobre tu cita, contacta al centro de atención.
                </p>
            </section>

            <div class="home-content__cta">
                <a href="/home/formulario" class="btn btn--primary">Solicitar cita</a>
            </div>
        </div>
    </main>

    <footer class="footer">
        <span>MecaQuick &mdash; Sistema de gestión de revisión mecánica</span>
    </footer>
</body>
</html>
