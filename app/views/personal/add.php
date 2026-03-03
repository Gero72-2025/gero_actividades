<?php require APPROOT . '/views/layouts/header.php'; ?>
<div class="row">
    <div class="col-md-8 mx-auto">
        <div class="card card-body bg-light mt-5">
            <h2><?php echo $data['title']; ?></h2>
            <p>Complete el formulario para añadir un nuevo empleado.</p>
            <form action="<?php echo URLROOT; ?>/personal/add" method="post">
                
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="nombre">Nombre(s): <sup>*</sup></label>
                        <input type="text" name="nombre" class="form-control form-control-lg <?php echo (!empty($data['nombre_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['nombre']; ?>">
                        <span class="invalid-feedback"><?php echo $data['nombre_err']; ?></span>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="apellido">Apellido(s): <sup>*</sup></label>
                        <input type="text" name="apellido" class="form-control form-control-lg <?php echo (!empty($data['apellido_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['apellido']; ?>">
                        <span class="invalid-feedback"><?php echo $data['apellido_err']; ?></span>
                    </div>
                </div>

                <div class="form-group">
                    <label for="puesto">Puesto:</label>
                    <input type="text" name="puesto" class="form-control form-control-lg <?php echo (!empty($data['puesto_err'])) ? 'is-invalid' : ''; ?>" value="<?php echo $data['puesto']; ?>">
                    <span class="invalid-feedback"><?php echo $data['puesto_err']; ?></span>
                </div>

                <div class="form-group">
                    <label for="tipo_servicio">Tipo de Servicio: <sup>*</sup></label>
                    <select name="tipo_servicio" class="form-control form-control-lg <?php echo (!empty($data['tipo_servicio_err'])) ? 'is-invalid' : ''; ?>">
                        <option value="">Seleccione una opción</option>
                        <option value="1" <?php echo ($data['tipo_servicio'] === '1') ? 'selected' : ''; ?>>Servicios Profesionales</option>
                        <option value="0" <?php echo ($data['tipo_servicio'] === '0') ? 'selected' : ''; ?>>Servicios Técnicos</option>
                    </select>
                    <span class="invalid-feedback"><?php echo $data['tipo_servicio_err']; ?></span>
                </div>
                
                <div class="row">
                    <div class="form-group col-md-6">
                        <label for="id_division">División:</label>
                        <select name="id_division" class="form-control form-control-lg">
                            <option value="">Seleccione una división (Opcional)</option>
                            <?php 
                            // Asume que $data['divisiones'] tiene objetos con Id_Division y Nombre
                            if (isset($data['divisiones']) && is_array($data['divisiones'])) :
                                foreach($data['divisiones'] as $division): ?>
                                    <option 
                                        value="<?php echo $division->Id_Division; ?>" 
                                        <?php echo ($data['id_division'] == $division->Id_Division) ? 'selected' : ''; ?>
                                    >
                                        <?php echo $division->Nombre; ?>
                                    </option>
                                <?php endforeach; 
                            endif; ?>
                        </select>
                    </div>

                    <div class="form-group col-md-6">
                        <label for="id_contrato">Contrato:</label>
                        <select name="id_contrato" class="form-control form-control-lg">
                            <option value="">Seleccione un contrato (Opcional)</option>
                            <?php 
                            // Asume que $data['contratos'] tiene objetos con Id_contrato y Nombre_Contrato
                            if (isset($data['contratos']) && is_array($data['contratos'])) :
                                foreach($data['contratos'] as $contrato): ?>
                                    <option 
                                        value="<?php echo $contrato->Id_contrato; ?>" 
                                        <?php echo ($data['id_contrato'] == $contrato->Id_contrato) ? 'selected' : ''; ?>
                                    >
                                        <?php echo $contrato->Expediente; ?>
                                    </option>
                                <?php endforeach; 
                            endif; ?>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="id_usuario">ID de Usuario (Cuenta de Login): <sup>*</sup></label>
                    <div class="input-group">
                        <select name="id_usuario" id="id_usuario" class="form-control form-control-lg <?php echo (!empty($data['id_usuario_err'])) ? 'is-invalid' : ''; ?>">
                            <option value="">Seleccione un usuario</option>
                            <?php 
                            // Asume que $data['usuarios'] tiene objetos con Id_usuario y Email
                            if (isset($data['usuarios']) && is_array($data['usuarios'])) :
                                foreach($data['usuarios'] as $usuario): ?>
                                    <option 
                                        value="<?php echo $usuario->Id_usuario; ?>" 
                                        <?php echo ($data['id_usuario'] == $usuario->Id_usuario) ? 'selected' : ''; ?>
                                    >
                                        <?php echo $usuario->email; ?>
                                    </option>
                                <?php endforeach; 
                            endif; ?>
                        </select>
                        <button type="button" class="btn btn-outline-primary" data-toggle="modal" data-target="#modalNuevoUsuario" title="Crear nuevo usuario">
                            <i class="fas fa-plus"></i> Crear Usuario
                        </button>
                    </div>
                    <span class="invalid-feedback"><?php echo $data['id_usuario_err']; ?></span>
                    <small class="form-text text-muted">Solo se muestran usuarios no asignados.</small>
                </div>
                
                <div class="row mt-4">
                    <div class="col">
                        <input type="submit" value="Añadir Personal" class="btn btn-success btn-block">
                    </div>
                    <div class="col">
                         <a href="<?php echo URLROOT; ?>/personal/index" class="btn btn-light btn-block">Cancelar</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para crear nuevo usuario -->
<div class="modal fade" id="modalNuevoUsuario" tabindex="-1" role="dialog" aria-labelledby="modalNuevoUsuarioLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalNuevoUsuarioLabel">Crear Nuevo Usuario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formNuevoUsuario">
                    <div class="form-group">
                        <label for="modalEmail">Email: <sup>*</sup></label>
                        <input type="email" name="email" id="modalEmail" class="form-control" placeholder="usuario@ejemplo.com">
                        <div class="invalid-feedback" id="emailError"></div>
                    </div>
                    
                    <div class="row">
                        <div class="form-group col-md-6">
                            <label for="modalPass">Contraseña: <sup>*</sup></label>
                            <input type="password" name="pass" id="modalPass" class="form-control" placeholder="Mínimo 6 caracteres">
                            <div class="invalid-feedback" id="passError"></div>
                            <small class="form-text text-muted">Mínimo 6 caracteres.</small>
                        </div>

                        <div class="form-group col-md-6">
                            <label for="modalConfirmPass">Confirmar Contraseña: <sup>*</sup></label>
                            <input type="password" name="confirm_pass" id="modalConfirmPass" class="form-control" placeholder="Repetir contraseña">
                            <div class="invalid-feedback" id="confirmPassError"></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="modalRole">Rol:</label>
                        <select name="id_role" id="modalRole" class="form-control">
                            <option value="">-- Seleccionar Rol --</option>
                            <?php if(!empty($data['roles'])): ?>
                                <?php foreach($data['roles'] as $role): ?>
                                    <option value="<?php echo $role->Id_role; ?>">
                                        <?php echo $role->Nombre; ?>
                                    </option>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarUsuario">Guardar Usuario</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnGuardar = document.getElementById('btnGuardarUsuario');
    const formNuevoUsuario = document.getElementById('formNuevoUsuario');
    const selectUsuario = document.getElementById('id_usuario');

    // Guardar usuario desde el modal
    btnGuardar.addEventListener('click', function() {
        // Limpiar errores previos
        document.getElementById('emailError').textContent = '';
        document.getElementById('passError').textContent = '';
        document.getElementById('confirmPassError').textContent = '';
        
        // Remover clases de error
        document.getElementById('modalEmail').classList.remove('is-invalid');
        document.getElementById('modalPass').classList.remove('is-invalid');
        document.getElementById('modalConfirmPass').classList.remove('is-invalid');

        const email = document.getElementById('modalEmail').value.trim();
        const pass = document.getElementById('modalPass').value.trim();
        const confirmPass = document.getElementById('modalConfirmPass').value.trim();
        const id_role = document.getElementById('modalRole').value;

        // Validaciones del lado del cliente
        let hasErrors = false;

        if (!email) {
            document.getElementById('emailError').textContent = 'Por favor ingrese el correo electrónico.';
            document.getElementById('modalEmail').classList.add('is-invalid');
            hasErrors = true;
        }

        if (!pass) {
            document.getElementById('passError').textContent = 'Por favor ingrese una contraseña.';
            document.getElementById('modalPass').classList.add('is-invalid');
            hasErrors = true;
        } else if (pass.length < 6) {
            document.getElementById('passError').textContent = 'La contraseña debe tener al menos 6 caracteres.';
            document.getElementById('modalPass').classList.add('is-invalid');
            hasErrors = true;
        }

        if (pass !== confirmPass) {
            document.getElementById('confirmPassError').textContent = 'Las contraseñas no coinciden.';
            document.getElementById('modalConfirmPass').classList.add('is-invalid');
            hasErrors = true;
        }

        if (hasErrors) {
            return;
        }

        // Enviar petición AJAX
        fetch('<?php echo URLROOT; ?>/usuarios/addAjax', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: new URLSearchParams({
                email: email,
                pass: pass,
                confirm_pass: confirmPass,
                id_role: id_role
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Limpiar el formulario
                formNuevoUsuario.reset();

                // Agregar la nueva opción al select
                const newOption = document.createElement('option');
                newOption.value = data.usuario.Id_usuario;
                newOption.textContent = data.usuario.email;
                selectUsuario.appendChild(newOption);

                // Seleccionar la nueva opción
                selectUsuario.value = data.usuario.Id_usuario;

                // Cerrar el modal
                $('#modalNuevoUsuario').modal('hide');

                // Mostrar mensaje de éxito usando el sistema de alertas de la plataforma
                const successAlert = '<div class="alert alert-success alert-dismissible fade show" role="alert">' +
                    '<i class="bi bi-check-circle-fill mr-2"></i>' +
                    'Usuario creado exitosamente. Seleccionado en el campo de usuario.' +
                    '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                    '<span aria-hidden="true">&times;</span>' +
                    '</button>' +
                    '</div>';
                
                // Insertar el alerta antes del formulario
                $('.card-body').prepend(successAlert);
                
                // Auto-descartar después de 5 segundos (igual que los flash messages)
                setTimeout(function(){
                    $('.alert-success').alert('close');
                }, 5000);
            } else {
                // Mostrar errores del servidor
                if (data.email_err) {
                    document.getElementById('emailError').textContent = data.email_err;
                    document.getElementById('modalEmail').classList.add('is-invalid');
                }
                if (data.pass_err) {
                    document.getElementById('passError').textContent = data.pass_err;
                    document.getElementById('modalPass').classList.add('is-invalid');
                }
                if (data.confirm_pass_err) {
                    document.getElementById('confirmPassError').textContent = data.confirm_pass_err;
                    document.getElementById('modalConfirmPass').classList.add('is-invalid');
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            const errorAlert = '<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
                '<i class="bi bi-x-circle-fill mr-2"></i>' +
                'Error al crear el usuario. Intente nuevamente.' +
                '<button type="button" class="close" data-dismiss="alert" aria-label="Close">' +
                '<span aria-hidden="true">&times;</span>' +
                '</button>' +
                '</div>';
            
            $('.card-body').prepend(errorAlert);
            
            setTimeout(function(){
                $('.alert-danger').alert('close');
            }, 5000);
        });
    });
});
</script>

<?php require APPROOT . '/views/layouts/footer.php'; ?>