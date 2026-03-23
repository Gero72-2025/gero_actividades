<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITENAME; ?> - Recuperar Contraseña</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?php echo URLROOT; ?>/public/css/style.css">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
        }

        .recover-container {
            width: 100%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .recover-card-wrapper {
            width: 95%;
            max-width: 500px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
        }

        .recover-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 40px 30px;
            text-align: center;
            color: white;
        }

        .recover-header .icon {
            font-size: 60px;
            margin-bottom: 15px;
        }

        .recover-header h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .recover-header p {
            font-size: 14px;
            opacity: .85;
            margin: 0;
        }

        .recover-body {
            padding: 40px;
        }

        .form-label {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        .form-control {
            height: 45px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color .3s ease, box-shadow .3s ease;
            padding: 10px 15px;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, .15);
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        .btn-recover {
            width: 100%;
            height: 45px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: transform .2s ease, box-shadow .2s ease;
            letter-spacing: .5px;
        }

        .btn-recover:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, .4);
            color: white;
        }

        .btn-back {
            width: 100%;
            height: 45px;
            background: transparent;
            border: 2px solid #667eea;
            border-radius: 8px;
            color: #667eea;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: background .2s ease, color .2s ease;
        }

        .btn-back:hover {
            background: #667eea;
            color: white;
        }

        .alert-success-custom {
            background: #d1fae5;
            border: 1px solid #6ee7b7;
            border-radius: 10px;
            color: #065f46;
            padding: 20px 24px;
        }

        .alert-success-custom .icon-check {
            font-size: 40px;
            color: #10b981;
            margin-bottom: 10px;
        }

        .text-muted-sm {
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>
<div class="recover-container">
    <div class="recover-card-wrapper">

        <!-- Cabecera -->
        <div class="recover-header">
            <div class="icon"><i class="bi bi-key-fill"></i></div>
            <h1>Recuperar Contraseña</h1>
            <p>Ingresa tu correo y te enviaremos una contraseña temporal</p>
        </div>

        <!-- Cuerpo -->
        <div class="recover-body">

            <?php if($data['exito']): ?>
                <!-- ── Mensaje de éxito ──────────────────────────── -->
                <div class="alert-success-custom text-center mb-4">
                    <div class="icon-check"><i class="bi bi-check-circle-fill"></i></div>
                    <h5 class="fw-bold mb-2">¡Solicitud recibida!</h5>
                    <p class="mb-0">
                        Si el correo <strong><?php echo htmlspecialchars($data['email']); ?></strong>
                        está registrado en el sistema, recibirás un correo con tu contraseña temporal
                        en los próximos minutos.
                    </p>
                    <p class="mt-2 mb-0 text-muted-sm">
                        Revisa también tu carpeta de spam.
                    </p>
                </div>
                <div class="d-grid gap-2 mt-4">
                    <a href="<?php echo URLROOT; ?>/users/login" class="btn-back btn">
                        <i class="bi bi-box-arrow-in-right"></i> Volver al Inicio de Sesión
                    </a>
                </div>

            <?php else: ?>
                <!-- ── Formulario de solicitud ───────────────────── -->
                <p class="text-muted mb-4" style="font-size:14px;">
                    Ingresa el correo electrónico asociado a tu cuenta y generaremos
                    una contraseña temporal válida por <strong>24 horas</strong>.
                </p>

                <form action="<?php echo URLROOT; ?>/users/recover" method="post" novalidate>

                    <div class="mb-4">
                        <label for="email" class="form-label">
                            <i class="bi bi-envelope"></i> Correo Electrónico <sup class="text-danger">*</sup>
                        </label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="form-control <?php echo !empty($data['email_err']) ? 'is-invalid' : ''; ?>"
                            value="<?php echo htmlspecialchars($data['email']); ?>"
                            placeholder="tu@correo.com"
                            autofocus
                            required
                        >
                        <?php if(!empty($data['email_err'])): ?>
                            <div class="invalid-feedback d-block">
                                <i class="bi bi-exclamation-circle"></i>
                                <?php echo $data['email_err']; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="d-grid gap-2">
                        <button type="submit" class="btn-recover btn">
                            <i class="bi bi-send"></i> Enviar Contraseña Temporal
                        </button>
                        <a href="<?php echo URLROOT; ?>/users/login" class="btn-back btn">
                            <i class="bi bi-arrow-left"></i> Cancelar
                        </a>
                    </div>

                </form>
            <?php endif; ?>

        </div><!-- /.recover-body -->
    </div><!-- /.recover-card-wrapper -->
</div><!-- /.recover-container -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
