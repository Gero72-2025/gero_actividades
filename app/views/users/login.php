<!DOCTYPE html>
<html lang="es" class="login">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?php echo SITENAME; ?> - Login</title>
        <link rel="stylesheet" href="<?php echo URLROOT; ?>/public/css/style.css">
    </head>
    <body class="login">
        <div class="login-form-container">
            <div class="card login-card">
                <div class="card-body">
                    <h2>Iniciar Sesión</h2>
                    <p>Por favor, ingrese sus credenciales para iniciar sesión.</p>
                    <form action="<?php echo URLROOT; ?>/Users/login" method="post">
                        <div class="form-group">
                            <div class="row">
                                <label for="email">Email: <sup>*</sup></label>
                            </div>
                            <div class="row">
                                <input type="email" name="email" class="form-control form-control-lg <?php echo (!empty($data['email_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['email']; ?>">
                                <span class="invalid-feedback"><?php echo $data['email_err']; ?></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <label for="password">Contraseña: <sup>*</sup></label>
                            </div>
                            <div class="row">
                                <input type="password" name="password" class="form-control form-control-lg <?php echo (!empty($data['password_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['password']; ?>">
                                <span class="invalid-feedback"><?php echo $data['password_err']; ?></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <input type="submit" value="Login" class="btn btn-success btn-block">
                            </div>
                        </div>
                        <!-- <div class="form-group">
                            <div class="row">
                                <button type="button" value="Dashboard" class="btn btn-notification btn-block" onclick="window.location.href='<?php echo URLROOT; ?>/../gero/dashboard.html'">Dashboard</button>
                            </div>
                        </div> -->
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>
