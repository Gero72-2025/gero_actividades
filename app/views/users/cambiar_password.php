<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SITENAME; ?> - Establecer Nueva Contraseña</title>
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

        .change-container {
            width: 100%;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .change-card-wrapper {
            width: 95%;
            max-width: 520px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, .2);
            overflow: hidden;
        }

        .change-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 40px 30px;
            text-align: center;
            color: white;
        }

        .change-header .icon {
            font-size: 60px;
            margin-bottom: 15px;
        }

        .change-header h1 {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 8px;
        }

        .change-header p {
            font-size: 14px;
            opacity: .85;
            margin: 0;
        }

        .change-body {
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
            padding: 10px 42px 10px 15px;
        }

        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, .15);
        }

        .form-control.is-invalid {
            border-color: #dc3545;
        }

        .input-group .toggle-pass {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #999;
            font-size: 18px;
            z-index: 5;
        }

        .input-group .toggle-pass:hover {
            color: #667eea;
        }

        .input-wrapper {
            position: relative;
        }

        .btn-save {
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
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, .4);
            color: white;
        }

        /* Medidor de fortaleza */
        .strength-bar {
            height: 6px;
            border-radius: 3px;
            margin-top: 6px;
            background: #e0e0e0;
            overflow: hidden;
        }

        .strength-bar-fill {
            height: 100%;
            border-radius: 3px;
            width: 0;
            transition: width .4s ease, background-color .4s ease;
        }

        .strength-label {
            font-size: 11px;
            margin-top: 4px;
        }

        /* Requisitos */
        .req-list li {
            font-size: 12px;
            color: #888;
            margin-bottom: 4px;
            transition: color .3s ease;
        }

        .req-list li.ok {
            color: #10b981;
        }

        .req-list li .bi {
            margin-right: 4px;
        }

        .notice-box {
            background: #fffbeb;
            border: 1px solid #fcd34d;
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 13px;
            color: #92400e;
            margin-bottom: 24px;
        }
    </style>
</head>
<body>
<div class="change-container">
    <div class="change-card-wrapper">

        <!-- Cabecera -->
        <div class="change-header">
            <div class="icon"><i class="bi bi-shield-lock-fill"></i></div>
            <h1>Establecer Nueva Contraseña</h1>
            <p>Tu contraseña temporal debe ser reemplazada antes de continuar</p>
        </div>

        <!-- Cuerpo -->
        <div class="change-body">

            <!-- Aviso temporal -->
            <div class="notice-box">
                <i class="bi bi-exclamation-triangle-fill"></i>
                <strong>Atención:</strong> Ingresaste con una contraseña temporal.
                Por seguridad, debes establecer una nueva contraseña permanente ahora.
            </div>

            <form action="<?php echo URLROOT; ?>/users/cambiarPassword" method="post" novalidate id="formCambio">

                <!-- Nueva contraseña -->
                <div class="mb-3">
                    <label for="pass" class="form-label">
                        <i class="bi bi-lock"></i> Nueva Contraseña <sup class="text-danger">*</sup>
                    </label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            name="pass"
                            id="pass"
                            class="form-control <?php echo !empty($data['pass_err']) ? 'is-invalid' : ''; ?>"
                            placeholder="Mínimo 8 caracteres"
                            autofocus
                            required
                        >
                        <button type="button" class="toggle-pass" onclick="toggleVer('pass', this)">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                    <?php if(!empty($data['pass_err'])): ?>
                        <div class="invalid-feedback d-block">
                            <i class="bi bi-exclamation-circle"></i>
                            <?php echo $data['pass_err']; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Medidor de fortaleza -->
                    <div class="strength-bar mt-2">
                        <div class="strength-bar-fill" id="strengthFill"></div>
                    </div>
                    <div class="strength-label text-muted" id="strengthLabel"></div>
                </div>

                <!-- Requisitos -->
                <ul class="req-list list-unstyled mb-3" id="reqList">
                    <li id="req-len"><i class="bi bi-x-circle"></i> Al menos 8 caracteres</li>
                    <li id="req-upper"><i class="bi bi-x-circle"></i> Al menos una mayúscula</li>
                    <li id="req-digit"><i class="bi bi-x-circle"></i> Al menos un número</li>
                </ul>

                <!-- Confirmar contraseña -->
                <div class="mb-4">
                    <label for="confirm_pass" class="form-label">
                        <i class="bi bi-lock-fill"></i> Confirmar Contraseña <sup class="text-danger">*</sup>
                    </label>
                    <div class="input-wrapper">
                        <input
                            type="password"
                            name="confirm_pass"
                            id="confirm_pass"
                            class="form-control <?php echo !empty($data['confirm_pass_err']) ? 'is-invalid' : ''; ?>"
                            placeholder="Repite la contraseña"
                            required
                        >
                        <button type="button" class="toggle-pass" onclick="toggleVer('confirm_pass', this)">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                    <?php if(!empty($data['confirm_pass_err'])): ?>
                        <div class="invalid-feedback d-block">
                            <i class="bi bi-exclamation-circle"></i>
                            <?php echo $data['confirm_pass_err']; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn-save btn" id="btnGuardar">
                        <i class="bi bi-check-lg"></i> Guardar Nueva Contraseña
                    </button>
                </div>

            </form>
        </div><!-- /.change-body -->
    </div><!-- /.change-card-wrapper -->
</div><!-- /.change-container -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
// ── Mostrar/ocultar contraseña ─────────────────────────────────────
function toggleVer(inputId, btn) {
    const input = document.getElementById(inputId);
    const icon  = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        icon.className = 'bi bi-eye';
    } else {
        input.type = 'password';
        icon.className = 'bi bi-eye-slash';
    }
}

// ── Medidor de fortaleza ──────────────────────────────────────────
const passInput    = document.getElementById('pass');
const fill         = document.getElementById('strengthFill');
const label        = document.getElementById('strengthLabel');
const reqLen       = document.getElementById('req-len');
const reqUpper     = document_getElementById_safe('req-upper');
const reqDigit     = document_getElementById_safe('req-digit');

function document_getElementById_safe(id) {
    return document.getElementById(id);
}

const reqUpperEl = document.getElementById('req-upper');
const reqDigitEl = document.getElementById('req-digit');

passInput.addEventListener('input', function () {
    const val = this.value;
    let score = 0;

    const hasLen   = val.length >= 8;
    const hasUpper = /[A-Z]/.test(val);
    const hasDigit = /[0-9]/.test(val);
    const hasSpecial = /[@#$%!^&*]/.test(val);

    // Requisitos visuales
    setReq(reqLen,       hasLen);
    setReq(reqUpperEl,   hasUpper);
    setReq(reqDigitEl,   hasDigit);

    if (hasLen)     score++;
    if (hasUpper)   score++;
    if (hasDigit)   score++;
    if (hasSpecial) score++;
    if (val.length >= 12) score++;

    const levels = [
        { color: '#ef4444', text: 'Muy débil',  pct: '20%'  },
        { color: '#f97316', text: 'Débil',       pct: '40%'  },
        { color: '#eab308', text: 'Regular',     pct: '60%'  },
        { color: '#22c55e', text: 'Fuerte',      pct: '80%'  },
        { color: '#10b981', text: 'Muy fuerte',  pct: '100%' },
    ];

    if (val.length === 0) {
        fill.style.width = '0';
        label.textContent = '';
        return;
    }

    const lvl = levels[Math.min(score - 1, 4)] || levels[0];
    fill.style.width           = lvl.pct;
    fill.style.backgroundColor = lvl.color;
    label.textContent          = lvl.text;
    label.style.color          = lvl.color;
});

function setReq(el, ok) {
    if (!el) return;
    const icon = el.querySelector('i');
    if (ok) {
        el.classList.add('ok');
        icon.className = 'bi bi-check-circle-fill';
    } else {
        el.classList.remove('ok');
        icon.className = 'bi bi-x-circle';
    }
}
</script>
</body>
</html>
