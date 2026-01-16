<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-3">
    <div class="container">
        <span class="navbar-brand"><?php echo SITENAME; ?></span>
        
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarsExampleDefault" aria-controls="navbarsExampleDefault" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarsExampleDefault">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item">
                    <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/pages/index') !== false) ? 'active shadow-sm' : ''; ?>" href="<?php echo URLROOT; ?>/pages/index">Inicio</a>
                </li>
                <?php if(isLoggedIn()): ?>
                    <!-- Divisiones -->
                    <?php if(tieneAcceso('divisions', 'ver')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/divisions') !== false) ? 'active shadow-sm' : ''; ?>" href="<?php echo URLROOT; ?>/divisions/index">Divisiones</a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Personal -->
                    <?php if(tieneAcceso('personal', 'ver')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/personal') !== false) ? 'active shadow-sm' : ''; ?>" href="<?php echo URLROOT; ?>/personal/index">Personal</a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Contratos -->
                    <?php if(tieneAcceso('contratos', 'ver')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/contratos') !== false) ? 'active shadow-sm' : ''; ?>" href="<?php echo URLROOT; ?>/contratos/index">Contratos</a> 
                        </li>
                    <?php endif; ?>
                    
                    <!-- Usuarios -->
                    <?php if(tieneAcceso('usuarios', 'ver')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/usuarios') !== false) ? 'active shadow-sm' : ''; ?>" href="<?php echo URLROOT; ?>/usuarios/index">Usuarios</a> 
                        </li>
                    <?php endif; ?>
                    
                    <!-- Roles -->
                    <?php if(tieneAcceso('roles', 'ver')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/roles') !== false) ? 'active shadow-sm' : ''; ?>" href="<?php echo URLROOT; ?>/roles/index">Roles</a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Permisos -->
                    <?php if(tieneAcceso('permisos', 'ver')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/permisos') !== false) ? 'active shadow-sm' : ''; ?>" href="<?php echo URLROOT; ?>/permisos/index">Permisos</a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Alcances -->
                    <?php if(tieneAcceso('alcances', 'ver')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/alcances') !== false) ? 'active shadow-sm' : ''; ?>" href="<?php echo URLROOT; ?>/alcances/index">Alcances</a>
                        </li>
                    <?php endif; ?>
                    
                    <!-- Actividades -->
                    <?php if(tieneAcceso('actividades', 'ver')): ?>
                        <li class="nav-item">
                            <a class="nav-link <?php echo (strpos($_SERVER['REQUEST_URI'], '/actividades') !== false) ? 'active shadow-sm' : ''; ?>" href="<?php echo URLROOT; ?>/actividades/index">Actividades</a>
                        </li>
                    <?php endif; ?>
                <?php endif; ?>
            </ul>

            <ul class="navbar-nav ml-auto">
                <?php if(isLoggedIn()): ?>
                    <li class="nav-item">
                        <span class="nav-link text-white">
                            <i class="bi bi-person"></i> Bienvenido, <?php echo getUserDisplayName(); ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link btn btn-danger text-white ml-2" href="<?php echo URLROOT; ?>/users/logout" style="border-radius: 0.25rem; padding: 0.375rem 0.75rem;">
                            <i class="bi bi-box-arrow-right"></i> Cerrar Sesión
                        </a>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo URLROOT; ?>/users/login">Login</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>