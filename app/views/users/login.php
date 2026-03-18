<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITENAME; ?> - Iniciar Sesión</title>
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
        }

        .login-container {
            width: 100%;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }

        .login-card-wrapper {
            width: 95%;
            max-width: 1200px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            display: grid;
            grid-template-columns: 1fr 1fr;
            min-height: 550px;
        }

        .login-left-panel {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: white;
            padding: 50px;
        }

        .login-left-panel .platform-icon {
            font-size: 80px;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .login-left-panel h1 {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 20px;
            text-align: center;
        }

        .login-left-panel p {
            font-size: 16px;
            line-height: 1.8;
            text-align: center;
            opacity: 0.95;
            margin-bottom: 0;
        }

        .login-left-panel .features {
            margin-top: 30px;
            font-size: 14px;
            opacity: 0.85;
        }

        .login-left-panel .features li {
            margin-bottom: 10px;
        }

        .login-right-panel {
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 50px;
        }

        .login-right-panel h2 {
            font-size: 28px;
            font-weight: bold;
            color: #333;
            margin-bottom: 10px;
        }

        .login-right-panel .subtitle {
            color: #999;
            margin-bottom: 40px;
            font-size: 14px;
        }

        .login-form-group {
            margin-bottom: 25px;
        }

        .login-form-group label {
            display: block;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .login-form-group label sup {
            color: #dc3545;
        }

        .login-form-group input.form-control {
            height: 45px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 15px;
            transition: all 0.3s ease;
            padding: 10px 15px;
        }

        .login-form-group input.form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .login-form-group input.form-control.is-invalid {
            border-color: #dc3545;
        }

        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 12px;
            margin-top: 5px;
        }

        .login-buttons {
            display: flex;
            flex-direction: column;
            gap: 12px;
            margin-top: 30px;
        }

        .btn-login {
            height: 45px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 8px;
            color: white;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
            color: white;
            text-decoration: none;
        }

        .btn-recover {
            height: 45px;
            background: transparent;
            border: 2px solid #667eea;
            border-radius: 8px;
            color: #667eea;
            font-weight: 600;
            font-size: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn-recover:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }

        .login-divider {
            height: 100%;
            width: 1px;
            background: linear-gradient(to bottom, transparent, rgba(224, 224, 224, 0.5), transparent);
            position: absolute;
            left: 50%;
            transform: translateX(-0.5px);
        }

        @media (max-width: 768px) {
            .login-card-wrapper {
                grid-template-columns: 1fr;
                min-height: auto;
            }

            .login-left-panel {
                padding: 30px;
                min-height: auto;
            }

            .login-right-panel {
                padding: 30px;
            }

            .login-left-panel .platform-icon {
                font-size: 60px;
                margin-bottom: 15px;
            }

            .login-left-panel h1 {
                font-size: 20px;
                margin-bottom: 15px;
            }

            .login-left-panel p {
                font-size: 14px;
            }

            .login-left-panel .features {
                margin-top: 15px;
                font-size: 12px;
            }

            .login-right-panel h2 {
                font-size: 22px;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card-wrapper">
            <!-- Panel Izquierdo -->
            <div class="login-left-panel">
                <div class="platform-icon">
                    <i class="bi bi-kanban"></i>
                </div>
                <h1><?php echo SITENAME; ?></h1>
                <p>
                    Sistema integral de gestión de actividades, personal, contratos y división organizacional con panel de control ejecutivo y reportes PDF.
                </p>
                <ul class="features list-unstyled mt-4">
                    <li><i class="bi bi-check-circle"></i> Gestión completa de usuarios y roles</li>
                    <li><i class="bi bi-check-circle"></i> Tablero Kanban con cronómetro</li>
                    <li><i class="bi bi-check-circle"></i> Control de permisos granulares</li>
                    <li><i class="bi bi-check-circle"></i> Reportes PDF integrados</li>
                    <li><i class="bi bi-check-circle"></i> Dashboard ejecutivo</li>
                </ul>
            </div>

            <!-- Panel Derecho -->
            <div class="login-right-panel">
                <h2>Bienvenido</h2>
                <p class="subtitle">Ingresa tus credenciales para acceder al sistema</p>

                <form action="<?php echo URLROOT; ?>/Users/login" method="post" class="login-form">
                    <!-- Email -->
                    <div class="login-form-group">
                        <label for="email">
                            <i class="bi bi-envelope"></i> Correo Electrónico <sup>*</sup>
                        </label>
                        <input 
                            type="email" 
                            name="email" 
                            id="email"
                            class="form-control <?php echo (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>" 
                            value="<?php echo $data['email'] ?? ''; ?>"
                            placeholder="tu@correo.com"
                            required
                        >
                        <?php if(!empty($data['email_err'])): ?>
                            <span class="invalid-feedback"><?php echo $data['email_err']; ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Contraseña -->
                    <div class="login-form-group">
                        <label for="password">
                            <i class="bi bi-key"></i> Contraseña <sup>*</sup>
                        </label>
                        <input 
                            type="password" 
                            name="password" 
                            id="password"
                            class="form-control <?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>" 
                            value="<?php echo $data['password'] ?? ''; ?>"
                            placeholder="••••••••"
                            required
                        >
                        <?php if(!empty($data['password_err'])): ?>
                            <span class="invalid-feedback"><?php echo $data['password_err']; ?></span>
                        <?php endif; ?>
                    </div>

                    <!-- Botones de Acción -->
                    <div class="login-buttons">
                        <button type="submit" class="btn btn-login">
                            <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                        </button>
                        <button type="button" class="btn btn-recover" onclick="window.location.href='<?php echo URLROOT; ?>/users/recover'">
                            <i class="bi bi-key-fill"></i> Recuperar Contraseña
                        </button>
                    </div>
                </form>

                <!-- Info adicional -->
                <!-- <div style="margin-top: 30px; text-align: center; color: #999; font-size: 12px;">
                    <p style="margin: 0;">
                        <strong>Demo:</strong> admin@admin.com / Admin.62
                    </p>
                </div> -->
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
